<?php
/**
 * Database.php
 * Singleton PDO connection wrapper for CFMTS.
 */

require_once __DIR__ . '/config.php';

class Database
{
    private static ?Database $instance = null;
    private PDO $conn;

    private function __construct()
    {
        $candidates = cfmts_db_candidates();

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_TIMEOUT            => 5,
        ];

        $errors = [];

        foreach ($candidates as $candidate) {
            $dsn = "mysql:host=" . $candidate['host']
                 . ";dbname=" . $candidate['database']
                 . ";charset=" . DB_CHARSET;

            try {
                $this->conn = new PDO($dsn, $candidate['username'], $candidate['password'], $options);
                return;
            } catch (PDOException $e) {
                $errors[] = '[' . $candidate['label'] . '] ' . $e->getMessage();
            }
        }

        die('<div style="font-family:sans-serif;padding:2rem;color:#7a1f1f;background:#fdecea;border:1px solid #f5c2c0;border-radius:8px;margin:2rem;">
                <h2>Database Connection Failed</h2>
                <p>Tried ' . count($candidates) . ' configuration(s):</p>
                <ul><li>' . implode('</li><li>', array_map("htmlspecialchars", $errors)) . '</li></ul>
                <p><strong>Fix:</strong> open <code>config/config.php</code> and fill in the 4 InfinityFree
                   values from <em>Client Area &gt; MySQL Databases</em>. Also make sure
                   <code>sql/cfmts_db.sql</code> has been imported into that database.</p>
             </div>');
    }

    private function __clone() {}

    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection(): PDO
    {
        return $this->conn;
    }
}
