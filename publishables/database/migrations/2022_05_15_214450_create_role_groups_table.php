<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use MetaFramework\Models\RoleGroup;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('role_groups')) {
            Schema::create('role_groups', function (Blueprint $table): void {
                $table->id();
                $table->string('key')->unique();
                $table->longText('label');
                $table->longText('description')->nullable();
                $table->timestamps();
            });
        }

        $now = now();
        foreach ($this->defaultGroups() as $group) {
            DB::table('role_groups')->updateOrInsert(
                ['key' => $group['key']],
                [
                    'label' => $this->asTranslatedPayload($group['label']),
                    'description' => $this->asTranslatedPayload($group['description']),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('role_groups');
    }

    /**
     * @return array<int, array{key:string,label:string,description:string}>
     */
    private function defaultGroups(): array
    {
        return [
            [
                'key' => RoleGroup::CORE_ADMIN_KEY,
                'label' => 'Administration',
                'description' => 'Core administration roles',
            ],
            [
                'key' => RoleGroup::CORE_PUBLIC_KEY,
                'label' => 'Public',
                'description' => 'Public-facing roles',
            ],
        ];
    }

    private function asTranslatedPayload(string $value): string
    {
        $fallbackLocale = (string) config('app.fallback_locale', 'en');
        $locale = (string) config('app.locale', $fallbackLocale);
        if ($fallbackLocale === '') {
            $fallbackLocale = 'en';
        }
        if ($locale === '') {
            $locale = $fallbackLocale;
        }

        return (string) json_encode([
            $locale => $value,
            $fallbackLocale => $value,
        ], JSON_UNESCAPED_UNICODE);
    }
};
