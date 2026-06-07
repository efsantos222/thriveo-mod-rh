<?php
namespace Admin;

class AdminManager {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getDashboardStats() {
        $stats = [
            'users' => $this->getUserStats(),
            'pdis' => $this->getPDIStats(),
            'courses' => $this->getCourseStats(),
            'coaching' => $this->getCoachingStats()
        ];
        return $stats;
    }

    private function getUserStats() {
        $stmt = $this->pdo->query('
            SELECT 
                COUNT(*) as total_users,
                SUM(CASE WHEN role = \'employee\' THEN 1 ELSE 0 END) as employees,
                SUM(CASE WHEN role = \'manager\' THEN 1 ELSE 0 END) as managers,
                SUM(CASE WHEN role = \'coach\' THEN 1 ELSE 0 END) as coaches
            FROM users
        ');
        return $stmt->fetch();
    }

    private function getPDIStats() {
        $stmt = $this->pdo->query('
            SELECT 
                COUNT(*) as total_pdis,
                SUM(CASE WHEN status = \'draft\' THEN 1 ELSE 0 END) as drafts,
                SUM(CASE WHEN status = \'pending\' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = \'approved\' THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN status = \'completed\' THEN 1 ELSE 0 END) as completed
            FROM pdis
        ');
        return $stmt->fetch();
    }

    private function getCourseStats() {
        $stmt = $this->pdo->query('
            SELECT 
                (SELECT COUNT(*) FROM courses) as total_courses,
                (SELECT COUNT(*) FROM course_enrollments) as total_enrollments,
                (SELECT COUNT(*) FROM course_enrollments WHERE status = \'completed\') as completed_courses,
                (SELECT COUNT(*) FROM course_enrollments WHERE certificate_issued = true) as certificates_issued
        ');
        return $stmt->fetch();
    }

    private function getCoachingStats() {
        $stmt = $this->pdo->query('
            SELECT 
                COUNT(*) as total_sessions,
                SUM(CASE WHEN status = \'scheduled\' THEN 1 ELSE 0 END) as scheduled,
                SUM(CASE WHEN status = \'completed\' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status = \'cancelled\' THEN 1 ELSE 0 END) as cancelled
            FROM coaching_sessions
        ');
        return $stmt->fetch();
    }

    public function listUsers($filters = []) {
        $sql = 'SELECT * FROM users WHERE 1=1';
        $params = [];

        if (!empty($filters['role'])) {
            $sql .= ' AND role = ?';
            $params[] = $filters['role'];
        }

        if (!empty($filters['search'])) {
            $sql .= ' AND (name LIKE ? OR email LIKE ?)';
            $searchTerm = "%{$filters['search']}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $sql .= ' ORDER BY name';
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function updateUserRole($userId, $newRole) {
        $stmt = $this->pdo->prepare('
            UPDATE users
            SET role = ?
            WHERE id = ?
        ');
        return $stmt->execute([$newRole, $userId]);
    }

    public function getAuditLogs($limit = 100) {
        $stmt = $this->pdo->prepare('
            SELECT al.*, u.name as user_name
            FROM audit_logs al
            LEFT JOIN users u ON al.user_id = u.id
            ORDER BY al.created_at DESC
            LIMIT ?
        ');
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    public function addAuditLog($userId, $action, $details) {
        $stmt = $this->pdo->prepare('
            INSERT INTO audit_logs (user_id, action, details)
            VALUES (?, ?, ?)
        ');
        return $stmt->execute([$userId, $action, $details]);
    }
}
