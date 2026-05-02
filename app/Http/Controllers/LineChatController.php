<?php

namespace App\Http\Controllers;

use App\Models\LineConversation;
use App\Models\LineSetting;
use App\Models\Property;
use App\Models\Tenant;
use App\Services\LineService;
use Illuminate\Http\Request;

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
        $tenants  = Tenant::when($property, fn ($q) => $q->where('property_id', $property->id))
            ->whereNull('line_user_id')
            ->orWhereNull('id') // just to get all for linking dropdown
            ->get();
        $allTenants = Tenant::when($property, fn ($q) => $q->where('property_id', $property->id))->get();

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

        // Auto-generate name from tenant if not manually set
        if ($tenantId && !$chatName) {
            $tenant = Tenant::find($tenantId);
            $room   = $tenant->activeRental?->room?->room_number;
            $chatName = $room ? "{$tenant->name} - ห้อง {$room}" : $tenant->name;
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
        // Verify LINE signature
        $setting = LineSetting::where('property_id', $property->id)->first();
        if (!$setting?->oa_channel_secret) return response('', 200);

        $signature = $request->header('X-Line-Signature', '');
        $body      = $request->getContent();
        $expected  = base64_encode(hash_hmac('sha256', $body, $setting->oa_channel_secret, true));

        if (!hash_equals($expected, $signature)) {
            return response('Invalid signature', 400);
        }

        $this->line->handleOaWebhook($request->json()->all(), $property);

        return response('', 200);
    }

    private function authorizeConversation(LineConversation $conversation, ?Property $property): void
    {
        abort_if($property && $conversation->property_id !== $property->id, 403);
        abort_if(!$property && !request()->user()?->isSuperAdmin(), 403);
    }
}
