<?php

use App\Livewire\Concerns\DispatchesStarchoNotify;
use App\Models\Contact;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component {
    use DispatchesStarchoNotify;

    public int    $contactId = 0;
    public string $name      = '';
    public string $company   = '';
    public string $email     = '';
    public string $phone     = '';
    public string $status    = 'lead';
    public string $notes     = '';

    #[On('openContact')]
    public function openContact(int $id = 0): void
    {
        $this->contactId = $id;
        $this->name      = '';
        $this->company   = '';
        $this->email     = '';
        $this->phone     = '';
        $this->status    = 'lead';
        $this->notes     = '';

        if ($id > 0) {
            $contact = Contact::where('id', $id)->where('user_id', auth()->id())->firstOrFail();
            $this->name    = $contact->name;
            $this->company = $contact->company ?? '';
            $this->email   = $contact->email ?? '';
            $this->phone   = $contact->phone ?? '';
            $this->status  = $contact->status;
            $this->notes   = $contact->notes ?? '';
        }

        $this->resetValidation();
        $this->js("document.dispatchEvent(new CustomEvent('modal-show',{detail:{name:'modal-contact'}}))");
    }

    public function saveContact(): void
    {
        $this->validate([
            'name'    => 'required|string|max:150',
            'company' => 'nullable|string|max:150',
            'email'   => 'nullable|email|max:150',
            'phone'   => 'nullable|string|max:50',
            'status'  => 'required|in:lead,prospect,customer,churned',
            'notes'   => 'nullable|string|max:2000',
        ]);

        $data = [
            'name'    => $this->name,
            'company' => $this->company ?: null,
            'email'   => $this->email ?: null,
            'phone'   => $this->phone ?: null,
            'status'  => $this->status,
            'notes'   => $this->notes ?: null,
            'user_id' => auth()->id(),
        ];

        $isUpdate = $this->contactId > 0;

        if ($isUpdate) {
            $contact = Contact::where('id', $this->contactId)->where('user_id', auth()->id())->firstOrFail();
            $contact->update($data);
        } else {
            Contact::create($data);
        }

        $this->notifySuccess(__($isUpdate ? 'contacts.notify.updated' : 'contacts.notify.created'));

        $this->js("document.dispatchEvent(new CustomEvent('modal-close',{detail:{name:'modal-contact'}}))");
        $this->dispatch('pg:eventRefresh-contacts-table');
        $this->dispatch('contacts-updated');
    }
}; ?>

<div>
    <x-starcho-popup-stripe
        name="modal-contact"
        width="md:w-[640px]"
        icon="fas fa-users"
        :title="__('contacts.modal_contact')"
        :title-accent="$contactId > 0 ? __('contacts.modal_title_edit') : __('contacts.modal_title_new')"
        :subtitle="__('contacts.modal_subtitle')"
        submit-action="saveContact"
        :cancel-label="__('contacts.btn_cancel')"
        :save-label="$contactId > 0 ? __('contacts.btn_update') : __('contacts.btn_save')"
        :saving-label="__('contacts.btn_saving')"
        loading-target="saveContact"
    >

                    {{-- Nombre --}}
                    <div class="sc-field">
                           <label class="sc-label sc-label-stripe">{{ __('contacts.field_name') }} <span style="color:#ef4444">*</span></label>
                        <input wire:model="name" type="text" placeholder="{{ __('contacts.field_name_ph') }}"
                               class="sc-input sc-input-stripe">
                        @error('name')
                           <span class="sc-field-error sc-field-error-stripe">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Empresa --}}
                    <div class="sc-field">
                           <label class="sc-label sc-label-stripe">{{ __('contacts.field_company') }}</label>
                        <input wire:model="company" type="text" placeholder="{{ __('contacts.field_company_ph') }}"
                               class="sc-input sc-input-stripe">
                        @error('company')
                           <span class="sc-field-error sc-field-error-stripe">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Email + Teléfono --}}
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                        <div class="sc-field">
                            <label class="sc-label sc-label-stripe">{{ __('contacts.field_email') }}</label>
                            <input wire:model="email" type="email" placeholder="{{ __('contacts.field_email_ph') }}"
                                class="sc-input sc-input-stripe">
                            @error('email')
                            <span class="sc-field-error sc-field-error-stripe">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="sc-field">
                            <label class="sc-label sc-label-stripe">{{ __('contacts.field_phone') }}</label>
                            <input wire:model="phone" type="text" placeholder="{{ __('contacts.field_phone_ph') }}"
                                class="sc-input sc-input-stripe">
                            @error('phone')
                            <span class="sc-field-error sc-field-error-stripe">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    {{-- Estado --}}
                    <div class="sc-field">
                        <label class="sc-label sc-label-stripe">{{ __('contacts.field_status') }}</label>
                        <select wire:model="status" class="sc-select sc-select-stripe app-select">
                            <option value="lead">👥 {{ __('contacts.status_lead') }}</option>
                            <option value="prospect">🎯 {{ __('contacts.status_prospect') }}</option>
                            <option value="customer">💼 {{ __('contacts.status_customer') }}</option>
                            <option value="churned">❌ {{ __('contacts.status_churned') }}</option>
                        </select>
                        @error('status')
                        <span class="sc-field-error sc-field-error-stripe">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Notas --}}
                    <div class="sc-field">
                        <label class="sc-label sc-label-stripe">{{ __('contacts.field_notes') }}</label>
                        <textarea wire:model="notes" placeholder="{{ __('contacts.field_notes_ph') }}" rows="3"
                                  class="sc-textarea sc-textarea-stripe"></textarea>
                        @error('notes')
                        <span class="sc-field-error sc-field-error-stripe">{{ $message }}</span>
                        @enderror
                    </div>

        <x-slot:actions>
            <x-starcho-btn-stripe
                type="submit"
                icon="fas fa-bolt"
                :label="$contactId > 0 ? __('contacts.btn_update') : __('contacts.btn_save')"
                :loading-label="__('contacts.btn_saving')"
                loading-target="saveContact"
            />
        </x-slot:actions>
    </x-starcho-popup-stripe>
</div>
