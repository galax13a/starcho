<?php

use App\Livewire\Concerns\DispatchesStarchoNotify;
use App\Models\PostTag;
use App\Models\SiteLanguage;
use Livewire\Attributes\On;
use Livewire\Component;
use Illuminate\Support\Str;

new class extends Component {
    use DispatchesStarchoNotify;

    public int    $tagId    = 0;
    public array  $tagName  = [];
    public string $tagSlug  = '';
    public array  $languages = [];

    public function mount(): void
    {
        $this->languages = SiteLanguage::activeCodes();
        foreach ($this->languages as $locale) {
            $this->tagName[$locale] = '';
        }
    }

    #[On('openPostTag')]
    public function openTag(int $id = 0): void
    {
        $this->tagId  = $id;
        $this->tagSlug = '';

        foreach ($this->languages as $locale) {
            $this->tagName[$locale] = '';
        }

        if ($id > 0) {
            $tag = PostTag::findOrFail($id);
            foreach ($this->languages as $locale) {
                $this->tagName[$locale] = $tag->getTranslation('name', $locale, false) ?? '';
            }
            $this->tagSlug = $tag->slug;
        }

        $this->resetValidation();
        $this->js("document.dispatchEvent(new CustomEvent('modal-show',{detail:{name:'modal-post-tag'}}))");
    }

    public function updated(string $property): void
    {
        if (str_starts_with($property, 'tagName.') && $this->tagId === 0) {
            $primaryLocale = $this->languages[0] ?? 'es';
            $this->tagSlug = Str::slug($this->tagName[$primaryLocale] ?? '');
        }
    }

    public function saveTag(): void
    {
        $primaryLocale = $this->languages[0] ?? 'es';

        $rules = ['tagSlug' => 'required|string|max:120'];
        foreach ($this->languages as $locale) {
            $rules["tagName.{$locale}"] = $locale === $primaryLocale
                ? 'required|string|max:120'
                : 'nullable|string|max:120';
        }

        $this->validate($rules);

        $isUpdate  = $this->tagId > 0;
        $nameData  = array_filter($this->tagName, fn ($v) => $v !== '');

        $data = [
            'name' => $nameData,
            'slug' => Str::slug($this->tagSlug),
        ];

        if ($isUpdate) {
            PostTag::findOrFail($this->tagId)->update($data);
        } else {
            PostTag::create($data);
        }

        $this->js("document.dispatchEvent(new CustomEvent('modal-close',{detail:{name:'modal-post-tag'}}))");
        $this->dispatch('pg:eventRefresh-admin-post-tags-table');

        $displayName = $this->tagName[$primaryLocale] ?? reset($this->tagName);
        $this->notifyCrud('post_tags', $isUpdate ? 'updated' : 'created', ['name' => $displayName]);
    }
}; ?>

<div>
    <x-starcho-popup-standar
        name="modal-post-tag"
        width="md:w-[440px]"
        submit-action="saveTag"
        :title="$tagId > 0 ? 'Editar etiqueta' : 'Nueva etiqueta'"
        :save-label="$tagId > 0 ? 'Actualizar' : 'Guardar'"
        saving-label="Guardando…"
        loading-target="saveTag"
    >
        {{-- Names per locale --}}
        @if(count($languages) > 1)
        <div>
            <flux:label required class="mb-1.5 block">Nombre</flux:label>
            <div class="rounded-xl border border-zinc-100 dark:border-zinc-800 divide-y divide-zinc-100 dark:divide-zinc-800 overflow-hidden">
                @foreach($languages as $locale)
                <div class="flex items-center gap-3 px-3 py-2.5">
                    <span class="inline-flex items-center justify-center w-7 h-5 rounded text-[10px] font-bold bg-zinc-100 dark:bg-zinc-800 text-zinc-500 dark:text-zinc-400 uppercase tracking-wide shrink-0">
                        {{ strlen($locale) > 2 ? strtoupper(substr($locale, 0, 2)) : strtoupper($locale) }}
                    </span>
                    <flux:input
                        wire:model.live="tagName.{{ $locale }}"
                        placeholder="{{ $loop->first ? 'Laravel, JavaScript…' : '' }}"
                        class="flex-1"
                    />
                </div>
                @endforeach
            </div>
            @error("tagName.".($languages[0] ?? 'es'))
                <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
            @enderror
        </div>
        @else
        <flux:field>
            <flux:label required>Nombre</flux:label>
            <flux:input wire:model.live="tagName.{{ $languages[0] ?? 'es' }}" placeholder="Laravel, JavaScript…" />
            <flux:error name="tagName.{{ $languages[0] ?? 'es' }}" />
        </flux:field>
        @endif

        <flux:field>
            <flux:label required>Slug</flux:label>
            <flux:input wire:model="tagSlug" placeholder="laravel" class="font-mono text-sm" />
            <flux:error name="tagSlug" />
        </flux:field>
    </x-starcho-popup-standar>
</div>
