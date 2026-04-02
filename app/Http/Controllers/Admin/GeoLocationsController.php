<?php

namespace App\Http\Controllers\Admin;

use App\Models\UserGeoLocation;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class GeoLocationsController
{
    public function index(): View
    {
        $topCountryRow = UserGeoLocation::query()
            ->select('country', DB::raw('count(*) as total'))
            ->whereNotNull('country')
            ->groupBy('country')
            ->orderByRaw('total DESC')
            ->first();

        $topCityRow = UserGeoLocation::query()
            ->select('city', DB::raw('count(*) as total'))
            ->whereNotNull('city')
            ->groupBy('city')
            ->orderByRaw('total DESC')
            ->first();

        $topIspRow = UserGeoLocation::query()
            ->select('isp', DB::raw('count(*) as total'))
            ->whereNotNull('isp')
            ->where('isp', '!=', '')
            ->groupBy('isp')
            ->orderByRaw('total DESC')
            ->first();

        $latestCapture = UserGeoLocation::query()
            ->latest('captured_at')
            ->first();

        $byCountry = UserGeoLocation::query()
            ->select('country', DB::raw('count(*) as total'))
            ->whereNotNull('country')
            ->groupBy('country')
            ->orderByRaw('total DESC')
            ->limit(10)
            ->pluck('total', 'country');

        $byCity = UserGeoLocation::query()
            ->select('city', DB::raw('count(*) as total'))
            ->whereNotNull('city')
            ->groupBy('city')
            ->orderByRaw('total DESC')
            ->limit(10)
            ->pluck('total', 'city');

        $timeline = UserGeoLocation::query()
            ->select(DB::raw('DATE(captured_at) as date'), DB::raw('count(*) as total'))
            ->where('captured_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total', 'date');

        return view('admin.geolocations.index', [
            'byCountry' => $byCountry,
            'byCity' => $byCity,
            'timeline' => $timeline,
            'totalRecords' => UserGeoLocation::count(),
            'totalCountries' => UserGeoLocation::select('country')->distinct()->count(),
            'totalCities' => UserGeoLocation::select('city')->distinct()->count(),
            'totalUsers' => UserGeoLocation::select('user_id')->distinct()->count(),
            'totalIsps' => UserGeoLocation::query()->whereNotNull('isp')->where('isp', '!=', '')->distinct('isp')->count('isp'),
            'topCountry' => $topCountryRow?->country,
            'topCountryCount' => (int) ($topCountryRow?->total ?? 0),
            'topCity' => $topCityRow?->city,
            'topCityCount' => (int) ($topCityRow?->total ?? 0),
            'topIsp' => $topIspRow?->isp,
            'topIspCount' => (int) ($topIspRow?->total ?? 0),
            'latestCaptureAt' => $latestCapture?->captured_at,
        ]);
    }

    public function show(UserGeoLocation $geolocation): View
    {
        $userTimeline = $geolocation->user
            ->geolocations()
            ->orderBy('captured_at', 'desc')
            ->get();

        return view('admin.geolocations.show', [
            'geolocation' => $geolocation,
            'userTimeline' => $userTimeline,
        ]);
    }
}
