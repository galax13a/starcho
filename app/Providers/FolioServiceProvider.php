<?php

namespace App\Providers;

use Laravel\Folio\Folio;

class FolioServiceProvider extends \Laravel\Folio\FolioServiceProvider
{
    public function boot(): void
    {
        parent::boot();

        Folio::path(resource_path('views/pages'))->middleware([
            '*' => [],
        ]);
    }

    /**
     * Folio 1.1.18 declares both Symfony's AsCommand attribute and the legacy
     * command name. Laravel 13.25 then registers folio:list twice. The app does
     * not need Folio's maintenance commands, so keep its routing services while
     * skipping only those console registrations.
     */
    protected function registerCommands(): void {}
}
