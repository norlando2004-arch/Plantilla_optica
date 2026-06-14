<!DOCTYPE html>
<html lang="es" class="overflow-x-hidden">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reseñas de clientes | Optica</title>
    <meta name="description" content="Lee lo que opinan nuestros clientes sobre Optica.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @php
        $viteHot      = public_path('hot');
        $viteManifest = public_path('build/manifest.json');
        $hasViteAssets = file_exists($viteHot) || file_exists($viteManifest);
    @endphp

    @if($hasViteAssets)
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif

    @if(!$hasViteAssets || app()->isLocal())
        <script src="https://cdn.tailwindcss.com"></script>
    @endif

    <style>
        :root {
            --na-primary:      #4b9196;
            --na-primary-dark: #3b7e83;
            --na-text:         #20242a;
        }

        html, body {
            margin: 0; padding: 0;
            width: 100%; max-width: 100%;
            overflow-x: hidden;
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Baloo 2', ui-sans-serif, system-ui, -apple-system, sans-serif;
            background: #ffffff;
            color: var(--na-text);
        }

        /* ---- Hero ---- */
        .resenas-hero {
            background: linear-gradient(180deg, #eef7f7 0%, #ffffff 100%);
            border-bottom: 1px solid rgba(75, 145, 150, 0.10);
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            font-size: 0.78rem;
            font-weight: 700;
            color: var(--na-primary-dark);
            text-decoration: none;
            letter-spacing: 0.02em;
            transition: opacity 160ms;
        }
        .back-link:hover { opacity: 0.6; }

        .rating-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            background: var(--na-primary);
            color: #fff;
            border-radius: 9999px;
            padding: 0.55rem 1.25rem;
        }

        .filter-shell {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .filter-chip {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 2.2rem;
            padding: 0.45rem 0.85rem;
            border-radius: 9999px;
            border: 1px solid rgba(75, 145, 150, 0.25);
            background: rgba(75, 145, 150, 0.06);
            font-size: 0.78rem;
            font-weight: 700;
            color: #2f5e62;
            transition: all 160ms ease;
        }

        .filter-chip:hover {
            border-color: rgba(75, 145, 150, 0.5);
            background: rgba(75, 145, 150, 0.12);
        }

        .filter-chip.is-active {
            background: var(--na-primary);
            border-color: var(--na-primary);
            color: #ffffff;
        }

        .filter-chip[disabled] {
            opacity: 0.7;
            cursor: wait;
        }

        /* ---- Review cards (ghost) ---- */
        .review-card {
            border-radius: 1.35rem;
            padding: 1.25rem 1.1rem;
            transition: background 180ms ease;
            border-top: 1.5px solid rgba(0, 0, 0, 0.055);
        }

        .review-card:hover {
            background: rgba(75, 145, 150, 0.045);
        }

        .review-avatar {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 2.4rem;
            height: 2.4rem;
            border-radius: 9999px;
            background: linear-gradient(135deg, #4b9196 0%, #1f2937 100%);
            color: #fff;
            font-size: 0.7rem;
            font-weight: 800;
            flex-shrink: 0;
        }

        .verified-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.2rem;
            font-size: 0.63rem;
            font-weight: 800;
            letter-spacing: 0.09em;
            text-transform: uppercase;
            color: #059669;
        }

        .review-photo {
            width: 100%;
            max-height: 13rem;
            object-fit: cover;
            border-radius: 1rem;
            margin-top: 0.85rem;
            border: 1px solid rgba(0, 0, 0, 0.07);
        }

        .comment-toggle-btn {
            margin-top: 0.45rem;
            border: 0;
            background: transparent;
            padding: 0;
            font-size: 0.78rem;
            font-weight: 700;
            color: var(--na-primary-dark);
            cursor: pointer;
        }

        .comment-toggle-btn:hover {
            opacity: 0.75;
        }

        /* Grid fluida: evita huecos laterales del masonry */
        .reviews-grid {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 0.75rem;
        }

        @media (max-width: 639px) {
            .reviews-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 0.6rem;
            }

            .resenas-hero {
                padding-top: 1.5rem;
                padding-bottom: 1.8rem;
            }

            .rating-badge {
                gap: 0.45rem;
                padding: 0.5rem 0.85rem;
                font-size: 0.82rem;
            }

            .filter-shell {
                justify-content: flex-start;
                flex-wrap: nowrap;
                overflow-x: auto;
                overflow-y: hidden;
                -webkit-overflow-scrolling: touch;
                scrollbar-width: thin;
                padding-bottom: 0.2rem;
            }

            .filter-chip {
                flex: 0 0 auto;
                white-space: nowrap;
            }

            .review-card {
                padding: 0.8rem 0.7rem;
            }

            .review-avatar {
                width: 1.9rem;
                height: 1.9rem;
                font-size: 0.58rem;
            }

            .review-photo {
                max-height: 7.5rem;
            }

            .review-card p {
                font-size: 0.72rem;
                line-height: 1.35;
            }

            .verified-badge {
                font-size: 0.54rem;
            }
        }

        @media (min-width: 640px) {
            .reviews-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (min-width: 1024px) {
            .reviews-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }

        /* Pagination */
        nav[role="navigation"] { display: flex; justify-content: center; }
    </style>
</head>
<body>

    @include('partials.store-navbar', ['showStoreBanner' => false])

    @php
        $avgRounded     = max(0, min(5, (int) round((float) ($reviewsAvg ?? 0))));
        $avgFormatted   = number_format((float) ($reviewsAvg ?? 0), 1, '.', '');
        $countFormatted = number_format((int) ($reviewsCount ?? 0), 0, ',', '.');
        $starsFilter    = in_array((int) ($selectedStars ?? 0), [1, 2, 3, 4, 5], true) ? (int) $selectedStars : null;
        $perPageCurrent = in_array((int) ($perPage ?? 10), [5, 10], true) ? (int) $perPage : 10;
        $reviewModerator = auth()->user();
        $canDeleteReviews = $reviewModerator && in_array((int) ($reviewModerator->rol_id ?? 0), [2, 3, 4], true);
    @endphp

    {{-- ── Hero ── --}}
    <section class="resenas-hero px-4 pb-10 pt-8 text-center sm:pb-14 sm:pt-10">
        <a href="{{ route('landing') }}" class="back-link">
            <svg viewBox="0 0 24 24" class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M19 12H5M12 5l-7 7 7 7"/>
            </svg>
            Volver al inicio
        </a>

        <h1 class="mt-4 text-3xl font-extrabold tracking-tight text-zinc-900 sm:text-5xl">
            Lo que dicen nuestros clientes
        </h1>
        <p class="mt-2 text-sm text-zinc-500">Opiniones reales, sin filtros</p>

        <div class="mt-5 flex justify-center">
            <div class="rating-badge">
                <span class="text-base leading-none text-amber-300">
                    {{ str_repeat('★', $avgRounded) }}<span style="opacity:0.38">{{ str_repeat('★', 5 - $avgRounded) }}</span>
                </span>
                <span class="text-base font-extrabold">{{ $avgFormatted }}/5</span>
                <span class="text-xs font-semibold" style="opacity:0.7">({{ $countFormatted }} reseñas)</span>
            </div>
        </div>
    </section>

    {{-- ── Reviews ── --}}
    <main class="mx-auto w-full max-w-7xl px-4 py-10 sm:py-12">

        <section class="mb-7">
            <form method="GET" action="{{ route('resenas.index') }}" class="filter-shell" data-filter-form>
                <input type="hidden" name="per_page" value="{{ $perPageCurrent }}" data-per-page-input>
                <button type="submit" name="stars" value="" class="filter-chip {{ $starsFilter === null ? 'is-active' : '' }}" data-filter-submit>
                    Todas
                </button>
                @for($star = 5; $star >= 1; $star--)
                    <button type="submit" name="stars" value="{{ $star }}" class="filter-chip {{ $starsFilter === $star ? 'is-active' : '' }}" data-filter-submit>
                        {{ str_repeat('★', $star) }}
                    </button>
                @endfor
            </form>

            @if($starsFilter !== null)
                <p class="mt-3 text-center text-xs font-semibold uppercase tracking-[0.08em] text-zinc-500">
                    Mostrando reseñas de {{ $starsFilter }} estrella{{ $starsFilter > 1 ? 's' : '' }}
                </p>
            @endif
        </section>

        @if(($allReviews ?? collect())->count() === 0)
            <div class="py-24 text-center">
                <p class="text-5xl" style="opacity:0.45">&#128172;</p>
                <h2 class="mt-5 text-xl font-extrabold text-zinc-800">Todavía no hay reseñas</h2>
                <p class="mt-2 text-sm text-zinc-500">Sé el primero en compartir tu experiencia.</p>
            </div>
        @else
            <div class="reviews-grid">
                @foreach($allReviews as $review)
                    @php
                        $rName     = trim((string) ($review->autor_nombre ?? ($review->usuario->nombre ?? 'Cliente')));
                        $rName     = $rName !== '' ? $rName : 'Cliente';
                        $rParts    = preg_split('/\s+/', $rName) ?: [];
                        $rInitials = collect($rParts)->filter()->take(2)->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('');
                        $rInitials = $rInitials !== '' ? $rInitials : 'NA';
                        $rStars    = max(1, min(5, (int) ($review->estrellas ?? 5)));
                        $rPhoto    = trim((string) ($review->foto_data ?: $review->foto_url ?: ''));
                        $rComment  = trim((string) ($review->comentario ?? ''));
                        $rCommentShort = \Illuminate\Support\Str::limit($rComment, 100, '...');
                        $rCommentHasOverflow = mb_strlen($rComment) > 100;
                        $rVerified = in_array((int) ($review->usuario_id ?? 0), $verifiedBuyerUserIds ?? [], true);
                        $rDate     = optional($review->created_at)->format('d/m/Y');
                    @endphp

                    <article class="review-card">

                        {{-- Cabecera --}}
                        <div class="flex items-start gap-2.5">
                            <div class="review-avatar">{{ $rInitials }}</div>

                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-extrabold text-zinc-800">{{ $rName }}</p>
                                <div class="mt-0.5 flex flex-wrap items-center gap-1.5">
                                    <span class="text-sm leading-none text-amber-400">
                                        {{ str_repeat('★', $rStars) }}<span class="text-zinc-200">{{ str_repeat('★', 5 - $rStars) }}</span>
                                    </span>
                                    @if($rVerified)
                                        <span class="verified-badge">
                                            <svg viewBox="0 0 24 24" class="h-2.5 w-2.5" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                                            Verificado
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="mt-0.5 shrink-0 text-right sm:mt-0">
                                <p class="whitespace-nowrap text-[11px] font-semibold text-zinc-400">{{ $rDate }}</p>
                                @if($canDeleteReviews)
                                    <form method="POST" action="{{ route('resenas.destroy', $review) }}" class="mt-1" onsubmit="return confirm('¿Eliminar esta reseña? Esta acción no se puede deshacer.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex h-6 w-6 items-center justify-center rounded-full border border-red-200 bg-white text-red-600 transition hover:bg-red-50" aria-label="Eliminar reseña de {{ $rName }}">×</button>
                                    </form>
                                @endif
                            </div>
                        </div>

                        {{-- Comentario --}}
                        @if($rComment !== '')
                            <p class="mt-3 text-sm leading-[1.65] text-zinc-600" data-review-comment>
                                <span data-comment-short>&ldquo;{{ $rCommentShort }}&rdquo;</span>
                                <span class="hidden" data-comment-full>&ldquo;{{ $rComment }}&rdquo;</span>
                            </p>
                            @if($rCommentHasOverflow)
                                <button type="button" class="comment-toggle-btn" data-comment-toggle aria-expanded="false">
                                    Ver más
                                </button>
                            @endif
                        @endif

                        {{-- Foto --}}
                        @if($rPhoto !== '')
                            <img src="{{ $rPhoto }}" alt="Foto de reseña de {{ $rName }}" loading="lazy" class="review-photo">
                        @endif

                    </article>
                @endforeach
            </div>

            {{-- Paginación --}}
            <div class="mt-10">
                {{ $allReviews->onEachSide(1)->links() }}
            </div>
        @endif

    </main>

    <script>
        (() => {
            const desiredPerPage = window.matchMedia('(max-width: 639px)').matches ? '5' : '10';
            const currentUrl = new URL(window.location.href);
            const currentPerPage = currentUrl.searchParams.get('per_page');

            if (currentPerPage !== desiredPerPage) {
                currentUrl.searchParams.set('per_page', desiredPerPage);
                window.location.replace(currentUrl.toString());
                return;
            }

            const form = document.querySelector('[data-filter-form]');
            if (!form) return;

            const perPageInput = form.querySelector('[data-per-page-input]');
            if (perPageInput instanceof HTMLInputElement) {
                perPageInput.value = desiredPerPage;
            }

            const buttons = Array.from(form.querySelectorAll('[data-filter-submit]'));
            let alreadySubmitted = false;
            let clickedButton = null;

            buttons.forEach((btn) => {
                btn.addEventListener('click', () => {
                    clickedButton = btn;
                });
            });

            form.addEventListener('submit', (event) => {
                if (alreadySubmitted) {
                    event.preventDefault();
                    return;
                }

                alreadySubmitted = true;

                const loadingBtn = clickedButton instanceof HTMLButtonElement ? clickedButton : null;
                if (loadingBtn) {
                    loadingBtn.setAttribute('data-original-label', loadingBtn.textContent || '');
                    loadingBtn.setAttribute('aria-busy', 'true');
                    loadingBtn.textContent = 'Cargando...';
                }

                buttons.forEach((b) => {
                    b.disabled = loadingBtn ? b !== loadingBtn : true;
                });

                // Si por alguna razon la navegación no ocurre, restauramos estado.
                window.setTimeout(() => {
                    if (document.visibilityState === 'visible') {
                        alreadySubmitted = false;
                        buttons.forEach((b) => {
                            b.disabled = false;
                        });

                        if (loadingBtn) {
                            const original = loadingBtn.getAttribute('data-original-label') || loadingBtn.textContent;
                            loadingBtn.removeAttribute('aria-busy');
                            loadingBtn.textContent = original;
                        }
                    }
                }, 5000);
            });

            const commentToggles = Array.from(document.querySelectorAll('[data-comment-toggle]'));
            commentToggles.forEach((toggle) => {
                toggle.addEventListener('click', () => {
                    const card = toggle.closest('.review-card');
                    if (!(card instanceof HTMLElement)) return;

                    const shortText = card.querySelector('[data-comment-short]');
                    const fullText = card.querySelector('[data-comment-full]');
                    if (!(shortText instanceof HTMLElement) || !(fullText instanceof HTMLElement)) return;

                    const isExpanded = toggle.getAttribute('aria-expanded') === 'true';
                    shortText.classList.toggle('hidden', !isExpanded);
                    fullText.classList.toggle('hidden', isExpanded);
                    toggle.setAttribute('aria-expanded', isExpanded ? 'false' : 'true');
                    toggle.textContent = isExpanded ? 'Ver más' : 'Ver menos';
                });
            });
        })();
    </script>

</body>
</html>
