<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\PostCategory;
use App\Models\PostTag;
use App\Models\User;
use Illuminate\Database\Seeder;

class BlogPostWebDesignSeeder extends Seeder
{
    public function run(): void
    {
        $author = User::first();

        // ── Ensure category ────────────────────────────────────────────────────
        $cat = PostCategory::updateOrCreate(
            ['slug' => 'diseno-web'],
            [
                'color'       => '#06b6d4',
                'sort_order'  => 5,
                'name'        => ['es' => 'Diseño Web', 'en' => 'Web Design'],
                'description' => [
                    'es' => 'Tendencias, herramientas y mejores prácticas para diseñar experiencias web modernas.',
                    'en' => 'Trends, tools and best practices for designing modern web experiences.',
                ],
            ]
        );

        // ── Ensure tags ────────────────────────────────────────────────────────
        $tagDefs = [
            ['slug' => 'ui-ux',    'name' => ['es' => 'UI/UX',    'en' => 'UI/UX']],
            ['slug' => 'css',      'name' => ['es' => 'CSS',       'en' => 'CSS']],
            ['slug' => 'frontend', 'name' => ['es' => 'Frontend',  'en' => 'Frontend']],
            ['slug' => 'tendencias',    'name' => ['es' => 'Tendencias', 'en' => 'Trends']],
        ];
        $tagIds = [];
        foreach ($tagDefs as $td) {
            $tag      = PostTag::updateOrCreate(['slug' => $td['slug']], $td);
            $tagIds[] = $tag->id;
        }

        // ── Create post ────────────────────────────────────────────────────────
        $post = Post::updateOrCreate(
            ['slug->es' => 'tendencias-diseno-web-2026'],
            [
                'type'   => Post::TYPE_POST,
                'status' => Post::STATUS_PUBLISHED,
                'published_at' => now(),
                'author_id' => $author->id,
                'user_id'   => $author->id,
                'allow_comments' => true,
                'slug' => [
                    'es' => 'tendencias-diseno-web-2026',
                    'en' => 'web-design-trends-2026',
                ],
                'title' => [
                    'es' => 'Tendencias de Diseño Web para 2026: Lo que viene y lo que se queda',
                    'en' => 'Web Design Trends for 2026: What\'s Coming and What\'s Here to Stay',
                ],
                'excerpt' => [
                    'es' => 'Exploramos las tendencias más importantes en diseño web para 2026: de las interfaces glassmorphism hasta la IA generativa aplicada al UX, y cómo adoptarlas sin perder el foco en la conversión.',
                    'en' => 'We explore the most important web design trends for 2026: from glassmorphism interfaces to generative AI applied to UX, and how to adopt them without losing focus on conversion.',
                ],
                'seo_title' => [
                    'es' => 'Tendencias Diseño Web 2026 — Guía Completa',
                    'en' => 'Web Design Trends 2026 — Complete Guide',
                ],
                'seo_description' => [
                    'es' => 'Descubre las 8 tendencias de diseño web más importantes para 2026, con ejemplos prácticos y cómo implementarlas en tu próximo proyecto.',
                    'en' => 'Discover the 8 most important web design trends for 2026, with practical examples and how to implement them in your next project.',
                ],
                'content' => [
                    'es' => $this->contentEs(),
                    'en' => $this->contentEn(),
                ],
            ]
        );

        $post->categories()->sync([$cat->id]);
        $post->tags()->sync($tagIds);

        $this->command->info('✓ Blog post "Tendencias de Diseño Web 2026" creado correctamente.');
    }

