<div class="flex flex-wrap items-center gap-2">
    {{-- Filtro estado --}}
    <select wire:model.live="filterStatus"
        class="sc-select sc-select-stripe starcho-stripeX-filter"
        style="height:36px;font-size:12.5px;padding:0 34px 0 12px;width:auto;">
        <option value="">{{ __('contacts.filter_all_statuses') }}</option>
        <option value="lead">{{ __('contacts.status_lead') }}</option>
        <option value="prospect">{{ __('contacts.status_prospect') }}</option>
        <option value="customer">{{ __('contacts.status_customer') }}</option>
        <option value="churned">{{ __('contacts.status_churned') }}</option>
    </select>
</div>
