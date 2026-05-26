<?php

use App\Livewire\Concerns\DispatchesStarchoNotify;
use App\Models\PostCategory;
use App\Models\SiteLanguage;
use Livewire\Attributes\On;
use Livewire\Component;
use Illuminate\Support\Str;

new class extends Component {
    use DispatchesStarchoNotify;

    public int    $categoryId   = 0;
    public array  $catName      = [];
    public array  $catDesc      = [];
    public string $catSlug      = '';
    public string $catColor     = '#6366f1';
    public int    $catSortOrder = 0;
    public array  $languages    = [];

    public function mount(): void
    {
        $this->languages = SiteLanguage::activeCodes();
        foreach ($this->languages as $locale) {
            $this->catName[$locale] = '';
            $this->catDesc[$locale] = '';
        }
    }

    #[On('openPostCategory')]
    public function openCategory(int $id = 0): void
    {
        $this->categoryId   = $id;
        $this->catSlug      = '';
        $this->catColor     = '#6366f1';
        $this->catSortOrder = 0;

        foreach ($this->languages as $locale) {
            $this->catName[$locale] = '';
            $this->catDesc[$locale] = '';
        }

        if ($id > 0) {
            $cat = PostCategory::findOrFail($id);
            foreach ($this->languages as $locale) {
                $this->catName[$locale] = $cat->getTranslation('name', $locale, false) ?? '';
                $this->catDesc[$locale] = $cat->getTranslation('description', $locale, false) ?? '';
            }
            $this->catSlug      = $cat->slug;
            $this->catColor     = $cat->color ?? '#6366f1';
            $this->catSortOrder = $cat->sort_order ?? 0;
        }

        $this->resetValidation();
        $this->js("document.dispatchEvent(new CustomEvent('modal-show',{detail:{name:'modal-post-category'}}))");
    }

    public function updated(string $property): void
    {
        // Auto-generate slug from primary locale name when creating
        if (str_starts_with($property, 'catName.') && $this->categoryId === 0) {
            $primaryLocale  = $this->languages[0] ?? 'es';
            $this->catSlug  = Str::slug($this->catName[$primaryLocale] ?? '');
        }
    }

    public function saveCategory(): void
    {
        $primaryLocale = $this->languages[0] ?? 'es';

        $rules = [
            "catName.{$primaryLocale}" => 'required|string|max:120',
            'catSlug'                  => 'required|string|max:120',
            'catColor'                 => 'required|string|max:20',
            'catSortOrder'             => 'nullable|integer|min:0',
        ];
        foreach ($this->languages as $locale) {
            $rules["catName.{$locale}"]  = $locale === $primaryLocale ? 'required|string|max:120' : 'nullable|string|max:120';
            $rules["catDesc.{$locale}"]  = 'nullable|string|max:500';
        }

        $this->validate($rules);

        $isUpdate = $this->categoryId > 0;

        $nameData = array_filter($this->catName, fn ($v) => $v !== '');
        $descData = array_filter($this->catDesc, fn ($v) => $v !== '');

        $data = [
            'name'       => $nameData,
            'slug'       => Str::slug($this->catSlug),
            'description'=> $descData ?: null,
            'color'      => $this->catColor,
            'sort_order' => $this->catSortOrder,
        ];

        if ($isUpdate) {
            PostCategory::findOrFail($this->categoryId)->update($data);
        } else {
            PostCategory::create($data);
        }

        $this->js("document.dispatchEvent(new CustomEvent('modal-close',{detail:{name:'modal-post-category'}}))");
        $this->dispatch('pg:eventRefresh-admin-post-categories-table');
        $this->dispatch('categoryUpdated');

        $displayName = $this->catName[$primaryLocale] ?? reset($this->catName);
        $this->notifyCrud('post_categories', $isUpdate ? 'updated' : 'created', ['name' => $displayName]);
    }
}; ?>

