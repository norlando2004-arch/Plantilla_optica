<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Checkout — Carrito — Óptica</title>

    @php($viteHot = public_path('hot'))
    @php($viteManifest = public_path('build/manifest.json'))
    @php($hasViteAssets = file_exists($viteHot) || file_exists($viteManifest))

    @if($hasViteAssets)
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif

    @if(!$hasViteAssets || app()->isLocal())
        <script src="https://cdn.tailwindcss.com"></script>
    @endif
</head>
<body class="bg-zinc-50 text-zinc-900 antialiased font-sans">
@include('partials.store-navbar')

<main class="mx-auto max-w-4xl px-4 py-6 sm:py-10">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <p class="text-sm font-semibold">Óptica</p>
            <h1 class="mt-0.5 text-base font-semibold">Tus datos</h1>
        </div>
        <a href="{{ route('landing') }}" class="w-full rounded-2xl px-3 py-2 text-center text-sm font-semibold text-zinc-700 hover:bg-zinc-100 sm:w-auto">Volver</a>
    </div>
    @if(session('status'))
        <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
            {{ session('status') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-900">
            <p class="font-semibold">Revisa los campos:</p>
            <ul class="mt-2 list-disc pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @php($items = $carrito->items ?? collect())

    <div class="grid gap-4 sm:gap-6 lg:grid-cols-3">
        <section class="rounded-3xl border border-zinc-200 bg-white p-4 sm:p-5 lg:col-span-1">
            <p class="text-sm font-semibold text-zinc-900">Resumen</p>
            <p class="mt-2 text-sm text-zinc-600">Artículos:</p>

            <div class="mt-3 grid gap-2">
                @foreach($items as $item)
                    @php($linea = ((float) $item->precio_unitario) * ((int) $item->cantidad))
                    <div class="flex items-start justify-between gap-3 rounded-2xl border border-zinc-200 bg-zinc-50 px-3 py-2">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-zinc-900">{{ $item->nombre_producto }}</p>
                            <p class="mt-0.5 text-xs text-zinc-600">x{{ (int) $item->cantidad }}</p>
                        </div>
                        <p class="shrink-0 text-sm font-semibold text-zinc-900">{{ number_format((float)$linea, 0, ',', '.') }} {{ $item->moneda }}</p>
                    </div>
                @endforeach
            </div>

            <div class="mt-4 rounded-2xl border border-zinc-200 bg-zinc-50 p-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Total</p>
                <p class="mt-1 text-xl font-semibold text-zinc-900">{{ number_format((float)$carrito->total, 0, ',', '.') }} {{ $carrito->moneda }}</p>
            </div>
        </section>

        <section class="rounded-3xl border border-zinc-200 bg-white p-4 sm:p-5 lg:col-span-2">
            <p class="text-sm font-semibold text-zinc-900">Información personal</p>
            <p class="mt-1 text-sm text-zinc-500">Selecciona una guardada o agrega una nueva.</p>

            <form id="pagoForm" class="mt-5" action="{{ route('pagos.start', ['producto' => $carrito->items->first()->producto->slug ?? '#']) }}" method="POST">
                @csrf

                <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Tus perfiles</p>

                    <div class="mt-3 grid gap-3">
                        @forelse($perfiles as $perfil)
                            <div class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-zinc-200 bg-white p-4">
                                <label class="flex min-w-0 items-start gap-3">
                                    <input type="radio" name="perfil_cliente_id" value="{{ $perfil->id }}" class="mt-1 h-4 w-4 rounded border-zinc-300" {{ (string) old('perfil_cliente_id') === (string) $perfil->id ? 'checked' : ((!old('perfil_cliente_id') && $loop->first && !old('crear_nuevo_perfil')) ? 'checked' : '') }}>
                                    <span class="min-w-0">
                                        <span class="block truncate text-sm font-semibold text-zinc-900">
                                            {{ $perfil->tipo_documento ? ($perfil->tipo_documento . ' ') : '' }}{{ $perfil->numero_documento ?: ('Perfil #' . $perfil->id) }}
                                        </span>
                                        <span class="mt-1 block text-xs text-zinc-600">{{ $perfil->ciudad ?: 'Ciudad no definida' }} • {{ $perfil->telefono ?: 'Teléfono no definido' }}</span>
                                    </span>
                                </label>

                                <button type="submit" form="delete-profile-{{ $perfil->id }}" onclick="return confirm('¿Eliminar esta información personal?')" class="w-full rounded-xl border border-zinc-200 bg-white px-3 py-2 text-sm font-semibold text-rose-700 hover:bg-zinc-50 sm:w-auto">Eliminar</button>
                            </div>
                        @empty
                            <div class="rounded-2xl border border-zinc-200 bg-white p-4 text-sm text-zinc-600">
                                No tienes información personal guardada todavía.
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="mt-5 rounded-2xl border border-zinc-200 bg-white p-4">
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-sm font-semibold text-zinc-900">Agregar nueva información</p>
                        <label class="inline-flex items-center gap-2 text-sm text-zinc-700">
                            <input type="checkbox" name="crear_nuevo_perfil" value="1" class="h-4 w-4 rounded border-zinc-300" {{ old('crear_nuevo_perfil') ? 'checked' : '' }}>
                            Usar esta nueva
                        </label>
                    </div>

                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="text-xs font-semibold text-zinc-700">Tipo documento</label>
                            <select name="tipo_documento" class="mt-2 w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm outline-none focus:border-zinc-400">
                                @php($tipoDoc = old('tipo_documento'))
                                <option value="">Selecciona</option>
                                <option value="CC" {{ $tipoDoc === 'CC' ? 'selected' : '' }}>CC</option>
                                <option value="CE" {{ $tipoDoc === 'CE' ? 'selected' : '' }}>CE</option>
                                <option value="NIT" {{ $tipoDoc === 'NIT' ? 'selected' : '' }}>NIT</option>
                                <option value="PAS" {{ $tipoDoc === 'PAS' ? 'selected' : '' }}>Pasaporte</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-zinc-700">Número documento</label>
                            <input name="numero_documento" value="{{ old('numero_documento') }}" class="mt-2 w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm outline-none focus:border-zinc-400" />
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-zinc-700">Teléfono</label>
                            <input name="telefono" value="{{ old('telefono') }}" class="mt-2 w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm outline-none focus:border-zinc-400" />
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-zinc-700">Ciudad</label>
                            <input name="ciudad" value="{{ old('ciudad') }}" class="mt-2 w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm outline-none focus:border-zinc-400" />
                        </div>
                        <div class="sm:col-span-2">
                            <label class="text-xs font-semibold text-zinc-700">Dirección</label>
                            <textarea name="direccion" rows="2" class="mt-2 w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm outline-none focus:border-zinc-400">{{ old('direccion') }}</textarea>
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-zinc-700">Fecha nacimiento (opcional)</label>
                            <input type="date" name="fecha_nacimiento" value="{{ old('fecha_nacimiento') }}" class="mt-2 w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm outline-none focus:border-zinc-400" />
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-zinc-700">Género (opcional)</label>
                            <select name="genero" class="mt-2 w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm outline-none focus:border-zinc-400">
                                @php($gen = old('genero'))
                                <option value="">Selecciona</option>
                                <option value="female" {{ $gen === 'female' ? 'selected' : '' }}>Mujer</option>
                                <option value="male" {{ $gen === 'male' ? 'selected' : '' }}>Hombre</option>
                                <option value="other" {{ $gen === 'other' ? 'selected' : '' }}>Otro</option>
                            </select>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="text-xs font-semibold text-zinc-700">Notas (opcional)</label>
                            <textarea name="notas" rows="2" class="mt-2 w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm outline-none focus:border-zinc-400">{{ old('notas') }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex justify-stretch sm:justify-end">
                    <button type="submit" class="inline-flex w-full items-center justify-center rounded-2xl bg-zinc-950 px-5 py-3 text-sm font-semibold text-white hover:bg-zinc-900 sm:w-auto">
                        Continuar a pagar
                    </button>
                </div>
            </form>

            @foreach($perfiles as $perfil)
                <form id="delete-profile-{{ $perfil->id }}" action="{{ route('perfiles-cliente.destroy', $perfil) }}" method="POST" class="hidden">
                    @csrf
                    @method('DELETE')
                </form>
            @endforeach
        </section>
    </div>
</main>
</body>
</html>
