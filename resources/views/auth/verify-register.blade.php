<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verifica tu cuenta - Óptica</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg-main: #020817;
            --bg-panel: rgba(9, 15, 30, 0.96);
            --accent: #22d3ee;
            --accent-strong: #38bdf8;
            --text-main: #e5f0ff;
            --text-soft: #9ca3af;
            --border-subtle: rgba(148, 163, 184, 0.35);
        }

        *, *::before, *::after { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: "Inter", system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background:
                radial-gradient(800px circle at 0% 0%, rgba(45, 212, 191, 0.12), transparent 60%),
                radial-gradient(800px circle at 100% 100%, rgba(56, 189, 248, 0.14), transparent 60%),
                var(--bg-main);
            color: var(--text-main);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .shell {
            width: 100%;
            max-width: 440px;
            margin: 1.75rem auto;
            padding-inline: 1.5rem;
        }

        .panel {
            border-radius: 26px;
            background: radial-gradient(circle at top, rgba(15, 23, 42, 0.98), rgba(15, 23, 42, 0.96));
            border: 1px solid rgba(148, 163, 184, 0.55);
            box-shadow: 0 26px 70px rgba(15, 23, 42, 0.95);
            padding: 2rem 1.9rem 1.8rem;
        }

        .brand {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1.8rem;
        }

        .brand-left {
            display: inline-flex;
            align-items: center;
            gap: 0.55rem;
            padding: 0.3rem 0.7rem 0.3rem 0.35rem;
            border-radius: 999px;
            border: 1px solid rgba(148, 163, 184, 0.65);
            background: rgba(15, 23, 42, 0.9);
            box-shadow: 0 14px 35px rgba(15, 23, 42, 0.9);
        }

        .brand-logo {
            width: 26px;
            height: 26px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background:
                radial-gradient(circle at 20% 0%, #e0f2fe, transparent 50%),
                radial-gradient(circle at 100% 100%, #22c55e, transparent 45%),
                #020617;
            color: #e0f2fe;
        }

        .brand-left span {
            font-size: 0.75rem;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: var(--text-soft);
        }

        .brand-right {
            text-align: right;
        }

        .brand-right h2 {
            margin: 0;
            font-size: 0.8rem;
            letter-spacing: 0.22em;
            text-transform: uppercase;
            color: rgba(148, 163, 184, 0.92);
        }

        .brand-right p {
            margin: 0.16rem 0 0;
            font-size: 0.85rem;
            color: var(--text-soft);
        }

        h1 {
            margin: 0;
            font-size: 1.5rem;
            letter-spacing: -0.03em;
        }

        .subtitle {
            margin: 0.55rem 0 0;
            font-size: 0.9rem;
            color: var(--text-soft);
        }

        .subtitle span { color: #a5b4fc; }

        .info-box {
            margin-top: 1.3rem;
            border-radius: 1rem;
            padding: 0.8rem 0.9rem;
            background: rgba(15, 23, 42, 0.9);
            border: 1px dashed rgba(148, 163, 184, 0.6);
            font-size: 0.85rem;
            color: var(--text-soft);
        }

        .info-box strong { color: #e5e7eb; }

        form { margin-top: 1.4rem; }

        .field-label { font-size: 0.78rem; color: var(--text-soft); margin-bottom: 0.35rem; }

        .code-input {
            width: 100%;
            border-radius: 999px;
            border: 1px solid var(--border-subtle);
            background: rgba(15, 23, 42, 0.94);
            color: var(--text-main);
            padding: 0.8rem 1.1rem;
            font-size: 1.1rem;
            letter-spacing: 0.5em;
            text-align: center;
            outline: none;
        }

        .code-input::placeholder { color: rgba(148, 163, 184, 0.6); letter-spacing: 0.3em; }

        .error-text { margin-top: 0.4rem; font-size: 0.78rem; color: #fecaca; min-height: 1em; }

        .btn-primary {
            width: 100%;
            margin-top: 1.1rem;
            border-radius: 999px;
            border: none;
            padding: 0.85rem 1rem;
            font-size: 0.95rem;
            font-weight: 600;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: #0b1120;
            background-image: linear-gradient(135deg, #22d3ee, #38bdf8, #a855f7);
            box-shadow: 0 18px 40px rgba(56, 189, 248, 0.55), 0 0 0 1px rgba(15, 23, 42, 1);
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.45rem;
        }

        .btn-primary:disabled { opacity: 0.8; cursor: wait; }

        .secondary-link {
            margin-top: 0.9rem;
            font-size: 0.8rem;
            color: var(--text-soft);
            text-align: center;
        }

        .secondary-link button {
            background: none;
            border: none;
            padding: 0;
            color: #e5e7eb;
            font-size: 0.8rem;
            text-decoration: underline;
            text-underline-offset: 3px;
            cursor: pointer;
        }

        .status {
            margin-top: 0.9rem;
            font-size: 0.8rem;
            color: #bbf7d0;
        }
    </style>
</head>
<body>
<div class="shell">
    <div class="panel">
        <div class="brand">
            <div class="brand-left">
                <div class="brand-logo">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="7" />
                        <circle cx="12" cy="12" r="3" />
                        <path d="M5 12s2.5-4 7-4 7 4 7 4-2.5 4-7 4-7-4-7-4z" />
                    </svg>
                </div>
                <span>Verificación</span>
            </div>
            <div class="brand-right">
                <h2>ÓPTICA</h2>
                <p>Confirma tu correo</p>
            </div>
        </div>

        <h1>Ingresa tu código</h1>
        <p class="subtitle">
            Te enviamos un código de <span>6 dígitos</span> al correo<br>
            <strong>{{ $correo ?? 'tu correo' }}</strong>.
        </p>

        <div class="info-box">
            <p style="margin:0;">Revisa tu bandeja de entrada y, si no lo ves, también la carpeta de <strong>spam</strong> o <strong>promociones</strong>.</p>
        </div>

        <form id="verifyForm" method="POST" action="{{ route('register.verify') }}" novalidate>
            @csrf
            <div style="margin-top:1.3rem;">
                <label for="codigo" class="field-label">Código de verificación</label>
                <input
                    id="codigo"
                    name="codigo"
                    type="text"
                    inputmode="numeric"
                    pattern="[0-9]*"
                    maxlength="6"
                    class="code-input"
                    placeholder="000000"
                    autofocus
                    value="{{ old('codigo') }}"
                >
                <p class="error-text">
                    @error('codigo'){{ $message }}@enderror
                    @if ($errors->has('general')){{ $errors->first('general') }}@endif
                </p>
            </div>

            <button type="submit" class="btn-primary" data-submit-button="verify">
                <span data-label-normal>Confirmar código</span>
            </button>

            <p class="secondary-link">
                Si el código expiró, vuelve a <a href="{{ route('register.show') }}" style="color:#e5e7eb;text-decoration:underline;text-underline-offset:3px;">registrarte</a> para recibir uno nuevo.
            </p>

            @if (session('status'))
                <p class="status">{{ session('status') }}</p>
            @endif
        </form>
    </div>
</div>

<script>
(function () {
    const form = document.getElementById('verifyForm');
    if (!form) return;
    const input = document.getElementById('codigo');
    const btn = form.querySelector('[data-submit-button="verify"]');

    form.addEventListener('submit', function (e) {
        if (btn && btn.disabled) {
            e.preventDefault();
            return;
        }
        if (!input) return;
        input.value = (input.value || '').replace(/\D/g, '').slice(0, 6);
        if (!input.value || input.value.length !== 6) {
            e.preventDefault();
            alert('Ingresa un código de 6 dígitos.');
            return;
        }
        if (btn) btn.disabled = true;
    });
})();
</script>
</body>
</html>
