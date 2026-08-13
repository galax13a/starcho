<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Instalar Starcho</title>
    <style>
        :root { color-scheme: dark; font-family: Inter, ui-sans-serif, system-ui, sans-serif; background:#09090b; color:#f4f4f5; }
        body { margin:0; min-height:100vh; display:grid; place-items:center; padding:24px; background:radial-gradient(circle at 10% 10%,#312e81 0,transparent 35%),#09090b; }
        main { width:min(760px,100%); border:1px solid #3f3f46; border-radius:24px; padding:32px; background:rgba(24,24,27,.94); box-shadow:0 24px 80px rgba(0,0,0,.35); }
        h1 { margin:0 0 8px; font-size:30px; } p { color:#a1a1aa; line-height:1.6; }
        .grid { display:grid; gap:10px; margin:24px 0; } .check { display:flex; justify-content:space-between; gap:16px; padding:12px 14px; border:1px solid #3f3f46; border-radius:12px; }
        .ok { color:#86efac; } .bad { color:#fca5a5; } small { color:#a1a1aa; overflow-wrap:anywhere; text-align:right; }
        form { display:grid; gap:14px; border-top:1px solid #3f3f46; padding-top:24px; } label { display:grid; gap:6px; color:#d4d4d8; font-size:14px; } input { border:1px solid #52525b; border-radius:10px; background:#18181b; color:#fff; padding:11px 12px; }
        button { border:0; border-radius:10px; padding:12px 16px; color:#fff; background:linear-gradient(90deg,#7c3aed,#db2777); font-weight:700; cursor:pointer; } button:disabled { opacity:.5; cursor:not-allowed; }
        .alert { border-radius:10px; padding:12px 14px; margin:16px 0; } .success { background:#064e3b; color:#a7f3d0; } .warning { background:#78350f; color:#fde68a; } .error { background:#7f1d1d; color:#fecaca; }
        ul { margin:8px 0 0; padding-left:20px; color:#a1a1aa; } a { color:#c4b5fd; }
    </style>
</head>
<body>
<main>
    <h1>Instalar Starcho</h1>
    <p>Asistente de instalación para comprobar el servidor, ejecutar migraciones y crear el primer administrador sin usar credenciales por defecto.</p>

    @foreach(['success' => 'success', 'warning' => 'warning', 'error' => 'error'] as $key => $class)
        @if(session($key)) <div class="alert {{ $class }}">{{ session($key) }}</div> @endif
    @endforeach

    @if($installed)
        <div class="alert success">La aplicación ya está instalada. Por seguridad, desactiva <code>STARCHO_INSTALL_ENABLED</code> y limpia la configuración.</div>
        <p><a href="{{ route('home') }}">Volver al sitio</a></p>
    @else
        <section class="grid" aria-label="Comprobaciones del sistema">
            @foreach($checks as $check)
                <div class="check">
                    <span class="{{ $check['ok'] ? 'ok' : 'bad' }}">{{ $check['ok'] ? '✓' : '✗' }} {{ $check['label'] }}</span>
                    <small>{{ $check['detail'] }}</small>
                </div>
            @endforeach
        </section>

        @if($errors->any())
            <div class="alert error"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif

        <form method="post" action="{{ route('install.store') }}">
            @csrf
            <label>Nombre del administrador <input name="name" value="{{ old('name', 'Administrador') }}" required maxlength="120"></label>
            <label>Correo del administrador <input type="email" name="email" value="{{ old('email') }}" required maxlength="255"></label>
            <label>Contraseña segura <input type="password" name="password" required minlength="8" autocomplete="new-password"></label>
            <label>Repite la contraseña <input type="password" name="password_confirmation" required minlength="8" autocomplete="new-password"></label>
            <button type="submit" @disabled(collect($checks)->contains(fn ($check) => ! $check['ok']))>Instalar Starcho</button>
        </form>
    @endif
</main>
</body>
</html>
