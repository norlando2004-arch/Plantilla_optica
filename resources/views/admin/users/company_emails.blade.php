@extends('layouts.dashboard_empty_sidebar')

@section('title', 'Correos empresa')
@section('heading', 'Correos empresa')

@section('content')
    @php($emails = is_array($companyNotificationEmails['emails'] ?? null) ? $companyNotificationEmails['emails'] : [])

    <div class="space-y-5">
        <div class="rounded-2xl border border-zinc-200 bg-white p-5">
            <h2 class="text-lg font-semibold text-zinc-900">Correos de notificación de empresa</h2>
            <p class="mt-2 text-sm text-zinc-600">Administra los correos internos que recibirán confirmaciones de pagos aprobados.</p>
        </div>

        <div class="rounded-2xl border border-zinc-200 bg-white p-5">
            <h3 class="text-base font-semibold text-zinc-900">Agregar nuevo correo</h3>
            <form method="POST" action="{{ route('admin.company-emails.add') }}" class="mt-3 flex flex-col gap-3 md:flex-row md:items-center">
                @csrf
                <input
                    type="email"
                    name="new_email"
                    value="{{ old('new_email') }}"
                    placeholder="nuevo-correo@empresa.com"
                    class="w-full rounded-xl border border-zinc-300 px-4 py-2.5 text-sm outline-none focus:border-zinc-900 @error('new_email') border-amber-400 bg-amber-50 @enderror"
                    required
                >
                <button type="submit" class="rounded-xl bg-zinc-950 px-4 py-2.5 text-sm font-semibold text-white hover:bg-zinc-800">
                    Agregar
                </button>
            </form>
            @error('new_email')
                <p class="mt-2 text-xs font-semibold text-amber-700">{{ $message }}</p>
            @enderror
        </div>

        <div class="rounded-2xl border border-zinc-200 bg-white p-5">
            <h3 class="text-base font-semibold text-zinc-900">Correos existentes</h3>
            @if(empty($emails))
                <p class="mt-3 text-sm text-zinc-500">Aún no has agregado correos.</p>
            @else
                <div class="mt-3 space-y-3">
                    @foreach($emails as $email)
                        <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-3">
                            <form method="POST" action="{{ route('admin.company-emails.edit') }}" class="flex flex-col gap-2 md:flex-row md:items-center">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="current_email" value="{{ $email }}">
                                <input type="email" name="updated_email" value="{{ $email }}" class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-zinc-900" required>
                                <button type="submit" class="rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-sm font-semibold text-zinc-700 hover:bg-zinc-100">
                                    Guardar edición
                                </button>
                            </form>

                            <form method="POST" action="{{ route('admin.company-emails.delete') }}" class="mt-2">
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="email" value="{{ $email }}">
                                <button type="submit" class="rounded-xl border border-rose-300 bg-white px-4 py-2 text-sm font-semibold text-rose-700 hover:bg-rose-50" onclick="return confirm('¿Eliminar este correo?');">
                                    Eliminar
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @endif

            @error('updated_email')
                <p class="mt-3 text-xs font-semibold text-amber-700">{{ $message }}</p>
            @enderror
        </div>
    </div>
@endsection
