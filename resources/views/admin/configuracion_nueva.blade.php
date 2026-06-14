@extends('admin.dashboard_layout')

@section('title', 'Configuración nueva')

@section('content')
    <section class="rounded-3xl border border-zinc-200 bg-white p-8 shadow-sm">
        @if(session('status'))
            <div class="mt-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
                {{ session('status') }}
            </div>
        @endif

        <div class="mt-8">
            <a data-loader-link href="{{ url('/') }}" class="inline-flex items-center justify-center rounded-xl bg-zinc-900 px-4 py-2 text-sm font-semibold text-white hover:bg-zinc-800">
                Ver landing
            </a>
        </div>
    </section>
@endsection
