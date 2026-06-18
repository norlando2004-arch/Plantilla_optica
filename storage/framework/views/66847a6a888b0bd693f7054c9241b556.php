<?php
    $storeSearchLandingUrl = \Illuminate\Support\Facades\Route::has('landing') ? route('landing') : url('/');

    $storeSearchTargets = [
        [
            'title' => 'Gafas formuladas',
            'subtitle' => 'Sección',
            'url' => \Illuminate\Support\Facades\Route::has('gafas.index') ? route('gafas.index') : url('/gafas'),
            'keywords' => 'gafas formuladas formula progresivos bifocal monofocal lentes',
            'featured' => true,
        ],
        [
            'title' => 'Gafas para mujeres',
            'subtitle' => 'Sección',
            'url' => \Illuminate\Support\Facades\Route::has('gafas-mujeres.index') ? route('gafas-mujeres.index') : url('/gafas-mujeres'),
            'keywords' => 'mujeres mujer dama femeninas',
            'featured' => true,
        ],
        [
            'title' => 'Gafas para hombres',
            'subtitle' => 'Sección',
            'url' => \Illuminate\Support\Facades\Route::has('gafas-hombre.index') ? route('gafas-hombre.index') : url('/gafas-hombre'),
            'keywords' => 'hombres hombre caballero masculinas',
            'featured' => true,
        ],
        [
            'title' => 'Gafas para niñas',
            'subtitle' => 'Sección',
            'url' => \Illuminate\Support\Facades\Route::has('gafas-ninas.index') ? route('gafas-ninas.index') : url('/gafas-niñas'),
            'keywords' => 'niñas ninas niña infantil kids',
            'featured' => true,
        ],
        [
            'title' => 'Gafas para niños',
            'subtitle' => 'Sección',
            'url' => \Illuminate\Support\Facades\Route::has('gafas-ninos.index') ? route('gafas-ninos.index') : url('/gafas-niños'),
            'keywords' => 'niños ninos niño infantil kids',
            'featured' => true,
        ],
        [
            'title' => 'Gafas de sol',
            'subtitle' => 'Sección',
            'url' => \Illuminate\Support\Facades\Route::has('gafas-polarizadas.index') ? route('gafas-polarizadas.index') : url('/gafas-polarizadas'),
            'keywords' => 'gafas de sol polarizadas polarizados',
            'featured' => true,
        ],
        [
            'title' => 'Gafas deportivas',
            'subtitle' => 'Sección',
            'url' => \Illuminate\Support\Facades\Route::has('gafas-deportivas.index') ? route('gafas-deportivas.index') : url('/gafas-deportivas'),
            'keywords' => 'deportivas deporte rendimiento actividad',
            'featured' => true,
        ],
        [
            'title' => 'Categorías',
            'subtitle' => 'Sección',
            'url' => $storeSearchLandingUrl . '#categorias',
            'keywords' => 'categorias categorías secciones landing',
            'featured' => true,
        ],
        [
            'title' => 'Preguntas frecuentes',
            'subtitle' => 'Sección',
            'url' => $storeSearchLandingUrl . '#faq',
            'keywords' => 'preguntas frecuentes faq ayuda soporte',
            'featured' => true,
        ],
        [
            'title' => 'Contacto',
            'subtitle' => 'Sección',
            'url' => $storeSearchLandingUrl . '#contacto',
            'keywords' => 'contacto ofertas ubicacion whatsapp',
            'featured' => true,
        ],
    ];

    try {
        $storeSearchProducts = \App\Models\Producto::query()
            ->where('esta_activo', true)
            ->whereIn('tipo', ['gafas', 'gafas_ninas', 'gafas_ninos', 'gafas_polarizadas'])
            ->orderBy('nombre')
            ->get(['nombre', 'slug', 'tipo', 'genero_objetivo']);
    } catch (\Throwable $e) {
        $storeSearchProducts = collect();
    }

    foreach ($storeSearchProducts as $product) {
        $storeSearchTargets[] = [
            'title' => trim((string) $product->nombre),
            'subtitle' => 'Gafa',
            'url' => route('gafas.show', ['producto' => $product->slug]),
            'keywords' => trim(implode(' ', array_filter([
                (string) $product->nombre,
                (string) $product->tipo,
                (string) $product->genero_objetivo,
            ]))),
            'featured' => false,
        ];
    }

    $storeSearchTargets = collect($storeSearchTargets)
        ->filter(fn ($item) => !empty($item['title']) && !empty($item['url']))
        ->unique(fn ($item) => mb_strtolower(((string) $item['title']) . '|' . ((string) $item['url'])))
        ->values()
        ->all();
