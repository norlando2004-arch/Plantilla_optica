<?php
require 'bootstrap/app.php';

use App\Models\Pago;

$pagos = Pago::selectRaw('estado, COUNT(*) as cantidad')
    ->groupBy('estado')
    ->get();

echo "Estados de pagos encontrados:\n";
foreach ($pagos as $p) {
    echo "- Estado: '{$p->estado}', Cantidad: {$p->cantidad}\n";
}

echo "\nTotal de pagos: " . Pago::count() . "\n";

echo "\nPrimeros 5 pagos:\n";
$primeros = Pago::select('id', 'estado', 'monto', 'created_at')->limit(5)->get();
foreach ($primeros as $p) {
    echo "ID: {$p->id}, Estado: '{$p->estado}', Monto: {$p->monto}\n";
}
