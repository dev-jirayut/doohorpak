<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $columns = [
        'notify_token',
        'oa_channel_secret',
        'oa_channel_access_token',
    ];

    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        foreach ($this->columns as $column) {
            match ($driver) {
                'pgsql' => DB::statement("ALTER TABLE line_settings ALTER COLUMN {$column} TYPE TEXT"),
                'mysql', 'mariadb' => DB::statement("ALTER TABLE line_settings MODIFY {$column} TEXT NULL"),
                default => null,
            };
        }
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        foreach ($this->columns as $column) {
            match ($driver) {
                'pgsql' => DB::statement("ALTER TABLE line_settings ALTER COLUMN {$column} TYPE VARCHAR(255)"),
                'mysql', 'mariadb' => DB::statement("ALTER TABLE line_settings MODIFY {$column} VARCHAR(255) NULL"),
                default => null,
            };
        }
    }
};
