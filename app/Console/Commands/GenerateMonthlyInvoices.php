<?php

namespace App\Console\Commands;

use App\Models\Property;
use App\Services\InvoiceService;
use App\Services\LineService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateMonthlyInvoices extends Command
{
    protected $signature = 'invoices:generate-monthly
                            {--month= : Month number (default: previous month)}
                            {--year=  : Year (default: current year)}
                            {--property= : Specific property ID}';

    protected $description = 'Auto-generate monthly invoices for all active rentals';

    public function handle(InvoiceService $invoiceService, LineService $line): int
    {
        $month      = (int) ($this->option('month') ?: Carbon::now()->subMonth()->month);
        $year       = (int) ($this->option('year')  ?: Carbon::now()->subMonth()->year);
        $propertyId = $this->option('property') ? (int) $this->option('property') : null;

        $this->info("Generating invoices for {$month}/{$year}...");

        $result    = $invoiceService->generateBulk($month, $year, $propertyId);
        $generated = $result['generated'];
        $skipped   = $result['skipped'];

        $this->info('Generated: ' . count($generated));
        if ($skipped) {
            $this->warn('Skipped: ' . implode(', ', $skipped));
        }

        // Send LINE Flex messages for each new invoice
        foreach ($generated as $invoice) {
            try {
                $line->sendInvoiceFlex($invoice);
            } catch (\Throwable $e) {
                $this->warn("LINE notify failed for invoice #{$invoice->invoice_number}: {$e->getMessage()}");
            }
        }

        // Notify each affected property owner
        $propertyIds = collect($generated)->pluck('property_id')->unique();
        foreach ($propertyIds as $pid) {
            $property = Property::find($pid);
            $count    = collect($generated)->where('property_id', $pid)->count();
            $line->notifyOwner($property, "\n📊 สร้างใบแจ้งหนี้ประจำเดือน {$month}/{$year}\nทั้งหมด {$count} ใบ");
        }

        return self::SUCCESS;
    }
}
