<?php
namespace PDI;

class PDIManager {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function createPDI($userId, $managerId, $data) {
        $stmt = $this->pdo->prepare('
            INSERT INTO pdis (
                user_id, manager_id, short_term_goals, medium_term_goals,
                long_term_goals, competencies, actions, indicators, status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ');

        return $stmt->execute([
            $userId,
            $managerId,
            $data['short_term_goals'],
            $data['medium_term_goals'],
            $data['long_term_goals'],
            $data['competencies'],
            $data['actions'],
            $data['indicators'],
            'draft'
        ]);
    }

    public function updatePDI($pdiId, $data) {
        $stmt = $this->pdo->prepare('
            UPDATE pdis SET
                short_term_goals = ?,
                medium_term_goals = ?,
                long_term_goals = ?,
                competencies = ?,
                actions = ?,
                indicators = ?,
                status = ?
            WHERE id = ?
        ');

        return $stmt->execute([
            $data['short_term_goals'],
            $data['medium_term_goals'],
            $data['long_term_goals'],
            $data['competencies'],
            $data['actions'],
            $data['indicators'],
            $data['status'],
            $pdiId
        ]);
    }

    public function getPDI($pdiId) {
        $stmt = $this->pdo->prepare('
            SELECT p.*, u.name as user_name, m.name as manager_name
            FROM pdis p
            JOIN users u ON p.user_id = u.id
            JOIN users m ON p.manager_id = m.id
            WHERE p.id = ?
        ');
        $stmt->execute([$pdiId]);
        return $stmt->fetch();
    }

    public function getUserPDIs($userId) {
        $stmt = $this->pdo->prepare('
            SELECT p.*, u.name as user_name, m.name as manager_name
            FROM pdis p
            JOIN users u ON p.user_id = u.id
            JOIN users m ON p.manager_id = m.id
            WHERE p.user_id = ?
            ORDER BY p.created_at DESC
        ');
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function getManagerPDIs($managerId) {
        $stmt = $this->pdo->prepare('
            SELECT p.*, u.name as user_name, m.name as manager_name
            FROM pdis p
            JOIN users u ON p.user_id = u.id
            JOIN users m ON p.manager_id = m.id
            WHERE p.manager_id = ?
            ORDER BY p.created_at DESC
        ');
        $stmt->execute([$managerId]);
        return $stmt->fetchAll();
    }

    public function approvePDI($pdiId, $managerId) {
        $stmt = $this->pdo->prepare('
            UPDATE pdis SET status = \'approved\'
            WHERE id = ? AND manager_id = ? AND status = \'pending\'
        ');
        return $stmt->execute([$pdiId, $managerId]);
    }
}
