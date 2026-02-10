<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('roles')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE roles MODIFY label LONGTEXT NOT NULL');
        }

        $fallbackLocale = (string) config('app.fallback_locale', 'en');
        $locale = (string) config('app.locale', $fallbackLocale);
        if ($fallbackLocale === '') {
            $fallbackLocale = 'en';
        }
        if ($locale === '') {
            $locale = $fallbackLocale;
        }

        DB::table('roles')
            ->select('id', 'label')
            ->orderBy('id')
            ->chunkById(100, function ($rows) use ($locale, $fallbackLocale): void {
                foreach ($rows as $row) {
                    $label = (string) ($row->label ?? '');
                    if ($label === '') {
                        continue;
                    }

                    json_decode($label, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        continue;
                    }

                    DB::table('roles')
                        ->where('id', $row->id)
                        ->update([
                            'label' => json_encode([
                                $locale => $label,
                                $fallbackLocale => $label,
                            ], JSON_UNESCAPED_UNICODE),
                            'updated_at' => now(),
                        ]);
                }
            }, 'id');
    }

    public function down(): void
    {
        // Intentionally left empty.
    }
};
