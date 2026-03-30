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

    <div id="starcho-editor-loading" class="mb-4 rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-700 dark:border-blue-700/50 dark:bg-blue-900/20 dark:text-blue-300">
        Cargando editor de pagina...
    </div>

    <form id="starcho-page-editor-form"
        method="POST"
        action="{{ route('admin.site.pages.update') }}"
        class="space-y-6 hidden"
        data-supported="{{ $visualData['supported'] ? '1' : '0' }}">
        @csrf
        @method('PUT')

        <input type="hidden" name="path" value="{{ $page['path'] }}">
        <input type="hidden" name="visual_html" id="starcho-visual-html" value="">
        <textarea id="starcho-initial-html" class="hidden">{{ base64_encode($visualData['html'] ?? '') }}</textarea>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-5 shadow-sm space-y-4">
                <div id="starcho-editor-toolbar" class="flex flex-wrap gap-2 {{ $visualData['supported'] ? '' : 'hidden' }}">
                    <button type="button" data-cmd="bold" class="rounded-lg border border-zinc-300 dark:border-zinc-700 px-3 py-1.5 text-xs font-semibold">B</button>
                    <button type="button" data-cmd="italic" class="rounded-lg border border-zinc-300 dark:border-zinc-700 px-3 py-1.5 text-xs font-semibold italic">I</button>
                    <button type="button" data-cmd="underline" class="rounded-lg border border-zinc-300 dark:border-zinc-700 px-3 py-1.5 text-xs font-semibold underline">U</button>
                    <button type="button" data-cmd="insertUnorderedList" class="rounded-lg border border-zinc-300 dark:border-zinc-700 px-3 py-1.5 text-xs font-semibold">UL</button>
                    <button type="button" data-cmd="insertOrderedList" class="rounded-lg border border-zinc-300 dark:border-zinc-700 px-3 py-1.5 text-xs font-semibold">OL</button>
                    <button type="button" data-cmd="formatBlock" data-value="<h2>" class="rounded-lg border border-zinc-300 dark:border-zinc-700 px-3 py-1.5 text-xs font-semibold">H2</button>
                    <button type="button" data-cmd="formatBlock" data-value="<p>" class="rounded-lg border border-zinc-300 dark:border-zinc-700 px-3 py-1.5 text-xs font-semibold">P</button>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('admin_ui.site.visual_editor.canvas') }}</label>

                    <div id="starcho-visual-editor"
                        class="{{ $visualData['supported'] ? '' : 'hidden' }} min-h-[540px] rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white text-zinc-900 px-5 py-4 shadow-inner overflow-auto prose max-w-none"
                        contenteditable="true"></div>

                    <textarea id="starcho-code-fallback" rows="24" disabled class="{{ $visualData['supported'] ? 'hidden' : '' }} w-full rounded-xl border border-zinc-300 dark:border-zinc-700 bg-zinc-950 text-zinc-100 px-4 py-3 font-mono text-xs leading-6">{{ $page['blade_content'] }}</textarea>
                </div>
            </div>

            <div class="space-y-6">
                <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-5 shadow-sm space-y-4">
                    <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ __('admin_ui.site.visual_editor.preview') }}</h2>
                    <iframe id="starcho-editor-preview" class="h-[380px] w-full rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white"></iframe>
                </div>

                <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-5 shadow-sm space-y-4">
                    <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ __('admin_ui.site.sections.pages_seo') }}</h2>

                    @foreach($seoRows as $index => $row)
                        <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 p-4 space-y-3">
                            <div class="text-xs font-semibold uppercase tracking-wide text-zinc-500">{{ $row['locale'] }}</div>
                            <input type="hidden" name="page_settings[{{ $index }}][locale]" value="{{ $row['locale'] }}">
                            <input type="hidden" name="page_settings[{{ $index }}][path]" value="{{ $row['path'] }}">

                            <div class="grid grid-cols-1 gap-3">
                                <input class="w-full rounded border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 px-3 py-2 text-sm" name="page_settings[{{ $index }}][title]" value="{{ old('page_settings.'.$index.'.title', $row['title']) }}" placeholder="{{ __('admin_ui.site.form.page_title') }}">
                                <input class="w-full rounded border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 px-3 py-2 text-sm" name="page_settings[{{ $index }}][description]" value="{{ old('page_settings.'.$index.'.description', $row['description']) }}" placeholder="{{ __('admin_ui.site.form.page_description') }}">
                                <input class="w-full rounded border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 px-3 py-2 text-sm" name="page_settings[{{ $index }}][meta_keywords]" value="{{ old('page_settings.'.$index.'.meta_keywords', $row['meta_keywords']) }}" placeholder="{{ __('admin_ui.site.form.page_keywords') }}">
                                <input class="w-full rounded border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 px-3 py-2 text-sm" name="page_settings[{{ $index }}][og_title]" value="{{ old('page_settings.'.$index.'.og_title', $row['og_title']) }}" placeholder="{{ __('admin_ui.site.form.og_title') }}">
                                <input class="w-full rounded border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 px-3 py-2 text-sm" name="page_settings[{{ $index }}][og_description]" value="{{ old('page_settings.'.$index.'.og_description', $row['og_description']) }}" placeholder="{{ __('admin_ui.site.form.og_description') }}">
                            </div>

                            <div class="grid grid-cols-3 gap-3 text-sm text-zinc-700 dark:text-zinc-300">
                                <label class="flex items-center gap-2"><input type="hidden" name="page_settings[{{ $index }}][robots_index]" value="0"><input type="checkbox" name="page_settings[{{ $index }}][robots_index]" value="1" @checked(old('page_settings.'.$index.'.robots_index', $row['robots_index']))> RI</label>
                                <label class="flex items-center gap-2"><input type="hidden" name="page_settings[{{ $index }}][robots_follow]" value="0"><input type="checkbox" name="page_settings[{{ $index }}][robots_follow]" value="1" @checked(old('page_settings.'.$index.'.robots_follow', $row['robots_follow']))> RF</label>
                                <label class="flex items-center gap-2"><input type="hidden" name="page_settings[{{ $index }}][active]" value="0"><input type="checkbox" name="page_settings[{{ $index }}][active]" value="1" @checked(old('page_settings.'.$index.'.active', $row['active']))> {{ __('admin_ui.site.form.page_active') }}</label>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="inline-flex items-center rounded-lg bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 text-sm font-semibold">{{ __('admin_ui.site.visual_editor.save_page') }}</button>
        </div>
    </form>
@endsection
