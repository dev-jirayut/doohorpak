<?php

namespace App\Http\Controllers;

use App\Models\LineConversation;
use App\Models\LineSetting;
use App\Models\LineWebhookLog;
use App\Models\Property;
use App\Models\Tenant;
use App\Services\LineService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LineChatController extends Controller
{
    public function __construct(private LineService $line) {}

    /** Inbox: list all conversations for the current property. */
    public function index(Request $request)
    {
        $property = $request->get('current_property');

        $conversations = LineConversation::with(['tenant.activeRental.room', 'latestMessage'])
            ->when($property, fn ($q) => $q->where('property_id', $property->id))
            ->orderByDesc('last_message_at')
            ->paginate(20);

        return view('line-chat.index', compact('conversations', 'property'));
    }

    /** Show a single conversation thread and mark as read. */
    public function show(Request $request, LineConversation $conversation)
    {
        $property = $request->get('current_property');
        $this->authorizeConversation($conversation, $property);

        $conversation->update(['has_unread' => false]);

        $messages = $conversation->messages()->with('sentBy')->get();
        $allTenants = Tenant::with('activeRental.room')
            ->where('property_id', $conversation->property_id)
            ->orderBy('name')
            ->get();

        return view('line-chat.show', compact('conversation', 'messages', 'allTenants'));
    }

    /** Send a reply text message. */
    public function reply(Request $request, LineConversation $conversation)
    {
        $property = $request->get('current_property');
        $this->authorizeConversation($conversation, $property);

        $request->validate(['message' => 'required|string|max:2000']);

        $this->line->replyText($conversation, $request->message, auth()->id());

        return back()->with('success', 'ส่งข้อความแล้ว');
    }

    /** Update conversation label (chat_name) and optional tenant link. */
    public function updateLabel(Request $request, LineConversation $conversation)
    {
        $property = $request->get('current_property');
        $this->authorizeConversation($conversation, $property);

        $request->validate([
            'chat_name' => 'nullable|string|max:100',
            'tenant_id' => 'nullable|exists:tenants,id',
        ]);

        $tenantId = $request->tenant_id;
        $chatName = $request->chat_name;
        $previousTenantId = $conversation->tenant_id;

        // Auto-generate name from tenant if not manually set
        if ($tenantId && !$chatName) {
            $tenant = Tenant::find($tenantId);
            abort_if($tenant->property_id && $tenant->property_id !== $conversation->property_id, 403);

            $room   = $tenant->activeRental?->room?->room_number;
            $chatName = $room ? "{$tenant->name} - ห้อง {$room}" : $tenant->name;
        }

        if ($tenantId) {
            Tenant::where('property_id', $conversation->property_id)
                ->where('line_user_id', $conversation->line_user_id)
                ->where('id', '!=', $tenantId)
                ->update(['line_user_id' => null]);

            Tenant::whereKey($tenantId)->update([
                'line_user_id' => $conversation->line_user_id,
            ]);
        } elseif ($previousTenantId) {
            Tenant::whereKey($previousTenantId)
                ->where('line_user_id', $conversation->line_user_id)
                ->update(['line_user_id' => null]);
        }

        $conversation->update([
            'tenant_id' => $tenantId,
            'chat_name' => $chatName,
        ]);

        return back()->with('success', 'อัปเดตข้อมูลแล้ว');
    }

    public function broadcast(Request $request)
    {
        $property = $request->get('current_property');

        if (!$property) {
            return redirect()->route('line-chat.index')->with('error', 'กรุณาเลือกหอพักก่อนส่งข้อความถึงผู้เช่าทั้งหอ');
        }

        $tenantsWithLine = Tenant::with('activeRental.room')
            ->where('property_id', $property->id)
            ->whereNotNull('line_user_id')
            ->where('line_user_id', '!=', '')
            ->orderBy('name')
            ->get();

        $setting = LineSetting::firstOrNew(['property_id' => $property->id]);

        return view('line-chat.broadcast', compact('property', 'tenantsWithLine', 'setting'));
    }

    public function sendBroadcast(Request $request)
    {
        $property = $request->get('current_property');

        if (!$property) {
            return redirect()->route('line-chat.index')->with('error', 'กรุณาเลือกหอพักก่อนส่งข้อความถึงผู้เช่าทั้งหอ');
        }

        $data = $request->validate([
            'message' => 'required|string|max:2000',
            'confirm_send' => 'accepted',
        ], [
            'confirm_send.accepted' => 'กรุณายืนยันก่อนส่งข้อความถึงทุกคน',
        ]);

        try {
            $result = $this->line->broadcastTextToTenants($property, $data['message'], auth()->id());
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', 'ส่งข้อความไม่สำเร็จ: ' . $e->getMessage());
        }

        return redirect()->route('line-chat.index')
            ->with('success', "ส่งข้อความถึงผู้เช่าแล้ว {$result['sent']} คน" . ($result['failed'] ? " ส่งไม่สำเร็จ {$result['failed']} คน" : ''));
    }

    /**
     * LINE Messaging API webhook — receives inbound messages from LINE users.
     * Must be CSRF-exempt; registered separately in routes/web.php.
     */
    public function webhook(Request $request, Property $property)
    {
        $payload = $request->json()->all();
        Log::info('LINE webhook received', [
            'property_id' => $property->id,
            'event_count' => count($payload['events'] ?? []),
            'event_types' => collect($payload['events'] ?? [])->pluck('type')->filter()->unique()->values()->all(),
            'line_user_id' => collect($payload['events'] ?? [])->pluck('source.userId')->filter()->first(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
        ]);

        $log = $this->storeLineWebhookLog($request, $property, $payload, [
            'event_type' => collect($payload['events'] ?? [])->pluck('type')->filter()->unique()->implode(',') ?: null,
            'webhook_event_id' => collect($payload['events'] ?? [])->pluck('webhookEventId')->filter()->first(),
            'line_user_id' => collect($payload['events'] ?? [])->pluck('source.userId')->filter()->first(),
        ]);

        // Verify LINE signature
        $setting = LineSetting::where('property_id', $property->id)->first();
        if (!$setting?->oa_channel_secret) {
            $this->finishWebhookLog($log, 'skipped', 200, 'LINE channel secret is not configured.');
            return response('', 200);
        }

        $signature = $request->header('X-Line-Signature', '');
        $body      = $request->getContent();
        $expected  = base64_encode(hash_hmac('sha256', $body, $setting->oa_channel_secret, true));

        if (!hash_equals($expected, $signature)) {
            $this->finishWebhookLog($log, 'invalid_signature', 400, 'Invalid LINE signature.', null, false);
            return response('Invalid signature', 400);
        }

        $this->markLineSignatureValid($log, true);

        try {
            $this->line->handleOaWebhook($payload, $property);
        } catch (\Throwable $e) {
            $this->finishWebhookLog($log, 'failed', 500, $e->getMessage());
            throw $e;
        }

        $eventCount = count($payload['events'] ?? []);
        $this->finishWebhookLog($log, 'processed', 200, "Processed {$eventCount} LINE event(s).");
        return response('', 200);
    }

    private function authorizeConversation(LineConversation $conversation, ?Property $property): void
    {
        abort_if($property && $conversation->property_id !== $property->id, 403);
        abort_if(!$property && !request()->user()?->isSuperAdmin(), 403);
    }

    private function storeLineWebhookLog(Request $request, ?Property $property, array $payload, array $extra = []): ?LineWebhookLog
    {
        try {
            return LineWebhookLog::create(array_merge([
                'property_id' => $property?->id,
                'status' => 'received',
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'signature' => $request->header('X-Line-Signature'),
                'headers' => $this->safeWebhookHeaders($request),
                'payload' => $payload,
            ], $extra));
        } catch (\Throwable $e) {
            Log::warning("LINE webhook DB log write failed: {$e->getMessage()}", [
                'table' => 'line_webhook_log',
                'property_id' => $property?->id,
            ]);
            return null;
        }
    }

    private function finishWebhookLog(?LineWebhookLog $log, string $status, int $responseStatus, ?string $message = null, ?array $response = null, ?bool $signatureValid = null): void
    {
        if (!$log) return;

        try {
            $updates = [
                'status' => $status,
                'response_status' => $responseStatus,
                'message' => $message,
                'response' => $response,
                'processed_at' => now(),
            ];

            if ($signatureValid !== null) {
                $updates['signature_valid'] = $signatureValid;
            }

            $log->update($updates);
        } catch (\Throwable $e) {
            Log::warning("Webhook log update failed: {$e->getMessage()}");
        }
    }

    private function markLineSignatureValid(?LineWebhookLog $log, bool $valid): void
    {
        if (!$log) return;

        try {
            $log->update(['signature_valid' => $valid]);
        } catch (\Throwable $e) {
            Log::warning("LINE webhook signature log update failed: {$e->getMessage()}");
        }
    }

    private function safeWebhookHeaders(Request $request): array
    {
        return collect($request->headers->all())
            ->only(['content-type', 'user-agent', 'x-line-signature'])
            ->map(fn ($value) => is_array($value) ? implode(', ', $value) : $value)
            ->all();
    }
}
