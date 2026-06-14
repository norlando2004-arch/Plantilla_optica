<?php
$dsn = 'pgsql:host=ep-lingering-recipe-ai7kdxge-pooler.c-4.us-east-1.aws.neon.tech;port=5432;dbname=neondb;sslmode=require';
try {
    $pdo = new PDO($dsn, 'neondb_owner', 'npg_LqTZ5EyGH9UF', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $pdo->exec("SET client_encoding = 'UTF8'");

    $db  = $pdo->query('SELECT current_database()')->fetchColumn();
    $sch = $pdo->query('SELECT current_schema()')->fetchColumn();
    echo "DB: $db  Schema: $sch" . PHP_EOL;

    $allTables = $pdo->query("SELECT schemaname, tablename FROM pg_tables ORDER BY schemaname, tablename")->fetchAll(PDO::FETCH_ASSOC);
    echo 'Total tablas (todos los schemas): ' . count($allTables) . PHP_EOL;
    foreach ($allTables as $r) {
        echo "  {$r['schemaname']}.{$r['tablename']}" . PHP_EOL;
    }

    $rows = $pdo->query("SELECT tablename FROM pg_tables WHERE schemaname='public' ORDER BY tablename")->fetchAll(PDO::FETCH_COLUMN);
    echo PHP_EOL . 'Tablas public: ' . implode(', ', $rows) . PHP_EOL;
    echo 'Count public: ' . count($rows) . PHP_EOL;

    foreach ($rows as $t) {
        $count = $pdo->query('SELECT COUNT(*) FROM "' . $t . '"')->fetchColumn();
        echo "  $t: $count filas" . PHP_EOL;
    }
} catch (Throwable $e) {
    echo 'Error: ' . $e->getMessage() . PHP_EOL;
}
