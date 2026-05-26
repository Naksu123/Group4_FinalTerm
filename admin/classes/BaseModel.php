<?php
// admin/classes/BaseModel.php
// Base model provides PDO connection for child models

class BaseModel
{
    protected $pdo;
    protected string $table = '';
    /** list of allowed fields for insert/update */
    protected array $allowedFields = [];

    public function __construct()
    {
        global $pdo;
        
        // 1. Reuse existing PDO instance if available
        if (isset($pdo) && $pdo instanceof PDO) {
            $this->pdo = $pdo;
            return;
        }

        // 2. Validate configuration variables
        if (!defined('DB_HOST') || !defined('DB_NAME') || !defined('DB_USER') || !defined('DB_PASS')) {
            die("<strong>Configuration Error:</strong> Database credentials (DB_HOST, DB_NAME, DB_USER, DB_PASS) are not defined. Please check your config file.");
        }

        // 3. Setup DSN and connection options
        $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', DB_HOST, DB_NAME);
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false, // Best practice for security
        ];

        try {
            // 4. Attempt main database connection
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            $errorCode = $e->getCode();
            
            // 5. Handle "Unknown database" error (Error 1049) gracefully
            if ($errorCode == 1049 || (isset($e->errorInfo[1]) && $e->errorInfo[1] == 1049)) {
                try {
                    // Fallback: Connect to MySQL server without selecting a database
                    $fallbackDsn = sprintf('mysql:host=%s;charset=utf8mb4', DB_HOST);
                    $fallbackPdo = new PDO($fallbackDsn, DB_USER, DB_PASS, [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
                    ]);
                    
                    // Automatically create the missing database
                    $fallbackPdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                    
                    // Re-attempt connection to the newly created database
                    $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
                } catch (PDOException $fallbackEx) {
                    die("
                        <div style='border: 1px solid red; padding: 15px; font-family: sans-serif;'>
                            <h3 style='color: red;'>Database Connection Error</h3>
                            <p>The database <strong>" . DB_NAME . "</strong> does not exist and could not be created automatically.</p>
                            <p><strong>To fix this, please follow these steps:</strong></p>
                            <ol>
                                <li>Open XAMPP Control Panel and ensure MySQL is running.</li>
                                <li>Open <a href='http://localhost/phpmyadmin' target='_blank'>phpMyAdmin</a>.</li>
                                <li>Click on the <strong>SQL</strong> tab.</li>
                                <li>Run the following command:<br><br>
                                    <code style='background: #eee; padding: 5px; display: block;'>CREATE DATABASE " . DB_NAME . ";</code>
                                </li>
                            </ol>
                            <p><em>Error Details: " . htmlspecialchars($fallbackEx->getMessage()) . "</em></p>
                        </div>
                    ");
                }
            } else {
                // 6. Handle other connection errors (e.g., wrong credentials, MySQL not running)
                die("
                    <div style='border: 1px solid red; padding: 15px; font-family: sans-serif;'>
                        <h3 style='color: red;'>Database Connection Failed</h3>
                        <p>Could not connect to the database server. Please check your XAMPP MySQL service and credentials.</p>
                        <p><em>Error Details: " . htmlspecialchars($e->getMessage()) . "</em></p>
                    </div>
                ");
            }
        }
    }

    protected function filterData(array $data): array
    {
        if (empty($this->allowedFields)) return $data;
        return array_intersect_key($data, array_flip($this->allowedFields));
    }

    /**
     * Get all rows with optional pagination.
     * @param int|null $limit
     * @param int|null $offset
     * @return array
     */
    public function getAll(?int $limit = null, ?int $offset = null): array
    {
        if (empty($this->table)) return [];
        if ($limit === null) {
            $stmt = $this->pdo->query('SELECT * FROM ' . $this->table . ' ORDER BY id DESC');
            return $stmt->fetchAll();
        }

        $sql = 'SELECT * FROM ' . $this->table . ' ORDER BY id DESC LIMIT :limit';
        if ($offset !== null && $offset > 0) {
            $sql .= ' OFFSET :offset';
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        if ($offset !== null && $offset > 0) {
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Count total rows in the table.
     */
    public function countAll(): int
    {
        if (empty($this->table)) return 0;
        $stmt = $this->pdo->query('SELECT COUNT(*) AS cnt FROM ' . $this->table);
        $row = $stmt->fetch();
        return (int) ($row['cnt'] ?? 0);
    }

    public function getById(int $id): ?array
    {
        if (empty($this->table)) return null;
        $stmt = $this->pdo->prepare('SELECT * FROM ' . $this->table . ' WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function add(array $data): int
    {
        $data = $this->filterData($data);
        if (empty($data) || empty($this->table)) return 0;
        $cols = array_keys($data);
        $placeholders = array_map(fn($c) => ':' . $c, $cols);
        $sql = 'INSERT INTO ' . $this->table . ' (' . implode(', ', $cols) . ') VALUES (' . implode(', ', $placeholders) . ')';
        $stmt = $this->pdo->prepare($sql);
        $params = [];
        foreach ($data as $k => $v) $params[':' . $k] = $v;
        $stmt->execute($params);
        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $data = $this->filterData($data);
        if (empty($data) || empty($this->table)) return false;
        $sets = [];
        foreach (array_keys($data) as $col) $sets[] = $col . ' = :' . $col;
        $sql = 'UPDATE ' . $this->table . ' SET ' . implode(', ', $sets) . ' WHERE id = :id';
        $stmt = $this->pdo->prepare($sql);
        $params = [];
        foreach ($data as $k => $v) $params[':' . $k] = $v;
        $params[':id'] = $id;
        return $stmt->execute($params);
    }

    public function delete(int $id): bool
    {
        if (empty($this->table)) return false;
        $stmt = $this->pdo->prepare('DELETE FROM ' . $this->table . ' WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public function toggleStatus(int $id): bool
    {
        $row = $this->getById($id);
        if (!$row || !isset($row['status'])) return false;
        $new = ($row['status'] === 'Active') ? 'Inactive' : 'Active';
        $stmt = $this->pdo->prepare('UPDATE ' . $this->table . ' SET status = :status WHERE id = :id');
        return $stmt->execute([':status' => $new, ':id' => $id]);
    }
}
