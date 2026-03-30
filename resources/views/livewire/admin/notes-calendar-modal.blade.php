<?php

use App\Models\Note;
use Carbon\Carbon;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component {

    public string $currentMonth = '';
    public string $selectedDate = '';

    public function mount(): void
    {
        $today = now();
        $this->currentMonth = $today->format('Y-m');
        $this->selectedDate = $today->format('Y-m-d');
    }

    #[On('openAdminNotesCalendar')]
    public function openAdminNotesCalendar(): void
    {
        $this->js("document.dispatchEvent(new CustomEvent('modal-show',{detail:{name:'modal-admin-notes-calendar'}}))");
    }

    public function prevMonth(): void
    {
        $this->currentMonth = Carbon::createFromFormat('Y-m', $this->currentMonth)->subMonth()->format('Y-m');
    }

    public function nextMonth(): void
    {
        $this->currentMonth = Carbon::createFromFormat('Y-m', $this->currentMonth)->addMonth()->format('Y-m');
    }

    public function pickDate(string $date): void
    {
        $this->selectedDate = $date;
    }

    public function getMonthLabelProperty(): string
    {
        return Carbon::createFromFormat('Y-m', $this->currentMonth)->translatedFormat('F Y');
    }

    public function getCalendarDaysProperty(): array
    {
        $monthStart = Carbon::createFromFormat('Y-m', $this->currentMonth)->startOfMonth();
        $start = $monthStart->copy()->startOfWeek(Carbon::MONDAY);

        $monthEnd = Carbon::createFromFormat('Y-m', $this->currentMonth)->endOfMonth();
        $end = $monthEnd->copy()->endOfWeek(Carbon::SUNDAY);

        $counts = Note::query()
            ->whereNotNull('important_date')
            ->whereBetween('important_date', [$start->toDateString(), $end->toDateString()])
            ->selectRaw('important_date, COUNT(*) as total')
            ->groupBy('important_date')
            ->pluck('total', 'important_date')
            ->toArray();

        $days = [];
        $cursor = $start->copy();

        while ($cursor <= $end) {
            $iso = $cursor->toDateString();
            $days[] = [
                'date' => $iso,
                'day' => (int) $cursor->format('j'),
                'inMonth' => $cursor->month === $monthStart->month,
                'count' => (int) ($counts[$iso] ?? 0),
            ];
            $cursor->addDay();
        }

        return $days;
    }

    public function getSelectedNotesProperty()
    {
        return Note::query()
            ->with('creator')
            ->whereDate('important_date', $this->selectedDate)
            ->orderBy('created_at', 'desc')
            ->get();
    }
}; ?>

<div>
    <flux:modal name="modal-admin-notes-calendar" class="md:w-[920px]" focusable>
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <flux:heading size="lg">{{ __('admin_ui.notes.calendar.title') }}</flux:heading>
                <div class="flex items-center gap-2">
                    <button type="button" wire:click="prevMonth" class="px-3 py-1.5 rounded-lg border border-zinc-200 dark:border-zinc-700 text-sm">{{ __('admin_ui.notes.calendar.prev') }}</button>
                    <div class="text-sm font-semibold min-w-[150px] text-center">{{ $this->monthLabel }}</div>
                    <button type="button" wire:click="nextMonth" class="px-3 py-1.5 rounded-lg border border-zinc-200 dark:border-zinc-700 text-sm">{{ __('admin_ui.notes.calendar.next') }}</button>
                </div>
            </div>

            <div class="grid grid-cols-7 gap-2 text-xs font-semibold text-zinc-500">
                <div class="text-center">{{ __('admin_ui.notes.calendar.day_mon') }}</div>
                <div class="text-center">{{ __('admin_ui.notes.calendar.day_tue') }}</div>
                <div class="text-center">{{ __('admin_ui.notes.calendar.day_wed') }}</div>
                <div class="text-center">{{ __('admin_ui.notes.calendar.day_thu') }}</div>
                <div class="text-center">{{ __('admin_ui.notes.calendar.day_fri') }}</div>
                <div class="text-center">{{ __('admin_ui.notes.calendar.day_sat') }}</div>
                <div class="text-center">{{ __('admin_ui.notes.calendar.day_sun') }}</div>
            </div>

            <div class="grid grid-cols-7 gap-2">
                @foreach($this->calendarDays as $day)
                    <button
                        type="button"
                        wire:click="pickDate('{{ $day['date'] }}')"
                        class="min-h-[90px] rounded-xl border p-2 text-left transition {{ $day['inMonth'] ? 'border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900' : 'border-zinc-100 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-950 text-zinc-400' }} {{ $selectedDate === $day['date'] ? '!border-violet-500 ring-2 ring-violet-300/40' : '' }}">
                        <div class="text-xs font-semibold">{{ $day['day'] }}</div>
                        @if($day['count'] > 0)
                            <div class="mt-2 inline-flex items-center gap-1 rounded-full bg-violet-100 text-violet-700 dark:bg-violet-900/40 dark:text-violet-300 px-2 py-0.5 text-[11px]">
                                <span>{{ $day['count'] }}</span>
                                <span>{{ __('admin_ui.notes.calendar.notes') }}</span>
                            </div>
                        @endif
                    </button>
                @endforeach
            </div>

            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-3">
                <div class="text-sm font-semibold mb-2">{{ __('admin_ui.notes.calendar.selected') }}: {{ \Carbon\Carbon::parse($selectedDate)->format('d/m/Y') }}</div>
                <div class="space-y-2 max-h-52 overflow-auto pr-1">
                    @forelse($this->selectedNotes as $note)
                        <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 p-2">
                            <div class="flex items-center justify-between gap-2">
                                <div class="flex items-center gap-2">
                                    <span class="h-2.5 w-2.5 rounded-full" style="background: {{ $note->color }}"></span>
                                    <div class="text-sm font-medium">{{ $note->title }}</div>
                                </div>
                                <div class="text-xs text-zinc-500">{{ $note->creator?->name ?? '—' }}</div>
                            </div>
                            @if($note->content)
                                <div class="text-xs text-zinc-500 mt-1">{{ \Illuminate\Support\Str::limit($note->content, 120) }}</div>
                            @endif
                        </div>
                    @empty
                        <div class="text-sm text-zinc-500">{{ __('admin_ui.notes.calendar.empty') }}</div>
                    @endforelse
                </div>
            </div>
        </div>
    </flux:modal>
</div>
