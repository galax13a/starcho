<?php

namespace App\Livewire\Concerns;

trait AuthorizesAdminPanel
{
    public function bootAuthorizesAdminPanel(): void
    {
        $user = auth()->user();

        abort_unless(
            $user
                && $user->hasAnyRole(['root', 'admin'])
                && $user->can('view-admin'),
            403
        );
    }
}
