@extends('admin.dashboard_layout')

@section('title', 'Editar hero banner')

@section('content')
    <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">Editar banner</h1>
        </div>
        <a href="{{ route('configuracion.hero-banners.index') }}" class="rounded-xl px-3 py-2 text-sm font-semibold text-zinc-700 ring-1 ring-inset ring-zinc-200 hover:bg-zinc-50">Volver</a>
    </div>

    <form class="mt-6" method="POST" action="{{ route('configuracion.hero-banners.update', $banner) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('admin.hero_banners._form', ['banner' => $banner])
        <div class="mt-6 flex justify-end gap-2">
            <button type="submit" class="rounded-2xl bg-zinc-900 px-5 py-3 text-sm font-semibold text-white hover:bg-zinc-800">Guardar</button>
        </div>
    </form>
@endsection
