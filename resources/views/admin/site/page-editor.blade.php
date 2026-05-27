@extends('layouts.admin-editor-page')

@section('content')
    <div class="mb-6 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ __('admin_ui.site.visual_editor.heading') }}</h1>
            <p class="text-zinc-500">{{ $page['path'] }} · {{ $page['relative_path'] }}</p>
        </div>

        <div class="flex gap-2">
            <a href="{{ route('admin.site.index') }}" class="inline-flex items-center rounded-lg border border-zinc-300 dark:border-zinc-700 px-4 py-2 text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('admin_ui.common.back') }}</a>
            <a href="{{ $page['preview_url'] }}" target="_blank" class="inline-flex items-center rounded-lg border border-blue-300 dark:border-blue-700 px-4 py-2 text-sm font-medium text-blue-700 dark:text-blue-300">{{ __('admin_ui.site.form.page_preview') }}</a>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-700/50 dark:bg-emerald-900/20 dark:text-emerald-300">
            {{ session('success') }}
        </div>
    @endif

    @if(!$visualData['supported'])
        <div class="mb-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700 dark:border-amber-700/50 dark:bg-amber-900/20 dark:text-amber-300">
            {{ __('admin_ui.site.visual_editor.unsupported') }}
        </div>
    @endif

    <livewire:admin.site-page-editor :path="$path" />
@endsection
