<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\OmiseTransaction;
use App\Services\OmiseService;
use App\Services\PlatformRevenueService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function __construct(private OmiseService $omise) {}

    /**
     * Public payment page — accessible by tenant via link
     */
    public function show(Invoice $invoice)
    {
        abort_if($invoice->status === 'paid', 410, 'ใบแจ้งหนี้นี้ชำระเงินแล้ว');

        $invoice->load(['rental.tenant', 'rental.room', 'items', 'property']);

        return view('payment.show', [
            'invoice'        => $invoice,
            'omisePublicKey' => config('services.omise.public_key'),
        ]);
    }

    /**
     * Create PromptPay QR charge
     */
    public function createPromptPay(Request $request, Invoice $invoice)
    {
        abort_if($invoice->status === 'paid', 410);

        try {
            $txn = $this->omise->createPromptPayCharge($invoice);

            return response()->json([
                'success'       => true,
                'authorize_uri' => $txn->authorize_uri,
                'charge_id'     => $txn->omise_charge_id,
            ]);
        } catch (\Throwable $e) {
            Log::error("PromptPay charge error: {$e->getMessage()}");
            return response()->json(['success' => false, 'message' => 'เกิดข้อผิดพลาด กรุณาลองใหม่'], 422);
        }
    }

    /**
     * Create credit card charge (token from Omise.js)
     */
    public function createCreditCard(Request $request, Invoice $invoice)
    {
        abort_if($invoice->status === 'paid', 410);

        $token = $request->validate(['token' => 'required|string'])['token'];

        try {
            $txn = $this->omise->createCreditCardCharge($invoice, $token);

            if ($txn->status === 'successful') {
                return response()->json(['success' => true, 'redirect' => route('payment.success', $invoice)]);
            }

            // 3DS redirect
            return response()->json([
                'success'       => true,
                'authorize_uri' => $txn->authorize_uri,
            ]);
        } catch (\Throwable $e) {
            Log::error("Credit card charge error: {$e->getMessage()}");
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * Callback after 3DS / PromptPay redirect
     */
    public function callback(Request $request, Invoice $invoice)
    {
        $invoice->refresh();

        if ($invoice->status === 'paid') {
            return redirect()->route('payment.success', $invoice);
        }

        return redirect()->route('payment.show', $invoice)->with('info', 'กำลังตรวจสอบการชำระเงิน...');
    }

    public function success(Invoice $invoice)
    {
        $invoice->load(['rental.tenant', 'rental.room', 'property']);
        return view('payment.success', compact('invoice'));
    }

    /**
     * Omise webhook — no auth required, verify via signature
     */
    public function webhook(Request $request)
    {
        $payload = $request->getContent();
        $event   = json_decode($payload, true);

        if (!$event || !isset($event['key'])) {
            return response()->json(['ok' => false], 400);
        }

        try {
            $this->omise->handleWebhook($event);
        } catch (\Throwable $e) {
            Log::error("Webhook error: {$e->getMessage()}");
            return response()->json(['ok' => false], 500);
        }

        return response()->json(['ok' => true]);
    }

    /**
     * Admin: mark invoice paid manually
     */
    public function markPaidManual(Request $request, Invoice $invoice)
    {
        $property = $request->get('current_property');
        abort_if($invoice->property_id !== $property->id, 403);
        abort_if($invoice->status === 'paid', 422, 'ชำระแล้ว');

        $validated = $request->validate([
            'payment_date'     => 'required|date',
            'payment_method'   => 'required|in:cash,transfer,other',
            'reference_number' => 'nullable|string|max:100',
            'note'             => 'nullable|string',
        ]);

        $payment = $invoice->payments()->create([
            'amount'           => $invoice->total_amount,
            'payment_date'     => $validated['payment_date'],
            'payment_method'   => $validated['payment_method'],
            'reference_number' => $validated['reference_number'] ?? null,
            'note'             => $validated['note'] ?? null,
        ]);

        $invoice->update([
            'status'  => 'paid',
            'paid_at' => $validated['payment_date'],
        ]);

        app(PlatformRevenueService::class)->recordManualPayment($invoice->fresh('property'), $payment);

        return back()->with('success', 'บันทึกการชำระเงินเรียบร้อย');
    }
}