    private function contentEs(): string
    {
        return json_encode([
            'time'    => time() * 1000,
            'version' => '2.28.2',
            'blocks'  => [
                ['type' => 'header',    'data' => ['text' => 'El diseño web en 2026: entre lo humano y lo generativo', 'level' => 2]],
                ['type' => 'paragraph', 'data' => ['text' => 'El diseño web nunca se detiene. En 2026, la frontera entre lo creado por humanos y lo asistido por inteligencia artificial se difumina cada vez más. Los mejores diseñadores no son los que resisten la IA, sino los que la integran como parte de su flujo creativo. Aquí te presentamos las 8 tendencias más relevantes que están redefiniendo cómo construimos experiencias en la web.']],
                ['type' => 'header',    'data' => ['text' => '1. Glassmorphism evolucionado', 'level' => 3]],
                ['type' => 'paragraph', 'data' => ['text' => 'El efecto de cristal esmerilado ya no es tendencia nueva, pero en 2026 ha madurado. Ahora lo vemos combinado con <strong>profundidad real</strong> mediante sombras en capas, bordes luminosos y fondos de gradiente animado. La clave está en usarlo con moderación: un card, un modal o una barra de navegación flotante, nunca toda la página.']],
                ['type' => 'code',      'data' => ['code' => ".glass-card {\n  background: rgba(255, 255, 255, 0.08);\n  backdrop-filter: blur(20px) saturate(180%);\n  -webkit-backdrop-filter: blur(20px) saturate(180%);\n  border: 1px solid rgba(255, 255, 255, 0.12);\n  border-radius: 20px;\n  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3),\n              0 0 0 1px rgba(255,255,255,.05) inset;\n}", 'language' => 'css']],
                ['type' => 'header',    'data' => ['text' => '2. Tipografía como protagonista', 'level' => 3]],
                ['type' => 'paragraph', 'data' => ['text' => 'Las fuentes de gran tamaño y peso extremo toman el protagonismo en heroes y landings. Fuentes variables que se adaptan al viewport, texto cinético que reacciona al scroll o al cursor, y combinaciones tipográficas atrevidas serif + sans-serif que antes habrían parecido errores de diseño.']],
                ['type' => 'header',    'data' => ['text' => '3. IA generativa integrada en el UX', 'level' => 3]],
                ['type' => 'paragraph', 'data' => ['text' => 'No se trata solo de chatbots. En 2026, la IA generativa aparece como funcionalidad nativa en la UI: resúmenes automáticos de artículos largos, traducción en tiempo real con preservación del tono, sugerencias de contenido personalizadas o formularios que se autocompletan con contexto inteligente. Los usuarios ya lo esperan; los que no lo ofrecen pierden retención.']],
                ['type' => 'header',    'data' => ['text' => '4. Scrollytelling y narrativa vertical', 'level' => 3]],
                ['type' => 'paragraph', 'data' => ['text' => 'El scroll ya no es solo navegación: es narración. Las páginas de producto cuentan historias visuales mientras el usuario baja. Animaciones atadas al scroll, cambios de color de fondo, elementos que entran y salen de escena, texto que se escribe solo. Todo orquestado para que el usuario sienta que está leyendo un cómic interactivo, no un sitio corporativo.']],
                ['type' => 'header',    'data' => ['text' => '5. Dark mode como diseño, no como opción', 'level' => 3]],
                ['type' => 'paragraph', 'data' => ['text' => 'Muchos sitios en 2026 nacen directamente en oscuro, porque las paletas de color oscuro permiten mayor contraste, hacen que los acentos de color vibren más y se alinean con el gusto estético de audiencias tech. La clave ya no es "ofrecer modo oscuro", sino diseñar el oscuro primero y adaptarlo al claro, invirtiendo el proceso tradicional.']],
                ['type' => 'header',    'data' => ['text' => '6. Microinteracciones de alta fidelidad', 'level' => 3]],
                ['type' => 'paragraph', 'data' => ['text' => 'Los botones que vibran sutilmente al hover, los íconos que se transforman en feedback visual al hacer click, los loaders que cuentan mientras esperamos. Las microinteracciones ya no son un lujo; son el estándar de calidad que separa un producto de software de una "página web". Librerías como Motion (antes Framer Motion), GSAP y las Web Animations API las hacen accesibles a cualquier stack.']],
                ['type' => 'header',    'data' => ['text' => '7. Diseño modular con tokens de diseño', 'level' => 3]],
                ['type' => 'paragraph', 'data' => ['text' => 'Los equipos grandes ya trabajaban así; en 2026 lo hace hasta el freelance individual. Los design tokens (colores, tipografía, espaciado, radios, sombras como variables) se definen una vez y fluyen a Figma, al CSS y al código de componentes de forma sincronizada. Herramientas como Tokens Studio y el soporte nativo de variables en Figma han democratizado este workflow.']],
                ['type' => 'header',    'data' => ['text' => '8. Accesibilidad como ventaja competitiva', 'level' => 3]],
                ['type' => 'paragraph', 'data' => ['text' => 'El WCAG 3.0 entra en vigor progresivamente y los motores de búsqueda empiezan a penalizar sitios que no cumplen mínimos de accesibilidad. En 2026, el contraste, el foco visible, la semántica HTML correcta y el soporte para lectores de pantalla dejan de ser "buena práctica" para convertirse en requisito de negocio. Los que los adopten antes de la obligación ganarán ventaja SEO y de conversión.']],
                ['type' => 'header',    'data' => ['text' => 'Conclusión: diseña para personas, amplifica con tecnología', 'level' => 2]],
                ['type' => 'paragraph', 'data' => ['text' => 'Las tendencias cambian, pero el principio central del buen diseño web no: reducir la fricción entre el usuario y lo que necesita. Cada una de estas ocho tendencias, aplicada con criterio, sirve a ese objetivo. La trampa está en seguirlas por moda; el acierto está en adoptarlas cuando resuelven un problema real de tu audiencia.']],
            ],
        ]);
    }