?>

<div id="js-store-search-modal" class="fixed inset-0 z-[120] hidden" aria-hidden="true">
    <div class="absolute inset-0 bg-slate-950/45 backdrop-blur-[2px]" data-close-store-search="1"></div>
    <div class="relative mx-auto flex min-h-dvh max-w-5xl items-start justify-center px-3 py-4 sm:px-4 sm:py-6">
        <div class="w-full overflow-hidden rounded-[28px] border border-[#cfe9ef] bg-gradient-to-b from-[#eef9fc] via-[#f8fdff] to-white p-3 shadow-[0_24px_80px_rgba(50,92,101,0.22)] sm:p-4">
            <div class="rounded-[24px] border border-[#8fd3de] bg-gradient-to-r from-[#147b84] via-[#1d8790] to-[#1c7f89] px-4 py-3 text-white shadow-[inset_0_1px_0_rgba(255,255,255,0.18)] sm:px-5">
                <div class="flex items-start gap-3">
                    <div class="mt-0.5 flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-white/10 text-white">
                        <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <circle cx="11" cy="11" r="7"></circle>
                            <path d="m20 20-3.5-3.5"></path>
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <input id="js-store-search-input" type="search" placeholder="Busca una gafa o sección" class="w-full border-0 bg-transparent text-base font-bold text-white outline-none placeholder:text-white/90 sm:text-[1.15rem]">
                        <p class="mt-0.5 text-xs text-white/85 sm:text-sm">Ejemplo: niñas, niños, hombres, deportivas o el nombre de la gafa</p>
                    </div>
                    <button type="button" class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-white/95 transition hover:bg-white/10" data-close-store-search="1" aria-label="Cerrar búsqueda">
                        <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M18 6 6 18"></path>
                            <path d="m6 6 12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <div class="max-h-[70vh] overflow-y-auto px-1 pt-3 pb-1 sm:pt-4">
                <div id="js-store-search-results" class="grid gap-3 md:grid-cols-2"></div>
                <p id="js-store-search-empty" class="hidden rounded-[20px] border border-dashed border-[#b6dfe7] bg-white/80 px-4 py-6 text-sm text-slate-500">No encontré coincidencias. Prueba con otra palabra.</p>
            </div>
        </div>
    </div>
</div>

