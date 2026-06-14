<?php

namespace App\Http\Controllers\Admin\Concerns;

use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

trait HandlesGafasExcelImport
{
    protected function showExcelImportForm(string $view)
    {
        return view($view);
    }

    protected function importFromExcelGeneric(Request $request, string $tipo, string $defaultCategory, string $indexRoute)
    {
        $request->validate([
            'excel_file' => ['required', 'file', 'mimes:xlsx,xls,csv'],
        ], [
            'excel_file.required' => 'Por favor selecciona un archivo Excel',
            'excel_file.mimes' => 'El archivo debe ser un Excel (.xlsx, .xls) o CSV',
        ]);

        $file = $request->file('excel_file');

        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file);
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            if (count($rows) < 2) {
                return back()->withErrors(['excel_file' => 'El archivo Excel debe contener al menos un encabezado y una fila de datos.']);
            }

            $headerRowIndex = $this->findHeaderRow($rows);
            if ($headerRowIndex === null) {
                return back()->withErrors(['excel_file' => 'No se encontraron las columnas requeridas (nombre, precio) en el archivo.']);
            }

            $headers = array_map('strtolower', array_map('trim', $rows[$headerRowIndex]));
            $headers = $this->normalizeHeaders($headers);

            $requiredColumns = ['nombre', 'precio'];
            $missingColumns = array_diff($requiredColumns, $headers);
            if ($missingColumns !== []) {
                return back()->withErrors(['excel_file' => 'El archivo debe contener las columnas: ' . implode(', ', $requiredColumns)]);
            }

            $created = 0;
            $skipped = 0;
            $errors = [];

            $existingSlugs = Producto::query()
                ->where('tipo', $tipo)
                ->where('genero_objetivo', $defaultCategory)
                ->pluck('slug')
                ->toArray();

            foreach (array_slice($rows, $headerRowIndex + 1) as $index => $row) {
                if (!array_filter($row, static fn ($value) => trim((string) $value) !== '')) {
                    continue;
                }

                try {
                    $rowData = [];
                    foreach ($headers as $colIndex => $headerName) {
                        if (!array_key_exists($headerName, $rowData)) {
                            $rowData[$headerName] = $row[$colIndex] ?? null;
                        }
                    }

                    $nombre = trim((string) ($rowData['nombre'] ?? ''));
                    if ($nombre === '') {
                        $errors[] = 'Fila ' . ($index + $headerRowIndex + 2) . ': falta la columna/valor de Nombre (no se toma Referencia como nombre).';
                        continue;
                    }

                    $precio = $this->parseMoneyFromExcel($rowData['precio'] ?? null);
                    if ($precio === null || $precio <= 0) {
                        $errors[] = 'Fila ' . ($index + $headerRowIndex + 2) . ': Precio invalido.';
                        continue;
                    }

                    $slug = Str::slug($nombre);
                    if (in_array($slug, $existingSlugs, true)) {
                        $skipped++;
                        continue;
                    }

                    $slug = $this->makeUniqueSlug($nombre);

                    $meta = [];
                    $imageUrl = trim((string) ($rowData['imagen_url'] ?? ''));
                    if ($imageUrl !== '') {
                        $meta['imagen_url'] = $imageUrl;
                        $meta['imagen_url_manual'] = $imageUrl;
                        $meta['imagen_alt'] = $nombre;
                        $meta['image_pos_x'] = 50;
                        $meta['image_pos_y'] = 50;
                        $meta['image_zoom'] = self::IMG_MID_ZOOM;
                    }

                    $color = trim((string) ($rowData['color'] ?? ''));
                    if ($color !== '') {
                        $meta['color'] = $color;
                    }

                    $referencia = trim((string) ($rowData['referencia'] ?? ''));
                    if ($referencia !== '') {
                        $meta['referencia'] = $referencia;
                    }

                    $caracteristicas = [
                        'recomendado_para' => trim((string) ($rowData['recomendado_para'] ?? '')) ?: 'No especificado',
                        'incluye' => trim((string) ($rowData['incluye'] ?? '')) ?: 'No especificado',
                        'clip_on_compatible' => $this->parseBooleanFromExcel($rowData['clip_on_compatible'] ?? null),
                        'progresivos' => $this->parseBooleanFromExcel($rowData['progresivos'] ?? null),
                        'tipo_formula' => trim((string) ($rowData['tipo_formula'] ?? '')) ?: 'Bajas',
                    ];

                    $medidas = [
                        'ancho_total_montura_cm' => $this->parseMeasureFromExcel($rowData['ancho_total_montura'] ?? null),
                        'ancho_lente_cm' => $this->parseMeasureFromExcel($rowData['ancho_lente'] ?? null),
                        'alto_lente_cm' => $this->parseMeasureFromExcel($rowData['alto_lente'] ?? null),
                        'puente_cm' => $this->parseMeasureFromExcel($rowData['puente'] ?? null),
                        'largo_patillas_cm' => $this->parseMeasureFromExcel($rowData['largo_patillas'] ?? null),
                    ];
                    $medidas = array_filter($medidas, static fn ($value) => $value !== null);
                    if ($medidas !== []) {
                        $caracteristicas['medidas'] = $medidas;
                    }

                    $precioOferta = null;
                    if (trim((string) ($rowData['precio_oferta'] ?? '')) !== '') {
                        $precioOferta = $this->parseMoneyFromExcel($rowData['precio_oferta']);
                    }

                    $categoria = trim((string) ($rowData['categoria'] ?? ''));
                    if ($categoria === '') {
                        $categoria = $defaultCategory;
                    }

                    Producto::query()->create([
                        'nombre' => $nombre,
                        'slug' => $slug,
                        'tipo' => $tipo,
                        'genero_objetivo' => $categoria,
                        'material_montura' => trim((string) ($rowData['material_montura'] ?? '')) ?: null,
                        'color' => $color !== '' ? $color : null,
                        'descripcion' => trim((string) ($rowData['descripcion'] ?? '')) ?: null,
                        'caracteristicas' => $caracteristicas ?: null,
                        'precio' => $precio,
                        'precio_oferta' => $precioOferta,
                        'moneda' => 'COP',
                        // Import Excel no toca existencias para no romper la logica de stock por imagen/color.
                        'existencias' => null,
                        'esta_activo' => filter_var($rowData['esta_activo'] ?? true, FILTER_VALIDATE_BOOLEAN),
                        'meta' => $meta ?: null,
                    ]);

                    $created++;
                    $existingSlugs[] = $slug;
                } catch (\Throwable $e) {
                    $errors[] = 'Fila ' . ($index + $headerRowIndex + 2) . ': ' . $e->getMessage();
                }
            }

