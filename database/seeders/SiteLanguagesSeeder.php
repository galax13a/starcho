<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SiteLanguagesSeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasTable('site_languages')) {
            return;
        }

        $languages = [
            ['code' => 'es', 'name' => 'Spanish', 'native_name' => 'Espanol', 'active' => true, 'sort_order' => 1],
            ['code' => 'en', 'name' => 'English', 'native_name' => 'English', 'active' => true, 'sort_order' => 2],
            ['code' => 'pt_BR', 'name' => 'Portuguese (Brazil)', 'native_name' => 'Portugues (Brasil)', 'active' => false, 'sort_order' => 3],
            ['code' => 'fr', 'name' => 'French', 'native_name' => 'Francais', 'active' => false, 'sort_order' => 4],
            ['code' => 'de', 'name' => 'German', 'native_name' => 'Deutsch', 'active' => false, 'sort_order' => 5],
            ['code' => 'it', 'name' => 'Italian', 'native_name' => 'Italiano', 'active' => false, 'sort_order' => 6],
            ['code' => 'zh_CN', 'name' => 'Chinese (Simplified)', 'native_name' => 'JianTi ZhongWen', 'active' => false, 'sort_order' => 7],
            ['code' => 'ja', 'name' => 'Japanese', 'native_name' => 'Nihongo', 'active' => false, 'sort_order' => 8],
        ];

        foreach ($languages as $language) {
            DB::table('site_languages')->updateOrInsert(
                ['code' => $language['code']],
                array_merge($language, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
