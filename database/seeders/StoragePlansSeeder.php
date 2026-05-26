<?php

namespace Database\Seeders;

use App\Models\StoragePlan;
use App\Models\User;
use Illuminate\Database\Seeder;

class StoragePlansSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name'                => 'Free',
                'slug'                => 'free',
                'storage_limit_bytes' => 5 * 1024 * 1024,           // 5 MB
                'monthly_price'       => 0.00,
                'is_free'             => true,
                'is_active'           => true,
                'sort_order'          => 1,
            ],
            [
                'name'                => 'Básico',
                'slug'                => 'basic',
                'storage_limit_bytes' => 50 * 1024 * 1024,          // 50 MB
                'monthly_price'       => 4.99,
                'is_free'             => false,
                'is_active'           => true,
                'sort_order'          => 2,
            ],
            [
                'name'                => 'Starter',
                'slug'                => 'starter',
                'storage_limit_bytes' => 1 * 1024 * 1024 * 1024,    // 1 GB
                'monthly_price'       => 9.99,
                'is_free'             => false,
                'is_active'           => true,
                'sort_order'          => 3,
            ],
            [
                'name'                => 'Pro',
                'slug'                => 'pro',
                'storage_limit_bytes' => 3 * 1024 * 1024 * 1024,    // 3 GB
                'monthly_price'       => 19.99,
                'is_free'             => false,
                'is_active'           => true,
                'sort_order'          => 4,
            ],
            [
                'name'                => 'Business',
                'slug'                => 'business',
                'storage_limit_bytes' => 10 * 1024 * 1024 * 1024,   // 10 GB
                'monthly_price'       => 49.99,
                'is_free'             => false,
                'is_active'           => true,
                'sort_order'          => 5,
            ],
            [
                'name'                => 'Agency',
                'slug'                => 'agency',
                'storage_limit_bytes' => 25 * 1024 * 1024 * 1024,   // 25 GB
                'monthly_price'       => 99.99,
                'is_free'             => false,
                'is_active'           => true,
                'sort_order'          => 6,
            ],
        ];

        foreach ($plans as $plan) {
            StoragePlan::updateOrCreate(['slug' => $plan['slug']], $plan);
        }

        // Assign all users without a plan to the free plan
        $freePlan = StoragePlan::free();
        if ($freePlan) {
            User::whereNull('storage_plan_id')->update(['storage_plan_id' => $freePlan->id]);
        }

        $this->command->info('Storage plans seeded: ' . StoragePlan::count() . ' plans');
    }
}
