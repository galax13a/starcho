<x-layouts::admin :title="__('admin_pages.roles_import')">

    <div class="flex items-center gap-3 mb-6">
        <flux:button href="{{ route('admin.roles.index') }}" variant="ghost" icon="arrow-left" size="sm" wire:navigate>
            Volver
        </flux:button>
        <flux:heading size="xl" level="1">Importar Roles desde JSON</flux:heading>
    </div>

    @include('admin.partials.alerts')

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 max-w-4xl">

        {{-- Upload Form --}}
        <div class="p-5 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900">
            <flux:heading size="lg" class="mb-4">Subir archivo JSON</flux:heading>

            <form method="POST" action="{{ route('admin.roles.import.store') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf

                <flux:field>
                    <flux:label>Archivo JSON</flux:label>
                    <flux:description>Selecciona un archivo .json exportado previamente.</flux:description>
                    <input
                        type="file"
                        name="json_file"
                        accept=".json,.txt"
                        required
                        class="mt-1 block w-full text-sm text-zinc-500 dark:text-zinc-400
                               file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0
                               file:text-sm file:font-medium file:bg-blue-600 file:text-white
                               hover:file:bg-blue-700 cursor-pointer"
                    />
                    <flux:error name="json_file" />
                </flux:field>

                <flux:button type="submit" variant="primary" icon="arrow-up-tray">
                    Importar
                </flux:button>
            </form>
        </div>

        {{-- JSON Structure Example --}}
        <div class="p-5 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-900">
            <flux:heading size="lg" class="mb-3">Formato esperado</flux:heading>
            <flux:text class="text-sm text-zinc-500 mb-3">
                El archivo JSON debe tener el siguiente formato. Los permisos se crean automáticamente si no existen.
            </flux:text>
            <pre class="text-xs bg-zinc-900 dark:bg-zinc-950 text-green-400 p-4 rounded-lg overflow-x-auto leading-relaxed"><code>[
  {
    "name": "editor",
    "permissions": [
      "ver-contenido",
      "crear-contenido",
      "editar-contenido"
    ]
  },
  {
    "name": "moderador",
    "permissions": [
      "ver-usuarios",
      "ver-contenido"
    ]
  }
]</code></pre>
            <div class="mt-4">
                <a href="{{ route('admin.roles.export-json') }}"
                   class="inline-flex items-center gap-1.5 text-sm text-blue-600 dark:text-blue-400 hover:underline">
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                    </svg>
                    Descargar roles actuales como JSON
                </a>
            </div>
        </div>

    </div>

</x-layouts::admin>
