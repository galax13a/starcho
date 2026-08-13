<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\PostCategory;
use App\Models\PostTag;
use App\Models\SiteLanguage;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BlogPostAiLaravelSeeder extends Seeder
{
    public function run(): void
    {
        $activeCodes   = SiteLanguage::activeCodes();
        $primaryLocale = $activeCodes[0] ?? 'es';
        $author        = User::first();

        if (! $author) {
            $this->command->warn('No users found.');
            return;
        }

        // ── Category ──────────────────────────────────────────────────────────
        $category = PostCategory::firstOrCreate(
            ['slug' => 'desarrollo-web'],
            [
                'name'        => ['es' => 'Desarrollo Web', 'en' => 'Web Development'],
                'description' => ['es' => 'Artículos sobre desarrollo web moderno.', 'en' => 'Articles about modern web development.'],
                'slug'        => 'desarrollo-web',
                'color'       => '#7c3aed',
                'sort_order'  => 1,
            ]
        );

        // ── Tags ──────────────────────────────────────────────────────────────
        $tagNames = [
            ['slug' => 'laravel',   'name' => ['es' => 'Laravel',         'en' => 'Laravel']],
            ['slug' => 'nodejs',    'name' => ['es' => 'Node.js',          'en' => 'Node.js']],
            ['slug' => 'ia',        'name' => ['es' => 'Inteligencia Artificial', 'en' => 'Artificial Intelligence']],
            ['slug' => 'php',       'name' => ['es' => 'PHP',              'en' => 'PHP']],
            ['slug' => 'desarrollo-rapido', 'name' => ['es' => 'Desarrollo Rápido', 'en' => 'Rapid Development']],
        ];

        $tagIds = [];
        foreach ($tagNames as $t) {
            $tag = PostTag::firstOrCreate(
                ['slug' => $t['slug']],
                ['name' => $t['name'], 'slug' => $t['slug']]
            );
            $tagIds[] = $tag->id;
        }

        // ── Content ───────────────────────────────────────────────────────────
        $contentPerLocale = [];
        foreach ($activeCodes as $code) {
            $blocks = $code === 'en' ? $this->articleEn() : $this->articleEs();
            $contentPerLocale[$code] = json_encode($blocks);
        }

        // ── Post ──────────────────────────────────────────────────────────────
        $slug = 'desarrollo-rapido-con-ia-laravel-nodejs';

        $post = Post::updateOrCreate(
            ['slug' => $slug, 'type' => 'post'],
            [
                'type'   => 'post',
                'status' => 'published',
                'slug'   => $slug,
                'published_at' => now(),
                'author_id'    => $author->id,
                'user_id'      => $author->id,
                'allow_comments' => true,

                'title' => [
                    'es' => 'Desarrollo Rápido con IA: Cómo Laravel y Node.js Están Cambiando el Juego',
                    'en' => 'Rapid Development with AI: How Laravel and Node.js Are Changing the Game',
                ],
                'excerpt' => [
                    'es' => 'Descubre cómo la inteligencia artificial está acelerando el desarrollo de software con Laravel y Node.js: desde generación de código y pruebas automáticas hasta despliegue inteligente.',
                    'en' => 'Discover how artificial intelligence is accelerating software development with Laravel and Node.js — from code generation and automated testing to intelligent deployment.',
                ],
                'content' => $contentPerLocale,

                'seo_title' => [
                    'es' => 'Desarrollo Rápido con IA, Laravel y Node.js — 2025',
                    'en' => 'Rapid Development with AI, Laravel and Node.js — 2025',
                ],
                'seo_description' => [
                    'es' => 'Aprende cómo la IA acelera el desarrollo con Laravel y Node.js: herramientas, flujos de trabajo y casos de uso reales para equipos modernos.',
                    'en' => 'Learn how AI accelerates development with Laravel and Node.js: tools, workflows and real-world use cases for modern teams.',
                ],
                'seo_keywords' => [
                    'es' => 'laravel ia, node.js inteligencia artificial, desarrollo rapido php, ai coding tools, claude laravel',
                    'en' => 'laravel ai, node.js artificial intelligence, rapid php development, ai coding tools, claude laravel',
                ],
                'og_title' => [
                    'es' => 'Desarrollo Rápido con IA: Laravel y Node.js',
                    'en' => 'Rapid Development with AI: Laravel and Node.js',
                ],
                'og_description' => [
                    'es' => 'Cómo la inteligencia artificial está transformando el desarrollo web moderno con Laravel y Node.js.',
                    'en' => 'How artificial intelligence is transforming modern web development with Laravel and Node.js.',
                ],
            ]
        );

        $post->categories()->sync([$category->id]);
        $post->tags()->sync($tagIds);

        $this->command->info("✓ Post publicado: \"{$post->getTranslation('title', $primaryLocale)}\"");
        $this->command->line("  Categoría: {$category->getTranslation('name', $primaryLocale)} · Etiquetas: " . count($tagIds));
    }

    // ── ARTICLE ES ────────────────────────────────────────────────────────────

    private function articleEs(): array
    {
        return $this->doc([
            $this->p('<b>La inteligencia artificial ya no es el futuro del desarrollo de software: es el presente.</b> En 2025, los equipos que integran herramientas de IA en sus flujos de trabajo con Laravel y Node.js están entregando proyectos en la mitad del tiempo, con menos bugs y mayor calidad de código. En este artículo exploramos cómo lograrlo.'),

            $this->h2('El cambio de paradigma: de escribir código a dirigirlo'),
            $this->p('Durante décadas, el trabajo de un desarrollador consistía en escribir cada línea de código manualmente. Hoy, la IA actúa como un copiloto que escribe los primeros borradores, sugiere refactorizaciones, detecta vulnerabilidades y genera pruebas automáticas. El rol del desarrollador evoluciona: de ejecutor a director técnico.'),
            $this->p('Esto no implica que la IA reemplace a los programadores. Todo lo contrario: amplifica su capacidad. Un desarrollador senior con IA puede hacer el trabajo de un equipo de tres.'),

            $this->h2('IA + Laravel: el stack PHP más potente de la historia'),
            $this->h3('1. Generación de código con Claude y GPT-4'),
            $this->p('Las herramientas de asistencia de código, <b>GitHub Copilot</b> y <b>Cursor</b> entienden el contexto completo de un proyecto Laravel. Pueden generar:'),
            $this->ul([
                'Migraciones y modelos Eloquent a partir de una descripción en lenguaje natural',
                'Controladores REST completos con validación, autorización y manejo de errores',
                'Factories y seeders de prueba basados en la estructura real de la base de datos',
                'Tests unitarios y de integración con PHPUnit o Pest',
                'Comandos Artisan personalizados listos para producción',
            ]),
            $this->warning(
                'Importante',
                'La IA genera código plausible, no necesariamente correcto. Siempre revisa la lógica de negocio, valida las migraciones en staging y ejecuta la suite de tests antes de hacer merge.'
            ),

            $this->h3('2. Laravel Prism: el paquete oficial para LLMs'),
            $this->p('<b>Laravel Prism</b> (antes conocido como Prism PHP) es el paquete de referencia para integrar modelos de lenguaje directamente en aplicaciones Laravel. Permite llamar a Claude, GPT-4, Gemini o Mistral desde el código PHP con una API unificada:'),
            $this->code(
                "use EchoLabs\\Prism\\Prism;\n" .
                "use EchoLabs\\Prism\\Enums\\Provider;\n\n" .
                "\$respuesta = Prism::text()\n" .
                "    ->using(Provider::Anthropic, 'claude-opus-4-7')\n" .
                "    ->withSystemPrompt('Eres un asistente de soporte técnico experto en Laravel.')\n" .
                "    ->withPrompt('¿Cómo implemento rate limiting por usuario en Laravel 13?')\n" .
                "    ->generate();\n\n" .
                "return response()->json(['reply' => \$respuesta->text]);"
            ),
            $this->p('En cuestión de minutos puedes tener un chatbot de soporte, un generador de contenido SEO, un clasificador de tickets o un extractor de datos integrado directamente en tu aplicación Laravel.'),

            $this->h3('3. Pruebas automáticas con IA'),
            $this->p('Una de las tareas más tediosas del desarrollo es escribir tests. Con IA, este proceso se acelera drásticamente. El flujo típico:'),
            $this->ul([
                'Describes el comportamiento esperado en lenguaje natural',
                'La IA genera los tests con Pest o PHPUnit',
                'Tú revisas, ajustas los edge cases y haces merge',
                'El CI/CD ejecuta la suite completa automáticamente',
            ]),
            $this->p('Equipos que antes tenían una cobertura de tests del 40 % ahora alcanzan el 85 % sin aumentar el tiempo de desarrollo.'),

            $this->h2('IA + Node.js: velocidad y escalabilidad al máximo'),
            $this->h3('1. El ecosistema AI-native de JavaScript'),
            $this->p('Node.js tiene una ventaja enorme: el ecosistema JavaScript fue de los primeros en adoptar herramientas de IA. Paquetes como <b>LangChain.js</b>, <b>Vercel AI SDK</b> y <b>@anthropic-ai/sdk</b> permiten construir aplicaciones con IA de manera muy rápida:'),
            $this->code(
                "import Anthropic from '@anthropic-ai/sdk';\n\n" .
                "const client = new Anthropic();\n\n" .
                "const mensaje = await client.messages.create({\n" .
                "    model: 'claude-opus-4-7',\n" .
                "    max_tokens: 1024,\n" .
                "    messages: [{\n" .
                "        role: 'user',\n" .
                "        content: 'Genera un endpoint REST en Express.js para gestión de usuarios'\n" .
                "    }]\n" .
                "});\n\n" .
                "console.log(mensaje.content[0].text);"
            ),

            $this->h3('2. Generación de APIs en tiempo récord'),
            $this->p('Con Node.js + IA, generar una API completa para un dominio de negocio nuevo puede llevar menos de una hora:'),
            $this->ul([
                '<b>Diseño del schema</b>: describes tus entidades, la IA sugiere la estructura de base de datos',
                '<b>Generación de endpoints</b>: CRUD completo con validación y manejo de errores',
                '<b>Documentación automática</b>: OpenAPI / Swagger generado desde el código',
                '<b>Tests de integración</b>: Jest + Supertest generados automáticamente',
                '<b>Docker + CI/CD</b>: configuración de despliegue lista para producción',
            ]),

            $this->h3('3. Procesamiento asíncrono con IA'),
            $this->p('Node.js brilla en tareas asíncronas de alto throughput. Combinado con IA, puedes construir pipelines de procesamiento inteligente:'),
            $this->ul([
                'Clasificación automática de emails y tickets de soporte',
                'Extracción de datos estructurados desde documentos PDF o imágenes',
                'Moderación de contenido en tiempo real',
                'Generación de resúmenes automáticos de reuniones o transcripciones',
                'Análisis de sentimientos en comentarios y reseñas',
            ]),

            $this->h2('Laravel y Node.js juntos: arquitectura híbrida'),
            $this->p('Una arquitectura muy popular en 2025 combina lo mejor de ambos mundos:'),
            $this->ul([
                '<b>Laravel (PHP)</b>: lógica de negocio principal, ORM Eloquent, jobs en cola, autenticación, panel de administración',
                '<b>Node.js</b>: WebSockets en tiempo real, procesamiento de streams de IA, microservicios de alta concurrencia',
                '<b>Comunicación</b>: Redis como message broker entre ambos backends',
                '<b>Frontend</b>: React / Vue consumiendo ambas APIs según la necesidad',
            ]),
            $this->p('Esta separación permite que cada tecnología haga lo que mejor sabe hacer, mientras la IA acelera el desarrollo de ambas capas.'),

            $this->h2('Flujo de trabajo moderno con IA: paso a paso'),
            $this->ul([
                '<b>Planificación</b>: usa un LLM para descomponer el proyecto en tareas técnicas concretas',
                '<b>Scaffolding</b>: genera la estructura base del proyecto con IA (modelos, migraciones, rutas)',
                '<b>Iteración rápida</b>: describe el comportamiento deseado, la IA implementa, tú revisas',
                '<b>Testing continuo</b>: la IA genera y mantiene la suite de tests actualizada',
                '<b>Code review asistido</b>: pide a la IA que revise PRs antes del merge humano',
                '<b>Documentación viva</b>: genera documentación técnica desde el código automáticamente',
            ]),

            $this->h2('Herramientas recomendadas en 2025'),
            $this->ul([
                '<b>Asistentes de terminal</b> — útiles para proyectos Laravel complejos',
                '<b>GitHub Copilot</b> — integración nativa en VS Code, JetBrains y Neovim',
                '<b>Cursor</b> — editor con IA integrada, excelente para refactorizaciones grandes',
                '<b>Laravel Prism</b> — integración de LLMs en aplicaciones Laravel',
                '<b>Vercel AI SDK</b> — streaming de respuestas IA en aplicaciones Node.js / Next.js',
                '<b>LangChain.js</b> — chains y agentes de IA para flujos complejos',
            ]),

            $this->h2('Conclusión'),
            $this->p('La combinación de <b>inteligencia artificial + Laravel + Node.js</b> representa la convergencia perfecta entre madurez del ecosistema, velocidad de desarrollo y capacidad de procesamiento. Los equipos que adopten estas prácticas hoy tendrán una ventaja competitiva difícil de alcanzar.'),
            $this->p('No se trata de reemplazar el criterio del desarrollador, sino de amplificarlo. La IA se encarga del código repetitivo; tú te encargas de las decisiones de arquitectura, la experiencia de usuario y la lógica de negocio que realmente importa.'),
            $this->p('<b>El futuro del desarrollo ya llegó. La pregunta es si tu equipo está aprovechándolo.</b>'),
        ]);
    }

    // ── ARTICLE EN ────────────────────────────────────────────────────────────

    private function articleEn(): array
    {
        return $this->doc([
            $this->p('<b>Artificial intelligence is no longer the future of software development — it is the present.</b> In 2025, teams that integrate AI tools into their Laravel and Node.js workflows are shipping projects in half the time, with fewer bugs and higher code quality. This article explores how to make that happen.'),

            $this->h2('The paradigm shift: from writing code to directing it'),
            $this->p("For decades, a developer's job was to write every line of code manually. Today, AI acts as a co-pilot that writes first drafts, suggests refactors, detects vulnerabilities and generates automated tests. The developer's role evolves from executor to technical director."),
            $this->p('This does not mean AI replaces programmers. Quite the opposite: it amplifies their capacity. A senior developer with AI can do the work of a team of three.'),

            $this->h2('AI + Laravel: the most powerful PHP stack in history'),
            $this->h3('1. Code generation with Claude and GPT-4'),
            $this->p('Code assistance tools, <b>GitHub Copilot</b> and <b>Cursor</b> understand the full context of a Laravel project. They can generate:'),
            $this->ul([
                'Migrations and Eloquent models from a natural-language description',
                'Complete REST controllers with validation, authorisation and error handling',
                'Test factories and seeders based on the real database schema',
                'Unit and integration tests with PHPUnit or Pest',
                'Production-ready custom Artisan commands',
            ]),
            $this->warning(
                'Important',
                'AI generates plausible code, not necessarily correct code. Always review business logic, validate migrations in staging and run the full test suite before merging.'
            ),

            $this->h3('2. Laravel Prism: the official package for LLMs'),
            $this->p('<b>Laravel Prism</b> is the go-to package for integrating language models directly into Laravel applications. It lets you call Claude, GPT-4, Gemini or Mistral from PHP with a unified API:'),
            $this->code(
                "use EchoLabs\\Prism\\Prism;\n" .
                "use EchoLabs\\Prism\\Enums\\Provider;\n\n" .
                "\$response = Prism::text()\n" .
                "    ->using(Provider::Anthropic, 'claude-opus-4-7')\n" .
                "    ->withSystemPrompt('You are a technical support expert specialised in Laravel.')\n" .
                "    ->withPrompt('How do I implement per-user rate limiting in Laravel 13?')\n" .
                "    ->generate();\n\n" .
                "return response()->json(['reply' => \$response->text]);"
            ),
            $this->p('In minutes you can have a support chatbot, an SEO content generator, a ticket classifier or a data extractor integrated directly into your Laravel application.'),

            $this->h3('3. Automated testing with AI'),
            $this->p('One of the most tedious development tasks is writing tests. AI accelerates this dramatically. The typical workflow:'),
            $this->ul([
                'Describe the expected behaviour in plain language',
                'AI generates the tests with Pest or PHPUnit',
                'You review, adjust edge cases and merge',
                'CI/CD runs the full suite automatically',
            ]),
            $this->p('Teams that previously had 40% test coverage now reach 85% without increasing development time.'),

            $this->h2('AI + Node.js: speed and scalability at full throttle'),
            $this->h3('1. The AI-native JavaScript ecosystem'),
            $this->p('Node.js has a huge advantage: the JavaScript ecosystem was among the first to embrace AI tooling. Packages like <b>LangChain.js</b>, <b>Vercel AI SDK</b> and <b>@anthropic-ai/sdk</b> make it very fast to build AI-powered applications:'),
            $this->code(
                "import Anthropic from '@anthropic-ai/sdk';\n\n" .
                "const client = new Anthropic();\n\n" .
                "const message = await client.messages.create({\n" .
                "    model: 'claude-opus-4-7',\n" .
                "    max_tokens: 1024,\n" .
                "    messages: [{\n" .
                "        role: 'user',\n" .
                "        content: 'Generate a REST endpoint in Express.js for user management'\n" .
                "    }]\n" .
                "});\n\n" .
                "console.log(message.content[0].text);"
            ),

            $this->h3('2. Generating APIs in record time'),
            $this->p('With Node.js + AI, generating a complete API for a new business domain can take less than an hour:'),
            $this->ul([
                '<b>Schema design</b>: describe your entities, AI suggests the database structure',
                '<b>Endpoint generation</b>: full CRUD with validation and error handling',
                '<b>Automatic documentation</b>: OpenAPI / Swagger generated from code',
                '<b>Integration tests</b>: Jest + Supertest generated automatically',
                '<b>Docker + CI/CD</b>: deployment configuration ready for production',
            ]),

            $this->h3('3. Async processing with AI'),
            $this->p('Node.js shines for high-throughput async tasks. Combined with AI, you can build intelligent processing pipelines:'),
            $this->ul([
                'Automatic classification of emails and support tickets',
                'Extraction of structured data from PDF documents or images',
                'Real-time content moderation',
                'Automatic generation of meeting or transcript summaries',
                'Sentiment analysis on comments and reviews',
            ]),

            $this->h2('Laravel and Node.js together: hybrid architecture'),
            $this->p('A very popular 2025 architecture combines the best of both worlds:'),
            $this->ul([
                '<b>Laravel (PHP)</b>: core business logic, Eloquent ORM, queue jobs, authentication, admin panel',
                '<b>Node.js</b>: real-time WebSockets, AI stream processing, high-concurrency microservices',
                '<b>Communication</b>: Redis as message broker between both backends',
                '<b>Frontend</b>: React / Vue consuming both APIs as needed',
            ]),
            $this->p('This separation lets each technology do what it does best while AI accelerates development across both layers.'),

            $this->h2('Modern AI-powered workflow: step by step'),
            $this->ul([
                '<b>Planning</b>: use an LLM to break the project into concrete technical tasks',
                '<b>Scaffolding</b>: generate the project skeleton with AI (models, migrations, routes)',
                '<b>Rapid iteration</b>: describe the desired behaviour, AI implements, you review',
                '<b>Continuous testing</b>: AI generates and keeps the test suite up to date',
                '<b>Assisted code review</b>: ask AI to review PRs before human merge',
                '<b>Living documentation</b>: generate technical docs from code automatically',
            ]),

            $this->h2('Recommended tools in 2025'),
            $this->ul([
                '<b>Terminal assistants</b> — useful for complex Laravel projects',
                '<b>GitHub Copilot</b> — native integration in VS Code, JetBrains and Neovim',
                '<b>Cursor</b> — AI-native editor, excellent for large-scale refactors',
                '<b>Laravel Prism</b> — LLM integration for Laravel applications',
                '<b>Vercel AI SDK</b> — streaming AI responses in Node.js / Next.js apps',
                '<b>LangChain.js</b> — chains and AI agents for complex workflows',
            ]),

            $this->h2('Conclusion'),
            $this->p('The combination of <b>artificial intelligence + Laravel + Node.js</b> represents the perfect convergence of ecosystem maturity, development speed and processing power. Teams that adopt these practices today will have a competitive advantage that is very hard to close.'),
            $this->p("It is not about replacing the developer's judgement — it is about amplifying it. AI handles the repetitive code; you handle the architecture decisions, user experience and business logic that really matter."),
            $this->p('<b>The future of development is already here. The question is whether your team is taking advantage of it.</b>'),
        ]);
    }

    // ── Builder helpers ───────────────────────────────────────────────────────

    private function doc(array $blocks): array
    {
        return ['time' => time() * 1000, 'version' => '2.28.0', 'blocks' => $blocks];
    }

    private function h2(string $text): array
    {
        return ['type' => 'header', 'data' => ['text' => $text, 'level' => 2]];
    }

    private function h3(string $text): array
    {
        return ['type' => 'header', 'data' => ['text' => $text, 'level' => 3]];
    }

    private function p(string $text): array
    {
        return ['type' => 'paragraph', 'data' => ['text' => $text]];
    }

    private function ul(array $items): array
    {
        return ['type' => 'list', 'data' => ['style' => 'unordered', 'items' => $items]];
    }

    private function code(string $code): array
    {
        return ['type' => 'code', 'data' => ['code' => $code]];
    }

    private function warning(string $title, string $message): array
    {
        return ['type' => 'warning', 'data' => ['title' => $title, 'message' => $message]];
    }
}
