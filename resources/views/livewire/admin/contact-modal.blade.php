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
            <flux:heading size="lg">{{ $contactId > 0 ? 'Editar Contacto' : 'Nuevo Contacto' }}</flux:heading>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <flux:field>
                    <flux:label>Nombre</flux:label>
                    <flux:input wire:model="name" placeholder="Nombre completo" />
                    <flux:error name="name" />
                </flux:field>

                <flux:field>
                    <flux:label>Empresa</flux:label>
                    <flux:input wire:model="company" placeholder="Empresa del contacto" />
                    <flux:error name="company" />
                </flux:field>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <flux:field>
                    <flux:label>Email</flux:label>
                    <flux:input wire:model="email" type="email" placeholder="email@ejemplo.com" />
                    <flux:error name="email" />
                </flux:field>

                <flux:field>
                    <flux:label>Telefono</flux:label>
                    <flux:input wire:model="phone" placeholder="+34 600 000 000" />
                    <flux:error name="phone" />
                </flux:field>
            </div>

            <flux:field>
                <flux:label>Estado</flux:label>
                <flux:select wire:model="status">
                    <flux:select.option value="lead">Lead</flux:select.option>
                    <flux:select.option value="prospect">Prospecto</flux:select.option>
                    <flux:select.option value="customer">Cliente</flux:select.option>
                    <flux:select.option value="churned">Perdido</flux:select.option>
                </flux:select>
                <flux:error name="status" />
            </flux:field>

            <flux:field>
                <flux:label>Notas</flux:label>
                <flux:textarea wire:model="notes" rows="3" placeholder="Notas sobre este contacto" />
                <flux:error name="notes" />
            </flux:field>

            <div class="flex justify-end gap-2 pt-1">
                <flux:modal.close>
                    <flux:button variant="ghost">Cancelar</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="saveContact">{{ $contactId > 0 ? 'Actualizar' : 'Guardar' }}</span>
                    <span wire:loading wire:target="saveContact">Guardando…</span>
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>
