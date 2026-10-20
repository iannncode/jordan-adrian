<?php
/**
 * CFMTS - Global configuration
 *
 * Real database credentials live in config/config.local.php, which is
 * ignored by git. When that file is missing (fresh clone), the app falls
 * back to the XAMPP defaults below.
 */

if (!function_exists('cfmts_db_candidates')) {
    function cfmts_db_candidates(): array
    {
        $candidates = [];

        $localConfig = __DIR__ . '/config.local.php';
        if (is_file($localConfig)) {
            $loaded = require $localConfig;
            if (is_array($loaded)) {
                $candidates = $loaded;
            }
        }

        // ---------- XAMPP / local development (fallback defaults) ----------
        if (!$candidates) {
            $candidates = [
                [
                    'label'    => 'XAMPP (local)',
                    'host'     => 'localhost',
                    'database' => 'cfmts_db',
                    'username' => 'root',
                    'password' => '',
                    'active'   => true,
                ],
            ];
        }

        // Drop disabled / placeholder entries so we never dial a fake host.
        $candidates = array_values(array_filter($candidates, static function (array $c): bool {
            if (empty($c['active'])) {
                return false;
            }
            return !(strpos($c['host'], 'XXX') !== false
                  || $c['password'] === 'YOUR_DB_PASSWORD');
        }));

        // When running on a local machine (XAMPP / CLI), try localhost first
        // so local development does not wait on the remote host.
        $serverName = $_SERVER['SERVER_NAME'] ?? '';
        $isLocal = PHP_SAPI === 'cli'
                || in_array($serverName, ['localhost', '127.0.0.1', '::1'], true);

        if ($isLocal) {
            $local  = [];
            $remote = [];
            foreach ($candidates as $c) {
                if (strpos($c['host'], 'localhost') !== false) {
                    $local[] = $c;
                } else {
                    $remote[] = $c;
                }
            }
            $candidates = array_merge($local, $remote);
        }

        return $candidates;
    }
}

$DB_CANDIDATES = cfmts_db_candidates();

if (!$DB_CANDIDATES) {
    die('<div style="font-family:sans-serif;padding:2rem;">
            <h2>Database not configured</h2>
            <p>No active database entry found in <code>config/config.php</code>.</p>
         </div>');
}

define('DB_CHARSET', 'utf8mb4');

/**
 * Base URL of the app, auto-detected from the running script path.
 *   /cfmts/index.php          -> "/cfmts"
 *   /cfmts/actions/save.php   -> "/cfmts"
 *   /index.php                -> ""
 * So it works whether you upload to the site root or to a subfolder.
 */
$script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$base   = rtrim(dirname($script), '/');
if (substr($base, -8) === '/actions') {
    $base = substr($base, 0, -8);
}
define('APP_BASE_URL', $base);