    private function contentEn(): string
    {
        return json_encode([
            'time'    => time() * 1000,
            'version' => '2.28.2',
            'blocks'  => [
                ['type' => 'header',    'data' => ['text' => 'Web design in 2026: between the human and the generative', 'level' => 2]],
                ['type' => 'paragraph', 'data' => ['text' => 'Web design never stops. In 2026, the boundary between what\'s created by humans and what\'s assisted by artificial intelligence blurs increasingly. The best designers aren\'t those who resist AI, but those who integrate it as part of their creative workflow. Here are the 8 most relevant trends redefining how we build web experiences.']],
                ['type' => 'header',    'data' => ['text' => '1. Evolved glassmorphism', 'level' => 3]],
                ['type' => 'paragraph', 'data' => ['text' => 'The frosted glass effect is no longer a new trend, but in 2026 it has matured. We now see it combined with <strong>real depth</strong> through layered shadows, glowing borders and animated gradient backgrounds. The key is to use it sparingly: a card, a modal or a floating navigation bar — never the entire page.']],
                ['type' => 'code',      'data' => ['code' => ".glass-card {\n  background: rgba(255, 255, 255, 0.08);\n  backdrop-filter: blur(20px) saturate(180%);\n  -webkit-backdrop-filter: blur(20px) saturate(180%);\n  border: 1px solid rgba(255, 255, 255, 0.12);\n  border-radius: 20px;\n  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3),\n              0 0 0 1px rgba(255,255,255,.05) inset;\n}", 'language' => 'css']],
                ['type' => 'header',    'data' => ['text' => '2. Typography as the hero', 'level' => 3]],
                ['type' => 'paragraph', 'data' => ['text' => 'Large, extremely heavy typefaces take center stage in heroes and landing pages. Variable fonts that adapt to the viewport, kinetic text that reacts to scroll or cursor, and bold typographic combinations of serif + sans-serif that would previously have seemed like design mistakes.']],
                ['type' => 'header',    'data' => ['text' => '3. Generative AI built into UX', 'level' => 3]],
                ['type' => 'paragraph', 'data' => ['text' => 'It\'s not just chatbots. In 2026, generative AI appears as a native UI feature: automatic summaries of long articles, real-time translation that preserves tone, personalized content suggestions, and forms that autocomplete with intelligent context. Users already expect it; those who don\'t offer it lose retention.']],
                ['type' => 'header',    'data' => ['text' => '4. Scrollytelling and vertical narrative', 'level' => 3]],
                ['type' => 'paragraph', 'data' => ['text' => 'Scrolling is no longer just navigation — it\'s storytelling. Product pages tell visual stories as the user scrolls down. Scroll-tied animations, background color changes, elements entering and leaving the scene, text that writes itself. All orchestrated so the user feels they\'re reading an interactive comic, not a corporate site.']],
                ['type' => 'header',    'data' => ['text' => '5. Dark mode as design, not just an option', 'level' => 3]],
                ['type' => 'paragraph', 'data' => ['text' => 'Many sites in 2026 are born dark, because dark color palettes allow greater contrast, make accent colors vibrate more intensely and align with the aesthetic taste of tech audiences. The key is no longer "offer dark mode" — it\'s design dark first and adapt to light, inverting the traditional process.']],
                ['type' => 'header',    'data' => ['text' => '6. High-fidelity microinteractions', 'level' => 3]],
                ['type' => 'paragraph', 'data' => ['text' => 'Buttons that vibrate subtly on hover, icons that transform into visual feedback on click, loaders that narrate while we wait. Microinteractions are no longer a luxury; they\'re the quality standard that separates a software product from a "webpage". Libraries like Motion (formerly Framer Motion), GSAP and the Web Animations API make them accessible to any stack.']],
                ['type' => 'header',    'data' => ['text' => '7. Modular design with design tokens', 'level' => 3]],
                ['type' => 'paragraph', 'data' => ['text' => 'Large teams already worked this way; in 2026 even solo freelancers do. Design tokens (colors, typography, spacing, radii, shadows as variables) are defined once and flow to Figma, CSS and component code in a synchronized way. Tools like Tokens Studio and native variable support in Figma have democratized this workflow.']],
                ['type' => 'header',    'data' => ['text' => '8. Accessibility as a competitive advantage', 'level' => 3]],
                ['type' => 'paragraph', 'data' => ['text' => 'WCAG 3.0 is progressively coming into force and search engines are beginning to penalize sites that don\'t meet minimum accessibility standards. In 2026, contrast, visible focus, correct HTML semantics and screen reader support stop being "best practice" and become a business requirement. Those who adopt them before being forced to will gain SEO and conversion advantages.']],
                ['type' => 'header',    'data' => ['text' => 'Conclusion: design for people, amplify with technology', 'level' => 2]],
                ['type' => 'paragraph', 'data' => ['text' => 'Trends change, but the central principle of good web design doesn\'t: reduce friction between the user and what they need. Each of these eight trends, applied thoughtfully, serves that goal. The trap is following them for fashion; the win is adopting them when they solve a real problem for your audience.']],
            ],
        ]);
    }
}
