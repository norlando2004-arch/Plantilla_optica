@extends('admin.dashboard_layout')

@section('title', 'Nuevo banner secundario')

@section('content')
    <div class="flex items-end justify-between">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">Nuevos banners secundarios</h1>
        </div>
        <a href="{{ route('configuracion.secondary-banners.index') }}" class="rounded-xl px-3 py-2 text-sm font-semibold text-zinc-700 ring-1 ring-inset ring-zinc-200 hover:bg-zinc-50">Volver</a>
    </div>

    <form class="mt-6" method="POST" action="{{ route('configuracion.secondary-banners.store') }}" enctype="multipart/form-data">
        @csrf
        @include('admin.secondary_banners._form')
        <div class="mt-6 flex justify-end gap-2">
            <button type="submit" class="rounded-2xl bg-zinc-900 px-5 py-3 text-sm font-semibold text-white hover:bg-zinc-800">Crear banners</button>
        </div>
    </form>
@endsection
