<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\OmiseTransaction;
use Illuminate\Support\Facades\Log;

class OmiseService
{
    public function __construct()
    {
        \OmiseCharge::setApiKey(
            config('services.omise.secret_key'),
            config('services.omise.public_key')
        );
    }

    /**
     * Create PromptPay QR charge — returns authorize_uri (QR image URL)
     */
    public function createPromptPayCharge(Invoice $invoice, string $description = ''): OmiseTransaction
    {
        $amountSatang = (int) round($invoice->total_amount * 100);

        $source = \OmiseSource::create([
            'type'   => 'promptpay',
            'amount' => $amountSatang,
        ]);

        $charge = \OmiseCharge::create([
            'amount'      => $amountSatang,
            'currency'    => 'thb',
            'source'      => $source['id'],
            'description' => $description ?: "ใบแจ้งหนี้ #{$invoice->invoice_number}",
            'metadata'    => [
                'invoice_id'  => $invoice->id,
                'property_id' => $invoice->property_id,
            ],
            'return_uri' => route('payment.callback', ['invoice' => $invoice->id]),
        ]);

        return OmiseTransaction::create([
            'invoice_id'      => $invoice->id,
            'property_id'     => $invoice->property_id,
            'omise_charge_id' => $charge['id'],
            'omise_source_id' => $source['id'],
            'payment_method'  => 'promptpay',
            'amount'          => $amountSatang,
            'currency'        => 'thb',
            'status'          => 'pending',
            'authorize_uri'   => $charge['authorize_uri'] ?? null,
            'metadata'        => $charge->toArray(),
        ]);
    }

    /**
     * Create credit card charge using a token from Omise.js
     */
    public function createCreditCardCharge(Invoice $invoice, string $cardToken): OmiseTransaction
    {
        $amountSatang = (int) round($invoice->total_amount * 100);

        $charge = \OmiseCharge::create([
            'amount'      => $amountSatang,
            'currency'    => 'thb',
            'card'        => $cardToken,
            'description' => "ใบแจ้งหนี้ #{$invoice->invoice_number}",
            'metadata'    => [
                'invoice_id'  => $invoice->id,
                'property_id' => $invoice->property_id,
            ],
            'return_uri' => route('payment.callback', ['invoice' => $invoice->id]),
        ]);

        $txn = OmiseTransaction::create([
            'invoice_id'      => $invoice->id,
            'property_id'     => $invoice->property_id,
            'omise_charge_id' => $charge['id'],
            'payment_method'  => 'credit_card',
            'amount'          => $amountSatang,
            'currency'        => 'thb',
            'status'          => $charge['status'] === 'successful' ? 'successful' : 'pending',
            'authorize_uri'   => $charge['authorize_uri'] ?? null,
            'metadata'        => $charge->toArray(),
            'charged_at'      => $charge['status'] === 'successful' ? now() : null,
        ]);

        if ($charge['status'] === 'successful') {
            $this->handleSuccessfulCharge($txn);
        }

        return $txn;
    }

    /**
     * Handle Omise webhook event
     */
    public function handleWebhook(array $event): void
    {
        if ($event['key'] !== 'charge.complete') return;

        $charge   = $event['data'];
        $chargeId = $charge['id'];

        $txn = OmiseTransaction::where('omise_charge_id', $chargeId)->first();
        if (!$txn) {
            Log::warning("Omise webhook: unknown charge {$chargeId}");
            return;
        }

        if ($charge['status'] === 'successful') {
            $txn->update([
                'status'     => 'successful',
                'charged_at' => now(),
            ]);
            $this->handleSuccessfulCharge($txn);
        } elseif ($charge['status'] === 'failed') {
            $txn->update([
                'status'          => 'failed',
                'failure_code'    => $charge['failure_code'] ?? null,
                'failure_message' => $charge['failure_message'] ?? null,
            ]);
        }
    }

    /**
     * Mark invoice paid and record platform revenue
     */
    private function handleSuccessfulCharge(OmiseTransaction $txn): void
    {
        $invoice = $txn->invoice;
        if (!$invoice || $invoice->status === 'paid') return;

        $invoice->update([
            'status'  => 'paid',
            'paid_at' => now(),
        ]);

        app(PlatformRevenueService::class)->recordOnlinePayment($txn);

        // Notify LINE
        app(LineService::class)->sendPaymentConfirmation($invoice);
    }

}
