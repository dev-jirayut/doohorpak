<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // PostgreSQL: drop the existing enum constraint and use varchar instead
        DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check');
        DB::statement("ALTER TABLE users ALTER COLUMN role TYPE VARCHAR(20) USING role::VARCHAR");
        DB::statement("ALTER TABLE users ALTER COLUMN role SET DEFAULT 'staff'");
        DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('super_admin','owner','staff','tenant'))");

        // Update any existing 'admin' roles to 'super_admin'
        DB::table('users')->where('role', 'admin')->update(['role' => 'super_admin']);
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check');
        DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('admin','staff'))");
        DB::table('users')->where('role', 'super_admin')->update(['role' => 'admin']);
    }
};
