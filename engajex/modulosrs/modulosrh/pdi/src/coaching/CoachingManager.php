<?php
namespace Coaching;

class CoachingManager {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function scheduleSession($userId, $coachId, $sessionDate) {
        $stmt = $this->pdo->prepare('
            INSERT INTO coaching_sessions (user_id, coach_id, session_date, status)
            VALUES (?, ?, ?, \'scheduled\')
        ');
        return $stmt->execute([$userId, $coachId, $sessionDate]);
    }

    public function updateSession($sessionId, $data) {
        $stmt = $this->pdo->prepare('
            UPDATE coaching_sessions
            SET summary = ?, feedback = ?, status = ?
            WHERE id = ?
        ');
        return $stmt->execute([
            $data['summary'],
            $data['feedback'],
            $data['status'],
            $sessionId
        ]);
    }

    public function getUserSessions($userId) {
        $stmt = $this->pdo->prepare('
            SELECT cs.*, u.name as user_name, c.name as coach_name
            FROM coaching_sessions cs
            JOIN users u ON cs.user_id = u.id
            JOIN users c ON cs.coach_id = c.id
            WHERE cs.user_id = ?
            ORDER BY cs.session_date DESC
        ');
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function getCoachSessions($coachId) {
        $stmt = $this->pdo->prepare('
            SELECT cs.*, u.name as user_name, c.name as coach_name
            FROM coaching_sessions cs
            JOIN users u ON cs.user_id = u.id
            JOIN users c ON cs.coach_id = c.id
            WHERE cs.coach_id = ?
            ORDER BY cs.session_date DESC
        ');
        $stmt->execute([$coachId]);
        return $stmt->fetchAll();
    }

    public function addFeedback($sessionId, $feedback) {
        $stmt = $this->pdo->prepare('
            UPDATE coaching_sessions
            SET feedback = ?, status = \'completed\'
            WHERE id = ?
        ');
        return $stmt->execute([$feedback, $sessionId]);
    }

    public function getAvailableCoaches() {
        $stmt = $this->pdo->prepare('
            SELECT id, name, email
            FROM users
            WHERE role = \'coach\'
            ORDER BY name
        ');
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getSessionDetails($sessionId) {
        $stmt = $this->pdo->prepare('
            SELECT cs.*, u.name as user_name, c.name as coach_name,
                   u.email as user_email, c.email as coach_email
            FROM coaching_sessions cs
            JOIN users u ON cs.user_id = u.id
            JOIN users c ON cs.coach_id = c.id
            WHERE cs.id = ?
        ');
        $stmt->execute([$sessionId]);
        return $stmt->fetch();
    }
}
