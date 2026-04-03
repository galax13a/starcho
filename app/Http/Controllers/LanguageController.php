<?php

namespace App\Http\Controllers;

use App\Models\SiteLanguage;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LanguageController extends Controller
{
    public function switch(Request $request, string $locale): RedirectResponse
    {
        $supported = SiteLanguage::activeCodes();

        if (! in_array($locale, $supported, true)) {
            return redirect()->back();
        }

        session(['locale' => $locale]);
        app()->setLocale($locale);

        if (Auth::check()) {
            $user = Auth::user();
            if ($user instanceof User) {
                $user->update(['locale' => $locale]);
            }
        }

        return redirect()->back();
    }
}
