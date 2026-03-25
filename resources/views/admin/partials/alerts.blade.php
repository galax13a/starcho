@if (session('success'))
    <flux:callout variant="success" icon="check-circle" class="mb-4">
        {{ session('success') }}
    </flux:callout>
@endif

@if (session('error'))
    <flux:callout variant="danger" icon="x-circle" class="mb-4">
        {{ session('error') }}
    </flux:callout>
@endif

@if ($errors->any())
    <flux:callout variant="danger" icon="exclamation-circle" class="mb-4">
        <ul class="list-disc list-inside space-y-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </flux:callout>
@endif
