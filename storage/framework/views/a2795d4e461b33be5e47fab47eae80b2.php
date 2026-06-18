<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar sesión - Óptica</title>
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

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

        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }

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

        .auth-page {
            width: 100%;
            max-width: 1120px;
            margin: 1.75rem auto;
            padding-inline: 1.5rem;
        }

        .auth-shell {
            display: grid;
            grid-template-columns: minmax(0, 1.12fr) minmax(0, 0.9fr);
            gap: 0;
            border-radius: 28px;
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.96), rgba(15, 23, 42, 0.94));
            border: 1px solid rgba(148, 163, 184, 0.5);
            box-shadow:
                0 28px 80px rgba(15, 23, 42, 0.9),
                0 0 0 1px rgba(15, 23, 42, 0.9);
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

        .auth-shell-inner {
            position: relative;
            display: contents;
            z-index: 1;
        }

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

        .brand-pill strong {
            font-size: 0.78rem;
            color: #e0f2fe;
        }

        .auth-visual-main {
            position: relative;
            margin-top: 2.25rem;
            max-width: 420px;
        }

        .auth-title {
            font-size: 1.9rem;
            line-height: 1.2;
            letter-spacing: -0.03em;
            margin: 0;
        }

        .auth-title span {
            background: linear-gradient(to right, #22d3ee, #38bdf8, #a855f7);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .auth-subtitle {
            margin-top: 0.65rem;
            font-size: 0.95rem;
            color: var(--text-soft);
        }

        .auth-highlight-card {
            margin-top: 2.2rem;
            border-radius: 20px;
            padding: 1.15rem 1.1rem;
            background: radial-gradient(circle at 0 0, rgba(56, 189, 248, 0.35), transparent 60%),
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

        .auth-highlight-title {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            font-size: 0.88rem;
            font-weight: 500;
        }

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

        .auth-visual-dot-row {
            display: inline-flex;
            gap: 0.25rem;
        }

        .auth-visual-dot {
            width: 6px;
            height: 6px;
            border-radius: 999px;
            background: linear-gradient(to bottom right, #22d3ee, #38bdf8);
            box-shadow: 0 0 0 2px rgba(56, 189, 248, 0.3);
        }

        .auth-panel {
            position: relative;
            padding: 2.25rem 2.5rem;
            background: radial-gradient(circle at top, rgba(15, 23, 42, 0.98), rgba(15, 23, 42, 0.94));
        }

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

        .brand-text {
            text-align: right;
        }

        .brand-text h2 {
            margin: 0;
            font-size: 0.78rem;
            letter-spacing: 0.22em;
            text-transform: uppercase;
            color: rgba(148, 163, 184, 0.9);
        }

        .brand-text p {
            margin: 0.16rem 0 0;
            font-size: 0.86rem;
            color: var(--text-soft);
        }

        .auth-panel-main-header h1 {
            margin: 0;
            font-size: 1.6rem;
            letter-spacing: -0.03em;
        }

        .auth-panel-main-header p {
            margin: 0.45rem 0 0;
            font-size: 0.9rem;
            color: var(--text-soft);
        }

        .auth-panel-main-header p span {
            color: #a5b4fc;
        }

        .auth-panel-main {
            margin-top: 1.75rem;
            max-width: 480px;
            margin-inline: auto;
        }

        .auth-panel-main-header {
            text-align: center;
        }

        .social-row {
            display: flex;
            gap: 0.75rem;
            margin-bottom: 1.35rem;
        }

        .social-btn {
            flex: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            font-size: 0.85rem;
            padding: 0.68rem 0.6rem;
            border-radius: 999px;
            border: 1px dashed rgba(148, 163, 184, 0.5);
            background: rgba(15, 23, 42, 0.8);
            color: var(--text-soft);
            cursor: not-allowed;
        }

        .social-btn span {
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .divider {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 0.8rem;
            color: var(--text-soft);
            margin: 0 0 1.4rem;
        }

        .divider::before,
        .divider::after {
            content: "";
            height: 1px;
            flex: 1;
            background: linear-gradient(to right, transparent, rgba(148, 163, 184, 0.6));
        }

        .divider::after {
            background: linear-gradient(to left, transparent, rgba(148, 163, 184, 0.6));
        }

        form {
            margin: 0;
        }

        .field {
            position: relative;
            margin: 0 auto 1.1rem;
            max-width: 480px;
        }

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

        .field-input::placeholder {
            color: transparent;
        }

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
            box-shadow:
                0 0 0 1px rgba(45, 212, 191, 0.65),
                0 0 0 10px rgba(8, 47, 73, 0.6);
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

        .input-error + .field-label {
            color: #fecaca !important;
        }

        .input-valid {
            border-color: rgba(52, 211, 153, 0.8);
        }

        .field-error-text {
            margin: 0.3rem 0 0;
            min-height: 1em;
            font-size: 0.78rem;
            color: #fecaca;
        }

        .extra-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            margin: 0.3rem 0 1.4rem;
            font-size: 0.8rem;
            color: var(--text-soft);
        }

        .extra-row label {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            cursor: pointer;
        }

        .extra-row input[type="checkbox"] {
            width: 15px;
            height: 15px;
            border-radius: 4px;
            border: 1px solid rgba(148, 163, 184, 0.7);
            background: rgba(15, 23, 42, 0.95);
            accent-color: #22d3ee;
        }

        .linklike {
            border: none;
            padding: 0;
            background: none;
            font-size: 0.8rem;
            color: #e5e7eb;
            cursor: pointer;
            text-decoration: underline;
            text-decoration-thickness: 1px;
            text-underline-offset: 3px;
        }

        .linklike:hover {
            color: #bfdbfe;
        }

        .btn-primary {
            width: auto;
            max-width: 100%;
            margin-top: 0.25rem;
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
            box-shadow:
                0 18px 40px rgba(56, 189, 248, 0.55),
                0 0 0 1px rgba(15, 23, 42, 1);
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
            box-shadow:
                0 22px 60px rgba(56, 189, 248, 0.7),
                0 0 0 1px rgba(15, 23, 42, 1);
        }

        .btn-primary:active:not(:disabled) {
            transform: translateY(0);
            box-shadow:
                0 10px 26px rgba(15, 23, 42, 0.9),
                0 0 0 1px rgba(15, 23, 42, 1);
        }

        .btn-primary:disabled {
            cursor: wait;
            opacity: 0.9;
        }

        .btn-primary svg {
            width: 16px;
            height: 16px;
        }

        .btn-primary span {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
        }

        .secondary-link {
            margin-top: 1rem;
            font-size: 0.82rem;
            color: var(--text-soft);
            text-align: center;
        }

        .secondary-link a {
            color: #e5e7eb;
            font-weight: 500;
            text-decoration: underline;
            text-decoration-thickness: 1px;
            text-underline-offset: 3px;
        }

        .secondary-link a:hover {
            color: #bfdbfe;
        }

        .hidden {
            display: none !important;
        }

        .items-center {
            align-items: center;
        }

        .gap-2 {
            gap: 0.5rem;
        }

        .animate-spin {
            animation: spin 0.9s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        @media (max-width: 950px) {
            .auth-shell {
                grid-template-columns: minmax(0, 1fr);
            }

            .auth-visual {
                display: none;
            }

            .auth-panel {
                padding-inline: 1.75rem;
            }
        }

        @media (max-width: 640px) {
            .auth-page {
                margin: 1.2rem auto;
                padding-inline: 1rem;
            }

            .auth-shell {
                border-radius: 22px;
            }

            .auth-panel {
                padding: 1.6rem 1.4rem 1.5rem;
            }

            .auth-panel-header {
                margin-bottom: 1.35rem;
            }

            .auth-panel-main-header h1 {
                font-size: 1.45rem;
            }

            .btn-primary {
                width: 100%;
            }
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
                        Tu visión, tu
                        <span>espacio privado</span>.
                    </h1>
                    <p class="auth-subtitle">
                        Gestiona tus citas, recetas y seguimiento visual desde un panel pensado
                        para verse tan bien como tus nuevos lentes.
                    </p>

                    <div class="auth-highlight-card">
                        <div class="auth-highlight-header">
                            <div class="auth-highlight-title">
                                <div class="auth-highlight-icon">
                                    <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M5 12s2.5-5 7-5 7 5 7 5-2.5 5-7 5-7-5-7-5z" />
                                        <circle cx="12" cy="12" r="2.7" />
                                    </svg>
                                </div>
                                Panel en vivo
                            </div>
                            <span class="auth-highlight-badge">Experiencia tipo app</span>
                        </div>
                        <div class="auth-highlight-body">
                            <div>
                                <strong>Historial clínico</strong>
                                Últimos diagnósticos, fórmulas y recomendaciones en un solo lugar.
                            </div>
                            <div>
                                <strong>Citas inteligentes</strong>
                                Recordatorios suaves y horarios pensados para tu rutina.
                            </div>
                            <div>
                                <strong>Órdenes y lentes</strong>
                                Revisa el estado de tus pedidos y garantías.
                            </div>
                            <div>
                                <strong>Modo noche</strong>
                                Interfaz oscura que descansa tu vista mientras gestionas todo.
                            </div>
                        </div>
                    </div>

                    <div class="auth-visual-footer">
                        <div class="auth-visual-dot-row">
                            <span class="auth-visual-dot"></span>
                            <span class="auth-visual-dot"></span>
                            <span class="auth-visual-dot"></span>
                        </div>
                        Panel diseñado para verse como tu red favorita, pero sin distracciones.
                    </div>
                </div>
            </aside>

            <main class="auth-panel" aria-label="Panel de inicio de sesión">
                <header class="auth-panel-header">
                    <button type="button" class="back-button" onclick="window.location.href='<?php echo e(route('landing')); ?>'" aria-label="Volver al inicio">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M15 18l-6-6 6-6" />
                        </svg>
                    </button>
                    <div class="brand-text">
                        <h2>ÓPTICA</h2>
                        <p>Accede a tu espacio de paciente</p>
                    </div>
                </header>

                <section class="auth-panel-main" aria-label="Formulario de inicio de sesión">
                    <div class="auth-panel-main-header">
                        <h1>Inicia sesión</h1>
                        <p>
                            Bienvenido de vuelta. Usa tu correo y contraseña
                            <span>para continuar.</span>
                        </p>
                    </div>



                    <div class="divider">o entra con tu correo</div>

                    <form id="loginForm" method="POST" action="<?php echo e(route('login')); ?>" novalidate>
                        <?php echo csrf_field(); ?>

                        <div class="field">
                            <input
                                id="loginCorreo"
                                name="correo"
                                type="email"
                                class="field-input"
                                autocomplete="email"
                                required
                                placeholder=" "
                                value="<?php echo e(old('correo')); ?>"
                            >
                            <label for="loginCorreo" class="field-label">Correo electrónico</label>
                            <span class="field-icon">
                                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="5" width="18" height="14" rx="2" ry="2" />
                                    <polyline points="3 7 12 13 21 7" />
                                </svg>
                            </span>
                            <p class="field-error-text" data-field-error="loginCorreo">
                                <?php $__errorArgs = ['correo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><?php echo e($message); ?><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </p>
                        </div>

                        <div class="field">
                            <input
                                id="loginContrasena"
                                name="contrasena"
                                type="password"
                                class="field-input"
                                autocomplete="current-password"
                                required
                                minlength="8"
                                placeholder=" "
                            >
                            <label for="loginContrasena" class="field-label">Contraseña</label>
                            <span class="field-icon">
                                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="11" width="18" height="10" rx="2" ry="2" />
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                                </svg>
                            </span>
                            <p class="field-error-text" data-field-error="loginContrasena">
                                <?php $__errorArgs = ['contrasena'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><?php echo e($message); ?><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </p>
                        </div>

                        <div class="extra-row">
                            <label>
                                <input type="checkbox" name="recordarme">
                                <span>Recordarme</span>
                            </label>
                        </div>

                        <button type="submit" class="btn-primary" data-submit-button="login">
                            <span data-label-normal>Iniciar sesión</span>
                            <span class="hidden items-center gap-2" data-label-loading>
                                <svg viewBox="0 0 24 24" class="animate-spin" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 12a9 9 0 1 1-6.219-8.56" />
                                </svg>
                                <span>Procesando…</span>
                            </span>
                        </button>

                        <p class="secondary-link">
                            ¿No tienes cuenta?
                            <a href="<?php echo e(route('register.show')); ?>">Crear cuenta</a>
                        </p>
                    </form>
                </section>
            </main>
        </div>
    </div>
</div>

<script>
(function () {
    const emailInput = document.getElementById('loginCorreo');
    const passInput = document.getElementById('loginContrasena');
    const emailError = document.querySelector('[data-field-error="loginCorreo"]');
    const passError = document.querySelector('[data-field-error="loginContrasena"]');
    const form = document.getElementById('loginForm');
    const submitBtn = form ? form.querySelector('[data-submit-button="login"]') : null;
    const rememberCheckbox = form ? form.querySelector('input[name="recordarme"]') : null;

    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const STORAGE_KEY = 'optica_login_recordarme';

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

    // Cargar datos recordados (correo + contraseña) si existen
    if (typeof window !== 'undefined' && window.localStorage) {
        try {
            const raw = localStorage.getItem(STORAGE_KEY);
            if (raw) {
                const data = JSON.parse(raw);
                if (data && data.remember) {
                    if (emailInput && data.correo) {
                        emailInput.value = data.correo;
                        updateFilledState(emailInput);
                    }
                    if (passInput && data.contrasena) {
                        passInput.value = data.contrasena;
                        updateFilledState(passInput);
                    }
                    if (rememberCheckbox) {
                        rememberCheckbox.checked = true;
                    }
                }
            }
        } catch (e) {
            // Si algo falla, limpiamos
            localStorage.removeItem(STORAGE_KEY);
        }
    }

    if (emailInput) {
        updateFilledState(emailInput);

        if (emailError) {
            emailInput.addEventListener('input', () => {
                const value = emailInput.value.trim();
                updateFilledState(emailInput);
                if (!value) {
                    setError(emailInput, emailError, 'El correo es obligatorio.');
                } else if (!emailPattern.test(value)) {
                    setError(emailInput, emailError, 'El correo no tiene un formato válido.');
                } else {
                    setError(emailInput, emailError, '');
                }
            });
        }
    }

    if (passInput) {
        updateFilledState(passInput);

        if (passError) {
            passInput.addEventListener('input', () => {
                const value = passInput.value;
                updateFilledState(passInput);
                if (!value) {
                    setError(passInput, passError, 'La contraseña es obligatoria.');
                } else if (value.length < 8) {
                    setError(passInput, passError, 'Mínimo 8 caracteres.');
                } else {
                    setError(passInput, passError, '');
                }
            });
        }
    }

    if (form && submitBtn) {
        form.addEventListener('submit', (event) => {
            // Evita múltiples envíos rápidos
            if (submitBtn.disabled) {
                event.preventDefault();
                return;
            }

            let isValid = true;

            if (emailInput && emailError) {
                const value = emailInput.value.trim();
                if (!value) {
                    setError(emailInput, emailError, 'El correo es obligatorio.');
                    isValid = false;
                } else if (!emailPattern.test(value)) {
                    setError(emailInput, emailError, 'El correo no tiene un formato válido.');
                    isValid = false;
                }
            }

            if (passInput && passError) {
                const value = passInput.value;
                if (!value) {
                    setError(passInput, passError, 'La contraseña es obligatoria.');
                    isValid = false;
                } else if (value.length < 8) {
                    setError(passInput, passError, 'Mínimo 8 caracteres.');
                    isValid = false;
                }
            }

            if (!isValid) {
                event.preventDefault();
                return;
            }

            // Guardar o limpiar datos recordados
            if (typeof window !== 'undefined' && window.localStorage) {
                if (rememberCheckbox && rememberCheckbox.checked) {
                    const payload = {
                        remember: true,
                        correo: emailInput ? emailInput.value.trim() : '',
                        contrasena: passInput ? passInput.value : '',
                    };
                    try {
                        localStorage.setItem(STORAGE_KEY, JSON.stringify(payload));
                    } catch (e) {
                        // ignorar errores de almacenamiento
                    }
                } else {
                    localStorage.removeItem(STORAGE_KEY);
                }
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
</html><?php /**PATH C:\Users\norla\Desktop\Proyectos con Ronaldo\copia_optica\resources\views/auth/login.blade.php ENDPATH**/ ?>