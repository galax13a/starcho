<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

new class extends Component {

    // ── Rol ───────────────────────────────────────────────────────────────
    public int    $roleId   = 0;
    public string $roleName = '';
    public array  $rolePerms = [];

    // ── Permiso ───────────────────────────────────────────────────────────
    public int    $permissionId   = 0;
    public string $permissionName = '';

    // ── Usuario ───────────────────────────────────────────────────────────
    public int    $userId                   = 0;
    public string $userName                 = '';
    public string $userEmail                = '';
    public string $userPassword             = '';
    public string $userPasswordConfirmation = '';
    public array  $userRoles                = [];

    // ── Computed ──────────────────────────────────────────────────────────
    #[Computed]
    public function allPermissions() { return Permission::orderBy('name')->get(); }

    #[Computed]
    public function allRoles() { return Role::orderBy('name')->get(); }

    // ── Rol ───────────────────────────────────────────────────────────────
    #[On('openRole')]
    public function openRole(int $id = 0): void
    {
        $this->roleId    = $id;
        $this->roleName  = '';
        $this->rolePerms = [];

        if ($id > 0) {
            $role            = Role::with('permissions')->findOrFail($id);
            $this->roleName  = $role->name;
            $this->rolePerms = $role->permissions->pluck('id')->map(fn ($i) => (string) $i)->toArray();
        }

        $this->resetValidation();
        $this->js("document.dispatchEvent(new CustomEvent('modal-show',{detail:{name:'modal-role'}}))");
    }

    public function saveRole(): void
    {
        $this->validate([
            'roleName' => 'required|string|min:2|max:100|unique:roles,name,' . ($this->roleId ?: 'NULL'),
        ]);

        if ($this->roleId > 0) {
            $role = Role::findOrFail($this->roleId);
            if ($role->name !== 'admin') {
                $role->update(['name' => $this->roleName, 'guard_name' => 'web']);
            }
            $role->syncPermissions(array_map('intval', $this->rolePerms));
        } else {
            Role::create(['name' => $this->roleName, 'guard_name' => 'web'])
                ->syncPermissions(array_map('intval', $this->rolePerms));
        }

        $this->js("document.dispatchEvent(new CustomEvent('modal-close',{detail:{name:'modal-role'}}))");
        $this->dispatch('pg:eventRefresh-roles-table');
    }

    // ── Permiso ───────────────────────────────────────────────────────────
    #[On('openPermission')]
    public function openPermission(int $id = 0): void
    {
        $this->permissionId   = $id;
        $this->permissionName = $id > 0 ? Permission::findOrFail($id)->name : '';

        $this->resetValidation();
        $this->js("document.dispatchEvent(new CustomEvent('modal-show',{detail:{name:'modal-permission'}}))");
    }

    public function savePermission(): void
    {
        $this->validate([
            'permissionName' => 'required|string|min:2|max:100|unique:permissions,name,' . ($this->permissionId ?: 'NULL'),
        ]);

        if ($this->permissionId > 0) {
            Permission::findOrFail($this->permissionId)->update(['name' => $this->permissionName]);
        } else {
            Permission::create(['name' => $this->permissionName, 'guard_name' => 'web']);
        }

        $this->js("document.dispatchEvent(new CustomEvent('modal-close',{detail:{name:'modal-permission'}}))");
        $this->dispatch('pg:eventRefresh-permissions-table');
    }

    // ── Usuario ───────────────────────────────────────────────────────────
    #[On('openUser')]
    public function openUser(int $id = 0): void
    {
        $this->userId                   = $id;
        $this->userName                 = '';
        $this->userEmail                = '';
        $this->userPassword             = '';
        $this->userPasswordConfirmation = '';
        $this->userRoles                = [];

        if ($id > 0) {
            $user            = User::with('roles')->findOrFail($id);
            $this->userName  = $user->name;
            $this->userEmail = $user->email;
            $this->userRoles = $user->roles->pluck('id')->map(fn ($i) => (string) $i)->toArray();
        }

        $this->resetValidation();
        $this->js("document.dispatchEvent(new CustomEvent('modal-show',{detail:{name:'modal-user'}}))");
    }

    public function saveUser(): void
    {
        $rules = [
            'userName'  => 'required|string|max:255',
            'userEmail' => 'required|email|max:255|unique:users,email,' . ($this->userId ?: 'NULL'),
        ];

        if ($this->userId === 0 || filled($this->userPassword)) {
            $rules['userPassword'] = ($this->userId === 0 ? 'required' : 'nullable')
                . '|string|min:8|confirmed';
        }

        $this->validate($rules);

        if ($this->userId > 0) {
            $user = User::findOrFail($this->userId);
            $data = ['name' => $this->userName, 'email' => $this->userEmail];
            if (filled($this->userPassword)) {
                $data['password'] = Hash::make($this->userPassword);
            }
            $user->update($data);
            $user->syncRoles(array_map('intval', $this->userRoles));
        } else {
            User::create([
                'name'              => $this->userName,
                'email'             => $this->userEmail,
                'password'          => Hash::make($this->userPassword),
                'email_verified_at' => now(),
            ])->syncRoles(array_map('intval', $this->userRoles));
        }

        $this->js("document.dispatchEvent(new CustomEvent('modal-close',{detail:{name:'modal-user'}}))");
        $this->dispatch('pg:eventRefresh-users-table');
    }
}; ?>

