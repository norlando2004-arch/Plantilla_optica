<?php

/**
 * Script de migración de datos entre dos bases de datos PostgreSQL (Neon → Neon).
 * Uso: php tools/migrate_db.php
 *
 * ⚠️  Borra este archivo después de usarlo (contiene credenciales).
 */

$OLD_URL = 'postgresql://neondb_owner:npg_LqTZ5EyGH9UF@ep-lingering-recipe-ai7kdxge-pooler.c-4.us-east-1.aws.neon.tech/neondb?sslmode=require&channel_binding=require';
$NEW_URL = 'postgresql://neondb_owner:npg_zMfFpYR59PQX@ep-mute-sea-adehu6y6-pooler.c-2.us-east-1.aws.neon.tech/neondb?sslmode=require&channel_binding=require';

// -----------------------------------------------------------------------
// Helpers
// -----------------------------------------------------------------------

function dsn_from_url(string $url): array
{
    $p = parse_url($url);
    $query = [];
    if (isset($p['query'])) {
        parse_str($p['query'], $query);
    }

    $sslmode = $query['sslmode'] ?? 'require';

    $dsn = sprintf(
        'pgsql:host=%s;port=%d;dbname=%s;sslmode=%s',
        $p['host'],
        $p['port'] ?? 5432,
        ltrim($p['path'], '/'),
        $sslmode
    );

    $user     = rawurldecode($p['user']);
    $password = rawurldecode($p['pass']);

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT            => 30,
    ];

    return [$dsn, $user, $password, $options];
}

function connect(string $url): PDO
{
    [$dsn, $user, $pass, $opts] = dsn_from_url($url);
    $pdo = new PDO($dsn, $user, $pass, $opts);
    $pdo->exec("SET client_encoding = 'UTF8'");
    return $pdo;
}

function log_msg(string $msg): void
{
    echo '[' . date('H:i:s') . '] ' . $msg . PHP_EOL;
}

