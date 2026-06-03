<?php

namespace Database\Seeders;

use App\Models\AiPlan;
use Illuminate\Database\Seeder;

class AiPlansSeeder extends Seeder
{
    public function run(): void
    {
        // quotas: null = ilimitado, 0 = no incluido. budget en dólares (se guarda en cents).
        $plans = [
            [
                'slug' => 'ai-free', 'price' => 0, 'free' => true, 'sort' => 0,
                'name' => ['es' => 'IA Gratis', 'en' => 'AI Free', 'pt_BR' => 'IA Grátis'],
                'desc' => ['es' => 'Para probar la generación con IA.', 'en' => 'To try AI generation.'],
                'text' => 30_000, 'image' => 3, 'video' => 0, 'budget' => 0.30,
            ],
            [
                'slug' => 'ai-starter', 'price' => 5, 'free' => false, 'sort' => 1,
                'name' => ['es' => 'IA Starter', 'en' => 'AI Starter'],
                'desc' => ['es' => 'Uso ligero para creadores individuales.', 'en' => 'Light use for solo creators.'],
                'text' => 150_000, 'image' => 20, 'video' => 2, 'budget' => 3,
            ],
            [
                'slug' => 'ai-basic', 'price' => 12, 'free' => false, 'sort' => 2,
                'name' => ['es' => 'IA Básico', 'en' => 'AI Basic'],
                'desc' => ['es' => 'Para blogs y contenido regular.', 'en' => 'For blogs and regular content.'],
                'text' => 400_000, 'image' => 60, 'video' => 6, 'budget' => 8,
            ],
            [
                'slug' => 'ai-pro', 'price' => 29, 'free' => false, 'sort' => 3,
                'name' => ['es' => 'IA Pro', 'en' => 'AI Pro'],
                'desc' => ['es' => 'Equipos pequeños y agencias.', 'en' => 'Small teams and agencies.'],
                'text' => 1_000_000, 'image' => 150, 'video' => 15, 'budget' => 20,
            ],
            [
                'slug' => 'ai-business', 'price' => 79, 'free' => false, 'sort' => 4,
                'name' => ['es' => 'IA Business', 'en' => 'AI Business'],
                'desc' => ['es' => 'Alto volumen de contenido.', 'en' => 'High content volume.'],
                'text' => 3_000_000, 'image' => 400, 'video' => 40, 'budget' => 55,
            ],
            [
                'slug' => 'ai-studio', 'price' => 199, 'free' => false, 'sort' => 5,
                'name' => ['es' => 'IA Studio', 'en' => 'AI Studio'],
                'desc' => ['es' => 'Producción intensiva de medios.', 'en' => 'Intensive media production.'],
                'text' => 8_000_000, 'image' => 1000, 'video' => 100, 'budget' => 140,
            ],
            [
                'slug' => 'ai-enterprise', 'price' => 499, 'free' => false, 'sort' => 6,
                'name' => ['es' => 'IA Enterprise', 'en' => 'AI Enterprise'],
                'desc' => ['es' => 'Sin límites, soporte prioritario.', 'en' => 'Unlimited, priority support.'],
                'text' => null, 'image' => null, 'video' => null, 'budget' => null,
            ],
        ];

        foreach ($plans as $p) {
            $plan = AiPlan::firstOrNew(['slug' => $p['slug']]);
            $plan->setTranslations('name', $p['name']);
            $plan->setTranslations('description', $p['desc'] ?? []);
            $plan->monthly_price = $p['price'];
            $plan->is_free = $p['free'];
            $plan->is_active = true;
            $plan->sort_order = $p['sort'];
            $plan->text_token_quota = $p['text'];
            $plan->image_quota = $p['image'];
            $plan->video_quota = $p['video'];
            $plan->monthly_budget_cents = $p['budget'] === null ? null : (int) round($p['budget'] * 100);
            $plan->save();
        }

        $this->command?->info('AI plans seeded: ' . AiPlan::count());
    }
}
