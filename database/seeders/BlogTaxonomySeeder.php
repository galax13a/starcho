<?php

namespace Database\Seeders;

use App\Models\PostCategory;
use App\Models\PostTag;
use Illuminate\Database\Seeder;

class BlogTaxonomySeeder extends Seeder
{
    public function run(): void
    {
        // ── CATEGORIES ────────────────────────────────────────────────────────
        $categories = [
            [
                'slug'        => 'tecnologia',
                'color'       => '#3b82f6',
                'sort_order'  => 2,
                'name'        => ['es' => 'Tecnología',        'en' => 'Technology'],
                'description' => ['es' => 'Artículos sobre tendencias, herramientas y novedades tecnológicas.', 'en' => 'Articles about technology trends, tools and news.'],
            ],
            [
                'slug'        => 'tutoriales',
                'color'       => '#10b981',
                'sort_order'  => 3,
                'name'        => ['es' => 'Tutoriales',        'en' => 'Tutorials'],
                'description' => ['es' => 'Guías paso a paso para aprender y dominar nuevas habilidades.', 'en' => 'Step-by-step guides to learn and master new skills.'],
            ],
            [
                'slug'        => 'noticias',
                'color'       => '#f59e0b',
                'sort_order'  => 4,
                'name'        => ['es' => 'Noticias',          'en' => 'News'],
                'description' => ['es' => 'Últimas noticias del sector tech, IA y desarrollo de software.', 'en' => 'Latest news from the tech, AI and software development industry.'],
            ],
        ];

        foreach ($categories as $data) {
            PostCategory::updateOrCreate(
                ['slug' => $data['slug']],
                $data
            );
            $this->command->line("  <fg=blue>■</> Categoría: <fg=cyan>{$data['name']['es']}</> / <fg=yellow>{$data['name']['en']}</>");
        }

        // ── TAGS ─────────────────────────────────────────────────────────────
        $tags = [
            [
                'slug' => 'seo',
                'name' => ['es' => 'SEO', 'en' => 'SEO'],
            ],
            [
                'slug' => 'open-source',
                'name' => ['es' => 'Open Source', 'en' => 'Open Source'],
            ],
            [
                'slug' => 'productividad',
                'name' => ['es' => 'Productividad', 'en' => 'Productivity'],
            ],
        ];

        foreach ($tags as $data) {
            PostTag::updateOrCreate(
                ['slug' => $data['slug']],
                $data
            );
            $this->command->line("  <fg=magenta>●</> Etiqueta: <fg=cyan>{$data['name']['es']}</> / <fg=yellow>{$data['name']['en']}</>");
        }

        $this->command->newLine();
        $this->command->info('3 categorías y 3 etiquetas creadas correctamente.');
    }
}