<div>
    <x-starcho-popup-standar
        name="modal-post-category"
        width="md:w-[560px]"
        submit-action="saveCategory"
        :title="$categoryId > 0 ? 'Editar categoría' : 'Nueva categoría'"
        :save-label="$categoryId > 0 ? 'Actualizar' : 'Guardar'"
        saving-label="Guardando…"
        loading-target="saveCategory"
    >
        {{-- Names per locale --}}
        @if(count($languages) > 1)
        <div class="rounded-xl border border-zinc-100 dark:border-zinc-800 divide-y divide-zinc-100 dark:divide-zinc-800 overflow-hidden">
            @foreach($languages as $locale)
            <div class="flex items-center gap-3 px-3 py-2.5">
                <span class="inline-flex items-center justify-center w-7 h-5 rounded text-[10px] font-bold bg-zinc-100 dark:bg-zinc-800 text-zinc-500 dark:text-zinc-400 uppercase tracking-wide shrink-0">
                    {{ strlen($locale) > 2 ? strtoupper(substr($locale, 0, 2)) : strtoupper($locale) }}
                </span>
                <flux:input
                    wire:model.live="catName.{{ $locale }}"
                    placeholder="{{ $loop->first ? 'Tecnología, Diseño…' : '' }}"
                    class="flex-1"
                />
            </div>
            @endforeach
        </div>
        @error("catName.".($languages[0] ?? 'es'))
            <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
        @enderror
        @else
        <flux:field>
            <flux:label required>Nombre</flux:label>
            <flux:input wire:model.live="catName.{{ $languages[0] ?? 'es' }}" placeholder="Tecnología, Diseño…" />
            <flux:error name="catName.{{ $languages[0] ?? 'es' }}" />
        </flux:field>
        @endif

        {{-- Descriptions per locale --}}
        @if(count($languages) > 1)
        <div class="space-y-1">
            <flux:label>Descripción</flux:label>
            <div class="rounded-xl border border-zinc-100 dark:border-zinc-800 divide-y divide-zinc-100 dark:divide-zinc-800 overflow-hidden">
                @foreach($languages as $locale)
                <div class="flex items-start gap-3 px-3 py-2.5">
                    <span class="inline-flex items-center justify-center w-7 h-5 rounded text-[10px] font-bold bg-zinc-100 dark:bg-zinc-800 text-zinc-500 dark:text-zinc-400 uppercase tracking-wide shrink-0 mt-1">
                        {{ strlen($locale) > 2 ? strtoupper(substr($locale, 0, 2)) : strtoupper($locale) }}
                    </span>
                    <flux:textarea wire:model="catDesc.{{ $locale }}" rows="2" placeholder="{{ $loop->first ? 'Descripción breve…' : '' }}" class="flex-1 !py-1 !text-sm" />
                </div>
                @endforeach
            </div>
        </div>
        @else
        <flux:field>
            <flux:label>Descripción</flux:label>
            <flux:textarea wire:model="catDesc.{{ $languages[0] ?? 'es' }}" rows="2" placeholder="Descripción breve de la categoría…" />
        </flux:field>
        @endif

        <flux:field>
            <flux:label required>Slug</flux:label>
            <flux:input wire:model="catSlug" placeholder="tecnologia" class="font-mono text-sm" />
            <flux:error name="catSlug" />
        </flux:field>

        <div class="grid grid-cols-2 gap-4">
            <flux:field>
                <flux:label>Color</flux:label>
                <div class="flex items-center gap-2">
                    <input type="color" wire:model="catColor"
                        class="h-9 w-12 rounded-lg border border-zinc-200 dark:border-zinc-700 cursor-pointer bg-white dark:bg-zinc-800 p-0.5"
                    />
                    <flux:input wire:model="catColor" placeholder="#6366f1" class="font-mono text-sm flex-1" />
                </div>
                <flux:error name="catColor" />
            </flux:field>

            <flux:field>
                <flux:label>Orden</flux:label>
                <flux:input type="number" wire:model="catSortOrder" min="0" />
                <flux:error name="catSortOrder" />
            </flux:field>
        </div>
    </x-starcho-popup-standar>
</div>
