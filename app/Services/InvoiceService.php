<?php

namespace App\Services;

use App\Models\Charge;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\MeterReading;
use App\Models\Rental;
use App\Models\UtilityRate;
use Carbon\Carbon;

class InvoiceService
{
    public function generateFromMeterReading(MeterReading $reading): Invoice
    {
        $rental = Rental::where('room_id', $reading->room_id)
            ->where('status', 'active')
            ->with('room')
            ->firstOrFail();

        $rate = UtilityRate::current();

        $electricityUnits  = $reading->electricity_units;
        $waterUnits        = $reading->water_units;
        $electricityRate   = $rate?->electricity_rate ?? 0;
        $waterRate         = $rate?->water_rate ?? 0;
        $electricityCharge = $electricityUnits * $electricityRate;
        $waterCharge       = $waterUnits * $waterRate;

        $applicableCharges = $this->getApplicableCharges($reading->room_id, $reading->month, $reading->year);
        $extraTotal = $applicableCharges->sum('amount');

        $total = $rental->monthly_rent + $electricityCharge + $waterCharge + $extraTotal;

        $invoiceNumber = $this->generateInvoiceNumber($reading->month, $reading->year);

        $invoice = Invoice::create([
            'property_id'        => $rental->room->property_id,
            'invoice_number'     => $invoiceNumber,
            'rental_id'          => $rental->id,
            'month'              => $reading->month,
            'year'               => $reading->year,
            'due_date'           => Carbon::create($reading->year, $reading->month, 1)->addMonth()->day(5),
            'room_charge'        => $rental->monthly_rent,
            'electricity_units'  => $electricityUnits,
            'electricity_rate'   => $electricityRate,
            'electricity_charge' => $electricityCharge,
            'water_units'        => $waterUnits,
            'water_rate'         => $waterRate,
            'water_charge'       => $waterCharge,
            'other_charge'       => 0,
            'total_amount'       => $total,
            'status'             => 'pending',
        ]);

        foreach ($applicableCharges as $idx => $charge) {
            InvoiceItem::create([
                'invoice_id'  => $invoice->id,
                'description' => $charge->name,
                'quantity'    => 1,
                'unit_price'  => $charge->amount,
                'amount'      => $charge->amount,
                'sort_order'  => $idx + 1,
            ]);
        }

        return $invoice;
    }

    public function generateBulk(int $month, int $year, ?int $propertyId = null): array
    {
        $readings = MeterReading::where('month', $month)
            ->where('year', $year)
            ->with('room')
            ->when($propertyId, fn ($q) => $q->whereHas('room', fn ($r) => $r->where('property_id', $propertyId)))
            ->get();

        $generated = [];
        $skipped   = [];

        foreach ($readings as $reading) {
            $exists = Invoice::whereHas('rental', fn ($q) => $q->where('room_id', $reading->room_id))
                ->where('month', $month)
                ->where('year', $year)
                ->exists();

            if ($exists) {
                $skipped[] = $reading->room->room_number;
                continue;
            }

            $rental = Rental::where('room_id', $reading->room_id)
                ->where('status', 'active')
                ->first();

            if (!$rental) {
                $skipped[] = $reading->room->room_number;
                continue;
            }

            $generated[] = $this->generateFromMeterReading($reading);
        }

        return compact('generated', 'skipped');
    }

    private function getApplicableCharges(int $roomId, int $month, int $year)
    {
        $periodStart = Carbon::create($year, $month, 1)->startOfDay();
        $periodEnd   = Carbon::create($year, $month, 1)->endOfMonth()->endOfDay();

        return Charge::where('is_active', true)
            ->whereHas('rooms', function ($q) use ($roomId, $periodStart, $periodEnd) {
                $q->where('rooms.id', $roomId)
                  ->where('charge_rooms.active_from', '<=', $periodEnd)
                  ->where(function ($q2) use ($periodStart) {
                      $q2->whereNull('charge_rooms.active_to')
                         ->orWhere('charge_rooms.active_to', '>=', $periodStart);
                  });
            })
            ->where(function ($q) use ($month, $year) {
                // monthly: always apply; one_time: match exact month/year of active_from
                $q->where('type', 'monthly')
                  ->orWhere(function ($q2) use ($month, $year) {
                      $q2->where('type', 'one_time')
                         ->whereHas('rooms', function ($q3) use ($month, $year) {
                             $q3->whereMonth('charge_rooms.active_from', $month)
                                ->whereYear('charge_rooms.active_from', $year);
                         });
                  });
            })
            ->get();
    }

    private function generateInvoiceNumber(int $month, int $year): string
    {
        $prefix = sprintf('INV-%d%02d', $year, $month);
        $last   = Invoice::where('invoice_number', 'like', $prefix . '%')
            ->orderBy('invoice_number', 'desc')
            ->value('invoice_number');

        $seq = $last ? ((int) substr($last, -4) + 1) : 1;
        return $prefix . '-' . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}
