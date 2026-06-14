@extends('layouts.dashboard_empty_sidebar')

@section('title', 'Editar gafa polarizada')
@section('heading', 'Gafas polarizadas')

@section('content')
    <div class="flex items-center justify-between gap-3">
        <div>
            <h2 class="text-lg font-semibold text-zinc-900">Editar gafa</h2>
            <p class="mt-1 text-sm text-zinc-500">Actualiza datos, precio e imagen.</p>
        </div>
        <a href="{{ route('admin.gafas-polarizadas.index') }}" class="rounded-xl px-3 py-2 text-sm font-semibold text-zinc-700 hover:bg-zinc-50">Volver</a>
    </div>

    <form class="mt-6" action="{{ route('admin.gafas-polarizadas.update', $producto) }}" method="POST" enctype="multipart/form-data" data-single-submit>
        @csrf
        @method('PUT')
        <div class="rounded-3xl border border-zinc-200 bg-zinc-50 p-6">
            @include('admin.gafas_mujeres._form', ['producto' => $producto, 'showFormulaPermitida' => true])

            <div class="mt-6 flex items-center justify-end gap-2">
                <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-zinc-950 px-5 py-3 text-sm font-semibold text-white hover:bg-zinc-900 disabled:cursor-not-allowed disabled:opacity-70" data-submit-button>
                    Guardar cambios
                </button>
            </div>
        </div>
    </form>
@endsection
