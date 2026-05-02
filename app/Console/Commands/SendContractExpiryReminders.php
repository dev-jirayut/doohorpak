<?php

namespace App\Console\Commands;

use App\Models\Contract;
use App\Services\LineService;
use Illuminate\Console\Command;

class SendContractExpiryReminders extends Command
{
    protected $signature = 'contracts:send-expiry-reminders';
    protected $description = 'Send LINE reminders for contracts expiring in 30 or 7 days';

    public function handle(LineService $line): int
    {
        // 30-day reminder
        Contract::with(['rental.tenant.user', 'property.lineSetting'])
            ->where('status', 'active')
            ->where('reminder_30_sent', false)
            ->whereDate('end_date', now()->addDays(30)->toDateString())
            ->each(function (Contract $contract) use ($line) {
                $tenant   = $contract->rental?->tenant;
                $lineId   = $tenant?->line_user_id ?? $tenant?->user?->line_user_id;
                $property = $contract->property;
                $endDate  = $contract->end_date->format('d/m/Y');

                $line->notifyOwner($property, "\n📋 สัญญาห้อง {$contract->rental?->room?->room_number} ใกล้หมดอายุ\nครบกำหนด: {$endDate}\n(อีก 30 วัน)");

                if ($lineId) {
                    $line->pushFlexMessage($lineId, "สัญญาเช่าใกล้หมดอายุ", [
                        'type' => 'bubble',
                        'header' => ['type' => 'box', 'layout' => 'vertical', 'backgroundColor' => '#f5a623',
                            'contents' => [['type' => 'text', 'text' => '📋 สัญญาใกล้หมดอายุ', 'color' => '#fff', 'weight' => 'bold']]],
                        'body' => ['type' => 'box', 'layout' => 'vertical', 'spacing' => 'md', 'contents' => [
                            ['type' => 'text', 'text' => "สัญญาของคุณจะหมดอายุในอีก 30 วัน", 'wrap' => true],
                            ['type' => 'text', 'text' => "วันที่หมดอายุ: {$endDate}", 'color' => '#cc0000'],
                        ]],
                    ], $property);
                }

                $contract->update(['reminder_30_sent' => true]);
            });

        // 7-day reminder
        Contract::with(['rental.tenant.user', 'property.lineSetting'])
            ->where('status', 'active')
            ->where('reminder_7_sent', false)
            ->whereDate('end_date', now()->addDays(7)->toDateString())
            ->each(function (Contract $contract) use ($line) {
                $tenant   = $contract->rental?->tenant;
                $lineId   = $tenant?->line_user_id ?? $tenant?->user?->line_user_id;
                $property = $contract->property;
                $endDate  = $contract->end_date->format('d/m/Y');

                $line->notifyOwner($property, "\n⚠️ สัญญาห้อง {$contract->rental?->room?->room_number} หมดอายุใน 7 วัน!\nครบกำหนด: {$endDate}");

                if ($lineId) {
                    $line->pushFlexMessage($lineId, "สัญญาเช่าหมดอายุอีก 7 วัน!", [
                        'type' => 'bubble',
                        'header' => ['type' => 'box', 'layout' => 'vertical', 'backgroundColor' => '#cc4444',
                            'contents' => [['type' => 'text', 'text' => '⚠️ สัญญาหมดอายุใน 7 วัน', 'color' => '#fff', 'weight' => 'bold']]],
                        'body' => ['type' => 'box', 'layout' => 'vertical', 'contents' => [
                            ['type' => 'text', 'text' => "วันที่หมดอายุ: {$endDate}", 'color' => '#cc0000', 'weight' => 'bold'],
                        ]],
                    ], $property);
                }

                $contract->update(['reminder_7_sent' => true]);
            });

        $this->info('Contract expiry reminders sent.');
        return self::SUCCESS;
    }
}
