<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            // Website information tab
            $table->string('app_name')->nullable()->after('site_name');
            $table->string('support_email')->nullable()->after('meta_author');
            $table->string('business_email')->nullable()->after('support_email');
            $table->string('company_name')->nullable()->after('business_email');
            $table->string('company_dni')->nullable()->after('company_name');
            $table->string('company_country')->nullable()->after('company_dni');
            $table->string('company_city')->nullable()->after('company_country');
            $table->string('support_whatsapp')->nullable()->after('company_city');
            $table->string('business_whatsapp')->nullable()->after('support_whatsapp');
            $table->string('server_timezone')->nullable()->default('UTC')->after('business_whatsapp');

            // Social media tab
            $table->string('social_facebook')->nullable()->after('server_timezone');
            $table->string('social_x')->nullable()->after('social_facebook');
            $table->string('social_telegram')->nullable()->after('social_x');
            $table->string('social_discord')->nullable()->after('social_telegram');
            $table->string('social_tiktok')->nullable()->after('social_discord');
            $table->string('social_linkedin')->nullable()->after('social_tiktok');
            $table->string('social_instagram')->nullable()->after('social_linkedin');
            $table->string('social_youtube')->nullable()->after('social_instagram');
            $table->string('social_pinterest')->nullable()->after('social_youtube');
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn([
                'app_name',
                'support_email',
                'business_email',
                'company_name',
                'company_dni',
                'company_country',
                'company_city',
                'support_whatsapp',
                'business_whatsapp',
                'server_timezone',
                'social_facebook',
                'social_x',
                'social_telegram',
                'social_discord',
                'social_tiktok',
                'social_linkedin',
                'social_instagram',
                'social_youtube',
                'social_pinterest',
            ]);
        });
    }
};
