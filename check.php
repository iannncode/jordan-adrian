<?php
/**
 * check.php - TEMPORARY diagnostic. Upload next to index.php, open it,
 * copy the output back, then DELETE it.
 */
header('Content-Type: text/plain; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "check.php ran from : " . __DIR__ . "\n";
echo "SCRIPT_NAME        : " . ($_SERVER['SCRIPT_NAME'] ?? '?') . "\n";
echo "DOCUMENT_ROOT      : " . ($_SERVER['DOCUMENT_ROOT'] ?? '?') . "\n";
echo "HTTP_HOST          : " . ($_SERVER['HTTP_HOST'] ?? '?') . "\n\n";

echo "--- files next to check.php ---\n";
foreach (['index.php', 'add.php', 'edit.php', 'config/config.php', 'config/Database.php',
          'includes/bootstrap.php', 'sql/cfmts_db.sql'] as $f) {
    $p = __DIR__ . '/' . $f;
    if (is_file($p)) {
        printf("%-24s EXISTS  %6d bytes  modified %s\n", $f, filesize($p), date('Y-m-d H:i', filemtime($p)));
    } else {
        printf("%-24s MISSING\n", $f);
    }
}

echo "\n--- which version of the code is deployed? ---\n";
$db = __DIR__ . '/config/Database.php';
if (is_file($db)) {
    $s = file_get_contents($db);
    echo "Database.php has 'candidates' : " . (strpos($s, 'candidates') !== false ? "YES -> NEW code\n" : "NO  -> OLD code (still not updated!)\n");
}
$cfg = __DIR__ . '/config/config.php';
if (is_file($cfg)) {
    $s = file_get_contents($cfg);
    echo "config.php has 'DB_HOST'      : " . (strpos($s, 'DB_HOST') !== false ? "YES -> OLD config (still not updated!)\n" : "no  -> NEW config\n");
    echo "config.php has 'sql211'       : " . (strpos($s, 'sql211') !== false ? "YES\n" : "no\n");
}

echo "\n--- environment ---\n";
echo "PHP " . PHP_VERSION . "  |  pdo_mysql: " . (extension_loaded('pdo_mysql') ? 'yes' : 'NO') . "\n";

echo "\n--- live DB test (using the app's own config) ---\n";
try {
    require __DIR__ . '/config/Database.php';
    if (function_exists('cfmts_db_candidates')) {
        foreach (cfmts_db_candidates() as $c) {
            echo "[{$c['label']}] {$c['host']} / {$c['database']} ... ";
            try {
                $pdo = new PDO("mysql:host={$c['host']};dbname={$c['database']};charset=utf8mb4",
                               $c['username'], $c['password'],
                               [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT => 8]);
                $t = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
                echo "OK. tables (" . count($t) . "): " . implode(', ', $t) . "\n";
            } catch (Throwable $e) {
                echo "FAILED: " . $e->getMessage() . "\n";
            }
        }
    } else {
        echo "OLD config.php (no cfmts_db_candidates) -> live DB test skipped.\n";
    }
} catch (Throwable $e) {
    echo "Could not load config: " . $e->getMessage() . "\n";
}
