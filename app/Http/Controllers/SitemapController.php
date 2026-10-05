<?php

namespace App\Http\Controllers;

use App\Services\SitemapService;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(SitemapService $sitemaps): Response
    {
        // A generated public copy is served directly when it is still fresh.
        $static = public_path('sitemap.xml');
        if (file_exists($static) && filemtime($static) > time() - 86400) {
            $contents = file_get_contents($static);

            if (is_string($contents)) {
                return response($contents, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
            }
        }

        // Cache the generated XML and refresh the file so expiry never causes every request to rebuild it.
        $xml = $sitemaps->cachedXml();
        $sitemaps->writePublicCopy($xml);

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }
}
