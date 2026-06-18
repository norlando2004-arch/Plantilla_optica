<!DOCTYPE html>
<html lang="es" class="overflow-x-hidden">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php
        $seoTitle = 'Optica S.A.S | Gafas con estilo y fórmula en Colombia';
        $seoDescription = 'Descubre gafas con estilo, comodidad y excelente precio en Optica. Lentes con fórmula, polarizados y asesoría personalizada para encontrar tu look ideal.';
        $seoUrl = url('/');
        $seoImage = asset('images/borrardespues2.png');
        $seoFavicon = asset('favicon.ico');
    ?>
    <title><?php echo e($seoTitle); ?></title>
    <meta name="description" content="<?php echo e($seoDescription); ?>">
    <link rel="canonical" href="<?php echo e($seoUrl); ?>">
    <meta name="robots" content="index,follow,max-snippet:-1,max-image-preview:large,max-video-preview:-1">

    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Optica S.A.S">
    <meta property="og:title" content="<?php echo e($seoTitle); ?>">
    <meta property="og:description" content="<?php echo e($seoDescription); ?>">
    <meta property="og:url" content="<?php echo e($seoUrl); ?>">
    <meta property="og:image" content="<?php echo e($seoImage); ?>">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo e($seoTitle); ?>">
    <meta name="twitter:description" content="<?php echo e($seoDescription); ?>">
    <meta name="twitter:image" content="<?php echo e($seoImage); ?>">

    <link rel="icon" href="<?php echo e($seoFavicon); ?>" sizes="any">
    <link rel="shortcut icon" href="<?php echo e($seoFavicon); ?>">
    <link rel="apple-touch-icon" href="<?php echo e($seoFavicon); ?>">

    <script type="application/ld+json">
        <?php echo json_encode([
            '<?php $__contextArgs = [];
if (context()->has($__contextArgs[0])) :
if (isset($value)) { $__contextPrevious[] = $value; }
$value = context()->get($__contextArgs[0]); ?>' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => 'Optica S.A.S',
            'url' => $seoUrl,
            'logo' => $seoFavicon,
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>

    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preload" href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@400;500;600;700;800&display=swap" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@400;500;600;700;800&display=swap" rel="stylesheet"></noscript>

    <?php
        $viteHot = public_path('hot');
        $viteManifest = public_path('build/manifest.json');
        $hasViteAssets = file_exists($viteHot) || file_exists($viteManifest);
    ?>

    <?php if($hasViteAssets): ?>
        <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    <?php endif; ?>

    <?php if(!$hasViteAssets || app()->isLocal()): ?>
        <script src="https://cdn.tailwindcss.com"></script>
    <?php endif; ?>

    <style>
        /* Variables base del estilo visual de Optica */
        :root {
            --na-primary: #374151;
            --na-primary-dark: #1f2937;
            --na-primary-blue: #111827;
            --na-accent: #374151;
            --na-soft-left: #F9FAFB;
            --na-soft-right: #F3F4F6;
            --na-text: #111827;
        }

        /* Reset básico para evitar desbordes laterales */
        html, body {
            margin: 0;
            padding: 0;
            width: 100%;
            max-width: 100%;
            overflow-x: hidden;
        }

        body {
            font-family: 'Baloo 2', ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            color: var(--na-text);
            background: #ffffff;
        }

        /* Hero principal — estilo Opticalia */
        .hero-panel {
            position: relative;
            overflow: hidden;
            background: #111827;
            min-height: 88vh;
            padding: 0;
        }

        .hero-panel-frame {
            position: absolute;
            inset: 0;
            z-index: 1;
        }

        .hero-panel-image {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
            transform: translateX(-100%);
            transition: transform 900ms cubic-bezier(0.22, 0.61, 0.36, 1);
            will-change: transform;
            z-index: 1;
            opacity: 0.9;
        }

        .hero-panel-image.is-active { transform: translateX(0); z-index: 2; }
        .hero-panel-image.is-exit   { transform: translateX(100%); z-index: 1; }

        /* Overlay izquierdo — texto sobre gradiente lateral */
        .hero-content-overlay {
            position: absolute;
            inset: 0;
            z-index: 20;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            justify-content: flex-end;
            text-align: left;
            padding: 4rem 5vw 5rem;
            background: linear-gradient(
                90deg,
                rgba(0,0,0,0.70) 0%,
                rgba(0,0,0,0.40) 45%,
                transparent 75%
            );
        }

        @media (max-width: 640px) {
            .hero-content-overlay {
                padding: 2rem 1.5rem 3.5rem;
                background: linear-gradient(180deg, transparent 0%, rgba(0,0,0,0.72) 60%, rgba(0,0,0,0.85) 100%);
            }
        }

        .hero-scroll-hint { display: none; }
        .hero-badge        { display: none; }
        .hero-badge-dot    { display: none; }
        .hero-social-proof { display: none; }

        .hero-headline {
            font-family: Georgia, 'Times New Roman', serif;
            font-size: clamp(2.2rem, 5.5vw, 4.8rem);
            font-weight: 400;
            line-height: 1.12;
            letter-spacing: -0.02em;
            color: #ffffff;
            max-width: 28rem;
            margin: 0;
        }

        .hero-headline-accent {
            color: #ffffff;
            font-style: italic;
        }

        .hero-subtitle {
            margin-top: 1.1rem;
            font-size: clamp(0.9rem, 1.6vw, 1.05rem);
            color: rgba(255,255,255,0.78);
            max-width: 26rem;
            line-height: 1.65;
        }

        .hero-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            justify-content: flex-start;
            margin-top: 2rem;
        }

        .hero-btn-primary {
            display: inline-block;
            padding: 0.8rem 2.5rem;
            background: #ffffff;
            color: #111827;
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            text-decoration: underline;
            text-underline-offset: 4px;
            border: none;
            transition: background 180ms ease;
        }
        .hero-btn-primary:hover { background: #f3f4f6; }

        .hero-btn-secondary {
            display: inline-block;
            padding: 0.8rem 2.5rem;
            background: transparent;
            color: #ffffff;
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            border: 1.5px solid rgba(255,255,255,0.65);
            text-decoration: none;
            transition: border-color 180ms ease;
        }
        .hero-btn-secondary:hover { border-color: #fff; }

        .hero-stars { display: none; }

        .secondary-banner-frame {
            position: absolute;
            inset: 0;
            z-index: 1;
        }

        /* Enlaces del menú principal */
        .nav-link {
            position: relative;
            transition: color 180ms ease;
        }

        .nav-link::after {
            content: "";
            position: absolute;
            left: 0;
            bottom: -0.35rem;
            width: 0;
            height: 2px;
            background: var(--na-primary);
            transition: width 180ms ease;
        }

        .nav-link:hover {
            color: var(--na-primary-dark);
        }

        .nav-link:hover::after {
            width: 100%;
        }

        /* Botones redondos para acciones rápidas del header */
        .icon-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.75rem;
            height: 2.75rem;
            border-radius: 9999px;
            transition: all 180ms ease;
        }

        .icon-btn:hover {
            background: rgba(0,0,0,0.06);
            color: #111827;
            transform: translateY(-1px);
        }

        /* Contenedores temporales para reemplazar luego por imágenes reales */
        .image-slot {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid rgba(0,0,0,0.08);
            border-radius: 1.5rem;
            background: #f9fafb;
            color: #9ca3af;
            text-align: center;
        }

        .image-slot span {
            padding: 0.75rem;
            font-size: 0.95rem;
            font-weight: 700;
            line-height: 1.2;
        }

        /* Espacio vacío reservado para futuras imágenes o slider */
        .hero-blank-space {
            width: 7rem;
            height: 7rem;
            flex-shrink: 0;
            background: transparent;
            pointer-events: none;
        }

        @media (min-width: 640px) {
            .hero-blank-space {
                width: 8rem;
                height: 8rem;
            }
        }

        .shipping-copy em {
            font-style: italic;
        }

        /* Franja horizontal con beneficios rápidos */
        .benefit-strip {
            background: #f9fafb;
            border-top: 1px solid #e5e7eb;
            border-bottom: 1px solid #e5e7eb;
        }

        .benefit-marquee {
            overflow: hidden;
        }

        .benefit-track {
            display: flex;
            width: max-content;
            align-items: center;
            gap: 0;
            white-space: nowrap;
            animation: benefit-scroll 24s linear infinite;
        }

        .benefit-item {
            display: inline-flex;
            align-items: center;
            flex: 0 0 auto;
            padding-right: 3.5rem;
        }

        @keyframes benefit-scroll {
            from {
                transform: translateX(0);
            }

            to {
                transform: translateX(-50%);
            }
        }

        @media (min-width: 640px) {
            .benefit-item {
                padding-right: 5.5rem;
            }
        }

        @media (max-width: 767px) {
            .hero-panel {
                min-height: 88vh;
            }

            .secondary-banner-frame {
                position: absolute;
                inset: 0;
            }

            .hero-panel-image {
                object-fit: cover;
                object-position: center;
            }

            .secondary-banner-frame .hero-panel-image {
                object-fit: cover;
                object-position: center;
            }

            .hero-headline {
                font-size: clamp(1.9rem, 9vw, 2.8rem);
            }

            .hero-buttons {
                flex-direction: column;
                align-items: center;
            }
        }

        /* Tarjetas de producto de la vitrina */
        #catalogo {
            background: #f5f3ef;
        }

        .product-card {
            text-align: left;
            cursor: pointer;
            transition: transform 220ms ease;
        }

        .product-card .card-img-wrap {
            position: relative;
            border-radius: 1.3rem;
            overflow: hidden;
            background: #EEECEA;
            transition: box-shadow 220ms ease;
        }

        .product-reveal {
            opacity: 0;
            transform: translate3d(92px, 0, 0) skewX(-8deg) scale(0.965);
            filter: blur(1.2px) saturate(0.9);
            transition:
                opacity 420ms cubic-bezier(0.16, 0.84, 0.25, 1),
                transform 520ms cubic-bezier(0.12, 0.9, 0.24, 1),
                filter 420ms cubic-bezier(0.16, 0.84, 0.25, 1);
            will-change: opacity, transform, filter;
        }

        .product-reveal.is-visible {
            opacity: 1;
            transform: translate3d(0, 0, 0) skewX(0) scale(1);
            filter: blur(0) saturate(1);
        }

        @media (prefers-reduced-motion: reduce) {
            .product-reveal {
                opacity: 1;
                transform: none;
                filter: none;
                transition: none;
            }
        }

        .product-card:hover {
            transform: translateY(-4px);
        }

        .product-card:hover .card-img-wrap {
            box-shadow: 0 14px 36px rgba(0,0,0,0.12);
        }

        .product-card .card-heart {
            position: absolute;
            top: 0.65rem;
            right: 0.65rem;
            z-index: 2;
            width: 2rem;
            height: 2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 9999px;
            background: rgba(255,255,255,0.7);
            border: none;
            cursor: pointer;
            color: #9ca3af;
            transition: color 180ms ease, background 180ms ease;
        }

        .product-card .card-heart:hover {
            background: #fff;
            color: #ef4444;
        }

        .product-card .card-name-row {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: 0.5rem;
            margin-top: 0.6rem;
        }

        .product-card .card-name {
            font-size: 0.88rem;
            font-weight: 700;
            color: #111;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            flex-shrink: 1;
            min-width: 0;
        }

        .product-card .card-price {
            font-size: 0.92rem;
            font-weight: 600;
            color: #374151;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .product-card .card-meta-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.5rem;
            margin-top: 0.3rem;
        }

        .product-card .card-brand {
            font-size: 0.78rem;
            color: #9ca3af;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .product-card .card-swatches {
            display: flex;
            align-items: center;
            gap: 0.25rem;
            flex-shrink: 0;
        }

        .product-card .card-swatch {
            width: 0.85rem;
            height: 0.85rem;
            border-radius: 9999px;
            border: 1px solid rgba(0,0,0,0.08);
            box-shadow: 0 0 0 1px rgba(255,255,255,0.6);
            display: inline-block;
        }

        .product-brand {
            display: block;
            font-size: 0.65rem;
            font-weight: 700;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: #374151;
            padding: 0.75rem 1rem 0.2rem;
        }

        .product-badge {
            position: absolute;
            top: 0.75rem;
            left: 0.75rem;
            z-index: 2;
            border-radius: 9999px;
            font-size: 0.62rem;
            font-weight: 800;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            padding: 0.32rem 0.8rem;
            background: #374151;
            color: #fff;
            pointer-events: none;
        }

        .product-badge.is-dark { background: #1a1a1a; }

        .catalog-header-btn {
            display: inline-flex;
            align-items: center;
            padding: 0.7rem 1.5rem;
            border: 2px solid #111;
            border-radius: 9999px;
            font-size: 0.88rem;
            font-weight: 600;
            color: #111;
            text-decoration: none;
            white-space: nowrap;
            transition: background 180ms ease, color 180ms ease;
            margin-bottom: 0.3rem;
        }

        .catalog-header-btn:hover { background: #111; color: #fff; }

        /* Evita artefactos de borde al cambiar slides del carrusel del catálogo */
        [data-catalog-carousel-desktop],
        [data-catalog-carousel-mobile] {
            contain: paint;
        }

        [data-catalog-carousel-desktop] .product-reveal,
        [data-catalog-carousel-mobile] .product-reveal {
            opacity: 1;
            transform: none;
            filter: none;
            transition: none;
            will-change: auto;
        }

        .product-image-slot {
            aspect-ratio: 4 / 3 !important;
            min-height: unset !important;
            max-width: 100% !important;
            margin: 0 !important;
            border: none !important;
            border-radius: 0 !important;
            background: #eeede9 !important;
            box-shadow: none !important;
            overflow: hidden;
            position: relative;
        }

        .product-image-slot span {
            color: rgba(0,0,0,0.25) !important;
            font-size: 0.65rem !important;
            font-weight: 600 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.12em !important;
        }

        .slider-dot {
            width: 0.7rem;
            height: 0.7rem;
            border-radius: 9999px;
            background: #a9a3a8;
            display: inline-block;
            border: 0;
            padding: 0;
            cursor: pointer;
            transition: transform 180ms ease, background-color 180ms ease;
        }

        .slider-dot.is-active {
            background: #111827;
            transform: scale(1.14);
        }

        .showcase-link { display: none; }

        @keyframes catalog-card-enter-left {
            from {
                transform: translate3d(-100vw, 0, 0);
                opacity: 0;
            }
            to {
                transform: translate3d(0, 0, 0);
                opacity: 1;
            }
        }

        @keyframes catalog-card-enter-right {
            from {
                transform: translate3d(100vw, 0, 0);
                opacity: 0;
            }
            to {
                transform: translate3d(0, 0, 0);
                opacity: 1;
            }
        }

        .catalog-slide.is-entering .product-card {
            opacity: 0;
            transform: translate3d(-100vw, 0, 0);
            animation: catalog-card-enter-left 520ms cubic-bezier(0.2, 0.9, 0.24, 1) forwards;
        }

        .catalog-slide.is-entering .product-card:nth-child(1) { animation-delay: 0ms; }
        .catalog-slide.is-entering .product-card:nth-child(2) { animation-delay: 90ms; }
        .catalog-slide.is-entering .product-card:nth-child(3) { animation-delay: 180ms; }
        .catalog-slide.is-entering .product-card:nth-child(4) { animation-delay: 270ms; }

        .catalog-slide.is-entering .product-card:nth-child(2) {
            animation-name: catalog-card-enter-right;
            transform: translate3d(100vw, 0, 0);
        }

        @media (min-width: 768px) {
            .catalog-slide.is-entering .product-card:nth-child(1),
            .catalog-slide.is-entering .product-card:nth-child(2) {
                animation-name: catalog-card-enter-left;
                transform: translate3d(-100vw, 0, 0);
            }

            .catalog-slide.is-entering .product-card:nth-child(3),
            .catalog-slide.is-entering .product-card:nth-child(4) {
                animation-name: catalog-card-enter-right;
                transform: translate3d(100vw, 0, 0);
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .catalog-slide.is-entering {
                animation: none;
            }

            .catalog-slide.is-entering .product-card {
                animation: none;
                opacity: 1;
                transform: none;
            }
        }

        /* Tarjetas grandes de categorías (niños, mujeres, hombres) */
        .category-card {
            position: relative;
            padding-top: 0;
            border-radius: 1.5rem;
            overflow: hidden;
            display: block;
            transition: transform 240ms ease, box-shadow 240ms ease;
        }

        .category-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 48px rgba(0,0,0,0.18);
        }

        .category-reveal {
            opacity: 0;
            transform: translate3d(0, 40px, 0) scale(0.97);
            transition: opacity 620ms ease, transform 620ms ease;
            will-change: opacity, transform;
            visibility: hidden;
            backface-visibility: hidden;
            transform-style: preserve-3d;
        }

        .category-reveal[data-reveal-side="left"] {
            transform: translate3d(-38px, 26px, 0) scale(0.97);
        }

        .category-reveal[data-reveal-side="right"] {
            transform: translate3d(38px, 26px, 0) scale(0.97);
        }

        .category-reveal.is-visible {
            opacity: 1;
            transform: translate3d(0, 0, 0) scale(1);
            visibility: visible;
            will-change: auto;
        }

        @media (max-width: 767px) {
            .category-reveal:not(.is-visible) {
                visibility: hidden;
                pointer-events: none;
            }

            .category-reveal.is-visible {
                visibility: visible;
                pointer-events: auto;
            }
        }

        /* Location cards — 3D POP DESDE ABAJO con spring bounce */
        .location-reveal {
            opacity: 0;
            transform: translate3d(0, 2rem, 0) scale(0.985);
            transition:
                opacity 320ms ease,
                transform 420ms cubic-bezier(0.22, 0.61, 0.36, 1);
            will-change: opacity, transform;
        }
        .location-reveal.is-visible {
            opacity: 1;
            transform: translate3d(0, 0, 0) scale(1);
        }
        @media (prefers-reduced-motion: reduce) {
            .location-reveal {
                opacity: 1;
                transform: none;
                transition: none;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .category-reveal {
                opacity: 1;
                transform: none;
                visibility: visible;
                transition: none;
            }
        }

        .category-photo-slot {
            position: relative;
            min-height: 460px;
            overflow: hidden;
            border-radius: 0;
            border: none;
            background-size: cover;
            background-position: center;
            contain: layout paint;
            isolation: isolate;
        }

        .category-photo-image {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
            backface-visibility: hidden;
            transform: translateZ(0) scale(1);
            transition: transform 500ms ease;
        }

        .category-card:hover .category-photo-image {
            transform: translateZ(0) scale(1.04);
        }

        /* Gradiente oscuro en la parte inferior */
        .category-photo-slot::after {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg,
                rgba(0,0,0,0) 35%,
                rgba(0,0,0,0.35) 65%,
                rgba(0,0,0,0.72) 100%);
            z-index: 1;
        }

        .category-photo-slot.has-cover-image::after {
            display: block;
        }

        /* Badge pill en la esquina superior izquierda */
        .category-badge {
            position: absolute;
            top: 1rem;
            left: 1rem;
            z-index: 3;
            background: #374151;
            color: #fff;
            font-size: 0.68rem;
            font-weight: 800;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            padding: 0.35rem 0.85rem;
            border-radius: 9999px;
            box-shadow: 0 4px 12px rgba(55, 65, 81, 0.4);
        }

        /* Bloque de texto sobre la imagen */
        .category-label {
            position: absolute;
            left: 1.25rem;
            right: 1.25rem;
            bottom: 1.4rem;
            z-index: 2;
            text-align: left;
        }

        .category-label-tagline {
            display: block;
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: #374151;
            margin-bottom: 0.3rem;
        }

        .category-label-name {
            display: block;
            font-size: clamp(1.8rem, 3vw, 2.5rem);
            font-weight: 800;
            letter-spacing: -0.01em;
            color: #ffffff;
            line-height: 1;
        }

        .category-placeholder-tag {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 2;
            border-radius: 9999px;
            background: rgba(255,255,255,0.15);
            border: 1px dashed rgba(255,255,255,0.3);
            padding: 0.5rem 1rem;
            font-size: 0.75rem;
            font-weight: 700;
            color: rgba(255,255,255,0.55);
        }

        /* Pequeño label encima del título de la sección */
        .section-eyebrow {
            display: block;
            text-align: center;
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: #374151;
            margin-bottom: 0.6rem;
        }

        .mascot-badge {
            position: absolute;
            top: 0;
            right: 0.45rem;
            z-index: 3;
            transform: translateY(-66%);
            pointer-events: none;
        }

        .mascot-badge.is-right-offset {
            right: -0.1rem;
        }

        .mascot-badge img {
            height: 4.2rem;
            width: auto;
            object-fit: contain;
            backface-visibility: hidden;
            transform: translateZ(0);
        }

        /* Bloque horizontal de tratamiento polarizado */
        .polarized-feature-shell {
            background: #111;
        }

        .polarized-feature-card {
            position: relative;
            overflow: hidden;
            min-height: 88vh;
            background: #111;
        }

        /* Gradiente oscuro sobre la imagen del banner secundario */
        .polarized-feature-card::after {
            content: "";
            position: absolute;
            inset: 0;
            z-index: 2;
            background: linear-gradient(90deg,
                rgba(0,0,0,0.72) 0%,
                rgba(0,0,0,0.45) 55%,
                rgba(0,0,0,0.10) 100%);
        }

        /* Imágenes del banner secundario oscurecidas */
        .secondary-banner-frame .hero-panel-image {
            opacity: 0.65 !important;
        }

        /* Overlay de texto del banner secundario */
        .secondary-text-overlay {
            position: absolute;
            inset: 0;
            z-index: 10;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 3rem 4rem;
            max-width: 56rem;
        }

        .secondary-eyebrow {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: rgba(255,255,255,0.65);
            margin-bottom: 1.5rem;
        }

        .secondary-eyebrow::before {
            content: "";
            display: block;
            width: 2rem;
            height: 2px;
            background: rgba(255,255,255,0.5);
            flex-shrink: 0;
        }

        .secondary-headline {
            font-size: clamp(2.4rem, 5vw, 4.2rem);
            font-weight: 800;
            line-height: 1.05;
            letter-spacing: -0.03em;
            color: #ffffff;
            margin-bottom: 1.5rem;
        }

        .secondary-headline-accent { color: #374151; }

        .secondary-subtitle {
            font-size: clamp(0.9rem, 1.4vw, 1.05rem);
            color: rgba(255,255,255,0.70);
            line-height: 1.7;
            max-width: 30rem;
            margin-bottom: 2rem;
        }

        .secondary-cta {
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            background: #374151;
            color: #fff;
            font-size: 0.95rem;
            font-weight: 700;
            padding: 0.85rem 1.8rem;
            border-radius: 9999px;
            text-decoration: none;
            width: fit-content;
            transition: background 180ms ease, transform 180ms ease, box-shadow 180ms ease;
            box-shadow: 0 4px 18px rgba(55, 65, 81, 0.45);
        }

        .secondary-cta:hover { background: #1f2937; transform: translateY(-2px); box-shadow: 0 8px 28px rgba(55, 65, 81, 0.55); }

        @media (max-width: 640px) {
            .secondary-text-overlay { padding: 2rem 1.5rem; }
            .polarized-feature-card { min-height: 100vh; }
        }

        .polarized-image-slot {
            min-height: 22rem;
            border: none;
            border-radius: 0;
            background: linear-gradient(135deg, #fafafa 0%, #efefef 100%);
        }

        .polarized-copy {
            min-height: 22rem;
            background: #d7dce1;
        }

        .polarized-divider {
            width: 2px;
            height: 6rem;
            background: rgba(0, 0, 0, 0.65);
        }

        /* Bloque de ubicaciones por ciudad */
        .location-city-card {
            display: flex;
            flex-direction: column;
            gap: 0;
            background: #F9FAFB;
            border: 1px solid #E5E7EB;
            border-radius: 1.25rem;
            overflow: hidden;
            transition: transform 220ms ease, box-shadow 220ms ease;
            text-decoration: none;
        }

        .location-city-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 16px 40px rgba(0,0,0,0.10);
        }

        .location-card-body {
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 0.85rem;
        }

        .location-badge {
            display: inline-block;
            background: #374151;
            color: #fff;
            font-size: 0.62rem;
            font-weight: 800;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            padding: 0.3rem 0.85rem;
            border-radius: 9999px;
            width: fit-content;
        }

        .location-city-name {
            font-size: clamp(1.8rem, 3vw, 2.5rem);
            font-weight: 800;
            color: #111827;
            line-height: 1;
            margin: 0;
        }

        .location-info-row {
            display: flex;
            align-items: flex-start;
            gap: 0.5rem;
            font-size: 0.88rem;
            color: #6b7280;
        }

        .location-info-icon {
            flex-shrink: 0;
            margin-top: 0.05rem;
            opacity: 0.7;
        }

        .location-view-link {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            color: #374151;
            font-size: 0.88rem;
            font-weight: 700;
            margin-top: 0.25rem;
        }

        .storefront-slot {
            min-height: 11rem;
            border: none;
            border-radius: 0;
            background: #f3f4f6;
            overflow: hidden;
            padding: 0;
        }

        .storefront-slot img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            opacity: 0.85;
            transition: opacity 220ms ease;
        }

        .location-city-card:hover .storefront-slot img { opacity: 1; }

        .map-slot {
            min-height: 15rem;
            border: 1px solid rgba(0, 0, 0, 0.06);
            border-radius: 0;
            background:
                linear-gradient(135deg, rgba(246, 244, 238, 0.96), rgba(233, 234, 236, 0.98)),
                repeating-linear-gradient(
                    0deg,
                    transparent 0,
                    transparent 26px,
                    rgba(170, 178, 188, 0.15) 26px,
                    rgba(170, 178, 188, 0.15) 27px
                ),
                repeating-linear-gradient(
                    90deg,
                    transparent 0,
                    transparent 28px,
                    rgba(170, 178, 188, 0.12) 28px,
                    rgba(170, 178, 188, 0.12) 29px
                );
            position: relative;
            overflow: hidden;
            contain: layout paint;
            isolation: isolate;
        }

        .map-slot.has-map {
            background: #ffffff;
            border: 1px solid rgba(0, 0, 0, 0.08);
        }

        .map-slot.has-map::before,
        .map-slot.has-map::after {
            display: none;
        }

        .map-slot iframe {
            width: 100%;
            height: 100%;
            min-height: 15rem;
            border: 0;
            display: block;
            backface-visibility: hidden;
            transform: translateZ(0);
        }

        .map-slot::before {
            content: "";
            position: absolute;
            left: 14%;
            top: 6%;
            width: 34%;
            height: 22%;
            background: rgba(255,255,255,0.95);
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }

        .map-slot::after {
            content: "";
            position: absolute;
            left: 50%;
            top: 48%;
            width: 14px;
            height: 14px;
            border-radius: 9999px;
            background: #d24b43;
            box-shadow: 0 0 0 6px rgba(210, 75, 67, 0.18);
            transform: translate(-50%, -50%);
        }

        /* Tarjetas de reseñas */
        #resenas {
            background: #f5f1ea;
        }

        .review-mini-card {
            border: none;
            border-radius: 1.25rem;
            background: #ffffff;
            box-shadow: 0 2px 16px rgba(0,0,0,0.07);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 1rem;
        }

        .reviews-marquee {
            overflow: hidden;
        }

        .reviews-marquee.full-bleed {
            width: 100vw;
            margin-left: calc(50% - 50vw);
            margin-right: calc(50% - 50vw);
        }

        .reviews-track {
            display: flex;
            flex-wrap: nowrap;
            justify-content: flex-start;
            gap: 1rem;
            width: max-content;
        }

        .reviews-group {
            display: flex;
            flex-wrap: nowrap;
            gap: 1rem;
            width: max-content;
        }

        .reviews-track.is-static {
            justify-content: center;
            width: 100%;
        }

        .reviews-track.is-static .reviews-group {
            width: 100%;
            justify-content: center;
            flex-wrap: wrap;
        }

        .reviews-track.is-animated {
            width: max-content;
            flex-wrap: nowrap;
            justify-content: flex-start;
            animation: reviews-scroll-left var(--reviews-duration, 48s) linear infinite;
            will-change: transform;
            cursor: grab;
        }

        .reviews-track.is-animated.is-dragging {
            cursor: grabbing;
        }

        .reviews-track .review-mini-card {
            width: calc((100vw - 4rem) / 3);
            max-width: 22rem;
            min-width: 16rem;
            flex: 0 0 auto;
        }

        @keyframes reviews-scroll-left {
            from {
                transform: translateX(0);
            }

            to {
                transform: translateX(calc(-1 * var(--reviews-loop-distance, 0px)));
            }
        }

        .review-form-shell {
            border: 1px solid #d1d5db;
            border-radius: 1rem;
            background: #ffffff;
            padding: 1rem;
        }

        .review-stars-input {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            margin-top: 0.5rem;
        }

        .review-star-btn {
            border: 0;
            padding: 0;
            background: transparent;
            cursor: pointer;
            color: #d4d4d8;
            font-size: 1.4rem;
            line-height: 1;
            transition: color 140ms ease, transform 140ms ease;
        }

        .review-star-btn:hover,
        .review-star-btn:focus-visible,
        .review-star-btn.is-active {
            color: #f59e0b;
            transform: translateY(-1px);
            outline: none;
        }

        .review-clickable {
            cursor: pointer;
            transition: transform 160ms ease, box-shadow 160ms ease;
        }

        .review-clickable:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.06);
        }

        .review-detail-photo {
            width: 100%;
            max-height: 16rem;
            object-fit: cover;
            border-radius: 0.9rem;
            border: 1px solid rgba(0, 0, 0, 0.08);
        }

        @media (max-width: 767px) {
            .location-city-card {
                gap: 0.6rem;
            }

            .storefront-slot {
                min-height: 7.5rem;
            }

            .map-slot,
            .map-slot iframe {
                min-height: 8rem;
            }

            .reviews-track {
                gap: 0.75rem;
            }

            .reviews-group {
                gap: 0.75rem;
            }

            .reviews-track .review-mini-card {
                width: min(17rem, 80vw);
                min-width: 0;
                max-width: none;
            }

            .review-form-shell {
                padding: 0.9rem;
            }
        }

        .review-avatar {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 2.75rem;
            height: 2.75rem;
            border-radius: 9999px;
            background: #374151;
            color: white;
            font-size: 0.78rem;
            font-weight: 800;
            flex-shrink: 0;
        }

        .social-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 3rem;
            height: 3rem;
            border-radius: 9999px;
            color: white;
            font-weight: 800;
            text-decoration: none;
        }

        .footer-shell {
            background: #0f1117;
        }

        .footer-col-title {
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: rgba(255,255,255,0.85);
            margin-bottom: 1.25rem;
        }

        .footer-link {
            display: block;
            font-size: 0.95rem;
            color: rgba(255,255,255,0.55);
            text-decoration: none;
            padding: 0.2rem 0;
            transition: color 150ms ease;
        }

        .footer-link:hover { color: #fff; }

        .footer-brand-name {
            font-size: 1.5rem;
            font-weight: 800;
            color: #fff;
            letter-spacing: -0.02em;
        }

        .footer-brand-accent { color: #374151; }

        .footer-tagline {
            margin-top: 0.75rem;
            font-size: 0.9rem;
            color: rgba(255,255,255,0.45);
            line-height: 1.6;
            max-width: 18rem;
        }

        .footer-divider {
            border-color: rgba(255,255,255,0.07);
        }

        .faq-modal-panel {
            border: 1px solid #e5e7eb;
            box-shadow: 0 22px 56px rgba(0,0,0,0.16);
            background: #ffffff;
        }

        .faq-modal-header {
            background: #f9fafb;
            border-bottom: 1px solid #e5e7eb;
        }

        .faq-trigger {
            border-radius: 0.75rem;
            padding-left: 0.5rem;
            padding-right: 0.5rem;
            transition: background-color 180ms ease, transform 180ms ease;
        }

        .faq-trigger:hover {
            background: rgba(75, 145, 150, 0.1);
            transform: translateX(2px);
        }

        .faq-body-content {
            margin-left: 0.5rem;
            border-left: 3px solid rgba(75, 145, 150, 0.28);
            padding-left: 0.75rem;
        }

        @media (max-width: 1024px) {
            .nav-desktop {
                display: none;
            }
        }

        .landing-global-loading {
            position: fixed;
            inset: 0;
            z-index: 130;
            display: none;
            align-items: center;
            justify-content: center;
            background: transparent;
            pointer-events: all;
            touch-action: none;
        }

        .landing-global-loading.is-visible {
            display: flex;
        }

        .landing-global-loading-content {
            width: min(92vw, 22rem);
            text-align: center;
        }

        .landing-global-loading-gif {
            display: block;
            width: 7.1rem;
            height: 7.1rem;
            margin: 0 auto 0.35rem;
            object-fit: contain;
            filter: drop-shadow(0 8px 18px rgba(28, 18, 8, 0.22));
        }

        .landing-global-loading-spinner {
            width: 5rem;
            height: 5rem;
            border-radius: 9999px;
            border: 5px solid rgba(63, 127, 95, 0.18);
            border-top-color: #3f7f5f;
            animation: landing-spinner-spin 0.75s linear infinite;
            margin: 0 auto 0.25rem;
            filter: drop-shadow(0 4px 10px rgba(28, 18, 8, 0.14));
        }

        @keyframes landing-spinner-spin {
            to { transform: rotate(360deg); }
        }

        .landing-global-loading-label {
            margin-top: 0.7rem;
            font-size: 1.05rem;
            font-weight: 800;
            letter-spacing: 0.01em;
            color: #17382f;
            text-shadow: 0 1px 0 rgba(255, 255, 255, 0.8);
        }

        .landing-global-loading-dots {
            display: inline-flex;
            align-items: flex-end;
            gap: 0.3rem;
            margin-left: 0.15rem;
        }

        .landing-global-loading-dot {
            width: 0.36rem;
            height: 0.36rem;
            border-radius: 9999px;
            background: #3f7f5f;
            opacity: 0.2;
            transform: translateY(0);
            animation: landing-loading-dot-bounce 1s infinite ease-in-out;
        }

        .landing-global-loading-dot:nth-child(2) {
            animation-delay: 0.18s;
        }

        .landing-global-loading-dot:nth-child(3) {
            animation-delay: 0.36s;
        }

        @keyframes landing-loading-dot-bounce {
            0%,
            80%,
            100% {
                opacity: 0.2;
                transform: translateY(0);
            }

            40% {
                opacity: 1;
                transform: translateY(-3px);
            }
        }

        @media (max-width: 640px) {
            .landing-global-loading-content {
                width: min(92vw, 18rem);
            }

            .landing-global-loading-gif {
                width: 6rem;
                height: 6rem;
            }

            .landing-global-loading-spinner {
                width: 4rem;
                height: 4rem;
            }

            .landing-global-loading-label {
                font-size: 0.98rem;
            }
        }
    </style>
</head>
<body>
    
    <div id="landingGlobalLoading"
         class="landing-global-loading"
         aria-hidden="true"
         role="status"
         aria-live="polite"
         data-loading-overlay>
        <div class="landing-global-loading-content">
            <img src="<?php echo e(asset('images/NARA PERRO.gif')); ?>" alt="Cargando" class="landing-global-loading-gif" loading="eager" decoding="async">
            <p class="landing-global-loading-label">
                Cargando
                <span class="landing-global-loading-dots" aria-hidden="true">
                    <span class="landing-global-loading-dot"></span>
                    <span class="landing-global-loading-dot"></span>
                    <span class="landing-global-loading-dot"></span>
                </span>
            </p>
        </div>
    </div>

    <div class="min-h-screen bg-white text-zinc-900">
        <?php echo $__env->make('partials.store-navbar', ['showStoreBanner' => false], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <main>
            
            <section class="hero-panel">
                <?php
                    $heroFallback = asset('images/borrardespues2.png');
                    $heroImages = [];

                    foreach (collect($heroBanners ?? []) as $heroBannerItem) {
                        $candidate = trim((string) $heroBannerItem->heroImageUrl(''));
                        if ($candidate !== '') {
                            $heroImages[] = $candidate;
                        }
                    }

                    if (count($heroImages) === 0) {
                        $heroImages[] = $heroFallback;
                    }

                    $heroImage = $heroImages[0];
                ?>
                
                <div class="hero-panel-frame" data-hero-frame>
                    <img
                        src="<?php echo e($heroImage); ?>"
                        alt="Imagen principal del hero"
                        class="hero-panel-image is-active"
                        loading="eager"
                        fetchpriority="high"
                        decoding="async"
                        draggable="false"
                        data-hero-image-a
                    >
                    <img
                        src="<?php echo e($heroImage); ?>"
                        alt=""
                        aria-hidden="true"
                        class="hero-panel-image"
                        loading="lazy"
                        fetchpriority="low"
                        decoding="async"
                        draggable="false"
                        data-hero-image-b
                    >
                </div>

                
                <div class="hero-content-overlay">
                    <span class="hero-badge">
                        <span class="hero-badge-dot"></span>
                        NUEVA COLECCIÓN 2025
                    </span>

                    <h1 class="hero-headline">
                        Ver mejor nunca había<br>
                        <span class="hero-headline-accent">lucido tan bien.</span>
                    </h1>

                    <p class="hero-subtitle">
                        Descubre monturas exclusivas, lentes certificados<br>
                        y diseños creados para destacar.
                    </p>

                    <div class="hero-buttons">
                        <a href="/gafas" class="hero-btn-primary">Comprar ahora</a>
                        <a href="/gafas" class="hero-btn-secondary">Explorar colección</a>
                    </div>

                    <div class="hero-social-proof">
                        <span class="hero-stars">★★★★★</span>
                        <span>+25,970 clientes satisfechos</span>
                    </div>
                </div>

                <div class="hero-scroll-hint">SCROLL</div>
            </section>

            <script>
                (() => {
                    const imageA = document.querySelector('[data-hero-image-a]');
                    const imageB = document.querySelector('[data-hero-image-b]');
                    const images = <?php echo json_encode($heroImages ?? [], 15, 512) ?>;
                    if (!imageA || !imageB || !Array.isArray(images) || images.length < 2) return;

                    // Precarga solo del siguiente slide para no saturar Android con descargas paralelas.
                    const preloadOne = (url) => {
                        try {
                            if (typeof url !== 'string' || url.trim() === '') return;
                            const connection = navigator.connection || navigator.mozConnection || navigator.webkitConnection;
                            const isSlowNetwork = Boolean(connection && (connection.saveData || /2g/.test(String(connection.effectiveType || ''))));
                            if (isSlowNetwork) return;

                            const img = new Image();
                            img.decoding = 'async';
                            img.src = url;
                        } catch (_) {
                            // no-op
                        }
                    };

                    preloadOne(images[1]);

                    let index = 0;
                    let frontIsA = true;
                    const total = images.length;
                    const seconds = Number(<?php echo json_encode((int) ($landingHeroCarousel['seconds_per_slide'] ?? 5), 15, 512) ?>);
                    const delayMs = (Number.isFinite(seconds) ? Math.max(2, Math.min(30, seconds)) : 5) * 1000;
                    const slideDurationMs = 900;

                    setInterval(() => {
                        index = (index + 1) % total;
                        const front = frontIsA ? imageA : imageB;
                        const back = frontIsA ? imageB : imageA;

                        back.classList.remove('is-exit');
                        back.classList.remove('is-active');
                        back.src = images[index];

                        requestAnimationFrame(() => {
                            back.classList.add('is-active');
                            front.classList.remove('is-active');
                            front.classList.add('is-exit');
                        });

                        window.setTimeout(() => {
                            front.classList.remove('is-exit');
                        }, slideDurationMs + 40);

                        frontIsA = !frontIsA;
                        preloadOne(images[(index + 1) % total]);
                    }, delayMs);
                })();
            </script>

            
            <section id="beneficios" class="benefit-strip py-2.5">
                <?php
                    $stripItems = is_array($landingBenefitStrip['items'] ?? null) ? $landingBenefitStrip['items'] : [];
                    $benefitItems = [];

                    foreach ($stripItems as $item) {
                        $line = trim((string) $item);
                        if ($line === '') {
                            continue;
                        }
                        $benefitItems[] = str_starts_with($line, '•') ? $line : ('• ' . $line);
                    }
                ?>

                <div class="benefit-marquee w-full px-0">
                    <div class="benefit-track text-sm font-semibold text-zinc-700 sm:text-[1.05rem]" aria-label="Beneficios incluidos">
                        <?php $__currentLoopData = array_merge(($benefitItems ?? []), ($benefitItems ?? [])); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $benefit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <span class="benefit-item"><?php echo e($benefit); ?></span>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            </section>

            <script>
                (() => {
                    const section = document.getElementById('beneficios');
                    if (!section) return;
                    const track = section.querySelector('.benefit-track');
                    if (!track) return;

                    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                    if (reducedMotion || !('IntersectionObserver' in window)) return;

                    // playbackRate: 1 = normal (24s), 14 = ~14x más rápido
                    const RUSH_RATE  = 14;    // aceleración inicial
                    const NORMAL_RATE = 1;
                    const RUSH_MS    = 220;   // ms volando antes de empezar a desacelerar
                    const DECEL_MS   = 3780;  // ms de frenada suave (total rush+decel = 4s exactos)

                    let rushTimer = null;
                    let rafId     = null;

                    function smoothDecel(anim, fromRate, toRate, duration) {
                        cancelAnimationFrame(rafId);
                        const start = performance.now();
                        function frame(now) {
                            const t = Math.min((now - start) / duration, 1);
                            // Ease-out exponencial: empieza frenando rápido, llega suavísimo al final
                            const factor = t === 1 ? 1 : 1 - Math.pow(2, -10 * t);
                            anim.playbackRate = fromRate + (toRate - fromRate) * factor;
                            if (t < 1) rafId = requestAnimationFrame(frame);
                        }
                        rafId = requestAnimationFrame(frame);
                    }

                    function fireRush() {
                        clearTimeout(rushTimer);
                        cancelAnimationFrame(rafId);

                        const anims = track.getAnimations();
                        if (!anims.length) return;
                        const anim = anims[0];

                        // Disparo inmediato a máxima velocidad
                        anim.playbackRate = RUSH_RATE;

                        // Luego desacelera suavísimo por DECEL_MS hasta velocidad normal
                        rushTimer = setTimeout(() => {
                            smoothDecel(anim, RUSH_RATE, NORMAL_RATE, DECEL_MS);
                        }, RUSH_MS);
                    }

                    const obs = new IntersectionObserver((entries) => {
                        entries.forEach((entry) => {
                            if (entry.isIntersecting) fireRush();
                        });
                    }, { threshold: 0.4 });

                    obs.observe(section);
                })();
            </script>

            
            <section id="catalogo" class="py-14 sm:py-20">
                <div class="mx-auto max-w-7xl px-4">
                    <div class="flex items-end justify-between mb-10">
                        <div>
                            <h2 class="text-left text-[2.6rem] font-extrabold leading-[1.1] tracking-tight text-zinc-900 sm:text-[3.4rem]">
                                <em style="font-family: Georgia, 'Times New Roman', serif; font-style: italic; font-weight: 400;">Inspírate! ;)</em> Quizá te gusten…
                            </h2>
                        </div>
                        <a href="<?php echo e(url('/gafas')); ?>" class="catalog-header-btn hidden md:inline-flex">Ver todas</a>
                    </div>

                    <?php
                        $catalogDesktopSlides = collect();
                        $catalogMobileSlides = collect();
                        $desktopDotCount = 1;
                        $mobileDotCount = 1;

                        try {
                            $catalogProducts = \App\Models\Producto::query()
                                ->whereIn('tipo', ['gafas', 'gafas_ninas', 'gafas_ninos', 'gafas_polarizadas'])
                                ->whereIn('genero_objetivo', ['female', 'male', 'unisex', 'ninos', 'ninas', 'gafas_polarizadas', 'descanso'])
                                ->where('esta_activo', true)
                                ->whereRaw("meta->>'imagen_url' IS NOT NULL AND trim(meta->>'imagen_url') <> ''")
                                ->inRandomOrder()
                                ->limit(16)
                                ->get();

                            $catalogDesktopSlides = $catalogProducts->chunk(4)->take(4)->values();
                            $catalogMobileSlides = $catalogProducts->chunk(2)->take(8)->values();
                            $desktopDotCount = max(1, min(4, $catalogDesktopSlides->count()));
                            $mobileDotCount = max(1, min(8, $catalogMobileSlides->count()));
                        } catch (\Throwable $e) {
                            $catalogDesktopSlides = collect();
                            $catalogMobileSlides = collect();
                            $desktopDotCount = 1;
                            $mobileDotCount = 1;
                        }
                    ?>

                    <div class="mt-8 hidden overflow-hidden md:block" data-catalog-carousel-desktop>
                        <div class="flex" data-catalog-track-desktop>
                            <?php if($catalogDesktopSlides->count() > 0): ?>
                                <?php $__currentLoopData = $catalogDesktopSlides; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $slide): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="catalog-slide w-full shrink-0">
                                        <div class="grid grid-cols-2 gap-5 sm:gap-6 xl:grid-cols-4">
                                            <?php $__currentLoopData = $slide; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <?php
                                                    $meta = is_array($product->meta) ? $product->meta : [];
                                                    $img = (string) ($meta['imagen_url'] ?? '');
                                                    $price = $product->precio_oferta ?? $product->precio;
                                                    $brand = (string) ($product->marca ?? $meta['marca'] ?? '');
                                                    $namedHex = ['gris'=>'#cec9bc','rosa'=>'#e6c0c3','negro'=>'#2a2020','azul'=>'#b6d6e7','marron'=>'#6f4e37','carey'=>'#7b5a46','transparente'=>'#e5e7eb','dorado'=>'#c6a75a','plateado'=>'#b5bcc9','verde'=>'#466b48','morado'=>'#6d4c8b','rojo'=>'#9f2b2b','nude'=>'#d7b39d','blanco'=>'#f5f5f4'];
                                                    $swatchHexes = [];
                                                    $rawSw = $meta['colores'] ?? ($meta['color_variants'] ?? null);
                                                    if (is_array($rawSw)) {
                                                        foreach (array_slice($rawSw, 0, 5) as $sw) {
                                                            if (is_array($sw)) {
                                                                $swHex = trim((string)($sw['hex'] ?? ''));
                                                                $swName = strtolower(trim((string)($sw['name'] ?? '')));
                                                                if ($swHex === '') $swHex = $namedHex[$swName] ?? '#cec9bc';
                                                                if (!str_starts_with($swHex, '#')) $swHex = '#'.$swHex;
                                                                if (!in_array($swHex, $swatchHexes)) $swatchHexes[] = $swHex;
                                                            }
                                                        }
                                                    }
                                                    if (count($swatchHexes) === 0) {
                                                        $pc = strtolower(trim((string)($meta['color'] ?? ($product->color ?? ''))));
                                                        if ($pc !== '') $swatchHexes[] = $namedHex[$pc] ?? '#cec9bc';
                                                    }
                                                    $swatchHexes = array_slice($swatchHexes, 0, 5);
                                                ?>
                                                <a href="<?php echo e(route('gafas.show', ['producto' => $product->slug])); ?>" class="product-card product-reveal block" title="Ver <?php echo e($product->nombre); ?>">
                                                    <div class="card-img-wrap aspect-square">
                                                        <button type="button" class="card-heart" onclick="event.preventDefault();event.stopPropagation();" aria-label="Guardar en favoritos">
                                                            <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20.5s-6.8-4.3-9-8.6c-1.9-3.7.3-6.9 3.7-6.9 2 0 3.6 1.1 5.3 3 1.7-1.9 3.3-3 5.3-3 3.4 0 5.6 3.2 3.7 6.9-2.2 4.3-9 8.6-9 8.6z"/></svg>
                                                        </button>
                                                        <?php if($img !== ''): ?>
                                                            <img src="<?php echo e($img); ?>" alt="<?php echo e(e($product->nombre)); ?>" class="h-full w-full object-contain p-8" loading="lazy" draggable="false">
                                                        <?php else: ?>
                                                            <div class="flex h-full w-full items-center justify-center text-zinc-300 text-xs">Sin imagen</div>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="card-name-row">
                                                        <span class="card-name"><?php echo e($product->nombre); ?></span>
                                                        <span class="card-price"><?php echo e($price !== null ? '$ '.number_format((float)$price, 0, ',', '.') : '—'); ?></span>
                                                    </div>
                                                    <div class="card-meta-row">
                                                        <span class="card-brand"><?php echo e($brand); ?></span>
                                                        <?php if(count($swatchHexes) > 0): ?>
                                                            <span class="card-swatches">
                                                                <?php $__currentLoopData = $swatchHexes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $hex): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                    <span class="card-swatch" style="background-color:<?php echo e($hex); ?>;"></span>
                                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                            </span>
                                                        <?php endif; ?>
                                                    </div>
                                                </a>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </div>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <?php else: ?>
                                <?php
                                    $placeholders = [
                                        ['brand'=>'RAY-BAN',     'name'=>'Classic Aviator Premium',  'price'=>'$ 289.000', 'badge'=>'NUEVO',      'dark'=>false],
                                        ['brand'=>'HAWKERS',     'name'=>'Urban Square Edition',     'price'=>'$ 195.000', 'badge'=>'EXCLUSIVO',  'dark'=>true],
                                        ['brand'=>'OAKLEY',      'name'=>'Sport Half Frame Pro',     'price'=>'$ 320.000', 'badge'=>'BESTSELLER', 'dark'=>false],
                                        ['brand'=>'WARBY PARKER','name'=>'Minimal Round Bold',       'price'=>'$ 175.000', 'badge'=>'OFERTA',     'dark'=>false],
                                    ];
                                ?>
                                <div class="catalog-slide w-full shrink-0">
                                    <div class="grid grid-cols-2 gap-5 sm:gap-6 xl:grid-cols-4">
                                        <?php $__currentLoopData = $placeholders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ph): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <a href="<?php echo e(url('/gafas')); ?>" class="product-card product-reveal block" title="Ver catálogo completo">
                                                <div class="card-img-wrap aspect-[5/3] flex items-center justify-center">
                                                    <span class="text-zinc-300 text-xs">Sin imagen</span>
                                                </div>
                                                <div class="card-name-row">
                                                    <span class="card-name"><?php echo e($ph['name']); ?></span>
                                                    <span class="card-price"><?php echo e($ph['price']); ?></span>
                                                </div>
                                                <div class="card-meta-row">
                                                    <span class="card-brand"><?php echo e($ph['brand']); ?></span>
                                                </div>
                                            </a>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="mt-8 overflow-hidden md:hidden" data-catalog-carousel-mobile>
                        <div class="flex" data-catalog-track-mobile>
                            <?php if($catalogMobileSlides->count() > 0): ?>
                                <?php $__currentLoopData = $catalogMobileSlides; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $slide): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="catalog-slide w-full shrink-0">
                                        <div class="grid grid-cols-2 gap-4">
                                            <?php $__currentLoopData = $slide; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <?php
                                                    $meta = is_array($product->meta) ? $product->meta : [];
                                                    $img = (string) ($meta['imagen_url'] ?? '');
                                                    $price = $product->precio_oferta ?? $product->precio;
                                                    $brand = (string) ($product->marca ?? $meta['marca'] ?? '');
                                                    $namedHex = ['gris'=>'#cec9bc','rosa'=>'#e6c0c3','negro'=>'#2a2020','azul'=>'#b6d6e7','marron'=>'#6f4e37','carey'=>'#7b5a46','transparente'=>'#e5e7eb','dorado'=>'#c6a75a','plateado'=>'#b5bcc9','verde'=>'#466b48','morado'=>'#6d4c8b','rojo'=>'#9f2b2b','nude'=>'#d7b39d','blanco'=>'#f5f5f4'];
                                                    $swatchHexes = [];
                                                    $rawSw = $meta['colores'] ?? ($meta['color_variants'] ?? null);
                                                    if (is_array($rawSw)) {
                                                        foreach (array_slice($rawSw, 0, 5) as $sw) {
                                                            if (is_array($sw)) {
                                                                $swHex = trim((string)($sw['hex'] ?? ''));
                                                                $swName = strtolower(trim((string)($sw['name'] ?? '')));
                                                                if ($swHex === '') $swHex = $namedHex[$swName] ?? '#cec9bc';
                                                                if (!str_starts_with($swHex, '#')) $swHex = '#'.$swHex;
                                                                if (!in_array($swHex, $swatchHexes)) $swatchHexes[] = $swHex;
                                                            }
                                                        }
                                                    }
                                                    if (count($swatchHexes) === 0) {
                                                        $pc = strtolower(trim((string)($meta['color'] ?? ($product->color ?? ''))));
                                                        if ($pc !== '') $swatchHexes[] = $namedHex[$pc] ?? '#cec9bc';
                                                    }
                                                    $swatchHexes = array_slice($swatchHexes, 0, 5);
                                                ?>
                                                <a href="<?php echo e(route('gafas.show', ['producto' => $product->slug])); ?>" class="product-card product-reveal block" title="Ver <?php echo e($product->nombre); ?>">
                                                    <div class="card-img-wrap aspect-square">
                                                        <button type="button" class="card-heart" onclick="event.preventDefault();event.stopPropagation();" aria-label="Guardar en favoritos">
                                                            <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20.5s-6.8-4.3-9-8.6c-1.9-3.7.3-6.9 3.7-6.9 2 0 3.6 1.1 5.3 3 1.7-1.9 3.3-3 5.3-3 3.4 0 5.6 3.2 3.7 6.9-2.2 4.3-9 8.6-9 8.6z"/></svg>
                                                        </button>
                                                        <?php if($img !== ''): ?>
                                                            <img src="<?php echo e($img); ?>" alt="<?php echo e(e($product->nombre)); ?>" class="h-full w-full object-contain p-6" loading="lazy" draggable="false">
                                                        <?php else: ?>
                                                            <div class="flex h-full w-full items-center justify-center text-zinc-300 text-xs">Sin imagen</div>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="card-name-row">
                                                        <span class="card-name"><?php echo e($product->nombre); ?></span>
                                                        <span class="card-price"><?php echo e($price !== null ? '$ '.number_format((float)$price, 0, ',', '.') : '—'); ?></span>
                                                    </div>
                                                    <div class="card-meta-row">
                                                        <span class="card-brand"><?php echo e($brand); ?></span>
                                                        <?php if(count($swatchHexes) > 0): ?>
                                                            <span class="card-swatches">
                                                                <?php $__currentLoopData = $swatchHexes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $hex): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                    <span class="card-swatch" style="background-color:<?php echo e($hex); ?>;"></span>
                                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                            </span>
                                                        <?php endif; ?>
                                                    </div>
                                                </a>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </div>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <?php else: ?>
                                <div class="catalog-slide w-full shrink-0">
                                    <div class="grid grid-cols-2 gap-4">
                                        <?php $__currentLoopData = array_slice($placeholders ?? [['brand'=>'RAY-BAN','name'=>'Classic Aviator','price'=>'$ 289.000','badge'=>'NUEVO','dark'=>false],['brand'=>'HAWKERS','name'=>'Urban Square','price'=>'$ 195.000','badge'=>'EXCLUSIVO','dark'=>true]], 0, 2); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ph): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <a href="<?php echo e(url('/gafas')); ?>" class="product-card product-reveal block" title="Ver catálogo completo">
                                                <div class="image-slot product-image-slot" style="position:relative;">
                                                    <span class="product-badge <?php echo e($ph['dark'] ? 'is-dark' : ''); ?>"><?php echo e($ph['badge']); ?></span>
                                                    <span>Espacio imagen</span>
                                                </div>
                                                <span class="product-brand"><?php echo e($ph['brand']); ?></span>
                                                <h3><?php echo e($ph['name']); ?></h3>
                                                <p><?php echo e($ph['price']); ?></p>
                                            </a>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="mt-5 hidden items-center justify-center gap-3 md:flex" data-catalog-dots-desktop>
                        <?php for($i = 0; $i < $desktopDotCount; $i++): ?>
                            <button type="button" class="slider-dot <?php echo e($i === 0 ? 'is-active' : ''); ?>" data-catalog-dot-desktop data-index="<?php echo e($i); ?>" aria-label="Ir a productos <?php echo e($i + 1); ?>"></button>
                        <?php endfor; ?>
                    </div>

                    <div class="mt-5 flex items-center justify-center gap-3 md:hidden" data-catalog-dots-mobile>
                        <?php for($i = 0; $i < $mobileDotCount; $i++): ?>
                            <button type="button" class="slider-dot <?php echo e($i === 0 ? 'is-active' : ''); ?>" data-catalog-dot-mobile data-index="<?php echo e($i); ?>" aria-label="Ir a productos <?php echo e($i + 1); ?>"></button>
                        <?php endfor; ?>
                    </div>

                    <div class="mt-6 text-center md:hidden">
                        <a href="<?php echo e(url('/gafas')); ?>" class="inline-flex items-center gap-1.5 text-sm font-semibold text-zinc-500 underline underline-offset-4 hover:text-zinc-900 transition-colors">Ver toda la colección →</a>
                    </div>
                </div>
            </section>

            <script>
                (() => {
                    const catalogSection = document.getElementById('catalogo');

                    if (catalogSection && 'IntersectionObserver' in window) {
                        const catalogObserver = new IntersectionObserver((entries) => {
                            entries.forEach((entry) => {
                                if (entry.isIntersecting) {
                                    window.dispatchEvent(new CustomEvent('catalog:reenter'));
                                }
                            });
                        }, {
                            threshold: 0.2,
                            rootMargin: '0px 0px -8% 0px',
                        });

                        catalogObserver.observe(catalogSection);
                    } else if (catalogSection) {
                        catalogSection.classList.add('is-in-view');
                    }

                    const initCatalogCarousel = ({ carouselSelector, trackSelector, dotSelector }) => {
                        const carousel = document.querySelector(carouselSelector);
                        const track = document.querySelector(trackSelector);
                        const dots = Array.from(document.querySelectorAll(dotSelector));
                        if (!carousel || !track || !dots.length) return;

                        const slides = Array.from(track.children);
                        const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                        const maxIndex = Math.max(0, dots.length - 1);
                        let current = 0;
                        let enterTimer = null;
                        let touchStartX = null;
                        let touchStartY = null;
                        let touchDeltaX = 0;
                        let touchDeltaY = 0;
                        let mouseStartX = null;
                        let mouseStartY = null;
                        let mouseDeltaX = 0;
                        let mouseDeltaY = 0;
                        let isMouseDragging = false;
                        let suppressMouseClick = false;

                        // Mantener scroll vertical natural y habilitar gesto horizontal en móvil.
                        carousel.style.touchAction = 'pan-y';
                        carousel.style.cursor = 'grab';

                        const animateCurrentSlide = () => {
                            if (reducedMotion || !slides.length) return;

                            slides.forEach((slide) => slide.classList.remove('is-entering'));
                            const activeSlide = slides[current];
                            if (!activeSlide) return;

                            // Reinicia la animación cada vez que cambias de dot.
                            void activeSlide.offsetWidth;
                            activeSlide.classList.add('is-entering');

                            if (enterTimer !== null) {
                                clearTimeout(enterTimer);
                            }
                            enterTimer = window.setTimeout(() => {
                                activeSlide.classList.remove('is-entering');
                                enterTimer = null;
                            }, 950);
                        };

                        window.addEventListener('catalog:reenter', () => {
                            animateCurrentSlide();
                        });

                        const render = () => {
                            track.style.transform = `translateX(-${current * 100}%)`;
                            slides.forEach((slide, idx) => {
                                slide.classList.toggle('is-active', idx === current);
                            });
                            dots.forEach((dot, idx) => {
                                dot.classList.toggle('is-active', idx === current);
                            });
                            animateCurrentSlide();
                        };

                        dots.forEach((dot) => {
                            dot.addEventListener('click', () => {
                                const idx = Number(dot.getAttribute('data-index') || 0);
                                current = Math.max(0, Math.min(maxIndex, idx));
                                render();
                            });
                        });

                        const swipeThreshold = 45;

                        carousel.addEventListener('touchstart', (event) => {
                            if (!event.touches || event.touches.length !== 1) return;
                            touchStartX = event.touches[0].clientX;
                            touchStartY = event.touches[0].clientY;
                            touchDeltaX = 0;
                            touchDeltaY = 0;
                        }, { passive: true });

                        carousel.addEventListener('touchmove', (event) => {
                            if (touchStartX === null || touchStartY === null || !event.touches || event.touches.length !== 1) return;

                            touchDeltaX = event.touches[0].clientX - touchStartX;
                            touchDeltaY = event.touches[0].clientY - touchStartY;

                            // Si el gesto ya es claramente horizontal, evita que el navegador lo convierta en scroll.
                            if (Math.abs(touchDeltaX) > Math.abs(touchDeltaY) && Math.abs(touchDeltaX) > 10) {
                                event.preventDefault();
                            }
                        }, { passive: false });

                        carousel.addEventListener('touchend', () => {
                            if (touchStartX === null || touchStartY === null) return;

                            const isHorizontal = Math.abs(touchDeltaX) > Math.abs(touchDeltaY);
                            if (isHorizontal && Math.abs(touchDeltaX) >= swipeThreshold) {
                                if (touchDeltaX < 0) {
                                    current = Math.min(maxIndex, current + 1);
                                } else {
                                    current = Math.max(0, current - 1);
                                }
                                render();
                            }

                            touchStartX = null;
                            touchStartY = null;
                            touchDeltaX = 0;
                            touchDeltaY = 0;
                        });

                        const endMouseDrag = () => {
                            if (!isMouseDragging) return;

                            const isHorizontal = Math.abs(mouseDeltaX) > Math.abs(mouseDeltaY);
                            if (isHorizontal && Math.abs(mouseDeltaX) >= swipeThreshold) {
                                if (mouseDeltaX < 0) {
                                    current = Math.min(maxIndex, current + 1);
                                } else {
                                    current = Math.max(0, current - 1);
                                }
                                render();
                            }

                            mouseStartX = null;
                            mouseStartY = null;
                            mouseDeltaX = 0;
                            mouseDeltaY = 0;
                            isMouseDragging = false;
                            carousel.style.cursor = 'grab';
                        };

                        carousel.addEventListener('mousedown', (event) => {
                            if (!(event instanceof MouseEvent)) return;
                            if (event.button !== 0) return;

                            isMouseDragging = true;
                            suppressMouseClick = false;
                            mouseStartX = event.clientX;
                            mouseStartY = event.clientY;
                            mouseDeltaX = 0;
                            mouseDeltaY = 0;
                            carousel.style.cursor = 'grabbing';
                        });

                        carousel.addEventListener('mousemove', (event) => {
                            if (!isMouseDragging || mouseStartX === null || mouseStartY === null) return;

                            mouseDeltaX = event.clientX - mouseStartX;
                            mouseDeltaY = event.clientY - mouseStartY;

                            if (Math.abs(mouseDeltaX) > 6) {
                                suppressMouseClick = true;
                            }

                            if (Math.abs(mouseDeltaX) > Math.abs(mouseDeltaY) && Math.abs(mouseDeltaX) > 10) {
                                event.preventDefault();
                            }
                        });

                        carousel.addEventListener('mouseup', endMouseDrag);
                        carousel.addEventListener('mouseleave', endMouseDrag);

                        carousel.addEventListener('click', (event) => {
                            if (!suppressMouseClick) return;
                            suppressMouseClick = false;
                            event.preventDefault();
                            event.stopPropagation();
                        }, true);

                        render();
                    };

                    initCatalogCarousel({
                        carouselSelector: '[data-catalog-carousel-desktop]',
                        trackSelector: '[data-catalog-track-desktop]',
                        dotSelector: '[data-catalog-dot-desktop]',
                    });

                    initCatalogCarousel({
                        carouselSelector: '[data-catalog-carousel-mobile]',
                        trackSelector: '[data-catalog-track-mobile]',
                        dotSelector: '[data-catalog-dot-mobile]',
                    });

                    const productCards = Array.from(document.querySelectorAll('.product-reveal'));
                    if (!productCards.length) return;

                    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                    if (reducedMotion || !('IntersectionObserver' in window)) {
                        productCards.forEach((card) => card.classList.add('is-visible'));
                        return;
                    }

                    const productObserver = new IntersectionObserver((entries) => {
                        entries.forEach((entry) => {
                            const card = entry.target;
                            if (entry.isIntersecting) {
                                card.classList.add('is-visible');
                            } else {
                                card.classList.remove('is-visible');
                            }
                        });
                    }, {
                        threshold: 0.18,
                        rootMargin: '0px 0px -8% 0px',
                    });

                    productCards.forEach((card, index) => {
                        card.style.transitionDelay = `${Math.min(index * 42, 220)}ms`;
                        productObserver.observe(card);
                    });
                })();
            </script>

            
            <section id="categorias" class="bg-white pb-14 pt-2 sm:pb-16">
                <div class="mx-auto max-w-7xl px-4">
                    <span class="section-eyebrow">NUESTRAS CATEGORÍAS</span>
                    <h2 class="text-center text-[2.3rem] leading-none text-black sm:text-[4rem]">
                        escribe información
                    </h2>

                    <?php
                        $categoryPhotos = is_array($landingCategoryPhotos ?? null) ? $landingCategoryPhotos : [];
                        $cardMinHeight = 400;

                        $categoryCards = [
                            [
                                'label'       => 'NIÑOS',
                                'tagline'     => 'PROTECCIÓN Y COLOR PARA ELLOS',
                                'badge'       => 'NUEVO',
                                'placeholder' => 'Espacio imagen niños',
                                'image'       => (string) ($categoryPhotos['ninos_image'] ?? '/images/Niños.png'),
                                'mascot'      => asset('images/capibara_esquinero.png'),
                                'style'       => 'background: #1a1a24; min-height: ' . $cardMinHeight . 'px;',
                                'mascot_class'      => '',
                                'mascot_wrap_class' => 'is-right-offset',
                                'href'        => route('gafas.index', ['categories' => ['ninos', 'ninas']])
                            ],
                            [
                                'label'       => 'MUJERES',
                                'tagline'     => 'ELEGANCIA QUE DEFINE TU MIRADA',
                                'badge'       => 'COLECCIÓN',
                                'placeholder' => 'Espacio imagen mujeres',
                                'image'       => (string) ($categoryPhotos['mujeres_image'] ?? '/images/Mujer.png'),
                                'mascot'      => asset('images/perroesquianderecha.png'),
                                'style'       => 'background: #1a1a24; min-height: ' . $cardMinHeight . 'px;',
                                'mascot_class'      => '',
                                'mascot_wrap_class' => '',
                                'href'        => route('gafas-mujeres.index')
                            ],
                            [
                                'label'       => 'HOMBRES',
                                'tagline'     => 'ESTILO QUE HABLA POR SÍ SOLO',
                                'badge'       => 'EXCLUSIVO',
                                'placeholder' => 'Espacio imagen hombres',
                                'image'       => (string) ($categoryPhotos['hombres_image'] ?? '/images/Hombre.png'),
                                'mascot'      => asset('images/castoresquinaderecha.png'),
                                'style'       => 'background: #1a1a24; min-height: ' . $cardMinHeight . 'px;',
                                'mascot_class'      => '',
                                'mascot_wrap_class' => '',
                                'href'        => route('gafas-hombre.index')
                            ],
                        ];
                    ?>

                    <div class="mt-8 grid gap-4 md:grid-cols-3">
                        <?php $__currentLoopData = $categoryCards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <a
                                href="<?php echo e($category['href']); ?>"
                                class="category-card category-reveal block"
                                data-reveal-side="<?php echo e($loop->index % 2 === 0 ? 'left' : 'right'); ?>"
                                title="Ver categoría <?php echo e($category['label']); ?>"
                            >
                                <div class="category-photo-slot<?php echo e(!empty($category['image']) ? ' has-cover-image' : ''); ?>" style="<?php echo e($category['style']); ?>">
                                    <?php if(!empty($category['image'])): ?>
                                        <img src="<?php echo e($category['image']); ?>" alt="<?php echo e($category['label']); ?>" class="category-photo-image" loading="lazy" draggable="false" style="object-position: <?php echo e($category['image_position'] ?? 'center'); ?>;">
                                    <?php else: ?>
                                        <span class="category-placeholder-tag"><?php echo e($category['placeholder']); ?></span>
                                    <?php endif; ?>

                                    
                                    <?php if(!empty($category['badge'])): ?>
                                        <span class="category-badge"><?php echo e($category['badge']); ?></span>
                                    <?php endif; ?>
                                </div>

                                <div class="category-label">
                                    <?php if(!empty($category['tagline'])): ?>
                                        <span class="category-label-tagline"><?php echo e($category['tagline']); ?></span>
                                    <?php endif; ?>
                                    <span class="category-label-name"><?php echo e($category['label']); ?></span>
                                </div>
                            </a>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            </section>

            <script>
                (() => {
                    const cards = Array.from(document.querySelectorAll('.category-reveal'));
                    if (!cards.length) return;

                    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                    if (reducedMotion || !('IntersectionObserver' in window)) {
                        cards.forEach((card) => card.classList.add('is-visible'));
                        return;
                    }

                    const isMobileViewport = () => window.matchMedia('(max-width: 767px)').matches;
                    const getEnterDelayMs = (index) => Math.min(index * 90, 220);

                    cards.forEach((card, index) => {
                        card.dataset.revealDelay = String(getEnterDelayMs(index));
                    });

                    const observer = new IntersectionObserver((entries) => {
                        entries.forEach((entry) => {
                            const card = entry.target;
                            const isMobile = isMobileViewport();
                            let shouldShow = entry.isIntersecting;

                            if (isMobile) {
                                const rect = entry.boundingClientRect;
                                const viewportHeight = window.innerHeight || document.documentElement.clientHeight;
                                const visiblePx = Math.min(rect.bottom, viewportHeight) - Math.max(rect.top, 0);
                                const minVisiblePx = Math.max(72, rect.height * 0.22);
                                shouldShow = entry.isIntersecting && visiblePx >= minVisiblePx;
                            }

                            if (shouldShow) {
                                const enterDelay = Number(card.dataset.revealDelay || 0);
                                card.style.transitionDelay = `${enterDelay}ms`;
                                card.classList.add('is-visible');
                            } else {
                                card.style.transitionDelay = '0ms';
                                card.classList.remove('is-visible');
                            }
                        });
                    }, {
                        threshold: [0, 0.12, 0.2, 0.35],
                        rootMargin: '-4% 0px -10% 0px',
                    });

                    cards.forEach((card) => {
                        observer.observe(card);
                    });
                })();
            </script>

            
            <section id="como-funciona" class="bg-white py-16 sm:py-24">
                <style>
                    #como-funciona .steps-title {
                        font-size: clamp(2rem, 5vw, 3.25rem);
                        font-weight: 300;
                        color: #111827;
                        text-align: center;
                        letter-spacing: -0.02em;
                        line-height: 1.15;
                        margin-bottom: 3.5rem;
                    }
                    #como-funciona .steps-grid {
                        display: grid;
                        grid-template-columns: 1fr;
                        gap: 2.5rem;
                        max-width: 900px;
                        margin: 0 auto;
                    }
                    @media (min-width: 768px) {
                        #como-funciona .steps-grid {
                            grid-template-columns: 1fr auto 1fr auto 1fr;
                            align-items: start;
                            gap: 0;
                        }
                    }
                    #como-funciona .step-connector {
                        display: none;
                    }
                    @media (min-width: 768px) {
                        #como-funciona .step-connector {
                            display: flex;
                            align-items: flex-start;
                            padding-top: 5.5rem;
                        }
                        #como-funciona .step-connector::after {
                            content: '';
                            display: block;
                            width: 60px;
                            height: 1px;
                            background: #d1d5db;
                        }
                    }
                    #como-funciona .step {
                        text-align: center;
                        display: flex;
                        flex-direction: column;
                        align-items: center;
                        gap: 0;
                    }
                    #como-funciona .step-icon {
                        height: 90px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        margin-bottom: 1.25rem;
                    }
                    #como-funciona .step-icon svg {
                        height: 70px;
                        width: auto;
                    }
                    #como-funciona .step-divider {
                        width: 100%;
                        height: 1px;
                        background: #e5e7eb;
                        margin-bottom: 1.1rem;
                    }
                    #como-funciona .step-title {
                        font-size: 1.15rem;
                        font-weight: 500;
                        color: #111827;
                        margin-bottom: 0.65rem;
                    }
                    #como-funciona .step-desc {
                        font-size: 0.9rem;
                        color: #6b7280;
                        line-height: 1.65;
                        max-width: 220px;
                    }
                </style>

                <div class="mx-auto max-w-5xl px-6">
                    <h2 class="steps-title">Compra de forma fácil, sencilla y segura.</h2>

                    <div class="steps-grid">

                        
                        <div class="step">
                            <div class="step-icon">
                                <svg viewBox="0 0 80 70" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="#5B7CE6" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                    <!-- Círculo izquierdo grande -->
                                    <circle cx="20" cy="42" r="13"/>
                                    <!-- Círculo derecho grande -->
                                    <circle cx="60" cy="42" r="13"/>
                                    <!-- Puente central -->
                                    <path d="M33 42 Q40 36 47 42"/>
                                    <!-- Patilla izquierda -->
                                    <path d="M7 42 L2 38"/>
                                    <!-- Patilla derecha -->
                                    <path d="M73 42 L78 38"/>
                                    <!-- Lente flotante superior izquierda (rectángulo redondeado) -->
                                    <rect x="5" y="8" width="20" height="14" rx="5"/>
                                    <!-- Lente flotante superior derecha (óvalo) -->
                                    <ellipse cx="62" cy="15" rx="11" ry="7"/>
                                    <!-- Conector decorativo -->
                                    <line x1="25" y1="15" x2="51" y2="15"/>
                                </svg>
                            </div>
                            <div class="step-divider"></div>
                            <p class="step-title">Elige la montura</p>
                            <p class="step-desc">Elige la montura que más te guste y mejor se adapte a tí, de entre más de 100 modelos.</p>
                        </div>

                        <div class="step-connector"></div>

                        
                        <div class="step">
                            <div class="step-icon">
                                <svg viewBox="0 0 80 70" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="#5B7CE6" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                    <!-- Lente oval -->
                                    <ellipse cx="46" cy="44" rx="26" ry="15" transform="rotate(-20 46 44)"/>
                                    <!-- Brillo/reflejo en la lente -->
                                    <line x1="36" y1="30" x2="38" y2="35"/>
                                    <line x1="38" y1="30" x2="36" y2="35"/>
                                    <!-- Estrella de 4 puntas (sparkle) -->
                                    <line x1="22" y1="18" x2="22" y2="8"/>
                                    <line x1="17" y1="13" x2="27" y2="13"/>
                                    <line x1="19" y1="10" x2="25" y2="16"/>
                                    <line x1="25" y1="10" x2="19" y2="16"/>
                                </svg>
                            </div>
                            <div class="step-divider"></div>
                            <p class="step-title">Elige las lentes</p>
                            <p class="step-desc">Si las necesitas graduar, puedes configurarlas y elegir el tratamiento que mejor se adapte a tus necesidades</p>
                        </div>

                        <div class="step-connector"></div>

                        
                        <div class="step">
                            <div class="step-icon">
                                <svg viewBox="0 0 80 70" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="#5B7CE6" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                    <!-- Caja de entrega -->
                                    <rect x="20" y="40" width="40" height="24" rx="3"/>
                                    <!-- Tapa de la caja -->
                                    <path d="M20 40 L40 34 L60 40"/>
                                    <!-- Cinta de la caja -->
                                    <line x1="40" y1="34" x2="40" y2="64"/>
                                    <line x1="20" y1="52" x2="60" y2="52"/>
                                    <!-- Arco del paracaídas -->
                                    <path d="M22 32 Q40 8 58 32"/>
                                    <!-- Cuerdas del paracaídas -->
                                    <line x1="22" y1="32" x2="30" y2="42"/>
                                    <line x1="40" y1="14" x2="40" y2="42"/>
                                    <line x1="58" y1="32" x2="50" y2="42"/>
                                </svg>
                            </div>
                            <div class="step-divider"></div>
                            <p class="step-title">Te lo enviamos</p>
                            <p class="step-desc">¡Todo listo! Elige la forma de envío, completa el pago y recibe las gafas donde quieras.</p>
                        </div>

                    </div>
                </div>
            </section>

            
            <section id="polarizado" class="polarized-feature-shell">
                <div class="polarized-feature-card">
                    <?php
                        $secondaryFallback = asset('images/Gemini_Generated_Image_11a6iv11a6iv11a6.png');
                        $secondaryImages = [];

                        foreach (collect($secondaryBanners ?? []) as $secondaryBannerItem) {
                            $candidate = trim((string) $secondaryBannerItem->bannerImageUrl('secondary-banners.image', 'secondary_banner', ''));
                            if ($candidate !== '') {
                                $secondaryImages[] = $candidate;
                            }
                        }

                        if (count($secondaryImages) === 0) {
                            $secondaryImages[] = $secondaryFallback;
                        }

                        $secondaryImage = $secondaryImages[0];
                    ?>

                    
                    <div class="secondary-banner-frame">
                        <img
                            src="<?php echo e($secondaryImage); ?>"
                            alt="Promoción lentes polarizados"
                            class="hero-panel-image is-active"
                            loading="eager"
                            fetchpriority="high"
                            decoding="async"
                            draggable="false"
                            data-secondary-image-a
                        >
                        <img
                            src="<?php echo e($secondaryImage); ?>"
                            alt=""
                            aria-hidden="true"
                            class="hero-panel-image"
                            loading="lazy"
                            fetchpriority="low"
                            decoding="async"
                            draggable="false"
                            data-secondary-image-b
                        >
                    </div>

                    
                    <div class="secondary-text-overlay">
                        <span class="secondary-eyebrow">TECNOLOGÍA UV</span>

                        <h2 class="secondary-headline">
                            Protección UV,<br>
                            comodidad<br>
                            y estilo en <span class="secondary-headline-accent">cada<br>
                            mirada.</span>
                        </h2>

                        <p class="secondary-subtitle">
                            Todos nuestros lentes cuentan con filtro UV400, antirreflejo y
                            tratamiento de durabilidad premium para que tu visión esté
                            siempre protegida.
                        </p>

                        <a href="<?php echo e(url('/gafas')); ?>" class="secondary-cta">
                            Conocer más &nbsp;→
                        </a>
                    </div>
                </div>
            </section>

            <script>
                (() => {
                    const imageA = document.querySelector('[data-secondary-image-a]');
                    const imageB = document.querySelector('[data-secondary-image-b]');
                    const images = <?php echo json_encode($secondaryImages ?? [], 15, 512) ?>;
                    if (!imageA || !imageB || !Array.isArray(images) || images.length < 2) return;

                    // Precarga solo del siguiente slide para no saturar Android con descargas paralelas.
                    const preloadOne = (url) => {
                        try {
                            if (typeof url !== 'string' || url.trim() === '') return;
                            const connection = navigator.connection || navigator.mozConnection || navigator.webkitConnection;
                            const isSlowNetwork = Boolean(connection && (connection.saveData || /2g/.test(String(connection.effectiveType || ''))));
                            if (isSlowNetwork) return;

                            const img = new Image();
                            img.decoding = 'async';
                            img.src = url;
                        } catch (_) {
                            // no-op
                        }
                    };

                    preloadOne(images[1]);

                    let index = 0;
                    let frontIsA = true;
                    const total = images.length;
                    const seconds = Number(<?php echo json_encode((int) ($landingSecondaryCarousel['seconds_per_slide'] ?? 5), 15, 512) ?>);
                    const delayMs = (Number.isFinite(seconds) ? Math.max(2, Math.min(30, seconds)) : 5) * 1000;
                    const slideDurationMs = 900;

                    setInterval(() => {
                        index = (index + 1) % total;
                        const front = frontIsA ? imageA : imageB;
                        const back = frontIsA ? imageB : imageA;

                        back.classList.remove('is-exit');
                        back.classList.remove('is-active');
                        back.src = images[index];

                        requestAnimationFrame(() => {
                            back.classList.add('is-active');
                            front.classList.remove('is-active');
                            front.classList.add('is-exit');
                        });

                        window.setTimeout(() => {
                            front.classList.remove('is-exit');
                        }, slideDurationMs + 40);

                        frontIsA = !frontIsA;
                        preloadOne(images[(index + 1) % total]);
                    }, delayMs);
                })();
            </script>

            
            <section id="ubicacion" class="py-14 sm:py-20" style="background:#ffffff;">
                <div class="mx-auto max-w-7xl px-4">
                    <span class="section-eyebrow" style="color:#9ca3af;">DÓNDE ESTAMOS</span>
                    <h2 class="mt-1 text-center text-3xl font-extrabold tracking-tight text-zinc-900 sm:text-[3.25rem]">
                        <?php echo e((string) ($landingLocation['title'] ?? 'Visítanos en persona')); ?>

                    </h2>

                    <?php
                        $locationData = is_array($landingLocation ?? null) ? $landingLocation : [];
                        $locations = is_array($locationData['locations'] ?? null) ? $locationData['locations'] : [];
                        if (count($locations) === 0) {
                            $locations = \App\Services\LandingLocationContent::defaults()['locations'];
                        }
                        $locationBadges = ['SEDE PRINCIPAL', 'NUEVA SEDE', 'SEDE SUR'];
                    ?>

                    <div class="mt-10 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                        <?php $__currentLoopData = $locations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $location): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $totalLocations = count($locations);
                                $isOddLastCard = ($totalLocations % 2 === 1) && $loop->last;
                                $cityName = (string) ($location['address'] ?? 'Ubicación');
                                $venueName = (string) ($location['venue_name'] ?? 'Sede');
                                $description = trim((string) ($location['description'] ?? ''));
                                $mapTitle = (string) ($location['map_title'] ?? ('Mapa ' . $cityName));
                                $imageTop = (string) ($location['image_url'] ?? '/images/naratodo.png');
                                $lat = is_numeric($location['lat'] ?? null) ? (float) $location['lat'] : 4.7110;
                                $lng = is_numeric($location['lng'] ?? null) ? (float) $location['lng'] : -74.0721;
                                $zoom = is_numeric($location['zoom'] ?? null) ? (int) $location['zoom'] : 15;
                                $lat = max(-90, min(90, $lat));
                                $lng = max(-180, min(180, $lng));
                                $zoom = max(1, min(20, $zoom));
                                $mapSrc = 'https://www.google.com/maps?q=' . rawurlencode($lat . ',' . $lng) . '&z=' . $zoom . '&output=embed';
                                $href = 'https://www.google.com/maps?ll=' . rawurlencode($lat . ',' . $lng) . '&z=' . $zoom;
                                $badge = $locationBadges[$loop->index] ?? 'SEDE';
                            ?>
                            <a href="<?php echo e($href); ?>" target="_blank" rel="noopener"
                               class="location-city-card location-reveal <?php echo e($isOddLastCard ? 'sm:col-span-2 lg:col-span-1' : ''); ?>"
                               title="Abrir ubicación de <?php echo e($cityName); ?> en Google Maps">

                                
                                <div class="image-slot storefront-slot">
                                    <img src="<?php echo e($imageTop); ?>" alt="<?php echo e($venueName); ?>" loading="lazy" draggable="false">
                                </div>

                                
                                <div class="location-card-body">
                                    <span class="location-badge"><?php echo e($badge); ?></span>

                                    <h3 class="location-city-name"><?php echo e($cityName); ?></h3>

                                    <?php if($description !== ''): ?>
                                        <div class="location-info-row">
                                            <svg class="location-info-icon" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 2C8.134 2 5 5.134 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.866-3.134-7-7-7zm0 9.5a2.5 2.5 0 1 1 0-5 2.5 2.5 0 0 1 0 5z"/></svg>
                                            <span><?php echo e($description); ?></span>
                                        </div>
                                    <?php endif; ?>

                                    <span class="location-view-link">
                                        Ver ubicación
                                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6M15 3h6v6M10 14 21 3"/></svg>
                                    </span>
                                </div>

                                
                                <div class="image-slot map-slot has-map" style="border-radius:0 0 1.25rem 1.25rem; min-height:12rem;">
                                    <iframe
                                        src="<?php echo e($mapSrc); ?>"
                                        title="<?php echo e($mapTitle); ?>"
                                        loading="lazy"
                                        referrerpolicy="no-referrer-when-downgrade"
                                    ></iframe>
                                </div>
                            </a>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            </section>

            <script>
                (() => {
                    const locCards = Array.from(document.querySelectorAll('.location-reveal'));
                    if (!locCards.length) return;

                    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                    if (reducedMotion || !('IntersectionObserver' in window)) {
                        locCards.forEach((c) => c.classList.add('is-visible'));
                        return;
                    }

                    const locObserver = new IntersectionObserver((entries) => {
                        entries.forEach((entry) => {
                            if (entry.isIntersecting) {
                                entry.target.classList.add('is-visible');
                            } else {
                                entry.target.classList.remove('is-visible');
                            }
                        });
                    }, {
                        threshold: 0.15,
                        rootMargin: '0px 0px -5% 0px',
                    });

                    locCards.forEach((card, index) => {
                        card.style.transitionDelay = `${index * 110}ms`;
                        locObserver.observe(card);
                    });
                })();
            </script>

            
            <section id="estrena-ya" class="overflow-hidden">
                <div class="flex flex-col md:flex-row" style="min-height: 420px;">

                    
                    <div class="relative w-full md:w-1/2 overflow-hidden" style="background: #f0efea; min-height: 340px;">
                        <?php
                            $estrenaImg = '';
                            foreach (collect($secondaryBanners ?? []) as $sb) {
                                $candidate = trim((string) $sb->bannerImageUrl('secondary-banners.image', 'secondary_banner', ''));
                                if ($candidate !== '') { $estrenaImg = $candidate; break; }
                            }
                            if ($estrenaImg === '') $estrenaImg = asset('images/Promo2.png');
                        ?>
                        <img
                            src="<?php echo e($estrenaImg); ?>"
                            alt="Estrena ya"
                            class="absolute inset-0 h-full w-full object-cover"
                            loading="lazy"
                            draggable="false"
                        >
                    </div>

                    
                    <div class="flex w-full flex-col justify-center px-10 py-16 md:w-1/2 md:px-16 lg:px-20" style="background: #E3EFF5;">
                        <h2 style="font-family: Georgia,'Times New Roman',serif; font-size: clamp(2.6rem,5vw,4rem); font-weight: 400; color: #111827; line-height: 1.1; margin: 0 0 1.75rem;">
                            ¡Estrena ya!
                        </h2>
                        <p style="font-size: 1rem; color: #374151; line-height: 1.7; margin: 0 0 2.5rem; max-width: 360px;">
                            No te quedes atrás en las últimas tendencias.
                        </p>
                        <div>
                            <a href="<?php echo e(url('/gafas')); ?>"
                               style="display: inline-block; border: 1.5px solid #111827; padding: 0.8rem 2.8rem; font-size: 0.8rem; font-weight: 700; letter-spacing: 0.12em; color: #111827; text-decoration: underline; text-underline-offset: 4px; transition: background 200ms, color 200ms;"
                               onmouseover="this.style.background='#111827';this.style.color='#fff';"
                               onmouseout="this.style.background='transparent';this.style.color='#111827';">
                                VER TODAS
                            </a>
                        </div>
                    </div>

                </div>
            </section>

            
            <section id="agenda-examen" class="overflow-hidden">
                <div class="flex flex-col md:flex-row" style="min-height: 460px;">

                    
                    <div class="flex w-full flex-col justify-between px-10 py-14 md:w-[38%] md:px-14 lg:px-16" style="background: #4D7FA5;">
                        <h2 style="font-family: Georgia,'Times New Roman',serif; font-size: clamp(2.1rem,4.5vw,3.75rem); font-weight: 400; color: #fff; line-height: 1.1; margin: 0;">
                            Agenda un examen visual <em style="font-style: italic;">ahora</em>.
                        </h2>
                        <div>
                            <p style="font-size: 0.95rem; color: rgba(255,255,255,0.82); line-height: 1.75; margin: 0 0 2.25rem; max-width: 340px;">
                                Es importante revisar periódicamente nuestra visión y usar gafas en caso de necesitarlo.
                            </p>
                            <a href="#contacto"
                               style="display: inline-block; border: 1.5px solid #fff; padding: 0.75rem 2.5rem; font-size: 0.78rem; font-weight: 700; letter-spacing: 0.12em; color: #fff; text-decoration: underline; text-underline-offset: 4px; transition: background 200ms, color 200ms;"
                               onmouseover="this.style.background='#fff';this.style.color='#4D7FA5';"
                               onmouseout="this.style.background='transparent';this.style.color='#fff';">
                                CITA EXAMEN
                            </a>
                        </div>
                    </div>

                    
                    <div class="relative w-full md:w-[62%] overflow-hidden" style="background: #d9d4cf; min-height: 340px;">
                        <?php
                            $agendaImg = '';
                            foreach (collect($heroBanners ?? []) as $hb) {
                                $hbMeta = is_array($hb->meta ?? null) ? $hb->meta : [];
                                $c = trim((string) ($hbMeta['imagen_url'] ?? ''));
                                if ($c !== '') { $agendaImg = $c; break; }
                            }
                            if ($agendaImg === '') {
                                foreach (collect($secondaryBanners ?? []) as $sb) {
                                    $c = trim((string) $sb->bannerImageUrl('secondary-banners.image', 'secondary_banner', ''));
                                    if ($c !== '') { $agendaImg = $c; break; }
                                }
                            }
                            if ($agendaImg === '') $agendaImg = asset('images/Mujer.png');
                        ?>
                        <img
                            src="<?php echo e($agendaImg); ?>"
                            alt="Agenda tu examen visual"
                            class="absolute inset-0 h-full w-full object-cover"
                            loading="lazy"
                            draggable="false"
                        >
                    </div>

                </div>
            </section>

            
            <section id="resenas" class="pb-0 pt-10 sm:pt-14">
                <div class="mx-auto max-w-7xl px-4">
                    <?php
                        $reviewsAvgGlobal = (float) ($reviewsAvg ?? 0);
                        $reviewsCountGlobal = (int) ($reviewsCount ?? 0);
                        $reviewsStarsRounded = max(0, min(5, (int) round($reviewsAvgGlobal)));
                        $reviewModerator = auth()->user();
                        $canDeleteReviews = $reviewModerator && in_array((int) ($reviewModerator->rol_id ?? 0), [2, 3, 4], true);
                    ?>

                    <div class="flex flex-col items-center justify-center gap-3 text-center">
                        <span class="section-eyebrow">OPINIONES REALES</span>
                        <h2 class="text-center text-3xl font-extrabold tracking-tight text-black sm:text-[3.15rem]">
                            Lo que dicen<br>nuestros clientes
                        </h2>
                        <div class="inline-flex items-center gap-2 mt-1">
                            <span class="text-lg text-[#374151] leading-none"><?php echo e(str_repeat('★', max(5, $reviewsStarsRounded))); ?></span>
                            <span class="text-sm font-semibold text-zinc-600">
                                <?php echo e($reviewsAvgGlobal > 0 ? number_format($reviewsAvgGlobal, 1, '.', '') : '4.9'); ?> / 5
                                basado en
                                <?php echo e($reviewsCountGlobal > 0 ? '+' . number_format($reviewsCountGlobal, 0, ',', '.') : '+2.100'); ?>

                                reseñas
                            </span>
                        </div>
                    </div>

                    <?php
                        $reviewsItems = collect($latestReviews ?? [])->values();

                        if ($reviewsItems->isEmpty()) {
                            $reviewsItems = collect([
                                [
                                    'autor_nombre' => 'Olivia Borcelle',
                                    'estrellas' => 5,
                                    'comentario' => 'Cada pieza está diseñada con atención al detalle y un toque de originalidad que transforma lo cotidiano en extraordinario.',
                                ],
                                [
                                    'autor_nombre' => 'Mateo Rojas',
                                    'estrellas' => 5,
                                    'comentario' => 'La atención fue excelente y las gafas llegaron justo como las esperaba. Muy recomendados.',
                                ],
                                [
                                    'autor_nombre' => 'Laura Jiménez',
                                    'estrellas' => 5,
                                    'comentario' => 'Buena calidad, cómodas y con un diseño súper bonito. Volvería a comprar sin duda.',
                                ],
                            ]);
                        }

                        $isReviewsAnimated = $reviewsItems->count() > 1;
                        $reviewsLoopItems = $reviewsItems->values();

                        if ($isReviewsAnimated) {
                            while ($reviewsLoopItems->count() < 6) {
                                $reviewsLoopItems = $reviewsLoopItems->concat($reviewsItems);
                            }

                            $reviewsLoopItems = $reviewsLoopItems->values();
                        }
                    ?>
                    <div class="mt-7 reviews-marquee full-bleed" data-reviews-marquee>
                        <div class="reviews-track <?php echo e(($isReviewsAnimated ?? false) ? 'is-animated' : 'is-static'); ?>" data-reviews-track>
                        <?php
                            $reviewGroups = ($isReviewsAnimated ?? false) ? [0, 1] : [0];
                        ?>
                        <?php $__currentLoopData = $reviewGroups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reviewGroupIndex): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="reviews-group" data-reviews-group <?php if(($isReviewsAnimated ?? false) && $reviewGroupIndex === 1): ?> aria-hidden="true" <?php endif; ?>>
                        <?php $__currentLoopData = (($isReviewsAnimated ?? false) ? $reviewsLoopItems : $reviewsItems); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $review): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $reviewName = trim((string) (is_array($review) ? ($review['autor_nombre'] ?? '') : ($review->autor_nombre ?? ($review->usuario->nombre ?? ''))));
                                $reviewName = $reviewName !== '' ? $reviewName : 'Cliente';
                                $reviewComment = trim((string) (is_array($review) ? ($review['comentario'] ?? '') : ($review->comentario ?? '')));
                                $reviewComment = $reviewComment !== '' ? $reviewComment : 'Gracias por compartir tu experiencia con Optica.';
                                $reviewCommentPreview = \Illuminate\Support\Str::limit($reviewComment, 100, '...');
                                $reviewStars = (int) (is_array($review) ? ($review['estrellas'] ?? 5) : ($review->estrellas ?? 5));
                                $reviewStars = max(1, min(5, $reviewStars));
                                $reviewPhoto = trim((string) (is_array($review)
                                    ? (($review['foto_data'] ?? '') ?: ($review['foto_url'] ?? ''))
                                    : (($review->foto_data ?? '') ?: ($review->foto_url ?? ''))
                                ));

                                $parts = preg_split('/\s+/', $reviewName) ?: [];
                                $initials = collect($parts)->filter()->take(2)->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('');
                                $initials = $initials !== '' ? $initials : 'NA';
                                $handle = '@' . strtolower(preg_replace('/[^a-z0-9]/i', '', $reviewName));
                            ?>
                            <article
                                class="review-mini-card review-clickable p-4 sm:p-5"
                                data-review-card
                                data-review-name="<?php echo e($reviewName); ?>"
                                data-review-handle="<?php echo e($handle); ?>"
                                data-review-stars="<?php echo e($reviewStars); ?>"
                                data-review-comment="<?php echo e($reviewComment); ?>"
                                data-review-photo="<?php echo e($reviewPhoto); ?>"
                                data-review-initials="<?php echo e($initials); ?>"
                                tabindex="0"
                                role="button"
                                aria-label="Ver detalle de reseña de <?php echo e($reviewName); ?>"
                            >
                                
                                <div class="flex items-center justify-between">
                                    <div class="text-base text-[#374151]"><?php echo e(str_repeat('★', $reviewStars)); ?><span class="text-zinc-200"><?php echo e(str_repeat('★', 5 - $reviewStars)); ?></span></div>
                                    <?php if($canDeleteReviews && !is_array($review) && isset($review->id)): ?>
                                        <form method="POST" action="<?php echo e(route('resenas.destroy', $review)); ?>" onsubmit="return confirm('¿Eliminar esta reseña?');" data-review-delete-form>
                                            <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                            <button type="submit" class="inline-flex h-6 w-6 items-center justify-center rounded-full border border-red-200 bg-white text-red-500 text-xs hover:bg-red-50" aria-label="Eliminar" data-review-delete-btn>×</button>
                                        </form>
                                    <?php endif; ?>
                                </div>

                                
                                <p class="flex-1 text-sm leading-6 text-zinc-700">
                                    "<?php echo e($reviewCommentPreview); ?>"
                                </p>

                                
                                <div class="flex items-center gap-3 pt-2 border-t border-zinc-100">
                                    <div class="review-avatar"><?php echo e($initials); ?></div>
                                    <div>
                                        <p class="text-sm font-extrabold text-zinc-800"><?php echo e($reviewName); ?></p>
                                        <p class="text-xs text-zinc-400"><?php echo e($handle); ?></p>
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>

                    <div class="mt-8 flex justify-center">
                        <a href="<?php echo e(route('resenas.index')); ?>" class="catalog-header-btn">
                            Ver todas las reseñas
                        </a>
                    </div>

                    <form action="<?php echo e(route('resenas.store')); ?>" method="POST" enctype="multipart/form-data" class="mt-8 review-form-shell" data-review-upload-form>
                        <?php echo csrf_field(); ?>
                        <?php if($errors->has('autor_nombre')): ?>
                            <div class="mb-4 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700">
                                <?php echo e($errors->first('autor_nombre')); ?>

                            </div>
                        <?php endif; ?>

                        <?php if(session('status')): ?>
                            <div class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
                                <?php echo e(session('status')); ?>

                            </div>
                        <?php endif; ?>

                        <div class="grid gap-4 lg:grid-cols-3">
                            <div>
                                <label for="resena_nombre" class="mb-2 block text-sm font-semibold text-zinc-600">Nombre</label>
                                <input id="resena_nombre" name="autor_nombre" type="text" value="<?php echo e(old('autor_nombre')); ?>" placeholder="Tu nombre" class="w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-zinc-200">
                            </div>
                            <div>
                                <label for="resena_estrellas" class="mb-2 block text-sm font-semibold text-zinc-600">Estrellas</label>
                                <select id="resena_estrellas" name="estrellas" class="w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-zinc-200">
                                    <option value="5" <?php echo e((string) old('estrellas', '5') === '5' ? 'selected' : ''); ?>>5</option>
                                    <option value="4" <?php echo e((string) old('estrellas') === '4' ? 'selected' : ''); ?>>4</option>
                                    <option value="3" <?php echo e((string) old('estrellas') === '3' ? 'selected' : ''); ?>>3</option>
                                    <option value="2" <?php echo e((string) old('estrellas') === '2' ? 'selected' : ''); ?>>2</option>
                                    <option value="1" <?php echo e((string) old('estrellas') === '1' ? 'selected' : ''); ?>>1</option>
                                </select>
                                <div class="review-stars-input" data-review-stars data-target="resena_estrellas" aria-label="Seleccionar estrellas">
                                    <button type="button" class="review-star-btn" data-review-star="1" aria-label="1 estrella">★</button>
                                    <button type="button" class="review-star-btn" data-review-star="2" aria-label="2 estrellas">★</button>
                                    <button type="button" class="review-star-btn" data-review-star="3" aria-label="3 estrellas">★</button>
                                    <button type="button" class="review-star-btn" data-review-star="4" aria-label="4 estrellas">★</button>
                                    <button type="button" class="review-star-btn" data-review-star="5" aria-label="5 estrellas">★</button>
                                </div>
                            </div>
                            <div>
                                <label for="resena_foto" class="mb-2 block text-sm font-semibold text-zinc-600">Foto opcional</label>
                                <input id="resena_foto" name="foto_archivo" type="file" accept="image/jpeg,image/png,image/webp,image/gif" data-max-mb="25" class="w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm text-zinc-600">
                                <p class="mt-2 text-xs font-semibold text-zinc-500" data-photo-help>Formatos: JPG, PNG, WEBP o GIF. Tamaño máximo: 25 MB.</p>
                                <p class="mt-2 hidden text-xs font-semibold text-red-600" data-photo-client-error></p>
                                <?php $__errorArgs = ['foto_archivo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <p class="mt-2 text-sm font-semibold text-red-600"><?php echo e($message); ?></p>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>

                        <div class="mt-4">
                            <label for="resena_comentario" class="mb-2 block text-sm font-semibold text-zinc-600">Comentario</label>
                            <textarea id="resena_comentario" name="comentario" rows="4" maxlength="1000" placeholder="Cuéntanos tu experiencia" class="w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-zinc-200" data-comment-input data-comment-max="1000"><?php echo e(old('comentario')); ?></textarea>
                            <div class="mt-1 flex justify-end">
                                <span id="resena_comentario_counter" class="text-xs font-semibold text-zinc-400" data-comment-counter>0/1000</span>
                            </div>
                            <?php $__errorArgs = ['comentario'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="mt-2 text-sm font-semibold text-red-600"><?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div class="mt-4 flex justify-end">
                            <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-[#111320] px-8 py-3 text-sm font-semibold text-white hover:bg-black">
                                Enviar reseña
                            </button>
                        </div>
                    </form>

                    <div id="reviewDetailModal" class="fixed inset-0 z-[60] flex items-end justify-center bg-black/50 opacity-0 transition-opacity duration-300 sm:items-center" style="pointer-events:none;visibility:hidden;opacity:0;" aria-modal="true" role="dialog" aria-labelledby="reviewDetailTitle">
                        <div id="reviewDetailPanel" class="w-full translate-y-6 transform overflow-hidden rounded-t-3xl bg-white shadow-2xl transition-all duration-300 sm:max-w-2xl sm:translate-y-0 sm:rounded-3xl" style="pointer-events:none;">
                            <div class="flex items-center justify-between border-b border-zinc-200 px-5 py-4 sm:px-6">
                                <h3 id="reviewDetailTitle" class="text-lg font-bold text-zinc-900">Detalle de reseña</h3>
                                <button type="button" id="reviewDetailClose" class="inline-flex h-8 w-8 items-center justify-center rounded-full text-zinc-500 hover:bg-zinc-100 hover:text-zinc-800" aria-label="Cerrar">×</button>
                            </div>

                            <div class="max-h-[72vh] overflow-y-auto px-5 py-5 sm:px-6">
                                <div class="flex items-start gap-3">
                                    <div id="reviewDetailAvatar" class="review-avatar">NA</div>
                                    <div>
                                        <p id="reviewDetailName" class="text-sm font-extrabold uppercase tracking-[0.16em] text-zinc-700">Cliente</p>
                                        <p id="reviewDetailHandle" class="text-xs italic text-zinc-500">@cliente</p>
                                    </div>
                                </div>

                                <p id="reviewDetailStars" class="mt-3 text-lg text-amber-400">★★★★★</p>
                                <p id="reviewDetailComment" class="mt-3 text-sm leading-6 text-zinc-700"></p>

                                <div id="reviewDetailPhotoWrap" class="mt-4 hidden">
                                    <img id="reviewDetailPhoto" src="" alt="Foto de reseña" class="review-detail-photo" loading="lazy">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        
        <?php ($landingFooter = \App\Services\LandingFooterContent::load()); ?>
        <footer id="footer-info" class="footer-shell text-white">
            <div class="mx-auto max-w-7xl px-4 py-14 sm:py-16">
                <div class="grid grid-cols-2 gap-10 lg:grid-cols-4 lg:items-start">

                    
                    <div class="col-span-2 lg:col-span-1">
                        <div class="flex items-center gap-2">
                            <img src="<?php echo e(asset('images/navbar.png')); ?>" alt="Logo" class="h-8 w-auto object-contain">
                            <span class="footer-brand-name">Óptica<span class="footer-brand-accent">Vision</span></span>
                        </div>
                        <p class="footer-tagline">
                            Más de 15 años llevando salud visual, moda y tecnología óptica a toda Colombia. Tu mirada, nuestra pasión.
                        </p>
                        <div class="mt-5 flex items-center gap-3">
                            <?php $__empty_1 = true; $__currentLoopData = $landingFooter['social_links']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $social): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <a href="<?php echo e($social['href']); ?>"
                                   class="social-pill <?php echo e($social['color']); ?>"
                                   onclick="return openSocialLink(event, <?php echo \Illuminate\Support\Js::from($social['platform'] ?? '')->toHtml() ?>, <?php echo \Illuminate\Support\Js::from($social['href'] ?? '#')->toHtml() ?>)"
                                   aria-label="<?php echo e($social['platform'] ?? 'Red social'); ?>"><?php echo e($social['icon']); ?></a>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <a href="#" class="social-pill" style="background:transparent;border:1.5px solid rgba(255,255,255,0.35);" onclick="return openSocialLink(event,'Instagram','#')" aria-label="Instagram">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="0.5" fill="currentColor"/></svg>
                                </a>
                                <a href="#" class="social-pill" style="background:transparent;border:1.5px solid rgba(255,255,255,0.35);" onclick="return openSocialLink(event,'Facebook','#')" aria-label="Facebook">
                                    <svg viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
                                </a>
                                <a href="#" class="social-pill" style="background:transparent;border:1.5px solid rgba(255,255,255,0.35);" onclick="return openSocialLink(event,'TikTok','#')" aria-label="TikTok">
                                    <svg viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5"><path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-2.88 2.5 2.89 2.89 0 0 1-2.89-2.89 2.89 2.89 0 0 1 2.89-2.89c.28 0 .54.04.79.1V9.01a6.33 6.33 0 0 0-.79-.05 6.34 6.34 0 0 0-6.34 6.34 6.34 6.34 0 0 0 6.34 6.34 6.34 6.34 0 0 0 6.33-6.34V8.89a8.15 8.15 0 0 0 4.77 1.52V7.02a4.85 4.85 0 0 1-1-.33z"/></svg>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>

                    
                    <div class="col-span-1">
                        <p class="footer-col-title">TIENDA</p>
                        <a href="<?php echo e(url('/gafas-hombre')); ?>" class="footer-link">Hombre</a>
                        <a href="<?php echo e(url('/gafas-mujeres')); ?>" class="footer-link">Mujer</a>
                        <a href="<?php echo e(url('/gafas?categories[]=ninos')); ?>" class="footer-link">Infantil</a>
                        <a href="<?php echo e(url('/gafas?categories[]=gafas_polarizadas')); ?>" class="footer-link">Deportivas</a>
                        <a href="<?php echo e(url('/gafas')); ?>" class="footer-link">Ver todo</a>
                    </div>

                    
                    <div class="col-span-1">
                        <p class="footer-col-title">AYUDA</p>
                        <?php $__empty_1 = true; $__currentLoopData = $landingFooter['faq']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $faqItem): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <button type="button" onclick="openFaqModal(<?php echo e($loop->index); ?>)"
                                    class="footer-link text-left cursor-pointer bg-transparent border-0 p-0 w-full">
                                <?php echo e($faqItem['question']); ?>

                            </button>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <a href="#" class="footer-link">Cómo hacer un pedido</a>
                            <a href="#" class="footer-link">Garantías</a>
                            <a href="#" class="footer-link">Política de envíos</a>
                            <a href="#" class="footer-link">Devoluciones</a>
                            <a href="#" class="footer-link">FAQ</a>
                        <?php endif; ?>
                    </div>

                    
                    <div class="col-span-1">
                        <p class="footer-col-title">EMPRESA</p>
                        <?php $__empty_1 = true; $__currentLoopData = $landingFooter['about_links']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $link): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php ($pdfUrl = trim((string) ($landingFooter['about_pdf_urls'][$loop->index] ?? ''))); ?>
                            <?php ($rawHref = $pdfUrl !== '' ? $pdfUrl : trim((string) ($link['href'] ?? '#'))); ?>
                            <?php ($isExternal = preg_match('/^https?:\/\//i', $rawHref) === 1); ?>
                            <?php ($isPdf = preg_match('/\.pdf($|\?)/i', $rawHref) === 1); ?>
                            <?php ($resolvedHref = ($isExternal && $isPdf) ? ('https://docs.google.com/gview?embedded=1&url=' . urlencode($rawHref)) : $rawHref); ?>
                            <a href="<?php echo e($resolvedHref); ?>" class="footer-link" <?php if($isExternal): ?> target="_blank" rel="noopener noreferrer" <?php endif; ?>>
                                <?php echo e($link['text']); ?>

                            </a>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <a href="#" class="footer-link">Nosotros</a>
                            <a href="#" class="footer-link">Trabaja con nosotros</a>
                            <a href="#" class="footer-link">Sostenibilidad</a>
                        <?php endif; ?>

                        <?php ($contactEmail = trim((string) ($landingFooter['contact_email'] ?? ''))); ?>
                        <?php ($contactPhone = trim((string) ($landingFooter['contact_phone'] ?? ''))); ?>
                        <?php if($contactEmail !== '' || $contactPhone !== ''): ?>
                            <p class="footer-col-title mt-6">CONTACTO</p>
                            <?php if($contactEmail !== ''): ?>
                                <a href="mailto:<?php echo e($contactEmail); ?>" class="footer-link"><?php echo e($contactEmail); ?></a>
                            <?php endif; ?>
                            <?php if($contactPhone !== ''): ?>
                                <a href="tel:<?php echo e(preg_replace('/[^0-9+]/', '', $contactPhone)); ?>" class="footer-link"><?php echo e($contactPhone); ?></a>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>

                </div>

                <div class="mt-12 flex flex-col gap-4 border-t pt-6 sm:flex-row sm:items-center sm:justify-between" style="border-color:rgba(255,255,255,0.08);">
                    <p class="text-xs" style="color:rgba(255,255,255,0.22);">© <?php echo e(date('Y')); ?> ÓpticaVision. Todos los derechos reservados.</p>
                    <div class="flex flex-wrap gap-5">
                        <a href="#" class="text-[11px] font-semibold tracking-[0.1em] underline underline-offset-4 transition-colors" style="color:rgba(255,255,255,0.38);" onmouseover="this.style.color='rgba(255,255,255,0.8)'" onmouseout="this.style.color='rgba(255,255,255,0.38)'">TÉRMINOS Y CONDICIONES</a>
                        <a href="#" class="text-[11px] font-semibold tracking-[0.1em] underline underline-offset-4 transition-colors" style="color:rgba(255,255,255,0.38);" onmouseover="this.style.color='rgba(255,255,255,0.8)'" onmouseout="this.style.color='rgba(255,255,255,0.38)'">POLÍTICA DE PRIVACIDAD</a>
                    </div>
                </div>
            </div>
        </footer>

        
        
        <div id="faqModal" class="fixed inset-0 z-50 flex items-end justify-center bg-black/55 opacity-0 transition-opacity duration-300 sm:items-center" style="pointer-events: none; opacity: 0; visibility: hidden;" aria-modal="true" role="dialog">
            <div id="faqModalPanel" class="faq-modal-panel w-full translate-y-8 transform overflow-hidden rounded-t-3xl shadow-2xl transition-all duration-300 sm:max-w-2xl sm:translate-y-0 sm:rounded-3xl" style="pointer-events: none;">
                <div class="faq-modal-header sticky top-0 z-10 px-6 py-4 sm:px-8">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-bold text-zinc-900">Preguntas Frecuentes</h2>
                        <button onclick="closeFaqModal()" class="flex h-8 w-8 items-center justify-center rounded-full text-zinc-500 transition hover:bg-zinc-100 hover:text-zinc-900 text-xl leading-none">&times;</button>
                    </div>
                </div>

                <div class="max-h-[65vh] overflow-y-auto divide-y divide-zinc-100 px-6 sm:px-8">
                    <?php $__empty_1 = true; $__currentLoopData = $landingFooter['faq']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $faqItem): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php ($hasAnswer = !empty(trim($faqItem['answer'] ?? ''))); ?>
                        <div class="faq-accordion">
                            <button
                                type="button"
                                class="faq-trigger flex w-full items-center justify-between gap-4 py-4 text-left"
                                onclick="toggleFaq(this)"
                                data-has-answer="<?php echo e($hasAnswer ? '1' : '0'); ?>"
                            >
                                <span class="text-base font-semibold text-zinc-900 leading-snug"><?php echo e($faqItem['question']); ?></span>
                                <?php if($hasAnswer): ?>
                                    <svg class="faq-chevron h-5 w-5 shrink-0 text-zinc-400 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                <?php endif; ?>
                            </button>
                            <?php if($hasAnswer): ?>
                                <div class="faq-body max-h-0 overflow-hidden transition-all duration-300 ease-in-out">
                                    <div class="faq-body-content pb-5 text-[0.95rem] leading-relaxed text-zinc-600"><?php echo nl2br(e($faqItem['answer'])); ?></div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <p class="py-10 text-center text-zinc-500">No hay preguntas frecuentes disponibles.</p>
                    <?php endif; ?>
                </div>

                <div class="border-t border-zinc-100 bg-zinc-50 px-6 py-4 sm:px-8">
                    <button onclick="closeFaqModal()" class="w-full rounded-xl bg-zinc-900 px-4 py-3 text-sm font-semibold text-white transition-all hover:bg-zinc-700">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>

        <script>
            function openSocialLink(event, platform, webUrl) {
                if (event) {
                    event.preventDefault();
                }

                const href = String(webUrl || '').trim();
                if (!href || href === '#') {
                    return false;
                }

                const mobile = /Android|iPhone|iPad|iPod/i.test(navigator.userAgent || '');
                if (!mobile) {
                    window.location.href = href;
                    return false;
                }

                let appUrl = '';
                const platformName = String(platform || '').toLowerCase();

                if (platformName.includes('facebook')) {
                    appUrl = `fb://facewebmodal/f?href=${encodeURIComponent(href)}`;
                } else if (platformName.includes('instagram')) {
                    const igMatch = href.match(/instagram\.com\/([^/?#]+)/i);
                    appUrl = igMatch ? `instagram://user?username=${encodeURIComponent(igMatch[1])}` : 'instagram://app';
                } else if (platformName.includes('tiktok')) {
                    const ttMatch = href.match(/tiktok\.com\/@([^/?#]+)/i);
                    appUrl = ttMatch ? `snssdk1233://user/profile/${encodeURIComponent(ttMatch[1])}` : 'snssdk1233://';
                }

                if (!appUrl) {
                    window.location.href = href;
                    return false;
                }

                let hidden = false;
                const onVisibilityChange = () => {
                    if (document.hidden) {
                        hidden = true;
                    }
                };

                document.addEventListener('visibilitychange', onVisibilityChange, { once: true });

                const fallbackTimer = window.setTimeout(() => {
                    if (!hidden) {
                        window.location.href = href;
                    }
                }, 1200);

                try {
                    window.location.href = appUrl;
                } catch (e) {
                    window.clearTimeout(fallbackTimer);
                    window.location.href = href;
                }

                return false;
            }

            const faqModal = document.getElementById('faqModal');
            const faqModalPanel = document.getElementById('faqModalPanel');

            function openFaqModal(index) {
                faqModal.style.visibility = 'visible';
                faqModal.style.pointerEvents = 'auto';
                faqModal.style.opacity = '1';
                faqModalPanel.style.pointerEvents = 'auto';
                faqModalPanel.style.transform = 'translateY(0)';
                // Auto-abrir la pregunta que se clickeó
                const triggers = faqModal.querySelectorAll('.faq-trigger');
                if (triggers[index] && triggers[index].dataset.hasAnswer === '1') {
                    setTimeout(() => openFaqItem(triggers[index]), 220);
                }
            }

            function closeFaqModal() {
                faqModal.style.opacity = '0';
                faqModalPanel.style.pointerEvents = 'none';
                setTimeout(() => {
                    faqModal.style.pointerEvents = 'none';
                    faqModal.style.visibility = 'hidden';
                }, 300);
                faqModal.querySelectorAll('.faq-trigger.open').forEach(t => closeFaqItem(t));
            }

            function openFaqItem(trigger) {
                const body = trigger.nextElementSibling;
                if (!body || !body.classList.contains('faq-body')) return;
                trigger.classList.add('open');
                trigger.querySelector('.faq-chevron')?.classList.add('rotate-180', 'text-blue-600');
                trigger.querySelector('span')?.classList.add('text-blue-600');
                body.style.maxHeight = body.scrollHeight + 64 + 'px';
            }

            function closeFaqItem(trigger) {
                const body = trigger.nextElementSibling;
                if (!body || !body.classList.contains('faq-body')) return;
                trigger.classList.remove('open');
                trigger.querySelector('.faq-chevron')?.classList.remove('rotate-180', 'text-blue-600');
                trigger.querySelector('span')?.classList.remove('text-blue-600');
                body.style.maxHeight = '0';
            }

            function toggleFaq(trigger) {
                if (trigger.dataset.hasAnswer !== '1') return;
                if (trigger.classList.contains('open')) {
                    closeFaqItem(trigger);
                } else {
                    faqModal.querySelectorAll('.faq-trigger.open').forEach(t => closeFaqItem(t));
                    openFaqItem(trigger);
                }
            }

            faqModal.addEventListener('click', (e) => {
                if (e.target === faqModal) closeFaqModal();
            });

            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && faqModal.style.opacity === '1') closeFaqModal();
            });

            // Estado inicial seguro: si algo externo alteró clases, igual no debe bloquear clics.
            faqModal.style.pointerEvents = 'none';
            faqModal.style.opacity = '0';
            faqModal.style.visibility = 'hidden';
            faqModalPanel.style.pointerEvents = 'none';
        </script>
    </div>

    <script>
        (() => {
            const overlay = document.getElementById('landingGlobalLoading');
            if (!overlay) return;

            const state = { visible: false };

            const showLoading = () => {
                if (state.visible) return;
                state.visible = true;
                overlay.classList.add('is-visible');
                overlay.setAttribute('aria-hidden', 'false');
                document.documentElement.style.overflow = 'hidden';
                document.body.style.overflow = 'hidden';
            };

            const hideLoading = () => {
                state.visible = false;
                overlay.classList.remove('is-visible');
                overlay.setAttribute('aria-hidden', 'true');
                document.documentElement.style.overflow = '';
                document.body.style.overflow = '';
            };

            window.showLandingGlobalLoading = showLoading;
            window.hideLandingGlobalLoading = hideLoading;

            showLoading();

            window.addEventListener('load', () => {
                window.setTimeout(hideLoading, 120);
            });

            window.addEventListener('pageshow', hideLoading);

            document.addEventListener('click', (event) => {
                if (state.visible) {
                    event.preventDefault();
                    event.stopPropagation();
                    return;
                }

                const anchor = event.target instanceof Element
                    ? event.target.closest('a[href]')
                    : null;

                if (!anchor) return;
                if (anchor.hasAttribute('data-no-global-loader')) return;

                const href = anchor.getAttribute('href') || '';
                if (!href || href.startsWith('#') || href.startsWith('javascript:')) return;

                // Evita mostrar loader para esquemas que no navegan dentro de la web.
                const safeHref = href.trim().toLowerCase();
                if (
                    safeHref.startsWith('mailto:') ||
                    safeHref.startsWith('tel:') ||
                    safeHref.startsWith('sms:') ||
                    safeHref.startsWith('whatsapp:') ||
                    safeHref.startsWith('intent:')
                ) {
                    return;
                }

                const target = (anchor.getAttribute('target') || '').toLowerCase();
                if (target === '_blank' || anchor.hasAttribute('download')) return;

                showLoading();
            }, true);
        })();
    </script>

    <script>
        (() => {
            const reviewStarGroups = document.querySelectorAll('[data-review-stars]');
            reviewStarGroups.forEach((group) => {
                const targetId = group.getAttribute('data-target');
                const select = targetId ? document.getElementById(targetId) : null;
                if (!(select instanceof HTMLSelectElement)) return;

                const stars = Array.from(group.querySelectorAll('[data-review-star]'));
                const paint = (value) => {
                    const n = Number(value) || 1;
                    stars.forEach((star, idx) => {
                        star.classList.toggle('is-active', idx < n);
                    });
                };

                paint(select.value);

                stars.forEach((star) => {
                    star.addEventListener('click', () => {
                        const value = String(star.getAttribute('data-review-star') || '5');
                        select.value = value;
                        paint(value);
                    });
                });

                select.addEventListener('change', () => paint(select.value));
            });

            const commentInput = document.querySelector('[data-comment-input]');
            const commentCounter = document.querySelector('[data-comment-counter]');
            if (commentInput instanceof HTMLTextAreaElement && commentCounter instanceof HTMLElement) {
                const max = Number(commentInput.getAttribute('data-comment-max') || '1000') || 1000;

                const paintCount = () => {
                    const current = commentInput.value.length;
                    commentCounter.textContent = `${current}/${max}`;
                    commentCounter.classList.toggle('text-red-600', current >= max);
                    commentCounter.classList.toggle('text-zinc-400', current < max);
                };

                paintCount();
                commentInput.addEventListener('input', paintCount);
            }

            const reviewUploadForm = document.querySelector('[data-review-upload-form]');
            const photoInput = document.getElementById('resena_foto');
            const photoError = document.querySelector('[data-photo-client-error]');
            const photoHelp = document.querySelector('[data-photo-help]');

            if (reviewUploadForm instanceof HTMLFormElement && photoInput instanceof HTMLInputElement) {
                const maxMb = Number(photoInput.getAttribute('data-max-mb') || '25') || 25;
                const maxBytes = maxMb * 1024 * 1024;
                const blockedUrlRegex = /(?:https?:\/\/|www\.|(?:[a-z0-9-]+\.)+[a-z]{2,})(?:\/[\w\-.~:\/?#[\]@!$&'()*+,;=%]*)?/i;
                const blockedSqlRegex = /(?:\bor\s+1\s*=\s*1\b|\bunion\s+select\b|\bselect\b.+\bfrom\b|\binsert\s+into\b|\bupdate\b.+\bset\b|\bdelete\s+from\b|\bdrop\s+table\b|\balter\s+table\b|\btruncate\s+table\b|--|\/\*|\*\/|;\s*(?:select|insert|update|delete|drop|alter|truncate)\b)/i;
                const allowedFileTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

                const nameInput = document.getElementById('resena_nombre');
                const commentInputField = document.getElementById('resena_comentario');

                const formatBytes = (bytes) => {
                    if (!Number.isFinite(bytes) || bytes <= 0) return '0 MB';
                    return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
                };

                const paintPhotoError = (message) => {
                    if (!(photoError instanceof HTMLElement)) return;
                    if (!message) {
                        photoError.textContent = '';
                        photoError.classList.add('hidden');
                        return;
                    }
                    photoError.textContent = message;
                    photoError.classList.remove('hidden');
                };

                const validateSelectedFile = () => {
                    const file = photoInput.files && photoInput.files.length ? photoInput.files[0] : null;
                    if (!file) {
                        if (photoHelp instanceof HTMLElement) {
                            photoHelp.textContent = `Formatos: JPG, PNG, WEBP o GIF. Tamaño máximo: ${maxMb} MB.`;
                        }
                        paintPhotoError('');
                        return true;
                    }

                    if (photoHelp instanceof HTMLElement) {
                        photoHelp.textContent = `Archivo seleccionado: ${file.name} (${formatBytes(file.size)})`;
                    }

                    if (file.size > maxBytes) {
                        paintPhotoError(`La imagen pesa ${formatBytes(file.size)} y supera el máximo de ${maxMb} MB. Comprime la foto y vuelve a intentarlo.`);
                        return false;
                    }

                    const fileType = String(file.type || '').toLowerCase();
                    if (!allowedFileTypes.includes(fileType)) {
                        paintPhotoError('Solo se permiten imágenes JPG, PNG, WEBP o GIF.');
                        return false;
                    }

                    paintPhotoError('');
                    return true;
                };

                const hasBlockedText = (value) => {
                    const normalized = String(value || '').trim();
                    if (!normalized) return false;
                    return blockedUrlRegex.test(normalized) || blockedSqlRegex.test(normalized);
                };

                photoInput.addEventListener('change', validateSelectedFile);
                reviewUploadForm.addEventListener('submit', (event) => {
                    const hasInvalidName = nameInput instanceof HTMLInputElement && hasBlockedText(nameInput.value);
                    const hasInvalidComment = commentInputField instanceof HTMLTextAreaElement && hasBlockedText(commentInputField.value);

                    if (hasInvalidName || hasInvalidComment) {
                        event.preventDefault();
                        alert('No se permiten URLs, enlaces ni patrones sospechosos en nombre o comentario.');
                        return;
                    }

                    if (!validateSelectedFile()) {
                        event.preventDefault();
                    }
                });
            }

            const reviewsTrack = document.querySelector('[data-reviews-track].is-animated');
            if (reviewsTrack instanceof HTMLElement) {
                const reviewsMarquee = document.querySelector('[data-reviews-marquee]');
                const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

                let resumeTimer = null;
                let rafId = null;
                let isDragging = false;
                let startPointerX = 0;
                let startX = 0;
                let currentX = 0;
                let loopDistance = 0;
                let pxPerSecond = 28;
                let lastTick = null;
                let suppressClick = false;
                const reviewsGroups = Array.from(reviewsTrack.querySelectorAll('[data-reviews-group]'));

                const syncReviewsLoop = () => {
                    const firstGroup = reviewsGroups[0];
                    if (!(firstGroup instanceof HTMLElement)) return;

                    const gapValue = window.getComputedStyle(reviewsTrack).columnGap || window.getComputedStyle(reviewsTrack).gap || '0';
                    const gap = Number.parseFloat(gapValue) || 0;
                    loopDistance = firstGroup.getBoundingClientRect().width + gap;
                    const duration = Math.max(24, loopDistance / 28);
                    pxPerSecond = loopDistance > 0 ? (loopDistance / duration) : 28;

                    reviewsTrack.style.setProperty('--reviews-loop-distance', `${loopDistance}px`);
                    reviewsTrack.style.setProperty('--reviews-duration', `${duration}s`);
                    normalizeCurrentX();
                    paintCurrentX();
                };

                const normalizeCurrentX = () => {
                    if (!Number.isFinite(loopDistance) || loopDistance <= 0) return;

                    while (currentX <= -loopDistance) {
                        currentX += loopDistance;
                    }

                    while (currentX > 0) {
                        currentX -= loopDistance;
                    }
                };

                const paintCurrentX = () => {
                    reviewsTrack.style.transform = `translate3d(${currentX}px, 0, 0)`;
                };

                const pause = () => {
                    if (resumeTimer) clearTimeout(resumeTimer);
                    reviewsTrack.dataset.autoPaused = '1';
                };

                const resume = () => {
                    if (resumeTimer) clearTimeout(resumeTimer);
                    reviewsTrack.dataset.autoPaused = '0';
                };

                const resumeDelayed = () => {
                    if (resumeTimer) clearTimeout(resumeTimer);
                    resumeTimer = window.setTimeout(resume, 900);
                };

                const nudgeBy = (deltaX) => {
                    pause();
                    currentX += deltaX;
                    normalizeCurrentX();
                    paintCurrentX();
                    resumeDelayed();
                };

                const tick = (ts) => {
                    if (lastTick === null) {
                        lastTick = ts;
                    }

                    const elapsed = Math.max(0, (ts - lastTick) / 1000);
                    lastTick = ts;

                    const autoPaused = reviewsTrack.dataset.autoPaused === '1';
                    if (!reducedMotion && !autoPaused && !isDragging && loopDistance > 0) {
                        currentX -= pxPerSecond * elapsed;
                        normalizeCurrentX();
                        paintCurrentX();
                    }

                    rafId = window.requestAnimationFrame(tick);
                };

                const getClientX = (event) => {
                    if (event instanceof PointerEvent) return event.clientX;
                    return null;
                };

                const isInteractiveTarget = (target) => {
                    if (!(target instanceof Element)) return false;
                    return Boolean(target.closest('button, a, input, textarea, select, label, form, [data-review-delete-btn], [data-review-delete-form]'));
                };

                const onPointerDown = (event) => {
                    if (!(event instanceof PointerEvent)) return;
                    if (event.button !== 0) return;
                    if (isInteractiveTarget(event.target)) return;

                    isDragging = true;
                    suppressClick = false;
                    startPointerX = getClientX(event) ?? 0;
                    startX = currentX;
                    pause();
                    reviewsTrack.classList.add('is-dragging');

                    try {
                        reviewsTrack.setPointerCapture(event.pointerId);
                    } catch {
                        // no-op
                    }
                };

                const onPointerMove = (event) => {
                    if (!isDragging || !(event instanceof PointerEvent)) return;

                    const clientX = getClientX(event);
                    if (clientX === null) return;

                    const deltaX = clientX - startPointerX;
                    if (Math.abs(deltaX) > 6) {
                        suppressClick = true;
                    }

                    currentX = startX + deltaX;
                    normalizeCurrentX();
                    paintCurrentX();
                };

                const endDrag = () => {
                    if (!isDragging) return;
                    isDragging = false;
                    reviewsTrack.classList.remove('is-dragging');
                    resumeDelayed();
                };

                reviewsTrack.style.animation = 'none';
                reviewsTrack.style.touchAction = 'pan-y';
                reviewsTrack.dataset.autoPaused = '0';

                reviewsTrack.addEventListener('mouseenter', pause);
                reviewsTrack.addEventListener('mouseleave', resumeDelayed);
                reviewsTrack.addEventListener('pointerdown', onPointerDown);
                reviewsTrack.addEventListener('pointermove', onPointerMove);
                reviewsTrack.addEventListener('pointerup', endDrag);
                reviewsTrack.addEventListener('pointercancel', endDrag);
                reviewsTrack.addEventListener('lostpointercapture', endDrag);
                reviewsTrack.addEventListener('click', (event) => {
                    if (!suppressClick) return;
                    suppressClick = false;
                    event.preventDefault();
                    event.stopPropagation();
                }, true);

                if (reviewsMarquee instanceof HTMLElement) {
                    reviewsMarquee.addEventListener('click', (event) => {
                        if (!(event instanceof MouseEvent)) return;
                        if (!(event.target instanceof Element)) return;
                        if (isInteractiveTarget(event.target)) return;
                        if (event.target.closest('[data-review-card]')) {
                            return;
                        }

                        const rect = reviewsMarquee.getBoundingClientRect();
                        const midpoint = rect.left + (rect.width / 2);
                        const isLeftSide = event.clientX < midpoint;
                        nudgeBy(isLeftSide ? 220 : -220);
                    });
                }

                window.addEventListener('resize', syncReviewsLoop, { passive: true });

                /* Reanudar si el tab vuelve a ser visible */
                document.addEventListener('visibilitychange', () => {
                    if (!document.hidden) resume();
                });

                syncReviewsLoop();
                paintCurrentX();
                rafId = window.requestAnimationFrame(tick);
            }

            const reviewDetailModal = document.getElementById('reviewDetailModal');
            const reviewDetailPanel = document.getElementById('reviewDetailPanel');
            const reviewDetailClose = document.getElementById('reviewDetailClose');
            const reviewCards = Array.from(document.querySelectorAll('[data-review-card]'));
            const reviewDeleteButtons = Array.from(document.querySelectorAll('[data-review-delete-btn]'));
            const detailName = document.getElementById('reviewDetailName');
            const detailHandle = document.getElementById('reviewDetailHandle');
            const detailStars = document.getElementById('reviewDetailStars');
            const detailComment = document.getElementById('reviewDetailComment');
            const detailPhoto = document.getElementById('reviewDetailPhoto');
            const detailPhotoWrap = document.getElementById('reviewDetailPhotoWrap');
            const detailAvatar = document.getElementById('reviewDetailAvatar');

            const paintStars = (value) => {
                const n = Math.max(1, Math.min(5, Number(value) || 5));
                if (!detailStars) return;
                detailStars.innerHTML = `${'★'.repeat(n)}<span class="text-zinc-300">${'★'.repeat(5 - n)}</span>`;
            };

            const openReviewModal = (card) => {
                if (!reviewDetailModal || !reviewDetailPanel || !(card instanceof HTMLElement)) return;

                const name = card.getAttribute('data-review-name') || 'Cliente';
                const handle = card.getAttribute('data-review-handle') || '@cliente';
                const comment = card.getAttribute('data-review-comment') || '';
                const photo = card.getAttribute('data-review-photo') || '';
                const stars = card.getAttribute('data-review-stars') || '5';
                const initials = card.getAttribute('data-review-initials') || 'NA';

                if (detailName) detailName.textContent = name;
                if (detailHandle) detailHandle.textContent = handle;
                if (detailComment) detailComment.textContent = comment;
                if (detailAvatar) detailAvatar.textContent = initials;
                paintStars(stars);

                if (detailPhoto && detailPhotoWrap && photo.trim() !== '') {
                    detailPhoto.src = photo;
                    detailPhotoWrap.classList.remove('hidden');
                } else if (detailPhoto && detailPhotoWrap) {
                    detailPhoto.src = '';
                    detailPhotoWrap.classList.add('hidden');
                }

                reviewDetailModal.style.visibility = 'visible';
                reviewDetailModal.style.pointerEvents = 'auto';
                reviewDetailModal.style.opacity = '1';
                reviewDetailPanel.style.pointerEvents = 'auto';
                reviewDetailPanel.style.transform = 'translateY(0)';
            };

            const closeReviewModal = () => {
                if (!reviewDetailModal || !reviewDetailPanel) return;
                reviewDetailModal.style.opacity = '0';
                reviewDetailPanel.style.pointerEvents = 'none';
                window.setTimeout(() => {
                    reviewDetailModal.style.pointerEvents = 'none';
                    reviewDetailModal.style.visibility = 'hidden';
                }, 260);
            };

            reviewCards.forEach((card) => {
                card.addEventListener('click', () => openReviewModal(card));
                card.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        openReviewModal(card);
                    }
                });
            });

            reviewDeleteButtons.forEach((button) => {
                button.addEventListener('click', (event) => {
                    event.stopPropagation();
                });
            });

            if (reviewDetailClose) {
                reviewDetailClose.addEventListener('click', closeReviewModal);
            }

            if (reviewDetailModal) {
                reviewDetailModal.addEventListener('click', (e) => {
                    if (e.target === reviewDetailModal) closeReviewModal();
                });
                reviewDetailModal.style.pointerEvents = 'none';
                reviewDetailModal.style.visibility = 'hidden';
                reviewDetailModal.style.opacity = '0';
            }

            const scrollToSection = (id) => {
                const el = document.getElementById(id);
                if (!el) return;
                el.scrollIntoView({ behavior: 'smooth', block: 'start' });
            };

            document.getElementById('landingSearchBtn')?.addEventListener('click', (event) => {
                event.preventDefault();
                scrollToSection('catalogo');
            });

            document.getElementById('landingShippingBtn')?.addEventListener('click', (event) => {
                event.preventDefault();
                scrollToSection('ubicacion');
            });

            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    closeReviewModal();
                }
            });
        })();
    </script>
</body>
</html>
<?php /**PATH C:\Users\norla\Desktop\Proyectos con Ronaldo\copia_optica\resources\views/landing2.blade.php ENDPATH**/ ?>