function get_user_tables(PDO $pdo, string $schema = 'public'): array
{
    $stmt = $pdo->prepare(
        "SELECT tablename FROM pg_tables WHERE schemaname = ? ORDER BY tablename"
    );
    $stmt->execute([$schema]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

function detect_app_schema(PDO $pdo): string
{
    // Check if the app tables are in a non-public schema (some Neon projects use a named schema)
    $schemas = $pdo->query(
        "SELECT schemaname FROM pg_tables WHERE tablename = 'migrations' LIMIT 1"
    )->fetchColumn();
    return $schemas ?: 'public';
}

function quote_ident(string $name): string
{
    return '"' . str_replace('"', '""', $name) . '"';
}

/** Returns ['col_name' => 'udt_name', ...] for all columns of a table in the target DB. */
function get_column_types(PDO $pdo, string $table, string $schema = 'public'): array
{
    $stmt = $pdo->prepare(
        "SELECT column_name, udt_name
         FROM information_schema.columns
         WHERE table_schema = ? AND table_name = ?
         ORDER BY ordinal_position"
    );
    $stmt->execute([$schema, $table]);
    $result = [];
    foreach ($stmt->fetchAll() as $row) {
        $result[$row['column_name']] = $row['udt_name'];
    }
    return $result;
}

function get_serial_columns(PDO $pdo, string $table): array
{
    $stmt = $pdo->prepare(
        "SELECT column_name
         FROM information_schema.columns
         WHERE table_schema = 'public'
           AND table_name = ?
           AND column_default LIKE 'nextval%'"
    );
    $stmt->execute([$table]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

/** Returns [child_table => [parent_table1, ...]] for all FK relationships. */
function get_fk_dependencies(PDO $pdo): array
{
    $stmt = $pdo->query("
        SELECT
            tc.table_name  AS child_table,
            ccu.table_name AS parent_table
        FROM information_schema.table_constraints AS tc
        JOIN information_schema.key_column_usage AS kcu
            ON tc.constraint_name = kcu.constraint_name
           AND tc.table_schema    = kcu.table_schema
        JOIN information_schema.constraint_column_usage AS ccu
            ON ccu.constraint_name = tc.constraint_name
           AND ccu.table_schema    = tc.table_schema
        WHERE tc.constraint_type = 'FOREIGN KEY'
          AND tc.table_schema     = 'public'
    ");
    $deps = [];
    foreach ($stmt->fetchAll() as $row) {
        $child  = $row['child_table'];
        $parent = $row['parent_table'];
        if ($child !== $parent) {
            $deps[$child][] = $parent;
        }
    }
    return $deps;
}

/** Returns $tables reordered so parents always come before their children. */
function topological_sort(array $tables, array $deps): array
{
    $result  = [];
    $visited = [];

    $visit = null;
    $visit = function (string $table) use ($deps, &$visited, &$result, &$visit): void {
        if (isset($visited[$table])) {
            return;
        }
        $visited[$table] = true;
        foreach ($deps[$table] ?? [] as $parent) {
            $visit($parent);
        }
        $result[] = $table;
    };

    foreach ($tables as $table) {
        $visit($table);
    }

    return array_values(array_filter($result, fn ($t) => in_array($t, $tables, true)));
}

// -----------------------------------------------------------------------
// Tables to SKIP when copying data (Laravel-managed transient tables)
// -----------------------------------------------------------------------
$SKIP_DATA = [
    'migrations', 'cache', 'cache_locks',
    'jobs', 'job_batches', 'failed_jobs',
    'sessions', 'sesiones',
];

// -----------------------------------------------------------------------
// Step 1 – Connect to OLD database & export all rows
// -----------------------------------------------------------------------
log_msg('Conectando a la base de datos ANTIGUA...');
try {
    $old = connect($OLD_URL);
    log_msg('✔  Conectado.');
} catch (Throwable $e) {
    log_msg('✗  Falló: ' . $e->getMessage());
    exit(1);
}

$oldSchema = detect_app_schema($old);
log_msg("Schema detectado en DB antigua: {$oldSchema}");

// Set search_path so queries that don't qualify table names work correctly
$old->exec("SET search_path = \"{$oldSchema}\"");

$tables = get_user_tables($old, $oldSchema);
log_msg('Tablas encontradas en DB antigua: ' . implode(', ', $tables));

$export = [];
foreach ($tables as $table) {
    if (in_array($table, $SKIP_DATA, true)) {
        log_msg("  → Saltando {$table} (tabla transitoria)");
        continue;
    }

    $stmt  = $old->query('SELECT * FROM ' . quote_ident($table));
    $rows  = $stmt->fetchAll();
    $count = count($rows);
    $export[$table] = $rows;
    log_msg("  → {$table}: {$count} filas exportadas");
}

$old = null;
log_msg('Exportación completada.');
log_msg('');

// -----------------------------------------------------------------------
// Step 2 – Connect to NEW database & import in FK-safe order
// -----------------------------------------------------------------------
log_msg('Conectando a la base de datos NUEVA...');
try {
    $new = connect($NEW_URL);
    log_msg('✔  Conectado.');
} catch (Throwable $e) {
    log_msg('✗  Falló: ' . $e->getMessage());
    exit(1);
}

// Build topological insert order (parents before children)
$fkDeps    = get_fk_dependencies($new);
$tableList = topological_sort(array_keys($export), $fkDeps);
log_msg('Orden de inserción (topo): ' . implode(' → ', $tableList));
log_msg('');

// Truncate in REVERSE order (children first) so FK constraints do not block
log_msg('Truncando tablas (hijos primero)...');
foreach (array_reverse($tableList) as $table) {
    if (empty($export[$table])) {
        continue;
    }
    $qt = quote_ident($table);
    try {
        $new->exec("TRUNCATE TABLE {$qt} RESTART IDENTITY CASCADE");
        log_msg("  ✔ {$table} truncado");
    } catch (Throwable $e) {
        log_msg("  ⚠  No se pudo truncar {$table}: " . $e->getMessage());
    }
}
log_msg('');

// Insert in FORWARD topological order (parents before children)
log_msg('Insertando datos...');
$errors = [];

foreach ($tableList as $table) {
    $rows = $export[$table];
    if (empty($rows)) {
        log_msg("  → {$table}: vacía, omitida.");
        continue;
    }

    $qt = quote_ident($table);

    $columns      = array_keys($rows[0]);
        // Get target column names/types — only insert what the target actually has
        $targetCols = get_column_types($new, $table);

        // Determine which source columns to include (must exist in target by name)
        $srcColumns = array_keys($rows[0]);
        $columns    = array_values(array_filter($srcColumns, fn ($c) => isset($targetCols[$c])));

        if (empty($columns)) {
            log_msg("  ⚠ {$table}: sin columnas coincidentes — omitida.");
            continue;
        }

        $colList      = implode(', ', array_map('quote_ident', $columns));
        $placeholders = implode(', ', array_fill(0, count($columns), '?'));
        $sql          = "INSERT INTO {$qt} ({$colList}) VALUES ({$placeholders})";
    $colList      = implode(', ', array_map('quote_ident', $columns));
    $placeholders = implode(', ', array_fill(0, count($columns), '?'));
    $sql          = "INSERT INTO {$qt} ({$colList}) VALUES ({$placeholders})";

    $stmt     = $new->prepare($sql);
    $inserted = 0;
    $failed   = false;

    foreach ($rows as $row) {
        // Coerce values: keep only target-matching columns, fix empty strings for non-text types
        $values = [];
        foreach ($columns as $col) {
            $val  = $row[$col];
            $type = $targetCols[$col];
            // Empty string is not valid for non-text types in PostgreSQL — use null
            $nonText = !in_array($type, ['text', 'varchar', 'char', 'bpchar', 'name', 'citext'], true);
            if ($val === '' && $nonText) {
                $val = null;
            }
            $values[] = $val;
        }
        try {
            $stmt->execute($values);
            $inserted++;
        } catch (Throwable $e) {
            log_msg("  ✗  Error en fila de {$table}: " . $e->getMessage());
            $errors[] = $table;
            $failed   = true;
            break;
        }
    }

    if (! $failed) {
        log_msg("  ✔ {$table}: {$inserted} filas insertadas.");
    }

    // Fix auto-increment sequences so next INSERT gets the correct ID
    $serialCols = get_serial_columns($new, $table);
    foreach ($serialCols as $col) {
        try {
            $seqSql = "SELECT setval("
                . "pg_get_serial_sequence({$new->quote($table)}, {$new->quote($col)}), "
                . "(SELECT COALESCE(MAX(" . quote_ident($col) . "), 1) FROM {$qt})"
                . ")";
            $new->exec($seqSql);
        } catch (Throwable $e) {
            // non-fatal
        }
    }
}

$new = null;

log_msg('');
if (! empty($errors)) {
    log_msg('⚠  Tablas con errores: ' . implode(', ', array_unique($errors)));
} else {
    log_msg('✔  Migración de datos completada sin errores.');
}

log_msg('Listo. Borra este archivo (contiene credenciales).');
