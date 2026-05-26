<?php
// admin/classes/Subject.php
// PDO-backed Subject model used by admin/subjects.php

require_once __DIR__ . '/BaseModel.php';

class Subject extends BaseModel
{
    protected string $table = 'subjects';
    protected array $allowedFields = ['code','name','teacher','units','status'];

    public function stats(): array
    {
        $stmt = $this->pdo->query('SELECT COUNT(*) AS total, COALESCE(SUM(units),0) AS total_units, SUM(status = "Active") AS active_count FROM subjects');
        $row = $stmt->fetch();
        return [
            'total' => (int) ($row['total'] ?? 0),
            'total_units' => (int) ($row['total_units'] ?? 0),
            'active' => (int) ($row['active_count'] ?? 0),
        ];
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM subjects WHERE id = ?');
        return $stmt->execute([$id]);
    }
}
