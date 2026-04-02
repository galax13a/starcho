<div class="sa-menu-node" data-id="{{ $item['id'] }}">
    <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 shadow-sm">
    <div class="flex items-center gap-3 px-4 py-3">
        {{-- Drag handle (visual only) --}}
        <button type="button" class="inline-flex items-center justify-center size-8 rounded-lg text-zinc-400 hover:text-violet-500 hover:bg-violet-50 dark:hover:bg-violet-900/30 cursor-grab active:cursor-grabbing sa-drag-handle" title="{{ __('admin_ui.menu.actions.drag') }}">
            <i class="fas fa-grip-vertical text-sm"></i>
        </button>

        {{-- Status dot --}}
        <span class="w-2 h-2 rounded-full flex-shrink-0 {{ $item['active'] ? 'bg-emerald-400' : 'bg-zinc-300 dark:bg-zinc-600' }}"></span>

        {{-- Info --}}
        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 flex-wrap">
                @if($item['icon'])
                <i class="{{ $item['icon'] }} text-zinc-400 text-xs w-4 text-center"></i>
                @endif
                <span class="text-sm font-semibold text-zinc-800 dark:text-zinc-100">{{ data_get($item, 'name.'.app()->getLocale()) ?? data_get($item, 'name.en') ?? ($item['name'] ?? $item['label'] ?? '—') }}</span>
                @if(!empty($item['section']))
                    <span class="text-xs px-1.5 py-0.5 rounded bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400">{{ $item['section'] }}</span>
                @endif
                @if($item['module_key'])
                    <span class="text-xs px-1.5 py-0.5 rounded bg-violet-100 dark:bg-violet-900/30 text-violet-700 dark:text-violet-400">{{ $item['module_key'] }}</span>
                @endif
                @if($depth > 0)
                    <span class="text-xs px-1.5 py-0.5 rounded bg-zinc-100 dark:bg-zinc-800 text-zinc-500">{{ __('admin_ui.menu.sub_level', ['depth' => $depth]) }}</span>
                @endif
            </div>
            <p class="text-xs text-zinc-400 mt-0.5">
                @if($item['route']) {{ __('admin_ui.menu.route_label') }}: <code class="font-mono">{{ $item['route'] }}</code>
                @elseif($item['url']) {{ __('admin_ui.menu.url_label') }}: {{ $item['url'] }}
                @else <span class="italic">{{ __('admin_ui.menu.no_route') }}</span>
                @endif
                &middot; {{ __('admin_ui.menu.order_label') }}: {{ $item['sort_order'] }}
            </p>
        </div>

        {{-- Actions --}}
        <div class="flex items-center gap-1 flex-shrink-0">
            <span class="text-[11px] text-zinc-400 px-1">{{ __('admin_ui.menu.actions.drag') }}</span>

            <button wire:click="toggleActive({{ $item['id'] }})" title="{{ $item['active'] ? __('admin_ui.menu.actions.deactivate') : __('admin_ui.menu.actions.activate') }}"
                    class="inline-flex items-center justify-center size-7 rounded-lg transition
                    {{ $item['active'] ? 'text-emerald-500 hover:text-emerald-700 hover:bg-emerald-50 dark:hover:bg-emerald-900/30' : 'text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 dark:hover:bg-zinc-800' }}">
                <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['active'] ? 'M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178ZM15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z' : 'M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88' }}"/>
                </svg>
            </button>

            <button wire:click="openCreate({{ $item['id'] }})" title="{{ __('admin_ui.menu.actions.add_child') }}"
                    class="inline-flex items-center justify-center size-7 rounded-lg text-zinc-400 hover:text-violet-600 hover:bg-violet-50 dark:hover:bg-violet-900/30 transition">
                <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                </svg>
            </button>

            <button wire:click="openEdit({{ $item['id'] }})" title="{{ __('admin_ui.menu.actions.edit') }}"
                    class="inline-flex items-center justify-center size-7 rounded-lg text-zinc-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/30 transition">
                <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z"/>
                </svg>
            </button>

            @php
                $itemLabel = data_get($item, 'name.'.app()->getLocale())
                    ?? data_get($item, 'name.en')
                    ?? ($item['label'] ?? '—');
            @endphp
            <button wire:click="delete({{ $item['id'] }})"
                    onclick="return confirm('{{ __('admin_ui.menu.delete_confirm', ['name' => addslashes($itemLabel)]) }}')"
                    title="{{ __('admin_ui.menu.actions.delete') }}"
                    class="inline-flex items-center justify-center size-7 rounded-lg text-zinc-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30 transition">
                <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/>
                </svg>
            </button>
        </div>
    </div>
    </div>

{{-- Children --}}
    <div class="sa-menu-children sa-menu-dropzone mt-2 ml-8 space-y-2" data-parent-id="{{ $item['id'] }}">
        @if(!empty($item['all_children']))
            @foreach($item['all_children'] as $child)
                @include('livewire.admin.partials.menu-item-row', ['item' => $child, 'depth' => $depth + 1])
            @endforeach
        @endif
    </div>
</div>
