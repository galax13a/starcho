<?php

namespace App\Services;

use App\Models\AiSetting;
use App\Models\Post;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Laravel\Ai\Ai;
use RuntimeException;
use function Laravel\Ai\agent;

class PageAiContentService
{
    public function generate(Post $post, string $locale, string $prompt, string $currentContentJson, ?string $model = null, string $provider = 'openai', string $target = 'content'): string
    {
        $settings = AiSetting::singleton();

        if (! $settings->enabled || ! $settings->hasProviderKey($provider)) {
            throw new RuntimeException('Configura y habilita AI antes de generar contenido.');
        }

        Config::set('ai.default', $provider);
        Config::set("ai.providers.{$provider}.key", $settings->keyForProvider($provider));
        Ai::forgetInstance($provider);

        $title = $post->getTranslation('title', $locale, false)
            ?: collect($post->getTranslations('title'))->filter()->first()
            ?: 'Página sin título';

        $currentText = str($this->editorJsonToText($currentContentJson))
            ->limit(6000, "\n\n[Contenido actual recortado para reducir consumo de tokens]")
            ->toString();

        $instructions = $this->instructionsFor($target);

        $message = <<<PROMPT
Idioma activo: {$locale}
Página: {$title}

Contenido actual:
{$currentText}

Instrucción del usuario:
{$prompt}
PROMPT;

        $response = agent(instructions: $instructions)->prompt(
            $message,
            provider: $provider,
            model: filled($model) ? $model : $settings->default_model,
            timeout: 90,
        );

        return trim($response->text);
    }

    public function generatePageBlueprint(string $description, array $locales, ?string $model = null, string $provider = 'openai'): array
    {
        $settings = AiSetting::singleton();

        if (! $settings->enabled || ! $settings->hasProviderKey($provider)) {
            throw new RuntimeException('Configura y habilita AI antes de generar páginas.');
        }

        Config::set('ai.default', $provider);
        Config::set("ai.providers.{$provider}.key", $settings->keyForProvider($provider));
        Ai::forgetInstance($provider);

        $localeList = collect($locales)->filter()->values()->join(', ');

        $instructions = <<<'PROMPT'
Eres un arquitecto editorial para un CMS Laravel multi idioma.
Genera una pagina completa lista para guardar.
Responde unicamente JSON valido, sin markdown y sin bloque de codigo.
Estructura exacta:
{
  "locales": {
    "es": {
      "title": "...",
      "excerpt": "...",
      "seo_title": "...",
      "seo_description": "...",
      "seo_keywords": "keyword, keyword",
      "og_title": "...",
      "og_description": "...",
      "sections": [
        {"heading": "...", "body": "...", "bullets": ["...", "..."]}
      ]
    }
  }
}
Incluye todas las claves de idioma solicitadas. Haz contenido claro, publicable y con buena estructura. Limita seo_title a 70 caracteres, seo_description a 160 y og_description a 300.
PROMPT;

        $message = <<<PROMPT
Idiomas activos: {$localeList}

Descripcion de la pagina que se debe crear:
{$description}
PROMPT;

        $response = agent(instructions: $instructions)->prompt(
            $message,
            provider: $provider,
            model: filled($model) ? $model : $settings->default_model,
            timeout: 120,
        );

        $blueprint = $this->parseJsonResponse($response->text);

        if (! isset($blueprint['locales']) || ! is_array($blueprint['locales'])) {
            throw new RuntimeException('AI no devolvió una estructura válida para crear la página.');
        }

        return $this->normalizePageBlueprint($blueprint, $locales, $description);
    }

    public function generatePostBlueprint(string $description, array $locales, ?string $model = null, string $provider = 'openai'): array
    {
        return $this->generatePageBlueprint(
            "Crea un post de blog completo. Incluye introduccion, desarrollo, secciones claras, bullets utiles y cierre. Tema: {$description}",
            $locales,
            $model,
            $provider,
        );
    }

