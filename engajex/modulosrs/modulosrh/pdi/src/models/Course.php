<?php
namespace PDI\Models;

class Course {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getCategories() {
        try {
            $stmt = $this->pdo->query('SELECT id, name, description FROM course_categories ORDER BY name');
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            error_log("Error getting categories: " . $e->getMessage());
            return [];
        }
    }

    public function create($data) {
        try {
            error_log("Creating course with data: " . print_r($data, true));
            
            // Validar dados obrigatórios
            if (empty($data['title']) || empty($data['category_id']) || empty($data['level'])) {
                error_log("Missing required fields");
                return false;
            }

            $stmt = $this->pdo->prepare('
                INSERT INTO courses (
                    title, category_id, level, description, 
                    content, duration_hours
                ) VALUES (?, ?, ?, ?, ?, ?)
            ');
            
            $result = $stmt->execute([
                $data['title'],
                $data['category_id'],
                $data['level'],
                $data['description'] ?? '',
                $data['content'] ?? '',
                intval($data['duration_hours'])
            ]);

            if (!$result) {
                error_log("SQL Error: " . print_r($stmt->errorInfo(), true));
            }

            return $result;
        } catch (\PDOException $e) {
            error_log("Error creating course: " . $e->getMessage());
            return false;
        }
    }

    public function getAll($filters = []) {
        try {
            $sql = '
                SELECT c.*, cc.name as category_name 
                FROM courses c 
                LEFT JOIN course_categories cc ON c.category_id = cc.id 
            ';

            $where = [];
            $params = [];

            if (!empty($filters['category_id'])) {
                $where[] = 'c.category_id = ?';
                $params[] = $filters['category_id'];
            }

            if (!empty($filters['level'])) {
                $where[] = 'c.level = ?';
                $params[] = $filters['level'];
            }

            if (!empty($where)) {
                $sql .= ' WHERE ' . implode(' AND ', $where);
            }

            $sql .= ' ORDER BY c.title';

            error_log("SQL Query: " . $sql);
            error_log("Params: " . print_r($params, true));

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            error_log("Error getting courses: " . $e->getMessage());
            return [];
        }
    }

    public function get($id) {
        try {
            $stmt = $this->pdo->prepare('
                SELECT c.*, cc.name as category_name,
                       c.content, c.description, c.duration_hours 
                FROM courses c 
                LEFT JOIN course_categories cc ON c.category_id = cc.id 
                WHERE c.id = ?
            ');
            $stmt->execute([$id]);
            $course = $stmt->fetch();
            
            if ($course) {
                error_log("Course content: " . substr($course['content'], 0, 100)); // Debug log
            }
            
            return $course;
        } catch (\PDOException $e) {
            error_log("Error getting course: " . $e->getMessage());
            return null;
        }
    }

    public function update($id, $data) {
        try {
            if (empty($id) || empty($data['title']) || empty($data['category_id']) || empty($data['level'])) {
                return false;
            }

            error_log("Updating course content. Length: " . strlen($data['content'] ?? ''));
            error_log("Content preview: " . substr($data['content'] ?? '', 0, 100));

            $stmt = $this->pdo->prepare('
                UPDATE courses 
                SET title = ?, category_id = ?, level = ?, 
                    description = ?, content = ?, duration_hours = ? 
                WHERE id = ?
            ');

            $result = $stmt->execute([
                $data['title'],
                $data['category_id'],
                $data['level'],
                $data['description'] ?? '',
                $data['content'] ?? '',
                intval($data['duration_hours']),
                $id
            ]);

            if (!$result) {
                error_log("SQL Error: " . print_r($stmt->errorInfo(), true));
            }

            return $result;
        } catch (\PDOException $e) {
            error_log("Error updating course: " . $e->getMessage());
            return false;
        }
    }

    public function delete($id) {
        try {
            $stmt = $this->pdo->prepare('UPDATE courses SET active = 0 WHERE id = ?');
            return $stmt->execute([$id]);
        } catch (\PDOException $e) {
            error_log("Error deleting course: " . $e->getMessage());
            return false;
        }
    }

    public function createModule($courseId, $data) {
        try {
            error_log("Creating module for course " . $courseId);
            error_log("Module data: " . print_r($data, true));

            $stmt = $this->pdo->prepare('
                INSERT INTO course_modules (
                    course_id, title, description, 
                    content, order_index
                ) VALUES (
                    :course_id, :title, :description, 
                    :content, :order_index
                )
            ');
            
            $params = [
                ':course_id' => $courseId,
                ':title' => $data['title'],
                ':description' => $data['description'] ?? '',
                ':content' => $data['content'] ?? '',
                ':order_index' => intval($data['order_number'] ?? 1) 
            ];

            error_log("Executing insert with params: " . print_r($params, true));
            $result = $stmt->execute($params);

            if (!$result) {
                $error = $stmt->errorInfo();
                error_log("SQL Error: " . print_r($error, true));
                throw new \Exception("Erro ao criar módulo: " . $error[2]);
            }

            return $result;
        } catch (\PDOException $e) {
            error_log("PDO Error: " . $e->getMessage());
            throw new \Exception("Erro ao criar módulo: " . $e->getMessage());
        } catch (\Exception $e) {
            error_log("General Error: " . $e->getMessage());
            throw $e;
        }
    }

    public function updateModule($moduleId, $data) {
        try {
            $stmt = $this->pdo->prepare('
                UPDATE course_modules 
                SET title = ?, description = ?, 
                    content = ?, order_number = ?
                WHERE id = ?
            ');

            return $stmt->execute([
                $data['title'],
                $data['description'] ?? '',
                $data['content'] ?? '',
                $data['order_number'],
                $moduleId
            ]);
        } catch (\PDOException $e) {
            error_log("Error updating module: " . $e->getMessage());
            return false;
        }
    }

    public function deleteModule($moduleId) {
        try {
            $stmt = $this->pdo->prepare('DELETE FROM course_modules WHERE id = ?');
            return $stmt->execute([$moduleId]);
        } catch (\PDOException $e) {
            error_log("Error deleting module: " . $e->getMessage());
            return false;
        }
    }

    public function getModules($courseId) {
        try {
            $stmt = $this->pdo->prepare('
                SELECT id, course_id, title, description, 
                       content, order_index, created_at, updated_at
                FROM course_modules 
                WHERE course_id = ? 
                ORDER BY order_index ASC
            ');
            
            $stmt->execute([$courseId]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error getting modules: " . $e->getMessage());
            throw new \Exception("Erro ao carregar módulos: " . $e->getMessage());
        }
    }

    public function getModule($id) {
        try {
            $stmt = $this->pdo->prepare('SELECT * FROM course_modules WHERE id = ?');
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (\PDOException $e) {
            error_log("Error getting module: " . $e->getMessage());
            return null;
        }
    }
}
