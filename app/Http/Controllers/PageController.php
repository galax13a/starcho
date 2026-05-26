<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\SiteLanguage;
use Illuminate\View\View;

class PageController extends Controller
{
    public function show(string $locale, string $slug): View
    {
        $page = Post::where('type', Post::TYPE_PAGE)
            ->where(function ($q) use ($slug, $locale) {
                $q->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(slug, '$.$locale')) = ?", [$slug]);
                foreach (SiteLanguage::activeCodes() as $code) {
                    if ($code !== $locale) {
                        $q->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(slug, '$.$code')) = ?", [$slug]);
                    }
                }
            })
            ->where('status', Post::STATUS_PUBLISHED)
            ->where(fn ($q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()))
            ->first();

        if (!$page) {
            abort(404);
        }

        $activeCodes    = SiteLanguage::activeCodes();
        $fallbackLocale = null;
        $usingFallback  = false;

        $hasContentInLocale = filled($page->getTranslation('content', $locale, false));

        if (!$hasContentInLocale) {
            foreach ($activeCodes as $code) {
                if ($code !== $locale && filled($page->getTranslation('content', $code, false))) {
                    $fallbackLocale = $code;
                    $usingFallback  = true;
                    app()->setLocale($code);
                    break;
                }
            }
        }

        // Per-locale canonical URLs for the header language switcher
        $langUrls = [];
        foreach (SiteLanguage::active() as $lang) {
            $langUrls[$lang->code] = $page->publicUrl($lang->code);
        }

        return view('page.show', compact('page', 'locale', 'fallbackLocale', 'usingFallback', 'langUrls'));
    }
}