<script>
(() => {
    const modal = document.getElementById('js-store-search-modal');
    if (!modal || modal.dataset.ready === '1') return;
    modal.dataset.ready = '1';

    const input = document.getElementById('js-store-search-input');
    const results = document.getElementById('js-store-search-results');
    const empty = document.getElementById('js-store-search-empty');
    const openButtons = Array.from(document.querySelectorAll('[data-open-store-search]'));
    const closeButtons = Array.from(modal.querySelectorAll('[data-close-store-search]'));
    const targets = <?php echo json_encode($storeSearchTargets, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES, 512) ?>;
    const defaultTitles = [
        'Categorías',
        'Contacto',
        'Gafas deportivas',
        'Gafas de sol',
        'Gafas formuladas',
        'Gafas para mujeres',
    ];
    let previousOverflow = '';

    const normalizeText = (value) => (value || '')
        .toString()
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .trim();

    const scoreTarget = (target, query) => {
        if (!query) {
            return target.featured ? 100 : 5;
        }

        const title = normalizeText(target.title || '');
        const keywords = normalizeText(target.keywords || '');
        const haystack = `${title} ${keywords}`.trim();

        let score = 0;
        if (title === query) score += 200;
        if (title.startsWith(query)) score += 120;
        if (haystack.includes(query)) score += 80;

        query.split(/\s+/).filter(Boolean).forEach((token) => {
            if (title.includes(token)) score += 40;
            else if (keywords.includes(token)) score += 20;
        });

        return score;
    };

    const getMatches = (query) => {
        const normalizedQuery = normalizeText(query);

        if (!normalizedQuery) {
            const orderMap = new Map(defaultTitles.map((title, index) => [normalizeText(title), index]));

            return targets
                .filter((target) => orderMap.has(normalizeText(target.title || '')))
                .map((target) => ({
                    ...target,
                    score: 100 - (orderMap.get(normalizeText(target.title || '')) ?? 99),
                    order: orderMap.get(normalizeText(target.title || '')) ?? 99,
                }))
                .sort((a, b) => a.order - b.order)
                .slice(0, 6);
        }

        return targets
            .map((target) => ({ ...target, score: scoreTarget(target, normalizedQuery) }))
            .filter((target) => target.score > 0)
            .sort((a, b) => {
                if (b.score !== a.score) return b.score - a.score;
                if ((b.featured ? 1 : 0) !== (a.featured ? 1 : 0)) return (b.featured ? 1 : 0) - (a.featured ? 1 : 0);
                return String(a.title || '').localeCompare(String(b.title || ''), 'es');
            })
            .slice(0, 8);
    };

    const renderResults = (query = '') => {
        const matches = getMatches(query);
        results.innerHTML = '';

        if (!matches.length) {
            empty.classList.remove('hidden');
            return [];
        }

        empty.classList.add('hidden');

        matches.forEach((item) => {
            const link = document.createElement('a');
            const isHighlighted = normalizeText(item.title || '') === normalizeText('Gafas deportivas');

            link.href = item.url;
            link.className = [
                'group flex items-center justify-between gap-3 rounded-[22px] border px-4 py-4 text-left transition',
                isHighlighted
                    ? 'border-[#8ed8e5] bg-[#fbfeff] shadow-[inset_0_0_0_1px_rgba(142,216,229,0.55),0_10px_24px_rgba(77,145,150,0.12)]'
                    : 'border-[#a9dce6] bg-white/95 shadow-[0_8px_20px_rgba(77,145,150,0.08)] hover:border-[#7ccfdb] hover:bg-[#fbfeff]'
            ].join(' ');

            link.innerHTML = `
                <span class="min-w-0">
                    <span class="block truncate text-[1.05rem] font-semibold leading-tight text-[#2b3138]">${item.title}</span>
                    <span class="mt-1 block text-sm text-[#8b97a3]">${item.subtitle}</span>
                </span>
                <span class="shrink-0 rounded-full bg-[#0d7d87] px-4 py-1.5 text-xs font-bold text-white shadow-[0_6px_14px_rgba(13,125,135,0.28)]">Ir</span>
            `;
            results.appendChild(link);
        });

        return matches;
    };

    const openModal = () => {
        previousOverflow = document.body.style.overflow || '';
        modal.classList.remove('hidden');
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        renderResults('');
        window.setTimeout(() => input?.focus(), 20);
    };

    const closeModal = () => {
        modal.classList.add('hidden');
        modal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = previousOverflow;
    };

    openButtons.forEach((button) => {
        button.addEventListener('click', (event) => {
            event.preventDefault();
            openModal();
        });
    });

    closeButtons.forEach((button) => {
        button.addEventListener('click', (event) => {
            event.preventDefault();
            closeModal();
        });
    });

    modal.addEventListener('click', (event) => {
        if (event.target === modal) {
            closeModal();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
            closeModal();
        }
    });

    input?.addEventListener('input', () => {
        renderResults(input.value || '');
    });

    input?.addEventListener('keydown', (event) => {
        if (event.key === 'Enter') {
            event.preventDefault();
            const matches = renderResults(input.value || '');
            if (matches.length && matches[0].url) {
                window.location.href = matches[0].url;
            }
        }
    });

    window.addEventListener('store-search:open', openModal);
})();
</script>
<?php /**PATH C:\Users\norla\Desktop\Proyectos con Ronaldo\copia_optica\resources\views/partials/store_search_modal.blade.php ENDPATH**/ ?>