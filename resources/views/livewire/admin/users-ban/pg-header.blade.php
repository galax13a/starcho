<div>
    <!-- Modal: Banear usuario -->
    <div
        x-show="$wire.showBanModal"
        x-transition
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
        x-cloak
    >
        <div class="bg-white dark:bg-zinc-900 rounded-xl shadow-xl p-6 w-full max-w-lg mx-4">
            <h3 class="text-lg font-semibold text-zinc-900 dark:text-white mb-1 flex items-center gap-2">
                <i class="fas fa-ban text-red-500"></i>
                {{ __('admin_ui.users_ban.modal.ban_title') }}
            </h3>
            <p class="text-sm text-zinc-500 dark:text-zinc-400 mb-5">
                {{ __('admin_ui.users_ban.modal.ban_subtitle') }}
                <strong class="text-zinc-800 dark:text-zinc-200" x-text="$wire.selectedUserName"></strong>
            </p>

            <div class="space-y-4">
                <!-- Duración -->
                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                        {{ __('admin_ui.users_ban.form.duration') }}
                    </label>
                    <div class="grid grid-cols-4 gap-2">
                        @foreach(['1h','6h','12h','1d','3d','7d','30d','permanent'] as $dur)
                        <label class="cursor-pointer">
                            <input type="radio" wire:model="banDuration" value="{{ $dur }}" class="sr-only peer">
                            <div class="text-center py-2 rounded-lg border text-xs font-semibold transition
                                border-zinc-300 dark:border-zinc-700 text-zinc-600 dark:text-zinc-400
                                peer-checked:border-red-500 peer-checked:bg-red-50 peer-checked:text-red-700
                                dark:peer-checked:border-red-500 dark:peer-checked:bg-red-900/20 dark:peer-checked:text-red-400
                                hover:border-zinc-400">
                                {{ __('admin_ui.users_ban.duration.' . $dur) }}
                            </div>
                        </label>
                        @endforeach
                    </div>
                    @error('banDuration') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Razón -->
                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                        {{ __('admin_ui.users_ban.form.reason') }} <span class="text-red-500">*</span>
                    </label>
                    <textarea
                        wire:model="banReason"
                        rows="2"
                        maxlength="500"
                        placeholder="{{ __('admin_ui.users_ban.form.reason_placeholder') }}"
                        class="w-full rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-500"
                    ></textarea>
                    @error('banReason') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Notas internas -->
                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                        {{ __('admin_ui.users_ban.form.notes') }}
                        <span class="text-xs text-zinc-400">({{ __('admin_ui.users_ban.form.notes_help') }})</span>
                    </label>
                    <textarea
                        wire:model="banNotes"
                        rows="2"
                        maxlength="1000"
                        placeholder="{{ __('admin_ui.users_ban.form.notes_placeholder') }}"
                        class="w-full rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-zinc-400"
                    ></textarea>
                </div>
            </div>

            <div class="flex gap-3 justify-end mt-5">
                <button
                    type="button"
                    class="sa-btn sa-btn-secondary sa-btn-sm"
                    wire:click="$set('showBanModal', false)"
                >{{ __('actions.cancel') }}</button>
                <button
                    type="button"
                    class="sa-btn sa-btn-danger sa-btn-sm"
                    wire:click="saveBan"
                    wire:loading.attr="disabled"
                >
                    <span wire:loading.remove wire:target="saveBan">
                        <i class="fas fa-ban mr-1"></i>
                        {{ __('admin_ui.users_ban.actions.ban') }}
                    </span>
                    <span wire:loading wire:target="saveBan">{{ __('admin_ui.users_ban.actions.banning') }}</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Filtros de estado -->
    <div class="flex flex-wrap gap-2 mb-3">
        <button
            wire:click="setFilterStatus('active')"
            class="sa-btn sa-btn-sm {{ $filterStatus === 'active' ? 'sa-btn-danger' : 'sa-btn-secondary' }}"
        >
            <i class="fas fa-ban mr-1"></i>
            {{ __('admin_ui.users_ban.filter.active') }}
        </button>
        <button
            wire:click="setFilterStatus('all')"
            class="sa-btn sa-btn-sm {{ $filterStatus === 'all' ? 'sa-btn-primary' : 'sa-btn-secondary' }}"
        >
            <i class="fas fa-list mr-1"></i>
            {{ __('admin_ui.users_ban.filter.all') }}
        </button>
        <button
            wire:click="setFilterStatus('lifted')"
            class="sa-btn sa-btn-sm {{ $filterStatus === 'lifted' ? 'sa-btn-success' : 'sa-btn-secondary' }}"
        >
            <i class="fas fa-check-circle mr-1"></i>
            {{ __('admin_ui.users_ban.filter.lifted') }}
        </button>
    </div>

    <!-- Confirm unban dialog -->
    <div
        x-show="$wire.showUnbanModal"
        x-transition
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
        x-cloak
    >
        <div class="bg-white dark:bg-zinc-900 rounded-xl shadow-xl p-6 w-full max-w-sm mx-4">
            <h3 class="text-lg font-semibold text-zinc-900 dark:text-white mb-2">
                {{ __('admin_ui.users_ban.modal.unban_title') }}
            </h3>
            <p class="text-sm text-zinc-600 dark:text-zinc-400 mb-5">
                {{ __('admin_ui.users_ban.modal.unban_confirm') }}
                <strong x-text="$wire.selectedUserName"></strong>?
            </p>
            <div class="flex gap-3 justify-end">
                <button
                    type="button"
                    class="sa-btn sa-btn-secondary sa-btn-sm"
                    wire:click="$set('showUnbanModal', false)"
                >{{ __('actions.cancel') }}</button>
                <button
                    type="button"
                    class="sa-btn sa-btn-success sa-btn-sm"
                    wire:click="doUnban"
                    wire:loading.attr="disabled"
                >
                    <i class="fas fa-unlock mr-1"></i>
                    {{ __('admin_ui.users_ban.actions.unban') }}
                </button>
            </div>
        </div>
    </div>
</div>
