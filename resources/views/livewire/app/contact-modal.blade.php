<?php

use App\Models\Contact;
use Livewire\Attributes\On;
use Livewire\Volt\Component;

new class extends Component
{
    public bool $show = false;
    public ?int $contactId = null;

    public string $name = '';
    public string $company = '';
    public string $email = '';
    public string $phone = '';
    public string $status = 'lead';
    public string $notes = '';

    protected $rules = [
        'name'    => 'required|string|max:150',
        'company' => 'nullable|string|max:150',
        'email'   => 'nullable|email|max:150',
        'phone'   => 'nullable|string|max:50',
        'status'  => 'required|in:lead,prospect,customer,churned',
        'notes'   => 'nullable|string|max:2000',
    ];

    #[On('openContact')]
    public function openContact(?int $id = null): void
    {
        $this->reset(['name', 'company', 'email', 'phone', 'notes']);
        $this->status    = 'lead';
        $this->contactId = $id;

        if ($id) {
            $contact = Contact::where('id', $id)->where('user_id', auth()->id())->firstOrFail();
            $this->name    = $contact->name;
            $this->company = $contact->company ?? '';
            $this->email   = $contact->email ?? '';
            $this->phone   = $contact->phone ?? '';
            $this->status  = $contact->status;
            $this->notes   = $contact->notes ?? '';
        }

        $this->show = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name'    => $this->name,
            'company' => $this->company ?: null,
            'email'   => $this->email ?: null,
            'phone'   => $this->phone ?: null,
            'status'  => $this->status,
            'notes'   => $this->notes ?: null,
            'user_id' => auth()->id(),
        ];

        if ($this->contactId) {
            Contact::where('id', $this->contactId)->where('created_by', auth()->id())->update($data);
        } else {
            Contact::create($data);
        }

        $this->show = false;
        $this->dispatch('pg:eventRefresh-contacts-table');
    }

    public function close(): void
    {
        $this->show = false;
    }
}
?>

<div x-show="$wire.show" x-data>
    <div class="app-popup-overlay">
        <div class="absolute inset-0 bg-black/60" wire:click="close"></div>
        <div class="app-popup-card">
            <div class="app-popup-header">
                <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">
                    {{ $contactId ? 'Editar Contacto' : 'Nuevo Contacto' }}
                </h2>
                <button wire:click="close" class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200">
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="app-popup-body">
                <form wire:submit="save" class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Nombre *</label>
                            <input wire:model="name" type="text" class="w-full rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-3 py-2 text-sm text-zinc-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-violet-500" placeholder="Nombre completo">
                            @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Empresa</label>
                            <input wire:model="company" type="text" class="w-full rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-3 py-2 text-sm text-zinc-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-violet-500" placeholder="Empresa">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Estado</label>
                            <select wire:model="status" class="app-select w-full rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-3 py-2 text-sm text-zinc-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-violet-500">
                                <option value="lead">Lead</option>
                                <option value="prospect">Prospecto</option>
                                <option value="customer">Cliente</option>
                                <option value="churned">Perdido</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Email</label>
                            <input wire:model="email" type="email" class="w-full rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-3 py-2 text-sm text-zinc-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-violet-500" placeholder="email@ejemplo.com">
                            @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Teléfono</label>
                            <input wire:model="phone" type="text" class="w-full rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-3 py-2 text-sm text-zinc-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-violet-500" placeholder="+34 600 000 000">
                        </div>

                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Notas</label>
                            <textarea wire:model="notes" rows="3" class="w-full rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-3 py-2 text-sm text-zinc-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-violet-500 resize-none" placeholder="Notas sobre este contacto..."></textarea>
                        </div>
                    </div>
                </form>
            </div>

            <div class="app-popup-footer">
                <button type="button" wire:click="close"
                    class="px-4 py-2 text-sm font-medium text-zinc-700 dark:text-zinc-300 bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 rounded-lg transition">
                    Cancelar
                </button>
                <button type="submit" wire:click="save"
                    class="px-4 py-2 text-sm font-medium text-white bg-violet-600 hover:bg-violet-700 rounded-lg transition">
                    <span wire:loading.remove wire:target="save">{{ $contactId ? 'Actualizar' : 'Crear' }}</span>
                    <span wire:loading wire:target="save">Guardando…</span>
                </button>
            </div>
    </div>
</div>
</div>
