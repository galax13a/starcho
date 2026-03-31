@props([
    'action' => 'export', // 'export' o 'import'
    'module' => 'notes', // 'notes', 'tasks', 'contacts'
    'section' => 'app', // 'app' o 'admin'
])

@php
    $isImport = $action === 'import';
    $isExport = $action === 'export';
    
    // Textos en español e inglés
    $translations = [
        'es' => [
            'import' => 'Importar',
            'export' => 'Exportar',
        ],
        'en' => [
            'import' => 'Import',
            'export' => 'Export',
        ],
    ];
    
    $lang = app()->getLocale() === 'es' ? 'es' : 'en';
    $displayTitle = $translations[$lang][$action] ?? $action;
    
    // Icono y color según acción
    $icon = $isImport ? 'arrow-up-tray' : 'arrow-down-tray';
    $colorClass = $isImport ? '!text-sky-600 hover:!text-sky-500 dark:!text-sky-400 dark:hover:!text-sky-300' : '!text-emerald-600 hover:!text-emerald-500 dark:!text-emerald-400 dark:hover:!text-emerald-300';
    $baseClass = 'relative items-center font-medium justify-center gap-2 whitespace-nowrap disabled:opacity-75 dark:disabled:opacity-75 disabled:cursor-default disabled:pointer-events-none justify-center h-8 text-sm rounded-md w-8 inline-flex bg-transparent hover:bg-zinc-800/5 dark:hover:bg-white/15 text-zinc-800 dark:text-white !size-8 !min-w-8 !px-0 border border-zinc-200 dark:border-zinc-700 ' . $colorClass;
    
    if ($isExport) {
        $moduleName = str_replace('-', '_', $module);

        if ($section === 'admin') {
            $primaryRoute = "admin.{$moduleName}.export";
            $fallbackRoute = "admin.data.export.{$moduleName}";
        } else {
            $primaryRoute = "app.{$moduleName}.export";
            $fallbackRoute = "app.data.export.{$moduleName}";
        }

        $href = \Illuminate\Support\Facades\Route::has($primaryRoute)
            ? route($primaryRoute)
            : route($fallbackRoute);
    } else {
        $moduleCapital = ucwords(str_replace('-', ' ', $module));
        $moduleCapital = str_replace(' ', '', $moduleCapital);
        $dispatchEvent = $section === 'admin' 
            ? "openAdmin{$moduleCapital}Import"
            : "open{$moduleCapital}Import";
    }
@endphp

@if ($isExport)
    <flux:button
        :href="$href"
        variant="ghost"
        size="sm"
        icon="{{ $icon }}"
        tooltip="{{ $displayTitle }}"
        aria-label="{{ $displayTitle }}"
        class="{{ $baseClass }}"
    />
@else
    <flux:button
        type="button"
        wire:click="$dispatch('{{ $dispatchEvent }}')"
        variant="ghost"
        size="sm"
        icon="{{ $icon }}"
        tooltip="{{ $displayTitle }}"
        aria-label="{{ $displayTitle }}"
        class="{{ $baseClass }}"
    />
@endif