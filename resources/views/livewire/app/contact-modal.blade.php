<?php

use App\Models\Contact;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component {

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

        if ($this->contactId > 0) {
            Contact::where('id', $this->contactId)->where('user_id', auth()->id())->update($data);
        } else {
            Contact::create($data);
        }

        $this->js("document.dispatchEvent(new CustomEvent('modal-close',{detail:{name:'modal-contact'}}))");
        $this->dispatch('pg:eventRefresh-contacts-table');
        $this->dispatch('contacts-updated');
    }
}; ?>

<div>
    <flux:modal name="modal-contact" class="md:w-[640px] !p-0 app-popup-card" focusable>

        <div class="starcho-stripeX-modal">

            {{-- Header ── Stripe style ── --}}
            <div class="starcho-stripeX-modal-header">
                <div class="starcho-stripeX-modal-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div>
                    <div class="starcho-stripeX-modal-title">
                        {!! $contactId > 0 ? '<span>'.__('contacts.modal_title_edit').'</span> '.__('contacts.modal_contact') : '<span>'.__('contacts.modal_title_new').'</span> '.__('contacts.modal_contact') !!}
                    </div>
                    <div class="starcho-stripeX-modal-subtitle">{{ __('contacts.modal_subtitle') }}</div>
                </div>
            </div>

            {{-- Body ── Stripe style inputs ── --}}
            <form wire:submit="saveContact">
                <div class="starcho-stripeX-modal-body" style="display:flex;flex-direction:column;gap:16px;">

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

                </div>

                {{-- Footer ── Stripe style ── --}}
                <div class="starcho-stripeX-modal-footer">
                    <flux:modal.close>
                        <button type="button" class="sc-btn sc-btn-stripe sc-btn-ghost">
                            {{ __('contacts.btn_cancel') }}
                        </button>
                    </flux:modal.close>
                    <button type="submit" class="sc-btn sc-btn-stripe" wire:loading.attr="disabled" wire:loading.class="opacity-60">
                        <span wire:loading.remove="" wire:target="saveContact">
                            <i class="fas fa-bolt" style="font-size:11px;"></i>
                            {{ $contactId > 0 ? __('contacts.btn_update') : __('contacts.btn_save') }}
                        </span>
                        <span wire:loading="" wire:target="saveContact">{{ __('contacts.btn_saving') }}</span>
                    </button>
                </div>
            </form>

        </div>

    </flux:modal>
</div>
