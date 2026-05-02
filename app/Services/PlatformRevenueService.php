<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\OmiseTransaction;
use App\Models\Payment;
use App\Models\PlatformRevenue;
use App\Models\Property;

class PlatformRevenueService
{
    public function recordOnlinePayment(OmiseTransaction $txn): void
    {
        $property = Property::find($txn->property_id);
        if (!$property) {
            return;
        }

        $invoice = $txn->invoice;
        $grossAmount = $txn->amount / 100;

        if ($property->revenue_model === 'package') {
            $this->ensureMonthlyPackageBill($property, $invoice, null, 'online');

            PlatformRevenue::firstOrCreate(
                [
                    'omise_transaction_id' => $txn->id,
                    'type' => 'owner_payout',
                ],
                [
                    'property_id' => $txn->property_id,
                    'invoice_id' => $invoice?->id,
                    'billing_month' => $invoice?->month,
                    'billing_year' => $invoice?->year,
                    'payment_channel' => 'online',
                    'gross_amount' => $grossAmount,
                    'fee_amount' => 0,
                    'net_amount' => $grossAmount,
                    'status' => 'pending',
                    'note' => 'ยอดรับออนไลน์ที่ต้องโอนให้เจ้าของหอ',
                ]
            );

            return;
        }

        $feeAmount = round($grossAmount * ($property->revenue_percentage / 100), 2);

        PlatformRevenue::firstOrCreate(
            [
                'omise_transaction_id' => $txn->id,
                'type' => 'percentage_fee',
            ],
            [
                'property_id' => $txn->property_id,
                'invoice_id' => $invoice?->id,
                'billing_month' => $invoice?->month,
                'billing_year' => $invoice?->year,
                'payment_channel' => 'online',
                'gross_amount' => $grossAmount,
                'fee_amount' => $feeAmount,
                'net_amount' => $grossAmount - $feeAmount,
                'status' => 'pending',
            ]
        );
    }

    public function recordManualPayment(Invoice $invoice, Payment $payment): void
    {
        $property = $invoice->property;
        if (!$property) {
            return;
        }

        if ($property->revenue_model === 'package') {
            $this->ensureMonthlyPackageBill($property, $invoice, $payment, $payment->payment_method);
            return;
        }

        $grossAmount = (float) $payment->amount;
        $feeAmount = round($grossAmount * ($property->revenue_percentage / 100), 2);

        PlatformRevenue::firstOrCreate(
            [
                'invoice_id' => $invoice->id,
                'payment_channel' => $payment->payment_method,
                'type' => 'percentage_fee',
            ],
            [
                'property_id' => $invoice->property_id,
                'payment_id' => $payment->id,
                'billing_month' => $invoice->month,
                'billing_year' => $invoice->year,
                'gross_amount' => $grossAmount,
                'fee_amount' => $feeAmount,
                'net_amount' => 0,
                'status' => 'unpaid',
                'note' => 'เจ้าของหอรับเงินผู้เช่าเอง ระบบเปิดยอดค่าบริการแพลตฟอร์ม',
            ]
        );
    }

    private function ensureMonthlyPackageBill(Property $property, ?Invoice $invoice, ?Payment $payment, string $channel): void
    {
        if (!$invoice) {
            return;
        }

        $roomCount = $property->rooms()->count();
        $feeAmount = round($roomCount * (float) $property->revenue_package_per_room, 2);

        PlatformRevenue::firstOrCreate(
            [
                'property_id' => $property->id,
                'type' => 'package_fee',
                'billing_month' => $invoice->month,
                'billing_year' => $invoice->year,
            ],
            [
                'invoice_id' => $invoice->id,
                'payment_id' => $payment?->id,
                'payment_channel' => $channel,
                'gross_amount' => 0,
                'fee_amount' => $feeAmount,
                'net_amount' => 0,
                'status' => 'unpaid',
                'note' => "แพ็กเกจ {$roomCount} ห้อง x {$property->revenue_package_per_room} บาท/ห้อง",
            ]
        );
    }
}
