<?php
require_once SRC_PATH . '/models/Course.php';

// Verificar autenticação
if (!isset($_SESSION['user'])) {
    header('Location: ?route=login');
    exit;
}

if (isset($_GET['route'])) {
    $route = $_GET['route'];
    $parts = explode('/', $route);
    
    if ($parts[0] !== 'courses') {
        header('HTTP/1.1 404 Not Found');
        exit('Rota não encontrada');
    }

    $action = $_GET['action'] ?? '';
    error_log("Course controller - Action: " . $action); // Debug

    try {
        global $pdo;
        $courseModel = new \PDI\Models\Course($pdo);
        $response = ['success' => false];

        switch ($action) {
            case 'create':
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    error_log("Processing course creation");
                    $data = [
                        'title' => $_POST['title'] ?? '',
                        'category_id' => $_POST['category_id'] ?? '',
                        'level' => $_POST['level'] ?? '',
                        'description' => $_POST['description'] ?? '',
                        'content' => $_POST['content'] ?? '',
                        'duration_hours' => intval($_POST['duration_hours'] ?? 0)
                    ];

                    // Validar dados
                    if (empty($data['title'])) {
                        throw new \Exception("O título é obrigatório");
                    }
                    if (empty($data['category_id'])) {
                        throw new \Exception("A categoria é obrigatória");
                    }
                    if (empty($data['level'])) {
                        throw new \Exception("O nível é obrigatório");
                    }
                    if ($data['duration_hours'] <= 0) {
                        throw new \Exception("A duração deve ser maior que zero");
                    }

                    if ($courseModel->create($data)) {
                        $response = ['success' => true, 'message' => 'Curso criado com sucesso!'];
                    } else {
                        throw new \Exception("Erro ao criar o curso no banco de dados");
                    }
                }
                break;

            case 'get':
                $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
                if ($id > 0) {
                    $course = $courseModel->get($id);
                    if ($course) {
                        $response = ['success' => true] + $course;
                    } else {
                        throw new \Exception("Curso não encontrado");
                    }
                } else {
                    throw new \Exception("ID do curso inválido");
                }
                break;

            case 'update':
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $id = $_POST['course_id'] ?? 0;
                    $data = [
                        'title' => $_POST['title'] ?? '',
                        'category_id' => $_POST['category_id'] ?? '',
                        'level' => $_POST['level'] ?? '',
                        'description' => $_POST['description'] ?? '',
                        'content' => $_POST['content'] ?? '',
                        'duration_hours' => intval($_POST['duration_hours'] ?? 0)
                    ];

                    if ($courseModel->update($id, $data)) {
                        $response = ['success' => true, 'message' => 'Curso atualizado com sucesso!'];
                    } else {
                        throw new \Exception("Erro ao atualizar o curso");
                    }
                }
                break;

            case 'delete':
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $id = isset($_GET[2]) ? intval($_GET[2]) : 0;
                    if ($id > 0) {
                        if ($courseModel->delete($id)) {
                            $response = ['success' => true, 'message' => 'Curso excluído com sucesso!'];
                        } else {
                            throw new \Exception("Erro ao excluir o curso");
                        }
                    } else {
                        throw new \Exception("ID do curso inválido");
                    }
                }
                break;

            case 'modules':
                error_log("Loading modules for course"); // Debug
                $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
                if ($id > 0) {
                    $course = $courseModel->get($id);
                    if ($course) {
                        $modules = $courseModel->getModules($id);
                        $response = [
                            'success' => true,
                            'course' => $course,
                            'modules' => $modules
                        ];
                        error_log("Modules loaded: " . json_encode($response)); // Debug
                    } else {
                        throw new \Exception("Curso não encontrado");
                    }
                } else {
                    throw new \Exception("ID do curso inválido");
                }
                break;

            case 'getModule':
                $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
                if ($id > 0) {
                    $module = $courseModel->getModule($id);
                    if ($module) {
                        $response = [
                            'success' => true,
                            'module' => $module
                        ];
                    } else {
                        throw new \Exception("Módulo não encontrado");
                    }
                } else {
                    throw new \Exception("ID do módulo inválido");
                }
                break;

            case 'createModule':
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    error_log("Creating module - POST data: " . print_r($_POST, true));
                    
                    $data = [
                        'title' => $_POST['title'] ?? '',
                        'description' => $_POST['description'] ?? '',
                        'content' => $_POST['content'] ?? '',
                        'order_number' => intval($_POST['order_number'] ?? 1)
                    ];

                    $courseId = intval($_POST['course_id'] ?? 0);
                    if ($courseId <= 0) {
                        throw new \Exception("ID do curso inválido");
                    }

                    if (empty($data['title'])) {
                        throw new \Exception("O título é obrigatório");
                    }

                    try {
                        $courseModel->createModule($courseId, $data);
                        $response = ['success' => true, 'message' => 'Módulo criado com sucesso!'];
                    } catch (\Exception $e) {
                        error_log("Error creating module: " . $e->getMessage());
                        throw new \Exception($e->getMessage());
                    }
                }
                break;

            case 'updateModule':
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $moduleId = intval($_POST['module_id'] ?? 0);
                    if ($moduleId <= 0) {
                        throw new \Exception("ID do módulo inválido");
                    }

                    $data = [
                        'title' => $_POST['title'] ?? '',
                        'description' => $_POST['description'] ?? '',
                        'content' => $_POST['content'] ?? '',
                        'order_number' => intval($_POST['order_number'] ?? 1)
                    ];

                    if (empty($data['title'])) {
                        throw new \Exception("O título é obrigatório");
                    }

                    if ($courseModel->updateModule($moduleId, $data)) {
                        $response = ['success' => true, 'message' => 'Módulo atualizado com sucesso!'];
                    } else {
                        throw new \Exception("Erro ao atualizar o módulo");
                    }
                }
                break;

            case 'deleteModule':
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $data = json_decode(file_get_contents('php://input'), true);
                    $moduleId = intval($data['id'] ?? 0);
                    
                    if ($moduleId <= 0) {
                        throw new \Exception("ID do módulo inválido");
                    }

                    if ($courseModel->deleteModule($moduleId)) {
                        $response = ['success' => true, 'message' => 'Módulo excluído com sucesso!'];
                    } else {
                        throw new \Exception("Erro ao excluir o módulo");
                    }
                }
                break;

            default:
                throw new \Exception("Ação inválida");
        }
    } catch (\Exception $e) {
        error_log("Error in courses controller: " . $e->getMessage()); // Debug
        $response = [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }

    // Garantir que nenhum conteúdo foi enviado antes
    if (headers_sent($filename, $linenum)) {
        error_log("Headers already sent in $filename on line $linenum"); // Debug
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
