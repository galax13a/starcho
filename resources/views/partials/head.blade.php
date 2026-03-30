<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>
    {{ filled($title ?? null) ? $title.' - '.config('app.name', 'Laravel') : config('app.name', 'Laravel') }}
</title>

<link rel="icon" href="/favicon.ico" sizes="any">
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">

<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

{{-- Tailwind + Flux base (compartido entre /app y /admin) --}}
@vite(['resources/css/app.css'])

@php
    $starchoLang = [
        'confirm_title'   => __('js.confirm.title'),
        'confirm_message' => __('js.confirm.message'),
        'confirm_ok'      => __('js.confirm.ok'),
        'confirm_cancel'  => __('js.confirm.cancel'),
        'delete_title'    => __('js.delete.title'),
        'delete_message'  => __('js.delete.message'),
        'delete_ok'       => __('js.delete.ok'),
        'toast_success'   => __('js.toasts.success'),
        'toast_warning'   => __('js.toasts.warning'),
        'toast_error'     => __('js.toasts.error'),
        'toast_default'   => __('js.toasts.default'),
    ];
@endphp

<script>
    window.StarchoLang = @json($starchoLang);
</script>

@fluxAppearance
