<?php
$url = 'postgresql://neondb_owner:npg_zMfFpYR59PQX@ep-mute-sea-adehu6y6-pooler.c-2.us-east-1.aws.neon.tech/neondb?sslmode=require&channel_binding=require';
$p   = parse_url($url);
$dsn = sprintf('pgsql:host=%s;port=%d;dbname=%s;sslmode=require', $p['host'], $p['port'] ?? 5432, ltrim($p['path'], '/'));
$pdo = new PDO($dsn, rawurldecode($p['user']), rawurldecode($p['pass']), [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$rows = $pdo->query("SELECT tablename FROM pg_tables WHERE schemaname='public' ORDER BY tablename")->fetchAll(PDO::FETCH_COLUMN);
echo "Tablas en la nueva DB (" . count($rows) . "):\n";
echo implode("\n", $rows) . "\n";