<div>
    {{-- ── Modal: Rol ──────────────────────────────────────────────────── --}}
    <flux:modal name="modal-role" class="md:w-[560px]" focusable>
        <form wire:submit="saveRole" class="space-y-5">
            <flux:heading size="lg">{{ $roleId > 0 ? __('admin_ui.modals.role.edit') : __('admin_ui.modals.role.new') }}</flux:heading>

            <flux:field>
                <flux:label>{{ __('admin_ui.modals.role.name_label') }}</flux:label>
                <flux:input wire:model="roleName" placeholder="{{ __('admin_ui.modals.role.name_placeholder') }}"
                    :disabled="$roleId > 0 && $roleName === 'admin'" />
                <flux:error name="roleName" />
            </flux:field>

            @if ($this->allPermissions->count())
                <div>
                    <flux:label class="mb-2 block">{{ __('admin_ui.modals.role.assigned_permissions') }}</flux:label>
                    <div class="grid grid-cols-2 gap-1 max-h-52 overflow-y-auto p-3 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-900">
                        @foreach ($this->allPermissions as $perm)
                            <label class="flex items-center gap-2 cursor-pointer hover:bg-zinc-100 dark:hover:bg-zinc-800 rounded px-2 py-1 transition">
                                <input type="checkbox" wire:model="rolePerms" value="{{ $perm->id }}"
                                    class="rounded border-zinc-300 dark:border-zinc-600" />
                                <span class="text-sm font-mono">{{ $perm->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="flex justify-end gap-2 pt-1">
                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('admin_ui.common.cancel') }}</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="saveRole">{{ __('admin_ui.common.save') }}</span>
                    <span wire:loading wire:target="saveRole">{{ __('admin_ui.common.saving') }}</span>
                </flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- ── Modal: Permiso ──────────────────────────────────────────────── --}}
    <flux:modal name="modal-permission" class="md:w-96" focusable>
        <form wire:submit="savePermission" class="space-y-5">
            <flux:heading size="lg">{{ $permissionId > 0 ? __('admin_ui.modals.permission.edit') : __('admin_ui.modals.permission.new') }}</flux:heading>

            <flux:field>
                <flux:label>{{ __('admin_ui.modals.permission.name_label') }}</flux:label>
                <flux:description>{{ __('admin_ui.modals.permission.kebab_hint') }} <code>{{ __('admin_ui.modals.permission.kebab_example') }}</code></flux:description>
                <flux:input wire:model="permissionName" placeholder="{{ __('admin_ui.modals.permission.name_placeholder') }}" class="font-mono" />
                <flux:error name="permissionName" />
            </flux:field>

            <div class="flex justify-end gap-2 pt-1">
                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('admin_ui.common.cancel') }}</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="savePermission">{{ __('admin_ui.common.save') }}</span>
                    <span wire:loading wire:target="savePermission">{{ __('admin_ui.common.saving') }}</span>
                </flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- ── Modal: Usuario ──────────────────────────────────────────────── --}}
    <flux:modal name="modal-user" class="md:w-[580px]" focusable>
        <form wire:submit="saveUser" class="space-y-5">
            <flux:heading size="lg">{{ $userId > 0 ? __('admin_ui.modals.user.edit') : __('admin_ui.modals.user.new') }}</flux:heading>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <flux:field>
                    <flux:label>{{ __('admin_ui.modals.user.name_label') }}</flux:label>
                    <flux:input wire:model="userName" placeholder="{{ __('admin_ui.modals.user.name_placeholder') }}" />
                    <flux:error name="userName" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('admin_ui.modals.user.email_label') }}</flux:label>
                    <flux:input wire:model="userEmail" type="email" placeholder="{{ __('admin_ui.modals.user.email_placeholder') }}" />
                    <flux:error name="userEmail" />
                </flux:field>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <flux:field>
                    <flux:label>{{ $userId > 0 ? __('admin_ui.modals.user.new_password_optional') : __('admin_ui.modals.user.password_label') }}</flux:label>
                    <flux:input wire:model="userPassword" type="password" viewable />
                    <flux:error name="userPassword" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('admin_ui.modals.user.confirm_password_label') }}</flux:label>
                    <flux:input wire:model="userPasswordConfirmation" type="password" viewable />
                </flux:field>
            </div>

            @if ($this->allRoles->count())
                <div>
                    <flux:label class="mb-2 block">{{ __('admin_ui.modals.user.roles_label') }}</flux:label>
                    <div class="grid grid-cols-2 gap-1 p-3 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-900">
                        @foreach ($this->allRoles as $role)
                            <label class="flex items-center gap-2 cursor-pointer hover:bg-zinc-100 dark:hover:bg-zinc-800 rounded px-2 py-1 transition">
                                <input type="checkbox" wire:model="userRoles" value="{{ $role->id }}"
                                    class="rounded border-zinc-300 dark:border-zinc-600" />
                                <span class="text-sm font-medium">{{ $role->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="flex justify-end gap-2 pt-1">
                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('admin_ui.common.cancel') }}</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="saveUser">{{ __('admin_ui.common.save') }}</span>
                    <span wire:loading wire:target="saveUser">{{ __('admin_ui.common.saving') }}</span>
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>
