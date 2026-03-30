<x-layouts::app :title="'Contactos'">
    <div class="sa-page">
        <div class="sa-page-header">
            <div class="sa-page-header-left">
                <h1>Contactos</h1>
                <p>Gestiona tus leads, prospectos y clientes. Idéntico look admin pero en /app.</p>
            </div>
            <div class="sa-page-header-right">
                <button onclick="Livewire.dispatch('openContact', {id:0})" class="sa-btn sa-btn-primary">
                    <i class="fas fa-plus"></i> Nuevo Contacto
                </button>
            </div>
        </div>

        <livewire:app.contacts-table />
        <livewire:app.contact-modal />
    </div>
</x-layouts::app>
