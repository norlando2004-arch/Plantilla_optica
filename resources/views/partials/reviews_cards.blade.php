@php($latestReviews = $latestReviews ?? collect())
@php($verifiedBuyerUserIds = is_array($verifiedBuyerUserIds ?? null) ? $verifiedBuyerUserIds : [])
@php($reviewModerator = auth()->user())
@php($canDeleteReviews = $reviewModerator && in_array((int) ($reviewModerator->rol_id ?? 0), [2, 3, 4], true))

@if($latestReviews->isEmpty())
    <div class="rounded-3xl border border-zinc-200 bg-zinc-50 p-8">
        <p class="text-sm font-semibold">Todavía no hay reseñas</p>
        <p class="mt-1 text-sm text-zinc-600">Sé el primero en dejar una reseña.</p>
    </div>
@else
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3" data-animate-stagger="80">
        @foreach($latestReviews as $r)
            @php($rating = (int)($r->estrellas ?? 0))
            @php($photo = trim((string)($r->foto_data ?: $r->foto_url ?: '')))
            @php($reviewerName = trim((string)($r->autor_nombre ?? ($r->usuario->nombre ?? ''))))
            @php($reviewerName = $reviewerName !== '' ? $reviewerName : 'Cliente')
            <article data-animate="up" class="rounded-3xl border border-zinc-200 bg-white p-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold">Reseña</p>
                        <p class="mt-1 text-sm text-zinc-600">{{ $r->comentario }}</p>
                    </div>
                    <div class="flex items-start gap-2">
                        <div class="flex gap-1" aria-label="{{ $rating }} estrellas">
                            @for($i = 1; $i <= 5; $i++)
                                <svg viewBox="0 0 24 24" class="h-4 w-4 {{ $i <= $rating ? 'text-amber-500' : 'text-zinc-200' }}" fill="currentColor" aria-hidden="true">
                                    <path d="M12 17.3l-6.18 3.4 1.18-6.86L1.99 8.9l6.9-1L12 1.7l3.11 6.2 6.9 1-5.01 4.94 1.18 6.86L12 17.3z"/>
                                </svg>
                            @endfor
                        </div>
                        @if($canDeleteReviews)
                            <form method="POST" action="{{ route('resenas.destroy', $r) }}" onsubmit="return confirm('¿Eliminar esta reseña? Esta acción no se puede deshacer.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex h-7 w-7 items-center justify-center rounded-full border border-red-200 bg-white text-red-600 transition hover:bg-red-50" aria-label="Eliminar reseña de {{ $reviewerName }}">×</button>
                            </form>
                        @endif
                    </div>
                </div>

                @if($photo !== '')
                    <div class="mt-5">
                        <img class="h-36 w-full rounded-2xl border border-zinc-200 bg-white object-cover" src="{{ $photo }}" alt="Foto de reseña" loading="lazy">
                    </div>
                @endif

                <div class="mt-5 flex items-center justify-between gap-3">
                    <p class="text-sm font-semibold">{{ $reviewerName }}</p>
                    @if(in_array((int)$r->usuario_id, $verifiedBuyerUserIds, true))
                        <p class="text-xs font-semibold text-zinc-500">Compra verificada</p>
                    @endif
                </div>
            </article>
        @endforeach
    </div>
@endif
