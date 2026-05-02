<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('rentals', 'property_id')) {
            return;
        }

        DB::table('rentals')
            ->whereNull('property_id')
            ->whereNotNull('room_id')
            ->orderBy('id')
            ->chunkById(100, function ($rentals) {
                foreach ($rentals as $rental) {
                    $propertyId = DB::table('rooms')
                        ->where('id', $rental->room_id)
                        ->value('property_id');

                    if ($propertyId) {
                        DB::table('rentals')
                            ->where('id', $rental->id)
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
