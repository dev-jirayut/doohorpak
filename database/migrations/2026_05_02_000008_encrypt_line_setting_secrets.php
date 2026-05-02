<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $columns = [
        'notify_token',
        'oa_channel_secret',
        'oa_channel_access_token',
    ];

    public function up(): void
    {
        DB::table('line_settings')->orderBy('id')->chunkById(100, function ($settings) {
            foreach ($settings as $setting) {
                $updates = [];

                foreach ($this->columns as $column) {
                    if (!filled($setting->{$column})) {
                        continue;
                    }

                    $updates[$column] = $this->isEncrypted($setting->{$column})
                        ? $setting->{$column}
                        : Crypt::encryptString($setting->{$column});
                }

                if ($updates) {
                    DB::table('line_settings')->where('id', $setting->id)->update($updates);
                }
            }
        });
    }

    public function down(): void
    {
        DB::table('line_settings')->orderBy('id')->chunkById(100, function ($settings) {
            foreach ($settings as $setting) {
                $updates = [];

                foreach ($this->columns as $column) {
                    if (!filled($setting->{$column})) {
                        continue;
                    }

                    $updates[$column] = $this->isEncrypted($setting->{$column})
                        ? Crypt::decryptString($setting->{$column})
                        : $setting->{$column};
                }

                if ($updates) {
                    DB::table('line_settings')->where('id', $setting->id)->update($updates);
                }
            }
        });
    }

    private function isEncrypted(string $value): bool
    {
        try {
            Crypt::decryptString($value);
            return true;
        } catch (Throwable) {
            return false;
        }
    }
};