    public function generateFolioPageEdit(string $description, string $currentHtml, array $seoRows, array $locales, ?string $model = null, string $provider = 'openai'): array
    {
        $settings = AiSetting::singleton();

        if (! $settings->enabled || ! $settings->hasProviderKey($provider)) {
            throw new RuntimeException('Configura y habilita AI antes de generar contenido.');
        }

        Config::set('ai.default', $provider);
        Config::set("ai.providers.{$provider}.key", $settings->keyForProvider($provider));
        Ai::forgetInstance($provider);

        $localeList = collect($locales)->filter()->values()->join(', ');
        $current = Str::limit(strip_tags($currentHtml), 5000, "\n\n[HTML actual recortado]");
        $seo = json_encode($seoRows, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $instructions = <<<'PROMPT'
Eres un editor experto de paginas Laravel Folio.
Responde unicamente JSON valido, sin markdown ni bloque de codigo.
Estructura exacta:
{
  "html": "<section>...</section>",
  "seo": {
    "es": {
      "title": "...",
      "description": "...",
      "meta_keywords": "...",
      "og_title": "...",
      "og_description": "..."
    }
  }
}
El campo html debe ser HTML de body listo para reemplazar el contenido visual editable. Puede usar clases Tailwind si ayudan. No incluyas <html>, <head> ni <body>.
Incluye SEO para todos los idiomas solicitados. Limita title a 180, description a 300, og_title a 180 y og_description a 300.
PROMPT;

        $message = <<<PROMPT
Idiomas activos: {$localeList}

SEO actual:
{$seo}

Texto actual de la pagina:
{$current}

Instruccion:
{$description}
PROMPT;

        $response = agent(instructions: $instructions)->prompt(
            $message,
            provider: $provider,
            model: filled($model) ? $model : $settings->default_model,
            timeout: 120,
        );

        $payload = $this->parseJsonResponse($response->text);

        if (! isset($payload['html']) || ! is_string($payload['html'])) {
            throw new RuntimeException('AI no devolvió HTML válido para el editor.');
        }

        return [
            'html' => trim($payload['html']),
            'seo' => is_array($payload['seo'] ?? null) ? $payload['seo'] : [],
        ];
    }

    public function generateSitePageSeo(string $path, string $currentHtml, array $locales, ?string $model = null, string $provider = 'openai'): array
    {
        $settings = AiSetting::singleton();

        if (! $settings->enabled || ! $settings->hasProviderKey($provider)) {
            throw new RuntimeException('Configura y habilita AI antes de generar SEO.');
        }

        Config::set('ai.default', $provider);
        Config::set("ai.providers.{$provider}.key", $settings->keyForProvider($provider));
        Ai::forgetInstance($provider);

        $localeList = collect($locales)->filter()->values()->join(', ');
        $current = Str::limit(strip_tags($currentHtml), 6000, "\n\n[Contenido recortado]");

        $instructions = <<<'PROMPT'
Eres especialista SEO para paginas Laravel Folio.
Responde unicamente JSON valido, sin markdown ni bloque de codigo.
La estructura exacta debe ser:
{
  "seo": {
    "es": {
      "title": "...",
      "description": "...",
      "meta_keywords": "...",
      "og_title": "...",
      "og_description": "..."
    }
  }
}
Incluye todos los idiomas solicitados. title maximo 180, description maximo 300, og_title maximo 180, og_description maximo 300.
PROMPT;

        $message = <<<PROMPT
Path: {$path}
Idiomas activos: {$localeList}

Contenido actual:
{$current}
PROMPT;

        $response = agent(instructions: $instructions)->prompt(
            $message,
            provider: $provider,
            model: filled($model) ? $model : $settings->default_model,
            timeout: 90,
        );

        $payload = $this->parseJsonResponse($response->text);

        return is_array($payload['seo'] ?? null) ? $payload['seo'] : [];
    }

    private function instructionsFor(string $target): string
    {
        return match ($target) {
            'excerpt' => <<<'PROMPT'
Eres un asistente editorial para un CMS Laravel.
Genera un extracto breve, claro y publicable para listados, cards y buscadores.
Responde solo con el extracto final, sin markdown, sin comillas y sin explicaciones.
Usa el idioma solicitado y conserva la idea principal del contenido actual.
PROMPT,
            'seo' => <<<'PROMPT'
Eres un especialista SEO para un CMS Laravel.
Analiza el contenido y responde unicamente JSON valido, sin markdown, sin texto adicional y sin envolver en bloque de codigo.
El JSON debe tener estas claves exactas: seo_title, seo_description, seo_keywords, og_title, og_description.
Limites: seo_title maximo 70 caracteres, seo_description maximo 160 caracteres, seo_keywords como lista separada por comas, og_title breve y og_description persuasiva.
Usa el idioma solicitado y no inventes datos que no esten sustentados por el contenido.
PROMPT,
            default => <<<'PROMPT'
Eres un asistente editorial para un CMS Laravel. Escribe contenido listo para pegar en Editor.js.
Responde solo con el contenido final, sin explicaciones, sin markdown decorativo y sin envolver en comillas.
Usa el idioma solicitado, respeta el objetivo del usuario y conserva datos importantes del contenido actual cuando aplique.
PROMPT,
        };
    }

    private function parseJsonResponse(string $text): array
    {
        $raw = trim(preg_replace('/^```(?:json)?|```$/i', '', trim($text)) ?? '');
        $decoded = json_decode($raw, true);

        if (is_array($decoded)) {
            return $decoded;
        }

        $start = strpos($raw, '{');
        $end = strrpos($raw, '}');

        if ($start !== false && $end !== false && $end > $start) {
            $decoded = json_decode(substr($raw, $start, $end - $start + 1), true);
        }

        return is_array($decoded) ? $decoded : [];
    }

    private function normalizePageBlueprint(array $blueprint, array $locales, string $description): array
    {
        $fallbackTitle = Str::headline(Str::limit($description, 60, ''));

        return collect($locales)
            ->mapWithKeys(function (string $locale) use ($blueprint, $fallbackTitle, $description): array {
                $data = $blueprint['locales'][$locale] ?? [];
                $title = trim((string) ($data['title'] ?? $fallbackTitle));
                $sections = is_array($data['sections'] ?? null) ? $data['sections'] : [];

                if ($sections === []) {
                    $sections = [[
                        'heading' => $title,
                        'body' => trim((string) ($data['excerpt'] ?? $description)),
                        'bullets' => [],
                    ]];
                }

                return [$locale => [
                    'title' => $title,
                    'excerpt' => trim((string) ($data['excerpt'] ?? Str::limit(strip_tags($description), 180))),
                    'seo_title' => Str::limit(trim((string) ($data['seo_title'] ?? $title)), 70, ''),
                    'seo_description' => Str::limit(trim((string) ($data['seo_description'] ?? ($data['excerpt'] ?? $description))), 160, ''),
                    'seo_keywords' => trim((string) ($data['seo_keywords'] ?? '')),
                    'og_title' => trim((string) ($data['og_title'] ?? $title)),
                    'og_description' => Str::limit(trim((string) ($data['og_description'] ?? ($data['excerpt'] ?? $description))), 300, ''),
                    'content' => $this->sectionsToEditorJson($sections),
                ]];
            })
            ->all();
    }

    private function sectionsToEditorJson(array $sections): string
    {
        $blocks = [];

        foreach ($sections as $section) {
            if (! is_array($section)) {
                continue;
            }

            $heading = trim(strip_tags((string) ($section['heading'] ?? '')));
            $body = trim((string) ($section['body'] ?? ''));
            $bullets = is_array($section['bullets'] ?? null) ? $section['bullets'] : [];

            if ($heading !== '') {
                $blocks[] = ['type' => 'header', 'data' => ['text' => e($heading), 'level' => 2]];
            }

            if ($body !== '') {
                foreach (preg_split("/\n{2,}/", $body) ?: [] as $paragraph) {
                    $paragraph = trim($paragraph);
                    if ($paragraph !== '') {
                        $blocks[] = ['type' => 'paragraph', 'data' => ['text' => nl2br(e($paragraph), false)]];
                    }
                }
            }

            $items = collect($bullets)
                ->map(fn ($item) => trim(strip_tags((string) $item)))
                ->filter()
                ->map(fn ($item) => e($item))
                ->values()
                ->all();

            if ($items !== []) {
                $blocks[] = ['type' => 'list', 'data' => ['style' => 'unordered', 'items' => $items]];
            }
        }

        return json_encode([
            'time' => (int) round(microtime(true) * 1000),
            'blocks' => $blocks,
            'version' => '2.30.0',
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function editorJsonToText(string $json): string
    {
        $data = json_decode($json, true);

        if (! is_array($data)) {
            return '';
        }

        $metadata = collect([
            'Titulo' => $data['title'] ?? null,
            'Extracto' => $data['excerpt'] ?? null,
            'SEO title' => $data['seo_title'] ?? null,
            'SEO description' => $data['seo_description'] ?? null,
            'SEO keywords' => $data['seo_keywords'] ?? null,
            'OG title' => $data['og_title'] ?? null,
            'OG description' => $data['og_description'] ?? null,
        ])
            ->filter(fn ($value) => filled($value))
            ->map(fn ($value, $key) => "{$key}: {$value}")
            ->join("\n");

        $blocks = $data['content']['blocks'] ?? $data['blocks'] ?? [];
        $content = collect($blocks)
            ->map(function (array $block): ?string {
                $type = (string) ($block['type'] ?? '');
                $data = $block['data'] ?? [];

                return match ($type) {
                    'header' => strip_tags((string) ($data['text'] ?? '')),
                    'paragraph' => strip_tags((string) ($data['text'] ?? '')),
                    'quote' => strip_tags((string) ($data['text'] ?? '')),
                    'warning' => trim(strip_tags((string) ($data['title'] ?? '') . "\n" . (string) ($data['message'] ?? ''))),
                    'list' => collect($data['items'] ?? [])->map(fn ($item) => '- ' . strip_tags(is_array($item) ? (string) ($item['content'] ?? '') : (string) $item))->join("\n"),
                    'starchoHtml' => trim(strip_tags((string) ($data['html'] ?? ''))),
                    default => null,
                };
            })
            ->filter()
            ->join("\n\n");

        return trim(collect([$metadata, $content])->filter()->join("\n\n"));
    }
}
