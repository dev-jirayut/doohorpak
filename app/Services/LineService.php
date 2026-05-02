<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\LineConversation;
use App\Models\LineMessage;
use App\Models\MaintenanceRequest;
use App\Models\Property;
use App\Models\Rental;
use App\Models\Tenant;
use GuzzleHttp\Client;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class LineService
{
    private Client $http;

    public function __construct()
    {
        $this->http = new Client(['timeout' => 10]);
    }

    // ─── LINE Messaging API notifications to owner/admin recipients ─────────

    public function notifyOwner(Property $property, string $message): void
    {
        $setting = $property->lineSetting;
        $token = $setting?->oa_channel_access_token;
        if (!$token) return;

        $lineUserIds = collect($setting->admin_line_user_ids ?? [])
            ->merge([$property->owner?->line_user_id])
            ->merge($property->users()->pluck('line_user_id'))
            ->filter()
            ->unique()
            ->values();

        if ($lineUserIds->isEmpty()) {
            Log::info("LINE OA owner notification skipped: no admin LINE user IDs for property {$property->id}");
            return;
        }

        foreach ($lineUserIds as $lineUserId) {
            $this->pushTextMessage($lineUserId, trim($message), $property);
        }
    }

    // ─── LINE OA Push (Messaging API) ────────────────────────────────────────

    public function pushTextMessage(string $lineUserId, string $text, Property $property): bool
    {
        $token = $property->lineSetting?->oa_channel_access_token;
        if (!$token || !$lineUserId || !$text) return false;

        try {
            $this->http->post('https://api.line.me/v2/bot/message/push', [
                'headers' => [
                    'Authorization' => "Bearer {$token}",
                    'Content-Type'  => 'application/json',
                ],
                'json' => [
                    'to'       => $lineUserId,
                    'messages' => [
                        ['type' => 'text', 'text' => $text],
                    ],
                ],
            ]);

            return true;
        } catch (\Throwable $e) {
            Log::error("LINE OA text push error: {$e->getMessage()}");
            return false;
        }
    }

    public function broadcastTextToTenants(Property $property, string $text, int $senderUserId): array
    {
        $token = $property->lineSetting?->oa_channel_access_token;
        if (!$token) {
            throw new \RuntimeException('ยังไม่ได้ตั้งค่า LINE OA Channel Access Token');
        }

        $tenants = Tenant::with('activeRental.room')
            ->where('property_id', $property->id)
            ->whereNotNull('line_user_id')
            ->where('line_user_id', '!=', '')
            ->get()
            ->unique('line_user_id')
            ->values();

        $sent = 0;
        $failed = 0;

        foreach ($tenants as $tenant) {
            try {
                if (!$this->pushTextMessage($tenant->line_user_id, $text, $property)) {
                    $failed++;
                    continue;
                }

                $conversation = LineConversation::firstOrCreate(
                    ['property_id' => $property->id, 'line_user_id' => $tenant->line_user_id],
                    ['tenant_id' => $tenant->id]
                );

                $conversation->update([
                    'tenant_id' => $conversation->tenant_id ?: $tenant->id,
                    'last_message_at' => now(),
                ]);

                LineMessage::create([
                    'conversation_id' => $conversation->id,
                    'direction' => 'outbound',
                    'type' => 'text',
                    'content' => $text,
                    'metadata' => ['broadcast' => true],
                    'sent_by_user_id' => $senderUserId,
                ]);

                $sent++;
            } catch (\Throwable $e) {
                Log::error("LINE OA broadcast error for tenant {$tenant->id}: {$e->getMessage()}");
                $failed++;
            }
        }

        return [
            'total' => $tenants->count(),
            'sent' => $sent,
            'failed' => $failed,
        ];
    }

    public function pushFlexMessage(string $lineUserId, string $altText, array $flexContent, Property $property): void
    {
        $token = $property->lineSetting?->oa_channel_access_token;
        if (!$token || !$lineUserId) return;

        try {
            $this->http->post('https://api.line.me/v2/bot/message/push', [
                'headers' => [
                    'Authorization' => "Bearer {$token}",
                    'Content-Type'  => 'application/json',
                ],
                'json' => [
                    'to'       => $lineUserId,
                    'messages' => [
                        ['type' => 'flex', 'altText' => $altText, 'contents' => $flexContent],
                    ],
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error("LINE OA Flex error: {$e->getMessage()}");
        }
    }

    public function createRichMenu(Property $property, UploadedFile $image, array $actions): string
    {
        $token = $property->lineSetting?->oa_channel_access_token;
        if (!$token) {
            throw new \RuntimeException('ยังไม่ได้ตั้งค่า LINE OA Channel Access Token');
        }

        $richMenu = [
            'size' => ['width' => 2500, 'height' => 1686],
            'selected' => true,
            'name' => mb_substr($actions['name'] ?? "Dormitory menu {$property->id}", 0, 300),
            'chatBarText' => mb_substr($actions['chat_bar_text'] ?? 'หอพักของคุณ', 0, 14),
            'areas' => [
                $this->richMenuArea(0, 760, 625, 780, $actions['invoice'] ?? 'ตรวจสอบใบแจ้งหนี้'),
                $this->richMenuArea(625, 760, 625, 780, $actions['parcel'] ?? 'นัดรับพัสดุ'),
                $this->richMenuArea(1250, 760, 625, 780, $actions['maintenance'] ?? 'แจ้งซ่อม'),
                $this->richMenuArea(1875, 760, 625, 780, $actions['contract'] ?? 'ดูสัญญาหอ'),
                $this->richMenuArea(1940, 1540, 500, 130, $actions['contact'] ?? 'ติดต่อหอพัก'),
            ],
        ];

        $response = $this->http->post('https://api.line.me/v2/bot/richmenu', [
            'headers' => [
                'Authorization' => "Bearer {$token}",
                'Content-Type' => 'application/json',
            ],
            'json' => $richMenu,
        ]);

        $richMenuId = json_decode($response->getBody()->getContents(), true)['richMenuId'] ?? null;
        if (!$richMenuId) {
            throw new \RuntimeException('สร้าง rich menu ไม่สำเร็จ');
        }

        $this->http->post("https://api-data.line.me/v2/bot/richmenu/{$richMenuId}/content", [
            'headers' => [
                'Authorization' => "Bearer {$token}",
                'Content-Type' => $image->getMimeType() ?: 'image/png',
            ],
            'body' => fopen($image->getRealPath(), 'rb'),
        ]);

        $this->http->post("https://api.line.me/v2/bot/user/all/richmenu/{$richMenuId}", [
            'headers' => ['Authorization' => "Bearer {$token}"],
        ]);

        return $richMenuId;
    }

    private function richMenuArea(int $x, int $y, int $width, int $height, string $text): array
    {
        return [
            'bounds' => compact('x', 'y', 'width', 'height'),
            'action' => [
                'type' => 'message',
                'text' => mb_substr($text, 0, 300),
            ],
        ];
    }

    // ─── Invoice Flex Message ─────────────────────────────────────────────────

    public function sendInvoiceFlex(Invoice $invoice): void
    {
        $rental   = $invoice->rental;
        $tenant   = $rental?->tenant;
        $lineId   = $tenant?->line_user_id ?? $tenant?->user?->line_user_id;
        $property = $invoice->property;

        if (!$property->lineSetting?->notify_on_invoice) return;

        if ($lineId) {
            $flex = $this->buildInvoiceFlexContent($invoice);
            $this->pushFlexMessage($lineId, "ใบแจ้งหนี้เดือน {$invoice->month}/{$invoice->year}", $flex, $property);
        }

        // Also notify owner/admin via LINE Messaging API
        $total = number_format($invoice->total_amount, 2);
        $room  = $rental?->room?->room_number ?? '-';
        $this->notifyOwner($property, "\n📋 ออกใบแจ้งหนี้ใหม่\nห้อง: {$room}\nผู้เช่า: {$tenant?->name}\nยอด: ฿{$total}");
    }

    public function sendPaymentConfirmation(Invoice $invoice): void
    {
        $rental   = $invoice->rental;
        $property = $invoice->property;
        $tenant   = $rental?->tenant;
        $total    = number_format($invoice->total_amount, 2);
        $room     = $rental?->room?->room_number ?? '-';

        // Notify owner/admin
        $this->notifyOwner($property, "\n✅ ได้รับการชำระเงินแล้ว\nห้อง: {$room}\nผู้เช่า: {$tenant?->name}\nยอด: ฿{$total}");

        // Flex to tenant
        $lineId = $tenant?->line_user_id ?? $tenant?->user?->line_user_id;
        if ($lineId) {
            $flex = $this->buildPaymentConfirmFlexContent($invoice);
            $this->pushFlexMessage($lineId, 'ยืนยันการชำระเงินสำเร็จ', $flex, $property);
        }
    }

    public function sendOverdueReminder(Invoice $invoice): void
    {
        $rental   = $invoice->rental;
        $property = $invoice->property;

        if (!$property->lineSetting?->notify_on_overdue) return;

        $tenant = $rental?->tenant;
        $lineId = $tenant?->line_user_id ?? $tenant?->user?->line_user_id;
        $daysOverdue = now()->diffInDays($invoice->due_date);

        if ($lineId) {
            $flex = $this->buildOverdueFlexContent($invoice, $daysOverdue);
            $this->pushFlexMessage($lineId, "⚠️ ใบแจ้งหนี้เกินกำหนดชำระ {$daysOverdue} วัน", $flex, $property);
        }
    }

    public function sendMaintenanceUpdate(MaintenanceRequest $request): void
    {
        $property = $request->property;
        if (!$property->lineSetting?->notify_on_maintenance) return;

        $lineId = $request->tenant?->line_user_id ?? $request->tenant?->user?->line_user_id;
        if ($lineId) {
            $flex = $this->buildMaintenanceFlexContent($request);
            $this->pushFlexMessage($lineId, "อัปเดตคำขอซ่อม: {$request->title}", $flex, $property);
        }

        // Notify owner/admin too
        $this->notifyOwner($property, "\n🔧 แจ้งซ่อมใหม่\nห้อง: {$request->room?->room_number}\nเรื่อง: {$request->title}\nสถานะ: {$request->status_label}");
    }

    // ─── Flex Content Builders ────────────────────────────────────────────────

    private function buildInvoiceFlexContent(Invoice $invoice): array
    {
        $rental  = $invoice->rental;
        $room    = $rental?->room?->room_number ?? '-';
        $total   = number_format($invoice->total_amount, 2);
        $due     = $invoice->due_date?->format('d/m/Y') ?? '-';
        $payUrl  = route('payment.show', $invoice->id);

        return [
            'type' => 'bubble',
            'size' => 'mega',
            'header' => [
                'type'            => 'box',
                'layout'          => 'vertical',
                'backgroundColor' => '#00A884',
                'contents'        => [
                    ['type' => 'text', 'text' => '📄 ใบแจ้งหนี้ประจำเดือน', 'color' => '#FFFFFF', 'size' => 'md', 'weight' => 'bold'],
                    ['type' => 'text', 'text' => "{$invoice->month}/{$invoice->year}", 'color' => '#A1FFD1', 'size' => 'sm'],
                ],
            ],
            'body' => [
                'type'   => 'box',
                'layout' => 'vertical',
                'spacing' => 'md',
                'contents' => [
                    $this->flexRow('ห้อง', $room),
                    $this->flexRow('ค่าเช่า', '฿' . number_format($invoice->room_charge, 2)),
                    $this->flexRow('ค่าไฟ', '฿' . number_format($invoice->electricity_charge, 2)),
                    $this->flexRow('ค่าน้ำ', '฿' . number_format($invoice->water_charge, 2)),
                    ['type' => 'separator'],
                    $this->flexRow('รวมทั้งสิ้น', "฿{$total}", '#00A884', 'xl', 'bold'),
                    $this->flexRow('ครบกำหนด', $due, '#cc0000'),
                ],
            ],
            'footer' => [
                'type'   => 'box',
                'layout' => 'vertical',
                'contents' => [
                    [
                        'type'            => 'button',
                        'action'          => ['type' => 'uri', 'label' => '💳 ชำระเงินตอนนี้', 'uri' => $payUrl],
                        'style'           => 'primary',
                        'color'           => '#00A884',
                    ],
                ],
            ],
        ];
    }

    private function buildPaymentConfirmFlexContent(Invoice $invoice): array
    {
        $total = number_format($invoice->total_amount, 2);

        return [
            'type' => 'bubble',
            'header' => [
                'type'            => 'box',
                'layout'          => 'vertical',
                'backgroundColor' => '#00A884',
                'contents' => [
                    ['type' => 'text', 'text' => '✅ ชำระเงินสำเร็จ', 'color' => '#FFFFFF', 'size' => 'lg', 'weight' => 'bold'],
                ],
            ],
            'body' => [
                'type'   => 'box',
                'layout' => 'vertical',
                'spacing' => 'md',
                'contents' => [
                    $this->flexRow('เลขที่ใบแจ้งหนี้', $invoice->invoice_number),
                    $this->flexRow('ยอดที่ชำระ', "฿{$total}", '#00A884', 'lg', 'bold'),
                    $this->flexRow('วันที่ชำระ', now()->format('d/m/Y H:i')),
                ],
            ],
        ];
    }

    private function buildOverdueFlexContent(Invoice $invoice, int $days): array
    {
        $total  = number_format($invoice->total_amount, 2);
        $payUrl = route('payment.show', $invoice->id);

        return [
            'type' => 'bubble',
            'header' => [
                'type'            => 'box',
                'layout'          => 'vertical',
                'backgroundColor' => '#cc4444',
                'contents' => [
                    ['type' => 'text', 'text' => '⚠️ ค้างชำระ', 'color' => '#FFFFFF', 'size' => 'lg', 'weight' => 'bold'],
                    ['type' => 'text', 'text' => "เกินกำหนด {$days} วัน", 'color' => '#ffcccc', 'size' => 'sm'],
                ],
            ],
            'body' => [
                'type'   => 'box',
                'layout' => 'vertical',
                'spacing' => 'md',
                'contents' => [
                    $this->flexRow('ใบแจ้งหนี้', $invoice->invoice_number),
                    $this->flexRow('ยอดค้างชำระ', "฿{$total}", '#cc0000', 'xl', 'bold'),
                ],
            ],
            'footer' => [
                'type'   => 'box',
                'layout' => 'vertical',
                'contents' => [
                    [
                        'type'   => 'button',
                        'action' => ['type' => 'uri', 'label' => '💳 ชำระเงินด่วน', 'uri' => $payUrl],
                        'style'  => 'primary',
                        'color'  => '#cc4444',
                    ],
                ],
            ],
        ];
    }

    private function buildMaintenanceFlexContent(MaintenanceRequest $request): array
    {
        $statusColor = match ($request->status) {
            'done'        => '#00A884',
            'in_progress' => '#f5a623',
            'cancelled'   => '#888888',
            default       => '#002C2C',
        };

        return [
            'type' => 'bubble',
            'header' => [
                'type'            => 'box',
                'layout'          => 'vertical',
                'backgroundColor' => '#002C2C',
                'contents' => [
                    ['type' => 'text', 'text' => '🔧 อัปเดตคำขอซ่อม', 'color' => '#A1FFD1', 'size' => 'md', 'weight' => 'bold'],
                ],
            ],
            'body' => [
                'type'   => 'box',
                'layout' => 'vertical',
                'spacing' => 'md',
                'contents' => [
                    $this->flexRow('เรื่อง', $request->title),
                    $this->flexRow('ห้อง', $request->room?->room_number ?? '-'),
                    $this->flexRow('สถานะ', $request->status_label, $statusColor, 'md', 'bold'),
                    $request->technician_note ? $this->flexRow('หมายเหตุ', $request->technician_note) : null,
                ],
            ],
        ];
    }

    private function flexRow(string $label, string $value, string $color = '#002C2C', string $size = 'sm', string $weight = 'regular'): array
    {
        return [
            'type'   => 'box',
            'layout' => 'horizontal',
            'contents' => [
                ['type' => 'text', 'text' => $label, 'color' => '#888888', 'size' => 'sm', 'flex' => 3],
                ['type' => 'text', 'text' => $value, 'color' => $color, 'size' => $size, 'weight' => $weight, 'flex' => 5, 'align' => 'end'],
            ],
        ];
    }

    // ─── LINE OA Chat (Webhook + Reply) ──────────────────────────────────────

    /**
     * Process a raw LINE Messaging API webhook payload for a given property.
     * Stores inbound messages and returns the conversation records.
     */
    public function handleOaWebhook(array $body, Property $property): void
    {
        $accessToken = $property->lineSetting?->oa_channel_access_token;
        if (!$accessToken) return;

        foreach ($body['events'] ?? [] as $event) {
            if ($event['type'] !== 'message') continue;
            if (($event['source']['type'] ?? '') !== 'user') continue;

            $lineUserId = $event['source']['userId'];
            $msgEvent   = $event['message'];

            $conversation = $this->findOrCreateConversation($property, $lineUserId, $accessToken);

            // Dedup by LINE message id
            if (LineMessage::where('line_message_id', $msgEvent['id'])->exists()) continue;

            $type    = $msgEvent['type'];
            $content = $type === 'text' ? $msgEvent['text'] : null;
            $meta    = $type !== 'text' ? $msgEvent : null;

            LineMessage::create([
                'conversation_id' => $conversation->id,
                'line_message_id' => $msgEvent['id'],
                'direction'       => 'inbound',
                'type'            => $type,
                'content'         => $content,
                'metadata'        => $meta,
            ]);

            $conversation->update([
                'last_message_at' => now(),
                'has_unread'      => true,
            ]);
        }
    }

    /** Send a text reply to a LINE OA conversation and store it as outbound. */
    public function replyText(LineConversation $conversation, string $text, int $senderUserId): void
    {
        $token = $conversation->property->lineSetting?->oa_channel_access_token;
        if (!$token) return;

        try {
            $this->http->post('https://api.line.me/v2/bot/message/push', [
                'headers' => [
                    'Authorization' => "Bearer {$token}",
                    'Content-Type'  => 'application/json',
                ],
                'json' => [
                    'to'       => $conversation->line_user_id,
                    'messages' => [['type' => 'text', 'text' => $text]],
                ],
            ]);

            LineMessage::create([
                'conversation_id' => $conversation->id,
                'direction'       => 'outbound',
                'type'            => 'text',
                'content'         => $text,
                'sent_by_user_id' => $senderUserId,
            ]);

            $conversation->update(['last_message_at' => now()]);
        } catch (\Throwable $e) {
            Log::error("LINE OA reply error: {$e->getMessage()}");
        }
    }

    private function findOrCreateConversation(Property $property, string $lineUserId, string $accessToken): LineConversation
    {
        $conv = LineConversation::firstOrCreate(
            ['property_id' => $property->id, 'line_user_id' => $lineUserId],
        );

        // Fetch LINE profile if missing display name
        if (!$conv->display_name) {
            try {
                $res = $this->http->get("https://api.line.me/v2/bot/profile/{$lineUserId}", [
                    'headers' => ['Authorization' => "Bearer {$accessToken}"],
                ]);
                $profile = json_decode($res->getBody(), true);
                $conv->update([
                    'display_name' => $profile['displayName'] ?? null,
                    'picture_url'  => $profile['pictureUrl']  ?? null,
                ]);
            } catch (\Throwable) {}

            // Auto-link tenant by line_user_id
            if (!$conv->tenant_id) {
                $tenant = Tenant::where('property_id', $property->id)
                    ->where('line_user_id', $lineUserId)
                    ->first();
                if ($tenant) {
                    $conv->update(['tenant_id' => $tenant->id]);
                }
            }
        }

        return $conv->fresh();
    }
}
