@extends('layouts.dashboard_empty_sidebar')

@section('title', $onlyEmployees ? 'Empleados' : 'Usuarios')
@section('heading', $onlyEmployees ? 'Empleados' : 'Usuarios')

@section('content')
    <div class="rounded-2xl border border-zinc-200 bg-white p-5">
        @if(!$onlyEmployees)
            <div class="mb-5 flex flex-wrap items-center gap-2">
                @if($showAllUsers)
                    <a href="{{ route('admin') }}" class="rounded-xl border border-zinc-300 px-4 py-2.5 text-sm font-semibold text-zinc-700 hover:bg-zinc-50">
                        Ocultar Usuarios
                    </a>
                @else
                    <a href="{{ route('admin', ['usuarios' => 1]) }}" class="rounded-xl bg-zinc-950 px-4 py-2.5 text-sm font-semibold text-white hover:bg-zinc-800">
                        Usuarios
                    </a>
                @endif
            </div>
        @endif

        <form method="GET" action="{{ $onlyEmployees ? route('admin.empleados') : route('admin') }}" class="mb-5 flex flex-col gap-3 md:flex-row md:items-center">
            @if(!$onlyEmployees && $showAllUsers)
                <input type="hidden" name="usuarios" value="1">
            @endif
            <input
                type="text"
                name="q"
                value="{{ $search }}"
                placeholder="Buscar por ID, nombre o correo"
                class="w-full rounded-xl border border-zinc-300 px-4 py-2.5 text-sm text-zinc-900 outline-none focus:border-zinc-900"
            >
            <button type="submit" class="rounded-xl bg-zinc-950 px-4 py-2.5 text-sm font-semibold text-white hover:bg-zinc-800">
                Buscar
            </button>
            @if($search !== '')
                <a href="{{ $onlyEmployees ? route('admin.empleados') : route('admin', ['usuarios' => 1]) }}" class="rounded-xl border border-zinc-300 px-4 py-2.5 text-sm font-semibold text-zinc-700 hover:bg-zinc-50">
                    Limpiar
                </a>
            @endif
        </form>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-zinc-200 text-sm">
                <thead class="bg-zinc-50">
                <tr>
                    <th class="px-3 py-2 text-left font-semibold text-zinc-700">ID</th>
                    <th class="px-3 py-2 text-left font-semibold text-zinc-700">Nombre</th>
                    <th class="px-3 py-2 text-left font-semibold text-zinc-700">Correo</th>
                    <th class="px-3 py-2 text-left font-semibold text-zinc-700">Rol actual</th>
                    <th class="px-3 py-2 text-left font-semibold text-zinc-700">Cambiar rol</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 bg-white">
                @forelse($users as $user)
                    @php
                        $isProgramador = (int) $user->rol_id === 4;
                        $maskProgramadorAsUsuario = !$onlyEmployees && $showAllUsers && $isProgramador;
                        $displayRolId = $maskProgramadorAsUsuario ? 1 : (int) $user->rol_id;
                        $displayRolNombre = $maskProgramadorAsUsuario
                            ? ($roles->firstWhere('id', 1)?->nombre ?? 'Usuario')
                            : ($isProgramador ? 'Programador' : ($user->rol?->nombre ?? ('Rol #' . $user->rol_id)));
                    @endphp
                    <tr>
                        <td class="px-3 py-3 text-zinc-700">{{ $user->id }}</td>
                        <td class="px-3 py-3 font-medium text-zinc-900">{{ $user->nombre }}</td>
                        <td class="px-3 py-3 text-zinc-700">{{ $user->correo }}</td>
                        <td class="px-3 py-3 text-zinc-700">
                            {{ $displayRolNombre }}
                        </td>
                        <td class="px-3 py-3">
                            @if($isProgramador && !$maskProgramadorAsUsuario)
                                <span class="inline-flex items-center rounded-lg border border-zinc-200 bg-zinc-100 px-3 py-2 text-xs font-semibold text-zinc-600">
                                    Bloqueado para cambios
                                </span>
                            @else
                                <form method="POST" action="{{ route('admin.usuarios.update-role', $user) }}" class="flex flex-col gap-2 md:flex-row md:items-center">
                                    @csrf
                                    @method('PATCH')
                                    <select name="rol_id" class="rounded-lg border border-zinc-300 px-3 py-2 text-sm">
                                        @foreach($roles as $rol)
                                            <option value="{{ $rol->id }}" @selected($displayRolId === (int) $rol->id)>
                                                {{ $rol->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="rounded-lg bg-zinc-900 px-3 py-2 text-sm font-semibold text-white hover:bg-zinc-700">
                                        Guardar
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-3 py-6 text-center text-zinc-500">No se encontraron usuarios.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-5">
            {{ $users->onEachSide(1)->links() }}
        </div>
    </div>
@endsection
