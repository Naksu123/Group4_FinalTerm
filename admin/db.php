<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'FinalTerm');
define('DB_USER', 'root');
define('DB_PASS', '');

function getPDO()
{
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
    } catch (PDOException $e) {
        $sqlErrorCode = null;
        if (is_array($e->errorInfo) && isset($e->errorInfo[1])) {
            $sqlErrorCode = $e->errorInfo[1];
        }

        if ($sqlErrorCode == 1049) {
            try {
                $tmpDsn = 'mysql:host=' . DB_HOST . ';charset=utf8mb4';
                $tmp = new PDO($tmpDsn, DB_USER, DB_PASS, $options);
                $tmp->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
                ensureSchemaExists($pdo);
                return $pdo;
            } catch (PDOException $e2) {
                die('Database creation failed: ' . $e2->getMessage() . '. To fix manually, run: mysql -u ' . DB_USER . ' -p < admin/schema.sql');
            }
        }

        die('Database connection failed: ' . $e->getMessage());
    }
}

function ensureSchemaExists(PDO $pdo)
{
    try {
        $hasSubjects = (bool) $pdo->query("SHOW TABLES LIKE 'subjects'")->fetch();
        $hasGrades = (bool) $pdo->query("SHOW TABLES LIKE 'grades'")->fetch();

        if ($hasSubjects && $hasGrades) {
            return;
        }

        $schemaPath = __DIR__ . '/schema.sql';
        if (!file_exists($schemaPath)) {
            throw new Exception('Schema file not found: ' . $schemaPath);
        }

        $sql = file_get_contents($schemaPath);
        $sql = preg_replace('/CREATE\s+DATABASE.+?;\s*/is', '', $sql);
        $sql = preg_replace('/USE\s+[`\w]+;\s*/is', '', $sql);

        $stmts = array_filter(array_map('trim', preg_split('/;\s*\n/', $sql)));
        foreach ($stmts as $stmt) {
            if ($stmt) {
                $pdo->exec($stmt);
            }
        }
    } catch (Exception $e) {
        die('Failed to ensure schema: ' . $e->getMessage() . '. You can manually import admin/schema.sql: mysql -u ' . DB_USER . ' -p < admin/schema.sql');
    }
}
