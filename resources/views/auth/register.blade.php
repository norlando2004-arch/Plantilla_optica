<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Crear cuenta - Óptica</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg-main: #020817;
            --bg-panel: rgba(9, 15, 30, 0.92);
            --bg-panel-soft: rgba(15, 23, 42, 0.9);
            --accent: #22d3ee;
            --accent-soft: rgba(45, 212, 191, 0.18);
            --accent-strong: #38bdf8;
            --danger: #f97373;
            --text-main: #e5f0ff;
            --text-soft: #9ca3af;
            --border-subtle: rgba(148, 163, 184, 0.28);
            --radius-xl: 24px;
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

        .auth-page { width: 100%; max-width: 1120px; margin: 1.75rem auto; padding-inline: 1.5rem; }

        .auth-shell {
            display: grid;
            grid-template-columns: minmax(0, 1.12fr) minmax(0, 0.9fr);
            gap: 0;
            border-radius: 28px;
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.96), rgba(15, 23, 42, 0.94));
            border: 1px solid rgba(148, 163, 184, 0.5);
            box-shadow: 0 28px 80px rgba(15, 23, 42, 0.9), 0 0 0 1px rgba(15, 23, 42, 0.9);
            overflow: hidden;
            position: relative;
        }

        .auth-shell::before {
            content: "";
            position: absolute;
            inset: -40%;
            background:
                radial-gradient(900px circle at 0% 0%, rgba(45, 212, 191, 0.22), transparent 60%),
                radial-gradient(700px circle at 120% 120%, rgba(56, 189, 248, 0.2), transparent 55%);
            opacity: 0.35;
            mix-blend-mode: screen;
            pointer-events: none;
        }

        .auth-shell-inner { position: relative; display: contents; z-index: 1; }

        .auth-visual {
            position: relative;
            padding: 2.25rem 2.5rem;
            border-right: 1px solid rgba(148, 163, 184, 0.38);
            background: radial-gradient(circle at top left, rgba(34, 211, 238, 0.18), transparent 55%),
                        radial-gradient(circle at bottom right, rgba(59, 130, 246, 0.22), transparent 55%);
            overflow: hidden;
        }

        .auth-visual-orbit {
            position: absolute;
            inset: -40px;
            opacity: 0.45;
            background:
                radial-gradient(circle at 10% 20%, rgba(34, 211, 238, 0.55), transparent 55%),
                radial-gradient(circle at 60% 80%, rgba(56, 189, 248, 0.6), transparent 60%);
            mix-blend-mode: screen;
            pointer-events: none;
        }

        .auth-visual-grid {
            position: absolute;
            inset: 10%;
            border-radius: 999px;
            background-image:
                linear-gradient(rgba(148, 163, 184, 0.2) 1px, transparent 1px),
                linear-gradient(90deg, rgba(148, 163, 184, 0.18) 1px, transparent 1px);
            background-size: 22px 22px;
            opacity: 0.26;
            mask-image: radial-gradient(circle at 30% 20%, black 0, transparent 55%);
        }

        .brand-pill {
            position: relative;
            display: inline-flex;
            align-items: center;
            gap: 0.55rem;
            padding: 0.32rem 0.72rem 0.32rem 0.35rem;
            border-radius: 999px;
            background: rgba(15, 23, 42, 0.78);
            border: 1px solid rgba(148, 163, 184, 0.65);
            backdrop-filter: blur(20px);
            box-shadow: 0 18px 45px rgba(15, 23, 42, 0.85);
        }

        .brand-pill-logo {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            border-radius: 999px;
            background: radial-gradient(circle at 20% 0%, #e0f2fe, transparent 50%),
                        radial-gradient(circle at 100% 100%, #22c55e, transparent 45%),
                        #0f172a;
            color: #e0f2fe;
        }

        .brand-pill span {
            font-size: 0.78rem;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: var(--text-soft);
        }

        .brand-pill strong { font-size: 0.78rem; color: #e0f2fe; }

        .auth-visual-main { position: relative; margin-top: 2.25rem; max-width: 420px; }

        .auth-title { font-size: 1.9rem; line-height: 1.2; letter-spacing: -0.03em; margin: 0; }

        .auth-title span {
            background: linear-gradient(to right, #22d3ee, #38bdf8, #a855f7);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .auth-subtitle { margin-top: 0.65rem; font-size: 0.95rem; color: var(--text-soft); }

        .auth-highlight-card {
            margin-top: 2.2rem;
            border-radius: 20px;
            padding: 1.15rem 1.1rem;
            background:
                radial-gradient(circle at 0 0, rgba(56, 189, 248, 0.35), transparent 60%),
                radial-gradient(circle at 110% 120%, rgba(45, 212, 191, 0.35), transparent 60%),
                rgba(15, 23, 42, 0.88);
            border: 1px solid rgba(148, 163, 184, 0.7);
            box-shadow: 0 22px 45px rgba(15, 23, 42, 0.9);
            backdrop-filter: blur(24px);
        }

        .auth-highlight-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.85rem;
        }

        .auth-highlight-title { display: flex; align-items: center; gap: 0.65rem; font-size: 0.88rem; font-weight: 500; }

        .auth-highlight-icon {
            width: 26px;
            height: 26px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(8, 47, 73, 0.96);
            border: 1px solid rgba(56, 189, 248, 0.6);
            color: #e0f2fe;
        }

        .auth-highlight-badge {
            font-size: 0.75rem;
            padding: 0.2rem 0.6rem;
            border-radius: 999px;
            background: rgba(22, 163, 74, 0.26);
            color: #bbf7d0;
            border: 1px solid rgba(74, 222, 128, 0.5);
        }

        .auth-highlight-body {
            margin-top: 0.9rem;
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.75rem 1.2rem;
            font-size: 0.8rem;
            color: var(--text-soft);
        }

        .auth-highlight-body strong {
            color: #e5e7eb;
            font-weight: 500;
            display: block;
            font-size: 0.86rem;
        }

        .auth-visual-footer {
            position: relative;
            margin-top: 2rem;
            font-size: 0.75rem;
            color: rgba(148, 163, 184, 0.92);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .auth-visual-dot-row { display: inline-flex; gap: 0.25rem; }

        .auth-visual-dot {
            width: 6px;
            height: 6px;
            border-radius: 999px;
            background: linear-gradient(to bottom right, #22d3ee, #38bdf8);
            box-shadow: 0 0 0 2px rgba(56, 189, 248, 0.3);
        }

        .auth-panel { position: relative; padding: 2.25rem 2.5rem; background: radial-gradient(circle at top, rgba(15, 23, 42, 0.98), rgba(15, 23, 42, 0.94)); }

        .auth-panel-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            margin-bottom: 2rem;
        }

        .back-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 999px;
            border: 1px solid rgba(148, 163, 184, 0.7);
            background: rgba(15, 23, 42, 0.92);
            color: var(--text-soft);
            cursor: pointer;
            transition: all 0.18s ease-out;
        }

        .back-button:hover {
            border-color: rgba(148, 163, 184, 1);
            color: #e5e7eb;
            transform: translateX(-1px);
            box-shadow: 0 5px 18px rgba(15, 23, 42, 0.75);
        }

        .brand-text { text-align: right; }

        .brand-text h2 {
            margin: 0;
            font-size: 0.78rem;
            letter-spacing: 0.22em;
            text-transform: uppercase;
            color: rgba(148, 163, 184, 0.9);
        }

        .brand-text p { margin: 0.16rem 0 0; font-size: 0.86rem; color: var(--text-soft); }

        .auth-panel-main-header h1 { margin: 0; font-size: 1.6rem; letter-spacing: -0.03em; }

        .auth-panel-main-header p { margin: 0.45rem 0 0; font-size: 0.9rem; color: var(--text-soft); }

        .auth-panel-main-header p span { color: #a5b4fc; }

        .auth-panel-main { margin-top: 1.75rem; max-width: 480px; margin-inline: auto; }

        .auth-panel-main-header { text-align: center; }

        form { margin: 0; }

        .field { position: relative; margin: 0 auto 1.1rem; max-width: 480px; }

        .field-label {
            position: absolute;
            left: 0.95rem;
            top: 50%;
            transform: translateY(-50%);
            font-size: 0.82rem;
            color: var(--text-soft);
            pointer-events: none;
            transition: all 0.17s ease-out;
        }

        .field-input {
            width: 100%;
            border-radius: 999px;
            border: 1px solid var(--border-subtle);
            background: rgba(15, 23, 42, 0.94);
            color: var(--text-main);
            padding: 0.85rem 2.5rem 0.85rem 0.95rem;
            font-size: 0.9rem;
            outline: none;
            text-align: center;
            transition: all 0.17s ease-out;
        }

        .field-input::placeholder { color: transparent; }

        .field-icon {
            position: absolute;
            right: 0.9rem;
            top: 50%;
            transform: translateY(-50%);
            width: 18px;
            height: 18px;
            color: rgba(148, 163, 184, 0.95);
            pointer-events: none;
        }

        .field-input:focus {
            border-color: rgba(56, 189, 248, 0.9);
            box-shadow: 0 0 0 1px rgba(45, 212, 191, 0.65), 0 0 0 10px rgba(8, 47, 73, 0.6);
            background: rgba(15, 23, 42, 0.96);
        }

        .field-input:focus + .field-label,
        .field-input:not(:placeholder-shown) + .field-label,
        .field-input.field-filled + .field-label {
            top: 0.2rem;
            transform: translateY(0);
            font-size: 0.68rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: rgba(148, 163, 184, 0.9);
        }

        .input-error {
            border-color: rgba(248, 113, 113, 0.9) !important;
            box-shadow: 0 0 0 1px rgba(248, 113, 113, 0.6), 0 0 0 10px rgba(127, 29, 29, 0.5) !important;
        }

        .input-error + .field-label { color: #fecaca !important; }

        .input-valid { border-color: rgba(52, 211, 153, 0.8); }

        .field-error-text { margin: 0.3rem 0 0; min-height: 1em; font-size: 0.78rem; color: #fecaca; }

        .password-strength {
            margin-top: 0.35rem;
            height: 4px;
            border-radius: 999px;
            background: rgba(15, 23, 42, 0.9);
            border: 1px solid rgba(148, 163, 184, 0.4);
            overflow: hidden;
        }

        .password-strength-bar {
            width: 0;
            height: 100%;
            border-radius: inherit;
            background-color: #fecaca;
            transition: width 0.2s ease-out, background-color 0.2s ease-out;
        }

        .btn-primary {
            width: auto;
            max-width: 100%;
            margin-top: 0.4rem;
            margin-inline: auto;
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
            transition: transform 0.1s ease-out, box-shadow 0.1s ease-out, filter 0.1s ease-out;
        }

        .btn-primary:hover:not(:disabled) {
            filter: brightness(1.05);
            transform: translateY(-1px);
            box-shadow: 0 22px 60px rgba(56, 189, 248, 0.7), 0 0 0 1px rgba(15, 23, 42, 1);
        }

        .btn-primary:active:not(:disabled) {
            transform: translateY(0);
            box-shadow: 0 10px 26px rgba(15, 23, 42, 0.9), 0 0 0 1px rgba(15, 23, 42, 1);
        }

        .btn-primary:disabled { cursor: wait; opacity: 0.9; }

        .btn-primary svg { width: 16px; height: 16px; }

        .btn-primary span { display: inline-flex; align-items: center; gap: 0.35rem; }

        .secondary-link { margin-top: 1rem; font-size: 0.82rem; color: var(--text-soft); text-align: center; }

        .secondary-link a {
            color: #e5e7eb;
            font-weight: 500;
            text-decoration: underline;
            text-decoration-thickness: 1px;
            text-underline-offset: 3px;
        }

        .secondary-link a:hover { color: #bfdbfe; }

        .hidden { display: none !important; }
        .items-center { align-items: center; }
        .gap-2 { gap: 0.5rem; }
        .animate-spin { animation: spin 0.9s linear infinite; }

        @keyframes spin { to { transform: rotate(360deg); } }

        @media (max-width: 950px) {
            .auth-shell { grid-template-columns: minmax(0, 1fr); }
            .auth-visual { display: none; }
            .auth-panel { padding-inline: 1.75rem; }
        }

        @media (max-width: 640px) {
            .auth-page { margin: 1.2rem auto; padding-inline: 1rem; }
            .auth-shell { border-radius: 22px; }
            .auth-panel { padding: 1.6rem 1.4rem 1.5rem; }
            .auth-panel-header { margin-bottom: 1.35rem; }
            .auth-panel-main-header h1 { font-size: 1.45rem; }
            .btn-primary { width: 100%; }
        }
    </style>
</head>
<body>
<div class="auth-page">
    <div class="auth-shell">
        <div class="auth-shell-inner">
            <aside class="auth-visual" aria-hidden="true">
                <div class="auth-visual-orbit"></div>
                <div class="auth-visual-grid"></div>

                <div class="brand-pill">
                    <div class="brand-pill-logo">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="7" />
                            <circle cx="12" cy="12" r="3" />
                            <path d="M5 12s2.5-4 7-4 7 4 7 4-2.5 4-7 4-7-4-7-4z" />
                        </svg>
                    </div>
                    <span>Panel paciente</span>
                    <strong>Óptica digital</strong>
                </div>

                <div class="auth-visual-main">
                    <h1 class="auth-title">
                        Crea tu
                        <span>espacio visual</span>.
                    </h1>
                    <p class="auth-subtitle">
                        Registra tus datos una sola vez y mantén todo tu historial, recetas y
                        citas de la óptica organizado en un solo panel.
                    </p>

                    <div class="auth-highlight-card">
                        <div class="auth-highlight-header">
                            <div class="auth-highlight-title">
                                <div class="auth-highlight-icon">
                                    <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                                        <circle cx="10" cy="8" r="3.5" />
                                    </svg>
                                </div>
                                Perfil de paciente
                            </div>
                            <span class="auth-highlight-badge">Configura en minutos</span>
                        </div>
                        <div class="auth-highlight-body">
                            <div>
                                <strong>Datos esenciales</strong>
                                Nombre, contacto y preferencias visuales actualizados siempre.
                            </div>
                            <div>
                                <strong>Seguridad primero</strong>
                                Contraseñas con indicador de fuerza en tiempo real.
                            </div>
                            <div>
                                <strong>Acceso rápido</strong>
                                Ingresa desde cualquier dispositivo cuando lo necesites.
                            </div>
                            <div>
                                <strong>Experiencia tipo app</strong>
                                Interfaz pensada para sentirse como tu red favorita.
                            </div>
                        </div>
                    </div>
                </div>
            </aside>

            <main class="auth-panel" aria-label="Panel de registro de cuenta">
                <header class="auth-panel-header">
                    <button type="button" class="back-button" onclick="window.location.href='{{ route('landing') }}'" aria-label="Volver al inicio">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M15 18l-6-6 6-6" />
                        </svg>
                    </button>
                    <div class="brand-text">
                        <h2>ÓPTICA</h2>
                        <p>Crea tu cuenta de paciente</p>
                    </div>
                </header>

                <section class="auth-panel-main" aria-label="Formulario de registro">
                    <div class="auth-panel-main-header">
                        <h1>Crear cuenta</h1>
                        <p>
                            Registra tus datos básicos y
                            <span>empieza a cuidar tu visión.</span>
                        </p>
                    </div>

                    <form id="registerForm" method="POST" action="{{ route('register') }}" novalidate>
                        @csrf

                        @if ($errors->has('general'))
                            <div class="field" style="margin-bottom: 0.85rem;">
                                <p class="field-error-text" style="min-height:auto; text-align:center;">
                                    {{ $errors->first('general') }}
                                </p>
                            </div>
                        @endif

                        <div class="field">
                            <input
                                id="regNombre"
                                name="nombre"
                                type="text"
                                class="field-input"
                                autocomplete="name"
                                required
                                placeholder=" "
                                value="{{ old('nombre') }}"
                            >
                            <label for="regNombre" class="field-label">Nombre completo</label>
                            <span class="field-icon">
                                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                                    <circle cx="10" cy="8" r="3.5" />
                                </svg>
                            </span>
                            <p class="field-error-text" data-field-error="regNombre">
                                @error('nombre'){{ $message }}@enderror
                            </p>
                        </div>

                        <div class="field">
                            <input
                                id="regCorreo"
                                name="correo"
                                type="email"
                                class="field-input"
                                autocomplete="email"
                                required
                                placeholder=" "
                                value="{{ old('correo') }}"
                            >
                            <label for="regCorreo" class="field-label">Correo electrónico</label>
                            <span class="field-icon">
                                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="5" width="18" height="14" rx="2" ry="2" />
                                    <polyline points="3 7 12 13 21 7" />
                                </svg>
                            </span>
                            <p class="field-error-text" data-field-error="regCorreo">
                                @error('correo'){{ $message }}@enderror
                            </p>
                        </div>

                        <div class="field">
                            <input
                                id="regContrasena"
                                name="contrasena"
                                type="password"
                                class="field-input"
                                autocomplete="new-password"
                                required
                                minlength="8"
                                placeholder=" "
                            >
                            <label for="regContrasena" class="field-label">Contraseña</label>
                            <span class="field-icon">
                                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="11" width="18" height="10" rx="2" ry="2" />
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                                </svg>
                            </span>
                            <p class="field-error-text" data-field-error="regContrasena">
                                @error('contrasena'){{ $message }}@enderror
                            </p>
                            <div class="password-strength">
                                <div id="passwordStrengthBar" class="password-strength-bar"></div>
                            </div>
                        </div>

                        <div class="field">
                            <input
                                id="regContrasenaConfirmation"
                                name="contrasena_confirmation"
                                type="password"
                                class="field-input"
                                autocomplete="new-password"
                                required
                                minlength="8"
                                placeholder=" "
                            >
                            <label for="regContrasenaConfirmation" class="field-label">Confirmar contraseña</label>
                            <span class="field-icon">
                                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="11" width="18" height="10" rx="2" ry="2" />
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                                </svg>
                            </span>
                            <p class="field-error-text" data-field-error="regContrasenaConfirmation"></p>
                        </div>

                        <button type="submit" class="btn-primary" data-submit-button="register">
                            <span data-label-normal>Crear cuenta</span>
                            <span class="hidden items-center gap-2" data-label-loading>
                                <svg viewBox="0 0 24 24" class="animate-spin" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 12a9 9 0 1 1-6.219-8.56" />
                                </svg>
                                <span>Procesando…</span>
                            </span>
                        </button>

                        <p class="secondary-link">
                            ¿Ya tienes cuenta?
                            <a href="{{ route('login') }}">Iniciar sesión</a>
                        </p>
                    </form>
                </section>
            </main>
        </div>
    </div>
</div>

<script>
(function () {
    const form = document.getElementById('registerForm');
    const submitBtn = form ? form.querySelector('[data-submit-button="register"]') : null;

    const nombre = document.getElementById('regNombre');
    const correo = document.getElementById('regCorreo');
    const pass1 = document.getElementById('regContrasena');
    const pass2 = document.getElementById('regContrasenaConfirmation');

    const errNombre = document.querySelector('[data-field-error="regNombre"]');
    const errCorreo = document.querySelector('[data-field-error="regCorreo"]');
    const errPass1 = document.querySelector('[data-field-error="regContrasena"]');
    const errPass2 = document.querySelector('[data-field-error="regContrasenaConfirmation"]');

    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const strengthBar = document.getElementById('passwordStrengthBar');

    function updateFilledState(el) {
        if (!el) return;
        if (el.value && el.value.trim()) {
            el.classList.add('field-filled');
        } else {
            el.classList.remove('field-filled');
        }
    }

    function setError(el, errorEl, msg) {
        if (!el || !errorEl) return;
        errorEl.textContent = msg || '';
        if (msg) {
            el.classList.add('input-error');
            el.classList.remove('input-valid');
        } else {
            el.classList.remove('input-error');
            if (el.value.trim()) {
                el.classList.add('input-valid');
            } else {
                el.classList.remove('input-valid');
            }
        }
    }

    if (nombre) {
        updateFilledState(nombre);
        if (errNombre) {
            nombre.addEventListener('input', () => {
                const v = nombre.value.trim();
                updateFilledState(nombre);
                setError(nombre, errNombre, v ? '' : 'El nombre es obligatorio.');
            });
        }
    }

    if (correo) {
        updateFilledState(correo);
        if (errCorreo) {
            correo.addEventListener('input', () => {
                const v = correo.value.trim();
                updateFilledState(correo);
                if (!v) {
                    setError(correo, errCorreo, 'El correo es obligatorio.');
                } else if (!emailPattern.test(v)) {
                    setError(correo, errCorreo, 'El correo no tiene un formato válido.');
                } else {
                    setError(correo, errCorreo, '');
                }
            });
        }
    }

    function validatePasswords() {
        if (!pass1 || !pass2 || !errPass1 || !errPass2) return;

        const v1 = pass1.value;
        const v2 = pass2.value;

        updateFilledState(pass1);
        updateFilledState(pass2);

        if (!v1) {
            setError(pass1, errPass1, 'La contraseña es obligatoria.');
        } else if (v1.length < 8) {
            setError(pass1, errPass1, 'La contraseña debe tener mínimo 8 caracteres.');
        } else {
            setError(pass1, errPass1, '');
        }

        if (!v2) {
            setError(pass2, errPass2, 'Confirma tu contraseña.');
        } else if (v1 !== v2) {
            setError(pass2, errPass2, 'La confirmación no coincide.');
        } else {
            setError(pass2, errPass2, '');
        }

        if (strengthBar) {
            let score = 0;
            if (v1.length >= 8) score++;
            if (/[A-Z]/.test(v1)) score++;
            if (/[0-9]/.test(v1)) score++;
            if (/[^A-Za-z0-9]/.test(v1)) score++;

            const percent = [0, 25, 50, 75, 100][score];
            strengthBar.style.width = percent + '%';

            if (score <= 1) {
                strengthBar.style.backgroundColor = '#fecaca';
            } else if (score === 2) {
                strengthBar.style.backgroundColor = '#fde68a';
            } else if (score === 3) {
                strengthBar.style.backgroundColor = '#bef264';
            } else {
                strengthBar.style.backgroundColor = '#22c55e';
            }
        }
    }

    if (pass1) pass1.addEventListener('input', validatePasswords);
    if (pass2) pass2.addEventListener('input', validatePasswords);

    if (form && submitBtn) {
        form.addEventListener('submit', (event) => {
            if (submitBtn.disabled) {
                event.preventDefault();
                return;
            }

            let isValid = true;

            if (nombre && errNombre) {
                const v = nombre.value.trim();
                if (!v) {
                    setError(nombre, errNombre, 'El nombre es obligatorio.');
                    isValid = false;
                }
            }

            if (correo && errCorreo) {
                const v = correo.value.trim();
                if (!v) {
                    setError(correo, errCorreo, 'El correo es obligatorio.');
                    isValid = false;
                } else if (!emailPattern.test(v)) {
                    setError(correo, errCorreo, 'El correo no tiene un formato válido.');
                    isValid = false;
                }
            }

            if (pass1 && pass2 && errPass1 && errPass2) {
                const v1 = pass1.value;
                const v2 = pass2.value;

                if (!v1) {
                    setError(pass1, errPass1, 'La contraseña es obligatoria.');
                    isValid = false;
                } else if (v1.length < 8) {
                    setError(pass1, errPass1, 'La contraseña debe tener mínimo 8 caracteres.');
                    isValid = false;
                }

                if (!v2) {
                    setError(pass2, errPass2, 'Confirma tu contraseña.');
                    isValid = false;
                } else if (v1 !== v2) {
                    setError(pass2, errPass2, 'La confirmación no coincide.');
                    isValid = false;
                }
            }

            if (!isValid) {
                event.preventDefault();
                return;
            }

            submitBtn.disabled = true;
            const normal = submitBtn.querySelector('[data-label-normal]');
            const loading = submitBtn.querySelector('[data-label-loading]');
            if (normal) normal.classList.add('hidden');
            if (loading) loading.classList.remove('hidden');
        });
    }
})();
</script>
</body>
</html>
