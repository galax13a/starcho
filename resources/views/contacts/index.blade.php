<x-layouts::app :title="__('contacts.page_title')">
    <div class="sa-page starcho-stripeX">
        <div class="sa-page-header starcho-stripeX-header">
            <div class="sa-page-header-left">
                <h1>{{ __('contacts.page_title') }}</h1>
                <p>{{ __('contacts.page_subtitle') }}</p>
            </div>
            <div class="sa-page-header-right">
                <button onclick="Livewire.dispatch('openContact', {id:0})" class="sc-btn sc-btn-stripe starcho-stripeX-btn">
                    <i class="fas fa-plus"></i> {{ __('contacts.new_contact') }}
                </button>
            </div>
        </div>

        <livewire:app.contacts-stats />

        <livewire:app.contacts-table />
        <livewire:app.contact-modal />
        <livewire:app.contacts-import-modal />
    </div>
</x-layouts::app>

<style>
@media (max-width: 900px) {
    .stats-grid-contacts { grid-template-columns: repeat(3, 1fr) !important; }
}
@media (max-width: 540px) {
    .stats-grid-contacts { grid-template-columns: repeat(2, 1fr) !important; }
}
</style>
