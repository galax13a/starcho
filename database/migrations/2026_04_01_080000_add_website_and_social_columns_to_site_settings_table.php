<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const COLUMN_BUILDERS = [
        'app_name' => 'string',
        'support_email' => 'string',
        'business_email' => 'string',
        'company_name' => 'string',
        'company_dni' => 'string',
        'company_country' => 'string',
        'company_city' => 'string',
        'support_whatsapp' => 'string',
        'business_whatsapp' => 'string',
        'server_timezone' => 'string',
        'social_facebook' => 'string',
        'social_x' => 'string',
        'social_telegram' => 'string',
        'social_discord' => 'string',
        'social_tiktok' => 'string',
        'social_linkedin' => 'string',
        'social_instagram' => 'string',
        'social_youtube' => 'string',
        'social_pinterest' => 'string',
    ];

    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            foreach (self::COLUMN_BUILDERS as $column => $type) {
                if (Schema::hasColumn('site_settings', $column)) {
                    continue;
                }

                $definition = $table->{$type}($column)->nullable();

                if ($column === 'server_timezone') {
                    $definition->default('UTC');
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $existingColumns = array_values(array_filter(
                array_keys(self::COLUMN_BUILDERS),
                fn (string $column): bool => Schema::hasColumn('site_settings', $column)
            ));

            if ($existingColumns !== []) {
                $table->dropColumn($existingColumns);
            }
        });
    }
};
