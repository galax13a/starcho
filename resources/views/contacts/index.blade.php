<x-layouts::app :title="'Contactos'">

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Contactos</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Gestiona tus leads, prospectos y clientes</p>
    </div>

    <livewire:app.contacts-table />
    <livewire:app.contact-modal />

</x-layouts::app>
