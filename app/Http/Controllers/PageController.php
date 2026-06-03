<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\SiteLanguage;
use App\Models\SiteSetting;
use Illuminate\View\View;

class PageController extends Controller
{
    public function home(): View
    {
        $settings = SiteSetting::cached();

        if (($settings?->home_source ?? 'folio') !== 'dynamic' || ! $settings?->home_page_id) {
            return view('pages.index');
        }

        $page = Post::query()
            ->where('type', Post::TYPE_PAGE)
            ->where('id', $settings->home_page_id)
            ->where('status', Post::STATUS_PUBLISHED)
            ->where(fn ($q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()))
            ->first();

        if (! $page) {
            return view('pages.index');
        }

        $page->increment('views_count');

        $locale = app()->getLocale();
        $fallbackLocale = null;
        $usingFallback = false;
        $langUrls = [];

        foreach (SiteLanguage::active() as $lang) {
            $langUrls[$lang->code] = url('/');
        }

        return view('page.show', compact('page', 'locale', 'fallbackLocale', 'usingFallback', 'langUrls'));
    }

    public function show(string $locale, string $slug): View
    {
        $page = Post::where('type', Post::TYPE_PAGE)
            ->whereSlug($slug)
            ->where('status', Post::STATUS_PUBLISHED)
            ->where(fn ($q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()))
            ->first();

        if (!$page) {
            abort(404);
        }

        $page->increment('views_count');

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
