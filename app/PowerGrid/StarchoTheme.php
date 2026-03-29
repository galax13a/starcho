<?php

namespace App\PowerGrid;

use PowerComponents\LivewirePowerGrid\Themes\Tailwind;

class StarchoTheme extends Tailwind
{
    public function table(): array
    {
        $theme = parent::table();

        // ── Layout ────────────────────────────────────────────────────────────
        // Card wrapper: rounded, bordered, shadowed, clips overflow for corners
        $theme['layout']['container'] = 'w-full';
        $theme['layout']['base']      = 'w-full rounded-xl border border-zinc-200 dark:border-zinc-700 shadow-sm overflow-hidden bg-white dark:bg-zinc-800';
        // Table-scrollable zone: edge-to-edge, scrollable on overflow
        $theme['layout']['div']       = 'overflow-x-auto';
        // Table itself: full-width, auto column distribution
        $theme['layout']['table']     = 'w-full table-auto';
        // Action cell inner layout
        $theme['layout']['actions']   = 'flex items-center gap-1.5';

        // ── Header (thead) ────────────────────────────────────────────────────
        $theme['header']['thead']    = 'bg-zinc-50 dark:bg-zinc-900/60';
        $theme['header']['tr']       = 'border-b border-zinc-200 dark:border-zinc-700';
        $theme['header']['th']       = 'px-4 py-3 text-left text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider whitespace-nowrap';
        $theme['header']['thAction'] = 'px-4 py-3 text-right text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider whitespace-nowrap';

        // ── Body (tbody) ──────────────────────────────────────────────────────
        $theme['body']['tbody']              = 'divide-y divide-zinc-100 dark:divide-zinc-700/50 bg-white dark:bg-zinc-800';
        $theme['body']['tr']                 = 'hover:bg-zinc-50/80 dark:hover:bg-zinc-700/30 transition-colors duration-150';
        $theme['body']['td']                 = 'px-4 py-3 text-sm text-zinc-700 dark:text-zinc-300';
        $theme['body']['tdEmpty']            = 'px-4 py-14 text-sm text-center text-zinc-400 dark:text-zinc-500';
        $theme['body']['tdActionsContainer'] = 'flex items-center gap-1.5';

        // Filters (cleared — we use global search only)
        $theme['body']['tdFilters'] = 'hidden';
        $theme['body']['trFilters'] = 'hidden';

        return $theme;
    }

    public function footer(): array
    {
        $theme = parent::footer();

        $theme['footer'] =
            'flex items-center justify-between ' .
            'border-t border-zinc-200 dark:border-zinc-700 ' .
            'bg-zinc-50/60 dark:bg-zinc-800/60';

        $theme['footer_with_pagination'] = 'w-full';

        $theme['select'] =
            'appearance-none cursor-pointer ' .
            'rounded-lg border border-zinc-200 dark:border-zinc-600 ' .
            'bg-white dark:bg-zinc-700/80 ' .
            'text-xs text-zinc-700 dark:text-zinc-200 ' .
            'py-1.5 pl-3 pr-6 ' .
            'focus:outline-none focus:ring-2 focus:ring-violet-400/30 focus:border-violet-400 dark:focus:border-violet-500 ' .
            'transition-colors';

        return $theme;
    }
}
