<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('charges', 'property_id')) {
            return;
        }

        DB::table('charges')
            ->whereNull('property_id')
            ->orderBy('id')
            ->chunkById(100, function ($charges) {
                foreach ($charges as $charge) {
                    $propertyId = DB::table('charge_rooms')
                        ->join('rooms', 'rooms.id', '=', 'charge_rooms.room_id')
                        ->where('charge_rooms.charge_id', $charge->id)
                        ->value('rooms.property_id');

                    if ($propertyId) {
                        DB::table('charges')
                            ->where('id', $charge->id)
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
