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
    }
}; ?>

<div>
    <flux:modal name="modal-contact" class="md:w-[640px] !p-0 app-popup-card" focusable>

        <div class="sc-modal-kick">

            {{-- Header ── Kick style ── --}}
            <div class="sc-modal-kick-header">
                <div style="width:32px;height:32px;border-radius:5px;background:rgba(37,244,238,.12);border:1px solid rgba(37,244,238,.25);display:flex;align-items:center;justify-content:center;">
                    <i class="fas fa-users" style="color:#25f4ee;font-size:13px;"></i>
                </div>
                <div>
                    <div class="sc-modal-kick-title">
                        {!! $contactId > 0 ? '<span>Editar</span> Contacto' : '<span>Nuevo</span> Contacto' !!}
                    </div>
                    <div style="font-size:11px;color:var(--kick-text2);margin-top:1px;">Sistema de gestión de contactos</div>
                </div>
            </div>

            {{-- Body ── Kick style inputs ── --}}
            <form wire:submit="saveContact">
                <div class="sc-modal-kick-body" style="display:flex;flex-direction:column;gap:16px;">

                    {{-- Nombre --}}
                    <div class="sc-field">
                        <label class="sc-label sc-label-kick">Nombre <span style="color:#ff4242">*</span></label>
                        <input wire:model="name" type="text" placeholder="Nombre completo…"
                               class="sc-input sc-input-kick">
                        @error('name')
                        <span class="sc-field-error sc-field-error-kick">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Empresa --}}
                    <div class="sc-field">
                        <label class="sc-label sc-label-kick">Empresa</label>
                        <input wire:model="company" type="text" placeholder="Empresa del contacto…"
                               class="sc-input sc-input-kick">
                        @error('company')
                        <span class="sc-field-error sc-field-error-kick">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Email + Teléfono --}}
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                        <div class="sc-field">
                            <label class="sc-label sc-label-kick">Email</label>
                            <input wire:model="email" type="email" placeholder="email@ejemplo.com"
                                   class="sc-input sc-input-kick">
                            @error('email')
                            <span class="sc-field-error sc-field-error-kick">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="sc-field">
                            <label class="sc-label sc-label-kick">Teléfono</label>
                            <input wire:model="phone" type="text" placeholder="+34 600 000 000"
                                   class="sc-input sc-input-kick">
                            @error('phone')
                            <span class="sc-field-error sc-field-error-kick">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    {{-- Estado --}}
                    <div class="sc-field">
                        <label class="sc-label sc-label-kick">Estado</label>
                        <select wire:model="status" class="sc-select sc-select-kick app-select">
                            <option value="lead">👥 Lead</option>
                            <option value="prospect">🎯 Prospecto</option>
                            <option value="customer">💼 Cliente</option>
                            <option value="churned">❌ Perdido</option>
                        </select>
                        @error('status')
                        <span class="sc-field-error sc-field-error-kick">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Notas --}}
                    <div class="sc-field">
                        <label class="sc-label sc-label-kick">Notas</label>
                        <textarea wire:model="notes" placeholder="Notas sobre este contacto…" rows="3"
                                  class="sc-textarea sc-textarea-kick"></textarea>
                        @error('notes')
                        <span class="sc-field-error sc-field-error-kick">{{ $message }}</span>
                        @enderror
                    </div>

                </div>

                {{-- Footer ── Kick style ── --}}
                <div class="sc-modal-kick-footer">
                    <flux:modal.close>
                        <button type="button" class="sc-btn sc-btn-kick sc-btn-ghost">
                            Cancelar
                        </button>
                    </flux:modal.close>
                    <button type="submit" class="sc-btn sc-btn-kick" wire:loading.attr="disabled" wire:loading.class="opacity-60">
                        <span wire:loading.remove="" wire:target="saveContact">
                            <i class="fas fa-bolt" style="font-size:11px;"></i>
                            {!! $contactId > 0 ? 'Actualizar Contacto' : 'Crear Contacto' !!}
                        </span>
                        <span wire:loading="" wire:target="saveContact">Guardando…</span>
                    </button>
                </div>
            </form>

        </div>

    </flux:modal>
</div>
