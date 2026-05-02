<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Services\LineService;
use Illuminate\Console\Command;

class SendOverdueReminders extends Command
{
    protected $signature = 'invoices:send-overdue-reminders';
    protected $description = 'Send LINE reminders for overdue invoices daily';

    public function handle(LineService $line): int
    {
        $overdueInvoices = Invoice::with(['rental.tenant.user', 'rental.room', 'property.lineSetting'])
            ->whereIn('status', ['pending', 'sent'])
            ->where('due_date', '<', now())
            ->get();

        $this->info("Found {$overdueInvoices->count()} overdue invoices");

        foreach ($overdueInvoices as $invoice) {
            try {
                $invoice->update(['status' => 'overdue']);
                $line->sendOverdueReminder($invoice);
                $this->line("Sent reminder: invoice #{$invoice->invoice_number}");
            } catch (\Throwable $e) {
                $this->warn("Failed {$invoice->invoice_number}: {$e->getMessage()}");
            }
        }

        return self::SUCCESS;
    }
}
