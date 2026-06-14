@extends('layouts.dashboard_empty_sidebar')

@section('title', 'Importar gafas deportivas')
@section('heading', 'Importar gafas deportivas')

@section('content')
    <div class="mb-6 rounded-3xl border border-zinc-200 bg-white p-5">
        <div class="mb-6">
            <h3 class="text-base font-semibold text-zinc-900">Cargar gafas deportivas desde Excel</h3>
            <p class="mt-1 text-sm text-zinc-500">Sube un archivo Excel con las gafas deportivas que deseas agregar a tu catálogo.</p>
        </div>

        <form action="{{ route('admin.gafas-descanso.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="mb-6 rounded-2xl border border-zinc-200 bg-zinc-50 p-4">
                <h4 class="mb-3 text-sm font-semibold text-zinc-700">Formato del archivo Excel</h4>
                <p class="mb-3 text-xs text-zinc-600">Tu archivo Excel debe contener las siguientes columnas:</p>
                
                <p class="mb-2 text-xs font-semibold text-zinc-700">Requeridas:</p>
                <ul class="mb-4 space-y-1 text-xs text-zinc-600">
                    <li><strong>NOMBRE</strong> - Nombre de la gafa (requerido, no se toma desde Referencia)</li>
                    <li><strong>PRECIO</strong> - Precio en pesos (requerido). Ej: 50000 o 50.000</li>
                </ul>

                <p class="mb-2 text-xs font-semibold text-zinc-700">Opcionales (todos estos nombres funcionan):</p>
                <ul class="mb-4 space-y-1 text-xs text-zinc-600">
                    <li><strong>COLOR</strong> - Color de la gafa</li>
                    <li><strong>Material</strong> - Material de la montura</li>
                    <li><strong>INVENTARIO</strong> - Cantidad disponible</li>
                    <li><strong>Ancho total de la montura</strong> - En centímetros (ej: 13.9)</li>
                    <li><strong>Ancho del lente</strong> - En centímetros</li>
                    <li><strong>Alto del lente</strong> - En centímetros</li>
                    <li><strong>Puente</strong> - En centímetros</li>
                    <li><strong>Largo de patillas</strong> - En centímetros</li>
                    <li><strong>Recomendado para:</strong> - Para qué tipo de persona</li>
                    <li><strong>Incluye</strong> - Qué incluye el producto</li>
                    <li><strong>Clip-on compatible</strong> - 0 o 1 (booleano)</li>
                    <li><strong>Progresivos</strong> - 0 o 1 (booleano)</li>
                    <li><strong>Tipo de fórmula</strong> - Tipo de lente</li>
                </ul>

                <p class="text-xs text-blue-600 bg-blue-50 p-2 rounded">
                    Tip: El importer es flexible y reconoce variaciones en los nombres de columnas. 
                    Puedes usar tus propios nombres (ej: "Inventario" en lugar de "existencias") y funcionará.
                </p>
            </div>

            @if($errors->any())
                <div class="mb-4 rounded-2xl border border-rose-200 bg-rose-50 p-4">
                    <p class="text-sm font-semibold text-rose-700">Errores encontrados:</p>
                    <ul class="mt-2 space-y-1 text-sm text-rose-600">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="mb-6">
                <label for="excelFile" class="block text-sm font-semibold text-zinc-800 mb-2">
                    Selecciona tu archivo Excel
                </label>
                <input 
                    id="excelFile" 
                    name="excel_file" 
                    type="file" 
                    accept=".xlsx,.xls,.csv" 
                    required
                    class="block w-full rounded-2xl border border-zinc-200 px-4 py-3 text-sm file:mr-4 file:rounded-xl file:border-0 file:bg-zinc-900 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-zinc-800"
                >
                <p class="mt-2 text-xs text-zinc-500">Formatos soportados: .xlsx, .xls, .csv</p>
            </div>

            <div class="flex flex-wrap gap-3">
                <button 
                    type="submit" 
                    class="inline-flex items-center justify-center rounded-2xl bg-zinc-950 px-6 py-2 text-sm font-semibold text-white hover:bg-zinc-900"
                >
                    Importar gafas deportivas
                </button>
                <a 
                    href="{{ route('admin.gafas-descanso.import.template') }}" 
                    class="inline-flex items-center justify-center rounded-2xl border border-blue-200 bg-blue-50 px-6 py-2 text-sm font-semibold text-blue-700 hover:bg-blue-100"
                    download
                >
                    ⬇ Descargar plantilla
                </a>
                <a 
                    href="{{ route('admin.gafas-descanso.index') }}" 
                    class="inline-flex items-center justify-center rounded-2xl border border-zinc-200 bg-white px-6 py-2 text-sm font-semibold text-zinc-700 hover:bg-zinc-50"
                >
                    Cancelar
                </a>
            </div>
        </form>
    </div>
@endsection
