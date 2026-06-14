@extends('admin.dashboard_layout')

@section('title', 'Whatsapp')

@section('content')
    @php($landingWhatsapp = $landingWhatsapp ?? [])

    <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">Whatsapp</h1>
            <p class="mt-1 text-sm text-zinc-600">Configura el número al que apunta el botón flotante del landing.</p>
        </div>
        <a href="{{ url('/') }}" class="rounded-xl px-3 py-2 text-sm font-semibold text-zinc-700 ring-1 ring-inset ring-zinc-200 hover:bg-zinc-50">Ver landing</a>
    </div>

    <div class="mt-6 max-w-2xl rounded-3xl border border-zinc-200 bg-white p-6">
        <form method="POST" action="{{ route('configuracion.whatsapp.update') }}">
            @csrf

            <div>
                <label class="text-sm font-semibold">Número de WhatsApp</label>
                <input
                    name="phone_number"
                    value="{{ old('phone_number', $landingWhatsapp['phone_number'] ?? '') }}"
                    placeholder="Ej: +57 300 123 4567"
                    class="mt-2 w-full rounded-2xl border border-zinc-200 px-4 py-3 text-sm"
                >
                <p class="mt-2 text-xs text-zinc-500">
                    Tip: incluye el código de país. Se aceptan espacios, +, guiones y paréntesis.
                </p>
            </div>

            <div class="mt-5">
                <label class="text-sm font-semibold">Mensaje emergente (opcional)</label>
                <input
                    name="bubble_message"
                    value="{{ old('bubble_message', $landingWhatsapp['bubble_message'] ?? '') }}"
                    placeholder="Ej: Hola 👋 ¿en qué te ayudamos?"
                    class="mt-2 w-full rounded-2xl border border-zinc-200 px-4 py-3 text-sm"
                >
                <p class="mt-2 text-xs text-zinc-500">Si lo dejas vacío, no se mostrará ningún mensaje en el landing.</p>
            </div>

            <div class="mt-5">
                <label class="text-sm font-semibold">URL ícono WhatsApp (opcional)</label>
                <input
                    name="icon_url"
                    value="{{ old('icon_url', $landingWhatsapp['icon_url'] ?? '') }}"
                    placeholder="/images/whatsapp.png"
                    class="mt-2 w-full rounded-2xl border border-zinc-200 px-4 py-3 text-sm"
                >
                <p class="mt-2 text-xs text-zinc-500">Tip: puedes usar una ruta local, por ejemplo <span class="font-semibold">/images/whatsapp.png</span>. Si lo dejas vacío, se usará el ícono por defecto.</p>
            </div>

            <div class="mt-6 flex justify-end">
                <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-zinc-900 px-5 py-3 text-sm font-semibold text-white hover:bg-zinc-800">Guardar</button>
            </div>
        </form>
    </div>
@endsection
