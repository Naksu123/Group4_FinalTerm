<?php

require_once __DIR__ . '/BaseModel.php';

class Grade extends BaseModel
{
    protected string $table = 'grades';
    protected array $allowedFields = ['subject_id','prelim','midterm','final','grade','remarks','status'];

    public function stats(): array
    {
        $stmt = $this->pdo->query('SELECT AVG(grade) AS avg_grade, MAX(grade) AS highest, MIN(grade) AS lowest FROM grades');
        $row = $stmt->fetch();
        return [
            'avg_grade' => isset($row['avg_grade']) ? (float)$row['avg_grade'] : 0,
            'highest' => isset($row['highest']) ? (int)$row['highest'] : 0,
            'lowest' => isset($row['lowest']) ? (int)$row['lowest'] : 0,
        ];
    }

    /**
     * Override getAll to JOIN subjects table and fetch subject name
     */
    public function getAll(?int $limit = null, ?int $offset = null): array
    {
        $sql = 'SELECT grades.*, subjects.name AS subject_name, subjects.code AS subject_code 
                FROM grades 
                LEFT JOIN subjects ON grades.subject_id = subjects.id 
                ORDER BY grades.id DESC';

        if ($limit !== null) {
            $sql .= ' LIMIT :limit';
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

        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }
}
