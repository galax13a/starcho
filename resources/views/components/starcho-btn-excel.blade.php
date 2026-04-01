@props([
    'action' => 'export', // 'export' o 'import'
    'module' => 'notes', // 'notes', 'tasks', 'contacts'
    'section' => 'app', // 'app' o 'admin'
    'bulkWireMethod' => null, // Si se define, al haber selección llama $wire.método en vez de navegar
    'requireSelection' => false, // Si está en true, no exporta todo cuando no hay selección
    'requireSelectionMessage' => null, // Mensaje de alerta cuando no hay selección
])

@php
    $isImport = $action === 'import';
    $isExport = $action === 'export';
    $requireSelectionMessageJs = json_encode(
        $requireSelectionMessage ?? __('admin_ui.common.select_item_to_export'),
        JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT
    );
    
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

@if ($isExport && $bulkWireMethod)
    {{-- Modo inteligente: exporta seleccionados; opcionalmente exige selección previa --}}
    <flux:button
        type="button"
        x-on:click="if (typeof selected !== 'undefined' && selected.length > 0) { $wire.{{ $bulkWireMethod }}(); } else if ({{ $requireSelection ? 'true' : 'false' }}) { window.Starcho.alert('warning', {{ $requireSelectionMessageJs }}); } else { window.location.href='{{ $href }}'; }"
        variant="ghost"
        size="sm"
        icon="{{ $icon }}"
        tooltip="{{ $displayTitle }}"
        aria-label="{{ $displayTitle }}"
        class="{{ $baseClass }}"
    />
@elseif ($isExport)
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