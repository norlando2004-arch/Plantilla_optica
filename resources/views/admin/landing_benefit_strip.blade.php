@extends('admin.dashboard_layout')

@section('title', 'Franja de beneficios')

@section('content')
    <div class="flex items-end justify-between">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">Franja de beneficios</h1>
            <p class="mt-1 text-sm text-zinc-600">Agrega, edita o quita los textos que pasan en la franja del landing.</p>
        </div>
        <a href="{{ route('configuracion.index') }}" class="rounded-xl px-3 py-2 text-sm font-semibold text-zinc-700 ring-1 ring-inset ring-zinc-200 hover:bg-zinc-50">Volver</a>
    </div>

    <form class="mt-6" method="POST" action="{{ route('configuracion.landing-benefit-strip.update') }}">
        @csrf

        @php($items = is_array($landingBenefitStrip['items'] ?? null) ? $landingBenefitStrip['items'] : [])
        <section class="rounded-3xl border border-zinc-200 bg-white p-6">
            <label class="text-sm font-semibold">Textos (uno por línea)</label>
            <textarea
                name="items_text"
                rows="10"
                class="mt-2 w-full rounded-2xl border border-zinc-200 px-4 py-3 text-sm"
                placeholder="Exámen visual computarizado&#10;Garantía de lentes de 3 meses&#10;Kit de limpieza&#10;Estuche metálico"
            >{{ old('items_text', implode("\n", $items)) }}</textarea>
            <p class="mt-2 text-xs text-zinc-500">Máximo 12 líneas. Cada línea se mostrará con viñeta en la franja.</p>
        </section>

        <div class="mt-6 flex justify-end gap-2">
            <button type="submit" class="rounded-2xl bg-zinc-900 px-5 py-3 text-sm font-semibold text-white hover:bg-zinc-800">Guardar cambios</button>
        </div>
    </form>
@endsection