            $message = 'Se importaron ' . $created . ' gafas correctamente.';
            if ($skipped > 0) {
                $message .= ' Se omitieron ' . $skipped . ' gafas duplicadas.';
            }
            if ($errors !== []) {
                $message .= ' Con ' . count($errors) . ' errores.';
                $message .= ' Detalle: ' . implode(' | ', array_slice($errors, 0, 5));
            }

            return redirect()
                ->route($indexRoute)
                ->with('status', $message)
                ->with('import_errors', $errors);
        } catch (\Throwable $e) {
            return back()->withErrors(['excel_file' => 'Error al procesar el archivo: ' . $e->getMessage()]);
        }
    }

    protected function downloadExcelTemplate(string $filename)
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $headers = [
            'nombre',
            'referencia',
            'inventario',
            'color',
            'recomendado para',
            'material',
            'clip-on compatible',
            'ancho total de la montura',
            'ancho del lente',
            'alto del lente',
            'puente',
            'largo de patillas',
            'progresivos',
            'tipo de formula',
            'incluye',
            'precio',
        ];

        $sheet->fromArray($headers, null, 'A1');

        $headerStyle = [
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1F2937'],
            ],
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ];

        $sheet->getStyle('A1:P1')->applyFromArray($headerStyle);
        foreach (range('A', 'P') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        return response()->stream(
            function () use ($writer) {
                $writer->save('php://output');
            },
            200,
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]
        );
    }

    private function findHeaderRow(array $rows): ?int
    {
        $requiredColumns = ['nombre', 'precio'];

        foreach ($rows as $index => $row) {
            $headers = array_map('strtolower', array_map('trim', $row));
            $headers = $this->normalizeHeaders($headers);

            if (array_diff($requiredColumns, $headers) === []) {
                return $index;
            }
        }

        return null;
    }

    private function normalizeHeaders(array $headers): array
    {
        $mapping = [
            'nombre' => ['nombre', 'name', 'producto', 'gafa'],
            'referencia' => ['referencia', 'ref', 'codigo', 'code'],
            'precio' => ['precio', 'price', 'valor', 'costo', 'amount'],
            'color' => ['color', 'colour', 'colores'],
            'material_montura' => ['material', 'material_montura', 'montura', 'frame_material', 'material de la montura'],
            'descripcion' => ['descripcion', 'description', 'desc', 'descripción'],
            'precio_oferta' => ['precio_oferta', 'oferta', 'sale_price', 'discount_price'],
            'existencias' => ['existencias', 'stock', 'cantidad', 'quantity', 'inventory', 'inventario'],
            'imagen_url' => ['imagen_url', 'image', 'foto', 'url', 'image_url', 'link_imagen'],
            'recomendado_para' => ['recomendado_para', 'recomendado', 'recomendacion', 'recommended_for', 'recomendado para'],
            'incluye' => ['incluye', 'includes', 'incluido', 'what_includes'],
            'clip_on_compatible' => ['clip_on_compatible', 'clip_on', 'clipon', 'compatible_clip', 'clip-on compatible'],
            'progresivos' => ['progresivos', 'progressive', 'progresivo', 'progressive_lens'],
            'tipo_formula' => ['tipo_formula', 'formula', 'formula_type', 'tipo_lente', 'tipo de formula', 'tipo de fórmula'],
            'esta_activo' => ['esta_activo', 'activo', 'active', 'status', 'enabled'],
            'ancho_total_montura' => ['ancho_total_montura', 'ancho total de la montura', 'ancho total', 'frame width', 'total width', 'ancho montura', 'frente'],
            'ancho_lente' => ['ancho_lente', 'ancho del lente', 'ancho lente', 'lens width', 'width lens', 'ancho lente mm', 'ancho lente (mm)'],
            'alto_lente' => ['alto_lente', 'alto del lente', 'alto lente', 'lens height', 'height lens', 'alto lente mm', 'alto lente (mm)'],
            'puente' => ['puente', 'bridge', 'puente montura', 'bridge mm', 'puente (mm)'],
            'largo_patillas' => ['largo_patillas', 'largo de patillas', 'largo patillas', 'arm length', 'temple length', 'patillas', 'largo patilla'],
            'categoria' => ['categoria', 'genero', 'genero_objetivo', 'target_gender'],
        ];

        $normalized = [];
        foreach ($headers as $header) {
            $cleanHeader = Str::of((string) $header)
                ->lower()
                ->ascii()
                ->replaceMatches('/[^a-z0-9]+/', ' ')
                ->trim()
                ->toString();
            $found = false;

            foreach ($mapping as $standard => $variations) {
                $normalizedVariations = array_map(static function (string $variation): string {
                    return Str::of($variation)
                        ->lower()
                        ->ascii()
                        ->replaceMatches('/[^a-z0-9]+/', ' ')
                        ->trim()
                        ->toString();
                }, $variations);

                if (in_array($cleanHeader, $normalizedVariations, true)) {
                    $normalized[] = $standard;
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                $normalized[] = $cleanHeader;
            }
        }

        return $normalized;
    }

    private function parseMoneyFromExcel(mixed $value): ?float
    {
        if ($value === null) {
            return null;
        }

        if (is_int($value) || is_float($value)) {
            return (float) $value;
        }

        $raw = trim((string) $value);
        if ($raw === '') {
            return null;
        }

        $raw = preg_replace('/[^0-9,\.\-]/', '', $raw) ?? '';
        if ($raw === '' || $raw === '-' || $raw === '.' || $raw === ',') {
            return null;
        }

        if (str_contains($raw, ',') && str_contains($raw, '.')) {
            $lastComma = strrpos($raw, ',');
            $lastDot = strrpos($raw, '.');
            $lastSepPos = max($lastComma === false ? -1 : $lastComma, $lastDot === false ? -1 : $lastDot);
            $decimals = strlen($raw) - $lastSepPos - 1;

            if ($decimals >= 1 && $decimals <= 2) {
                // ultimo separador es decimal, los demas son miles
                if ($lastComma > $lastDot) {
                    $raw = str_replace('.', '', $raw);
                    $raw = str_replace(',', '.', $raw);
                } else {
                    $raw = str_replace(',', '', $raw);
                }
            } else {
                // todos como separadores de miles
                $raw = str_replace([',', '.'], '', $raw);
            }
        } elseif (str_contains($raw, ',')) {
            if (preg_match('/^\-?\d{1,3}(?:,\d{3})+$/', $raw)) {
                $raw = str_replace(',', '', $raw);
            } else {
                $raw = str_replace(',', '.', $raw);
            }
        } elseif (str_contains($raw, '.')) {
            if (preg_match('/^\-?\d{1,3}(?:\.\d{3})+$/', $raw)) {
                $raw = str_replace('.', '', $raw);
            }
        }

        if (!is_numeric($raw)) {
            return null;
        }

        return (float) $raw;
    }

    private function parseMeasureFromExcel(mixed $value): ?float
    {
        if ($value === null) {
            return null;
        }

        if (is_int($value) || is_float($value)) {
            $numeric = (float) $value;
            // Si viene en mm (ej. 52, 140), convertir a cm para respetar el modelo actual.
            return $numeric > 20 ? round($numeric / 10, 2) : $numeric;
        }

        $raw = Str::of((string) $value)->lower()->ascii()->trim()->toString();
        if ($raw === '') {
            return null;
        }

        $isMm = str_contains($raw, 'mm');
        $raw = preg_replace('/[^0-9,\.\-]/', '', $raw) ?? '';
        if ($raw === '' || $raw === '-' || $raw === '.' || $raw === ',') {
            return null;
        }

        $raw = str_replace(',', '.', $raw);
        if (!is_numeric($raw)) {
            return null;
        }

        $numeric = (float) $raw;
        if ($isMm || $numeric > 20) {
            return round($numeric / 10, 2);
        }

        return $numeric;
    }

    private function parseBooleanFromExcel(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value) || is_float($value)) {
            return (float) $value > 0;
        }

        $raw = Str::of((string) $value)->lower()->ascii()->trim()->toString();
        if ($raw === '') {
            return false;
        }

        $trueValues = ['1', 'si', 's', 'yes', 'y', 'true', 'verdadero', 'ok', 'x'];
        $falseValues = ['0', 'no', 'n', 'false', 'falso'];

        if (in_array($raw, $trueValues, true)) {
            return true;
        }
        if (in_array($raw, $falseValues, true)) {
            return false;
        }

        return filter_var($raw, FILTER_VALIDATE_BOOLEAN);
    }
}
