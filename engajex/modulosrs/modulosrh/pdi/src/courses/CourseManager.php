<?php
namespace Courses;

class CourseManager {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function createCourse($data) {
        $stmt = $this->pdo->prepare('
            INSERT INTO courses (title, category, level, description, content)
            VALUES (?, ?, ?, ?, ?)
        ');
        return $stmt->execute([
            $data['title'],
            $data['category'],
            $data['level'],
            $data['description'],
            $data['content']
        ]);
    }

    public function updateCourse($courseId, $data) {
        $stmt = $this->pdo->prepare('
            UPDATE courses
            SET title = ?, category = ?, level = ?, description = ?, content = ?
            WHERE id = ?
        ');
        return $stmt->execute([
            $data['title'],
            $data['category'],
            $data['level'],
            $data['description'],
            $data['content'],
            $courseId
        ]);
    }

    public function enrollUser($userId, $courseId) {
        $stmt = $this->pdo->prepare('
            INSERT INTO course_enrollments (user_id, course_id)
            VALUES (?, ?)
        ');
        return $stmt->execute([$userId, $courseId]);
    }

    public function updateProgress($userId, $courseId, $progress) {
        $stmt = $this->pdo->prepare('
            UPDATE course_enrollments
            SET progress = ?,
                status = CASE
                    WHEN ? >= 100 THEN \'completed\'
                    WHEN ? > 0 THEN \'in_progress\'
                    ELSE status
                END
            WHERE user_id = ? AND course_id = ?
        ');
        return $stmt->execute([$progress, $progress, $progress, $userId, $courseId]);
    }

    public function getCourse($courseId) {
        $stmt = $this->pdo->prepare('
            SELECT *
            FROM courses
            WHERE id = ?
        ');
        $stmt->execute([$courseId]);
        return $stmt->fetch();
    }

    public function listCourses($filters = []) {
        $sql = 'SELECT * FROM courses WHERE 1=1';
        $params = [];

        if (!empty($filters['category'])) {
            $sql .= ' AND category = ?';
            $params[] = $filters['category'];
        }

        if (!empty($filters['level'])) {
            $sql .= ' AND level = ?';
            $params[] = $filters['level'];
        }

        $sql .= ' ORDER BY title';
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getUserCourses($userId) {
        $stmt = $this->pdo->prepare('
            SELECT c.*, ce.progress, ce.status, ce.certificate_issued
            FROM courses c
            JOIN course_enrollments ce ON c.id = ce.course_id
            WHERE ce.user_id = ?
            ORDER BY ce.created_at DESC
        ');
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function issueCertificate($userId, $courseId) {
        $stmt = $this->pdo->prepare('
            UPDATE course_enrollments
            SET certificate_issued = true
            WHERE user_id = ? AND course_id = ? AND status = \'completed\'
        ');
        return $stmt->execute([$userId, $courseId]);
    }

    public function getCourseProgress($userId, $courseId) {
        $stmt = $this->pdo->prepare('
            SELECT progress, status, certificate_issued
            FROM course_enrollments
            WHERE user_id = ? AND course_id = ?
        ');
        $stmt->execute([$userId, $courseId]);
        return $stmt->fetch();
    }
}
