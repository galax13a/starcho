<?php

namespace App\Livewire\Concerns;

trait DispatchesStarchoNotify
{
    protected function notifyCrud(string $resource, string $action, array $replace = [], array $options = []): void
    {
        $type = match ($action) {
            'deleted', 'item_deleted', 'uninstalled', 'deactivated' => 'warning',
            'error', 'failed', 'cannot_delete_admin', 'forbidden', 'not_found' => 'failure',
            'info' => 'info',
            default => 'success',
        };

        $this->notify($type, __('admin_ui.' . $resource . '.notify.' . $action, $replace), $options);
    }

    protected function notifySuccess(string $message, array $options = []): void
    {
        $this->notify('success', $message, $options);
    }

    protected function notifyWarning(string $message, array $options = []): void
    {
        $this->notify('warning', $message, $options);
    }

    protected function notifyFailure(string $message, array $options = []): void
    {
        $this->notify('failure', $message, $options);
    }

    protected function notifyInfo(string $message, array $options = []): void
    {
        $this->notify('info', $message, $options);
    }

    protected function notify(string $type, string $message, array $options = []): void
    {
        $this->dispatch('notify', ...array_merge([
            'type' => $type,
            'message' => $message,
        ], $options));
    }
}
