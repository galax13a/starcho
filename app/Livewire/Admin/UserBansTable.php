<?php

namespace App\Livewire\Admin;

use App\Models\User;
use App\Models\UserBan;
use App\Livewire\Concerns\DispatchesStarchoNotify;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class UserBansTable extends PowerGridComponent
{
    use DispatchesStarchoNotify;

    public string $tableName = 'admin-user-bans-table';

    // ── Modal state ──────────────────────────────────────────────────────────
    public bool $showBanModal = false;
    public ?int $selectedUserId = null;
    public string $selectedUserName = '';
    public string $banReason = '';
    public string $banNotes = '';
    public string $banDuration = '1d';

    // ── Filter ───────────────────────────────────────────────────────────────
    public string $filterStatus = 'active'; // active | all | lifted

    public function setUp(): array
    {
        $this->persist(['columns'], 'admin');

        return [
            PowerGrid::header()
                ->showSearchInput()
                ->showToggleColumns()
                ->includeViewOnTop('livewire.admin.users-ban.pg-header'),
            PowerGrid::footer()
                ->showPerPage(25)
                ->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        $query = User::with('bans')
            ->where('id', '!=', Auth::id());

        if ($this->filterStatus === 'active') {
            $query->where('is_banned', true);
        } elseif ($this->filterStatus === 'lifted') {
            $query->where('is_banned', false)
                  ->whereHas('bans');
        }

        return $query->orderByDesc('is_banned');
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('name')
            ->add('email')
            ->add('is_banned')
            ->add('banned_status_label', fn (User $u) => $u->is_banned
                ? '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400"><i class="fas fa-ban"></i> ' . __('admin_ui.users_ban.status.banned') . '</span>'
                : '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400"><i class="fas fa-check"></i> ' . __('admin_ui.users_ban.status.active') . '</span>'
            )
            ->add('ban_reason')
            ->add('ban_reason_short', fn (User $u) => $u->ban_reason ? \Illuminate\Support\Str::limit($u->ban_reason, 50) : '—')
            ->add('banned_until')
            ->add('banned_until_label', fn (User $u) => $u->banned_until
                ? Carbon::parse($u->banned_until)->format('d/m/Y H:i')
                : ($u->is_banned ? '<span class="text-red-600 font-semibold text-xs">' . __('admin_ui.users_ban.duration.permanent') . '</span>' : '—')
            )
            ->add('bans_count', fn (User $u) => $u->bans->count());
    }

    public function columns(): array
    {
        return [
            Column::make('#', 'id')->sortable()->toggleable(false),
            Column::make(__('admin_ui.users_ban.columns.user'), 'name')->searchable()->sortable(),
            Column::make(__('admin_ui.users_ban.columns.email'), 'email')->searchable()->sortable()->hidden(),
            Column::make(__('admin_ui.users_ban.columns.status'), 'banned_status_label', 'is_banned')->sortable(),
            Column::make(__('admin_ui.users_ban.columns.reason'), 'ban_reason_short', 'ban_reason')->searchable(),
            Column::make(__('admin_ui.users_ban.columns.expires'), 'banned_until_label', 'banned_until')->sortable(),
            Column::make(__('admin_ui.users_ban.columns.total_bans'), 'bans_count'),
            Column::action(__('admin_ui.users_ban.columns.actions')),
        ];
    }

    public function actions(\App\Models\User $row): array
    {
        $actions = [];

        if (! $row->is_banned) {
            $actions[] = \PowerComponents\LivewirePowerGrid\Button::make('ban', __('admin_ui.users_ban.actions.ban'))
                ->class('sa-btn sa-btn-danger sa-btn-sm')
                ->dispatch('openBanModal', ['userId' => $row->id, 'userName' => $row->name]);
        } else {
            $actions[] = \PowerComponents\LivewirePowerGrid\Button::make('unban', __('admin_ui.users_ban.actions.unban'))
                ->class('sa-btn sa-btn-success sa-btn-sm')
                ->dispatch('confirmUnban', ['userId' => $row->id, 'userName' => $row->name]);
        }

        return $actions;
    }

    // ── Livewire listeners ───────────────────────────────────────────────────
    #[\Livewire\Attributes\On('openBanModal')]
    public function openBanModal(int $userId, string $userName): void
    {
        $this->selectedUserId   = $userId;
        $this->selectedUserName = $userName;
        $this->banReason        = '';
        $this->banNotes         = '';
        $this->banDuration      = '1d';
        $this->showBanModal     = true;
    }

    #[\Livewire\Attributes\On('confirmUnban')]
    public function confirmUnban(int $userId, string $userName): void
    {
        $this->selectedUserId   = $userId;
        $this->selectedUserName = $userName;
        $this->dispatch('openUnbanConfirm', userId: $userId, userName: $userName);
    }

    public function saveBan(): void
    {
        $this->validate([
            'banReason'   => ['required', 'string', 'max:500'],
            'banNotes'    => ['nullable', 'string', 'max:1000'],
            'banDuration' => ['required', 'in:1h,6h,12h,1d,3d,7d,30d,permanent'],
        ]);

        $user = User::findOrFail($this->selectedUserId);

        abort_if($user->hasRole(['root', 'admin']), 403);

        $expiresAt = match ($this->banDuration) {
            '1h'        => now()->addHour(),
            '6h'        => now()->addHours(6),
            '12h'       => now()->addHours(12),
            '1d'        => now()->addDay(),
            '3d'        => now()->addDays(3),
            '7d'        => now()->addDays(7),
            '30d'       => now()->addDays(30),
            'permanent' => null,
        };

        $user->ban(Auth::id(), $this->banReason, $expiresAt, $this->banNotes ?: null);

        $this->showBanModal = false;
        $this->notifySuccess(__('admin_ui.users_ban.notify.banned', ['name' => $user->name]));
        $this->dispatch('pg:eventRefresh-admin-user-bans-table');
    }

    #[\Livewire\Attributes\On('doUnban')]
    public function doUnban(int $userId): void
    {
        $user = User::findOrFail($userId);
        $user->unban(Auth::id());

        $this->notifySuccess(__('admin_ui.users_ban.notify.unbanned', ['name' => $user->name]));
        $this->dispatch('pg:eventRefresh-admin-user-bans-table');
    }

    public function setFilterStatus(string $status): void
    {
        $this->filterStatus = $status;
        $this->dispatch('pg:eventRefresh-admin-user-bans-table');
    }
}
