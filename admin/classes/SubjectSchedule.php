<?php
// admin/classes/SubjectSchedule.php
require_once __DIR__ . '/BaseModel.php';

class SubjectSchedule extends BaseModel
{
    protected string $table = 'subject_schedules';
    protected array $allowedFields = ['subject_id', 'time_slot', 'days', 'type'];

    public function getBySubjectId(int $subject_id): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM subject_schedules WHERE subject_id = ? ORDER BY id ASC');
        $stmt->execute([$subject_id]);
        return $stmt->fetchAll();
    }
    
    public function deleteBySubjectId(int $subject_id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM subject_schedules WHERE subject_id = ?');
        return $stmt->execute([$subject_id]);
    }
}
