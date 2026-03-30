<?php

use App\Models\Contact;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component {

    public int $contactId = 0;
    public int $userId = 0;
    public string $name = '';
    public string $company = '';
    public string $email = '';
    public string $phone = '';
    public string $status = 'lead';
    public string $notes = '';

    #[On('openAdminContact')]
    public function openAdminContact(int $id = 0): void
    {
        $this->contactId = $id;
        $this->userId = auth()->id();
        $this->name = '';
        $this->company = '';
        $this->email = '';
        $this->phone = '';
        $this->status = 'lead';
        $this->notes = '';

        if ($id > 0) {
            $contact = Contact::findOrFail($id);
            $this->userId = (int) ($contact->user_id ?? auth()->id());
            $this->name = $contact->name;
            $this->company = $contact->company ?? '';
            $this->email = $contact->email ?? '';
            $this->phone = $contact->phone ?? '';
            $this->status = $contact->status;
            $this->notes = $contact->notes ?? '';
        }

        $this->resetValidation();
        $this->js("document.dispatchEvent(new CustomEvent('modal-show',{detail:{name:'modal-admin-contact'}}))");
    }

    public function saveContact(): void
    {
        $this->validate([
            'name' => 'required|string|max:150',
            'company' => 'nullable|string|max:150',
            'email' => 'nullable|email|max:150',
            'phone' => 'nullable|string|max:50',
            'status' => 'required|in:lead,prospect,customer,churned',
            'notes' => 'nullable|string|max:2000',
        ]);

        $data = [
            'name' => $this->name,
            'company' => $this->company ?: null,
            'email' => $this->email ?: null,
            'phone' => $this->phone ?: null,
            'status' => $this->status,
            'notes' => $this->notes ?: null,
            'user_id' => $this->userId > 0 ? $this->userId : auth()->id(),
        ];

        if ($this->contactId > 0) {
            Contact::where('id', $this->contactId)->update($data);
        } else {
            Contact::create($data);
        }

        $this->js("document.dispatchEvent(new CustomEvent('modal-close',{detail:{name:'modal-admin-contact'}}))");
        $this->dispatch('pg:eventRefresh-admin-contacts-table');
    }
}; ?>

<div>
    <flux:modal name="modal-admin-contact" class="md:w-[680px]" focusable>
        <form wire:submit="saveContact" class="space-y-5">
            <flux:heading size="lg">{{ $contactId > 0 ? __('admin_ui.contacts.modal.edit') : __('admin_ui.contacts.modal.new') }}</flux:heading>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <flux:field>
                    <flux:label>{{ __('admin_ui.contacts.modal.name') }}</flux:label>
                    <flux:input wire:model="name" placeholder="{{ __('admin_ui.contacts.modal.name_placeholder') }}" />
                    <flux:error name="name" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('admin_ui.contacts.modal.company') }}</flux:label>
                    <flux:input wire:model="company" placeholder="{{ __('admin_ui.contacts.modal.company_placeholder') }}" />
                    <flux:error name="company" />
                </flux:field>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <flux:field>
                    <flux:label>{{ __('admin_ui.contacts.modal.email') }}</flux:label>
                    <flux:input wire:model="email" type="email" placeholder="{{ __('admin_ui.contacts.modal.email_placeholder') }}" />
                    <flux:error name="email" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('admin_ui.contacts.modal.phone') }}</flux:label>
                    <flux:input wire:model="phone" placeholder="{{ __('admin_ui.contacts.modal.phone_placeholder') }}" />
                    <flux:error name="phone" />
                </flux:field>
            </div>

            <flux:field>
                <flux:label>{{ __('admin_ui.contacts.modal.status') }}</flux:label>
                <flux:select wire:model="status">
                    <flux:select.option value="lead">{{ __('admin_ui.contacts.status.lead') }}</flux:select.option>
                    <flux:select.option value="prospect">{{ __('admin_ui.contacts.status.prospect') }}</flux:select.option>
                    <flux:select.option value="customer">{{ __('admin_ui.contacts.status.customer') }}</flux:select.option>
                    <flux:select.option value="churned">{{ __('admin_ui.contacts.status.churned') }}</flux:select.option>
                </flux:select>
                <flux:error name="status" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('admin_ui.contacts.modal.notes') }}</flux:label>
                <flux:textarea wire:model="notes" rows="3" placeholder="{{ __('admin_ui.contacts.modal.notes_placeholder') }}" />
                <flux:error name="notes" />
            </flux:field>

            <div class="flex justify-end gap-2 pt-1">
                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('admin_ui.common.cancel') }}</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="saveContact">{{ $contactId > 0 ? __('admin_ui.common.update') : __('admin_ui.common.save') }}</span>
                    <span wire:loading wire:target="saveContact">{{ __('admin_ui.common.saving') }}</span>
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>
