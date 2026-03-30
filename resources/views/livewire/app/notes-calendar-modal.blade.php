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

    #[On('openNotesCalendar')]
    public function openNotesCalendar(): void
    {
        $this->js("document.dispatchEvent(new CustomEvent('modal-show',{detail:{name:'modal-notes-calendar'}}))");
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
            ->where('user_id', auth()->id())
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
            ->where('user_id', auth()->id())
            ->whereDate('important_date', $this->selectedDate)
            ->orderBy('created_at', 'desc')
            ->get();
    }
}; ?>

<div>
    <flux:modal name="modal-notes-calendar" class="md:w-[960px] !p-0 starcho-tiktok-modal" focusable>
        <div class="sc-modal-tt">
            <div class="sc-modal-tt-header starcho-tiktok-modal-header">
                <div style="flex:1;display:flex;align-items:center;gap:10px;">
                    <div class="sc-modal-tt-icon"><i class="fas fa-calendar"></i></div>
                    <div>
                        <div class="sc-modal-tt-title"><span>{{ __('notes.calendar_title') }}</span></div>
                        <div class="starcho-tiktok-modal-subtitle">{{ __('notes.calendar_subtitle') ?? 'View notes by date' }}</div>
                    </div>
                </div>
                <div style="display:flex;align-items:center;gap:8px;">
                    <button type="button" wire:click="prevMonth" class="sc-btn sc-btn-tt sc-btn-sm" title="Previous month">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <div style="font-size:14px;font-weight:600;min-width:160px;text-align:center;color:var(--tt-text);">{{ $this->monthLabel }}</div>
                    <button type="button" wire:click="nextMonth" class="sc-btn sc-btn-tt sc-btn-sm" title="Next month">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>

            <div class="sc-modal-tt-body starcho-tiktok-modal-body" style="display:flex;flex-direction:column;gap:20px;">
                <!-- Calendar Grid -->
                <div style="display:flex;flex-direction:column;gap:12px;">
                    <div style="display:grid;grid-template-columns:repeat(7,1fr);gap:6px;text-align:center;">
                        <div style="font-size:10px;font-weight:700;text-transform:uppercase;color:var(--tt-text2);padding:8px 0;">{{ __('notes.day_mon') }}</div>
                        <div style="font-size:10px;font-weight:700;text-transform:uppercase;color:var(--tt-text2);padding:8px 0;">{{ __('notes.day_tue') }}</div>
                        <div style="font-size:10px;font-weight:700;text-transform:uppercase;color:var(--tt-text2);padding:8px 0;">{{ __('notes.day_wed') }}</div>
                        <div style="font-size:10px;font-weight:700;text-transform:uppercase;color:var(--tt-text2);padding:8px 0;">{{ __('notes.day_thu') }}</div>
                        <div style="font-size:10px;font-weight:700;text-transform:uppercase;color:var(--tt-text2);padding:8px 0;">{{ __('notes.day_fri') }}</div>
                        <div style="font-size:10px;font-weight:700;text-transform:uppercase;color:var(--tt-text2);padding:8px 0;">{{ __('notes.day_sat') }}</div>
                        <div style="font-size:10px;font-weight:700;text-transform:uppercase;color:var(--tt-text2);padding:8px 0;">{{ __('notes.day_sun') }}</div>
                    </div>

                    <div style="display:grid;grid-template-columns:repeat(7,1fr);gap:8px;">
                        @foreach($this->calendarDays as $day)
                            <button
                                type="button"
                                wire:click="pickDate('{{ $day['date'] }}')"
                                style="
                                    min-height:80px;
                                    padding:10px;
                                    border-radius:10px;
                                    border:1px solid {{ $day['inMonth'] ? 'var(--tt-border)' : 'rgba(255,255,255,.08)' }};
                                    background:{{ $day['inMonth'] ? 'var(--tt-bg2)' : 'rgba(0,0,0,.02)' }};
                                    color:{{ $day['inMonth'] ? 'var(--tt-text)' : 'var(--tt-text2)' }};
                                    text-align:left;
                                    cursor:pointer;
                                    transition:all .2s;
                                    {% if $selectedDate === $day['date'] %}border-color:var(--tt-pink);background:rgba(254,44,85,.08);{% endif %}
                                "
                                class="calendar-day"
                                data-date="{{ $day['date'] }}"
                                data-selected="{{ $selectedDate === $day['date'] ? 'true' : 'false' }}">
                                <div style="font-size:12px;font-weight:700;">{{ $day['day'] }}</div>
                                @if($day['count'] > 0)
                                    <div style="margin-top:6px;display:inline-flex;align-items:center;gap:4px;border-radius:12px;background:rgba(254,44,85,.12);color:var(--tt-pink);padding:3px 8px;font-size:10px;font-weight:600;">
                                        <span>{{ $day['count'] }}</span>
                                        <span>{{ __('notes.calendar_notes') }}</span>
                                    </div>
                                @endif
                            </button>
                        @endforeach
                    </div>
                </div>

                <!-- Selected Notes Section -->
                <div style="border-top:1px solid var(--tt-border);padding-top:16px;">
                    <div style="font-size:12px;font-weight:700;margin-bottom:12px;color:var(--tt-text);text-transform:uppercase;letter-spacing:.05em;">
                        {{ __('notes.calendar_selected') }}: <span style="color:var(--tt-pink);">{{ \Carbon\Carbon::parse($selectedDate)->format('d/m/Y') }}</span>
                    </div>
                    <div style="max-height:240px;overflow-y:auto;display:flex;flex-direction:column;gap:8px;">
                        @forelse($this->selectedNotes as $note)
                            <div style="border:1px solid var(--tt-border);border-radius:8px;padding:12px;display:flex;flex-direction:column;gap:6px;">
                                <div style="display:flex;align-items:center;gap:8px;">
                                    <span style="width:10px;height:10px;border-radius:50%;background:{{ $note->color }};flex-shrink:0;"></span>
                                    <div style="font-size:13px;font-weight:600;color:var(--tt-text);">{{ $note->title }}</div>
                                </div>
                                @if($note->content)
                                    <div style="font-size:11px;color:var(--tt-text2);margin-left:18px;">{{ \Illuminate\Support\Str::limit($note->content, 120) }}</div>
                                @endif
                            </div>
                        @empty
                            <div style="font-size:12px;color:var(--tt-text2);text-align:center;padding:20px;">{{ __('notes.calendar_empty') }}</div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="sc-modal-tt-footer starcho-tiktok-modal-footer">
                <flux:modal.close>
                    <button type="button" class="sc-btn sc-btn-tt sc-btn-ghost">{{ __('common.close') ?? 'Close' }}</button>
                </flux:modal.close>
            </div>
        </div>
    </flux:modal>
</div>
