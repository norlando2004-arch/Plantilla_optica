<?php
/**
 * Fuerza el DROP de TODAS las tablas del schema public en la nueva DB,
 * usando CASCADE para ignorar FKs. Luego reporta las tablas que quedaron.
 */
$url = 'postgresql://neondb_owner:npg_zMfFpYR59PQX@ep-mute-sea-adehu6y6-pooler.c-2.us-east-1.aws.neon.tech/neondb?sslmode=require&channel_binding=require';
$p   = parse_url($url);
$dsn = sprintf('pgsql:host=%s;port=%d;dbname=%s;sslmode=require', $p['host'], $p['port'] ?? 5432, ltrim($p['path'], '/'));
$pdo = new PDO($dsn, rawurldecode($p['user']), rawurldecode($p['pass']), [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$tables = $pdo->query("SELECT tablename FROM pg_tables WHERE schemaname='public' ORDER BY tablename")->fetchAll(PDO::FETCH_COLUMN);

echo "Tablas encontradas: " . count($tables) . "\n";

// Build one big DROP statement with all tables + CASCADE — handles FKs automatically
if (!empty($tables)) {
    $quoted = array_map(fn($t) => '"' . str_replace('"', '""', $t) . '"', $tables);
    $list   = implode(', ', $quoted);
    try {
        $pdo->exec("DROP TABLE IF EXISTS {$list} CASCADE");
        echo "  ✔ DROP de todas las tablas completado.\n";
    } catch (Throwable $e) {
        echo "  ✗ DROP masivo falló: " . $e->getMessage() . "\n";
        echo "  Intentando tabla por tabla...\n";
        foreach ($tables as $t) {
            $qt = '"' . str_replace('"', '""', $t) . '"';
            try {
                $pdo->exec("DROP TABLE IF EXISTS {$qt} CASCADE");
                echo "    ✔ DROP {$t}\n";
            } catch (Throwable $e2) {
                echo "    ✗ DROP {$t}: " . $e2->getMessage() . "\n";
            }
        }
    }
}

// Drop any orphan sequences
$seqs = $pdo->query("SELECT sequence_name FROM information_schema.sequences WHERE sequence_schema='public'")->fetchAll(PDO::FETCH_COLUMN);
if (!empty($seqs)) {
    $qseqs = array_map(fn($s) => '"' . str_replace('"', '""', $s) . '"', $seqs);
    try {
        $pdo->exec("DROP SEQUENCE IF EXISTS " . implode(', ', $qseqs) . " CASCADE");
        echo "  ✔ Sequences eliminadas.\n";
    } catch (Throwable $e) {}
}

// Verify
$remaining = $pdo->query("SELECT tablename FROM pg_tables WHERE schemaname='public'")->fetchAll(PDO::FETCH_COLUMN);
echo "\nTablas restantes: " . count($remaining) . "\n";
if (!empty($remaining)) {
    echo implode(', ', $remaining) . "\n";
} else {
    echo "DB limpia. Listo para migrate:fresh\n";
}
