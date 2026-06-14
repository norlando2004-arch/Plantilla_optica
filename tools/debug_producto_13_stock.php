<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$p = App\Models\Producto::find(13);
if (! $p) {
    echo "not found\n";
    exit(0);
}

echo 'id=' . $p->id . PHP_EOL;
echo 'color=' . (string) $p->color . PHP_EOL;
echo 'existencias=' . (string) $p->existencias . PHP_EOL;
echo 'color_stock=' . json_encode(data_get($p->meta, 'color_stock', []), JSON_UNESCAPED_UNICODE) . PHP_EOL;
echo 'color_variants=' . json_encode(data_get($p->meta, 'color_variants', []), JSON_UNESCAPED_UNICODE) . PHP_EOL;
