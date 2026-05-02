<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('tenants', 'property_id')) {
            return;
        }

        DB::table('tenants')
            ->whereNull('property_id')
            ->orderBy('id')
            ->chunkById(100, function ($tenants) {
                foreach ($tenants as $tenant) {
                    $propertyId = DB::table('rentals')
                        ->join('rooms', 'rooms.id', '=', 'rentals.room_id')
                        ->where('rentals.tenant_id', $tenant->id)
                        ->orderByDesc('rentals.start_date')
                        ->value('rooms.property_id');

                    if ($propertyId) {
                        DB::table('tenants')
                            ->where('id', $tenant->id)
                            ->update(['property_id' => $propertyId]);
                    }
                }
            });
    }

    public function down(): void
    {
        // Data backfill only. Do not clear property_id values on rollback.
    }
};
