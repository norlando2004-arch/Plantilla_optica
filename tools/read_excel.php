<?php
require 'vendor/autoload.php';

$file = 'C:/Dev/Proyectos/EJEMPLO EXCEL .xlsx';

if (!file_exists($file)) {
    echo "Archivo no encontrado: $file\n";
    exit(1);
}

$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file);
$worksheet = $spreadsheet->getActiveSheet();
$rows = $worksheet->toArray();

echo "Total filas: " . count($rows) . "\n";
echo "Primeras 5 filas:\n";
foreach (array_slice($rows, 0, 5) as $i => $row) {
    echo "Fila " . ($i + 1) . ": " . json_encode($row, JSON_UNESCAPED_UNICODE) . "\n";
}
