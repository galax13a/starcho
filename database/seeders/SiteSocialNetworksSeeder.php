<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SiteSocialNetworksSeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasTable('site_social_networks')) {
            return;
        }

        $networks = [
            ['key' => 'facebook',  'label' => 'Facebook',   'icon' => 'fab fa-facebook-f',           'color' => '#1877F2', 'sort_order' => 1],
            ['key' => 'x',         'label' => 'X (Twitter)', 'icon' => 'fab fa-x-twitter',            'color' => '#000000', 'sort_order' => 2],
            ['key' => 'instagram', 'label' => 'Instagram',  'icon' => 'fab fa-instagram',             'color' => '#E1306C', 'sort_order' => 3],
            ['key' => 'linkedin',  'label' => 'LinkedIn',   'icon' => 'fab fa-linkedin-in',           'color' => '#0A66C2', 'sort_order' => 4],
            ['key' => 'tiktok',    'label' => 'TikTok',     'icon' => 'fab fa-tiktok',                'color' => '#010101', 'sort_order' => 5],
            ['key' => 'youtube',   'label' => 'YouTube',    'icon' => 'fab fa-youtube',               'color' => '#FF0000', 'sort_order' => 6],
            ['key' => 'telegram',  'label' => 'Telegram',   'icon' => 'fab fa-telegram',              'color' => '#2CA5E0', 'sort_order' => 7],
            ['key' => 'discord',   'label' => 'Discord',    'icon' => 'fab fa-discord',               'color' => '#5865F2', 'sort_order' => 8],
            ['key' => 'pinterest', 'label' => 'Pinterest',  'icon' => 'fab fa-pinterest-p',           'color' => '#E60023', 'sort_order' => 9],
            ['key' => 'onlyfans',  'label' => 'OnlyFans',   'icon' => 'fas fa-circle-dollar-to-slot', 'color' => '#00AFF0', 'sort_order' => 10],
        ];

        foreach ($networks as $network) {
            DB::table('site_social_networks')->updateOrInsert(
                ['key' => $network['key']],
                array_merge($network, [
                    'url'        => null,
                    'active'     => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
