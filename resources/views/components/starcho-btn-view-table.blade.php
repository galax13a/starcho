@props([
    'tableName',
    'title' => __('admin_ui.powergrid.toggle_columns'),
])

<button
    data-cy="toggle-columns-{{ $tableName }}"
    @click.prevent="open = !open"
    title="{{ $title }}"
    aria-label="{{ $title }}"
    class="
        inline-flex items-center justify-center size-8 rounded-lg text-xs font-medium
        border border-zinc-200 dark:border-zinc-600
        bg-white dark:bg-zinc-700/50
        text-zinc-500 dark:text-zinc-400
        hover:text-[#00f2ff] hover:border-[#00f2ff]/50 hover:bg-[#00f2ff]/5
        dark:hover:text-[#00f2ff] dark:hover:border-[#00f2ff]/40
        transition-all duration-200
        focus:outline-none focus:ring-2 focus:ring-[#00f2ff]/25
    "
>
    <x-livewire-powergrid::icons.eye-off class="w-4 h-4" />
</button>
