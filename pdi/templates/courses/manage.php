<?php
// templates/courses/manage.php

use PDI\Models\Course;

// 1. Checks de Segurança
if (!isset($_SESSION['user']) || !$_SESSION['user']['is_admin']) {
    header('Location: ?route=home');
    exit;
}

// 2. Carregar dependências manualmente
// Importante: No Linux, o sistema de arquivos é Case-Sensitive.
// O namespace é PDI\Models, mas a pasta é 'models' (minúsculo) e o arquivo é 'Course.php'.
// Um autoloader padrão PSR-4 procuraria em 'Models/Course.php', o que falharia.
// Por isso, fazemos o require manual aqui.
require_once SRC_PATH . '/models/Course.php';

// 3. Instanciar o Model
$courseModel = new Course($pdo);

// ---------------------------------------------------------
// INÍCIO DO OUTPUT BUFFERING
// Todo o HTML gerado aqui será capturado na variável $content
// para ser renderizado dentro do base.php
// ---------------------------------------------------------
ob_start();

try {
    $categories = $courseModel->getCategories();
    $courses = $courseModel->getAll();
} catch (\Exception $e) {
    echo '<div class="alert alert-danger">Erro ao carregar cursos: ' . $e->getMessage() . '</div>';
    $categories = [];
    $courses = [];
}
?>

<!-- Conteúdo da Página -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">Catálogo de Cursos</h2>
        <p class="text-muted">Gerencie os conteúdos educacionais disponíveis.</p>
    </div>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCourseModal">
        <i class="fas fa-plus-circle me-2"></i> Novo Curso
    </button>
</div>

<div class="row">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">Curso</th>
                                <th>Categoria</th>
                                <th>Nível</th>
                                <th>Duração</th>
                                <th class="text-end pe-4">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($courses as $course): ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold text-dark"><?php echo htmlspecialchars($course['title']); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-secondary border">
                                            <?php echo htmlspecialchars($course['category_name']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php
                                        $levelColors = [
                                            'basic' => 'success',
                                            'intermediate' => 'warning',
                                            'advanced' => 'danger'
                                        ];
                                        $color = $levelColors[$course['level']] ?? 'secondary';
                                        ?>
                                        <span
                                            class="badge bg-<?php echo $color; ?> bg-opacity-10 text-<?php echo $color; ?> border border-<?php echo $color; ?> border-opacity-25">
                                            <?php echo ucfirst($course['level']); ?>
                                        </span>
                                    </td>
                                    <td class="text-muted">
                                        <i class="far fa-clock me-1"></i> <?php echo $course['duration_hours']; ?>h
                                    </td>
                                    <td class="text-end pe-4">
                                        <button class="btn btn-sm btn-light text-primary border me-1 edit-course"
                                            data-id="<?php echo $course['id']; ?>" data-bs-toggle="tooltip" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-light text-info border me-1 btn-modules"
                                            data-id="<?php echo $course['id']; ?>" data-bs-toggle="tooltip" title="Módulos"
                                            onclick="loadModules(<?php echo $course['id']; ?>)">
                                            <i class="fas fa-list"></i>
                                        </button>
                                        <button class="btn btn-sm btn-light text-danger border delete-course"
                                            data-id="<?php echo $course['id']; ?>" data-bs-toggle="tooltip" title="Excluir">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Adicionar/Editar Curso -->
<div class="modal fade" id="addCourseModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Novo Curso</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="courseForm">
                    <input type="hidden" name="course_id" id="courseId">

                    <div class="mb-3">
                        <label for="title" class="form-label fw-500">Título</label>
                        <input type="text" class="form-control" id="title" name="title" required
                            placeholder="Ex: Liderança Ágil">
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="category" class="form-label fw-500">Categoria</label>
                            <select class="form-select" id="category" name="category_id" required>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>">
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="level" class="form-label fw-500">Nível</label>
                            <select class="form-select" id="level" name="level" required>
                                <option value="basic">Básico</option>
                                <option value="intermediate">Intermediário</option>
                                <option value="advanced">Avançado</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="duration" class="form-label fw-500">Duração (h)</label>
                            <input type="number" class="form-control" id="duration" name="duration_hours" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label fw-500">Descrição Curta</label>
                        <textarea class="form-control" id="description" name="description" rows="2" required
                            placeholder="Breve resumo do curso..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="content" class="form-label fw-500">Conteúdo Detalhado</label>
                        <div id="editor-container" style="height: 200px; border-radius: 0 0 6px 6px;"></div>
                        <input type="hidden" name="content" id="content">
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary px-4" id="saveCourse">Salvar Curso</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Módulos (Estilo Atualizado) -->
<div class="modal fade" id="modulesModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 bg-light">
                <div>
                    <h5 class="modal-title fw-bold">Conteúdo Programático</h5>
                    <small class="text-muted course-title"></small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div class="p-3 bg-white border-bottom d-flex justify-content-between align-items-center">
                    <span class="text-muted small text-uppercase fw-bold">Módulos Cadastrados</span>
                    <button type="button" class="btn btn-sm btn-primary" id="addModuleBtn">
                        <i class="fas fa-plus me-1"></i> Novo Módulo
                    </button>
                </div>

                <div id="modulesList" class="list-group list-group-flush">
                    <!-- Módulos serão inseridos aqui dinamicamente -->
                </div>

                <!-- Formulário de Módulo -->
                <div id="moduleForm" class="d-none bg-light p-4">
                    <form>
                        <input type="hidden" name="module_id" id="moduleId">
                        <input type="hidden" name="course_id" id="moduleCourseId">

                        <h6 class="fw-bold mb-3 text-primary">Editar Módulo</h6>

                        <div class="row">
                            <div class="col-md-9 mb-3">
                                <label for="moduleTitle" class="form-label fw-500">Título do Módulo</label>
                                <input type="text" class="form-control" id="moduleTitle" name="title" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="moduleOrder" class="form-label fw-500">Ordem</label>
                                <input type="number" class="form-control" id="moduleOrder" name="order_number" required
                                    min="1">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="moduleDescription" class="form-label fw-500">Resumo</label>
                            <textarea class="form-control" id="moduleDescription" name="description"
                                rows="2"></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="moduleContent" class="form-label fw-500">Conteúdo do Módulo</label>
                            <div id="moduleEditor" style="height: 200px; background: white;"></div>
                            <input type="hidden" name="content" id="moduleContent">
                        </div>

                        <div class="text-end mt-4">
                            <button type="button" class="btn btn-white border me-2"
                                id="cancelModuleBtn">Cancelar</button>
                            <button type="submit" class="btn btn-primary px-4">Salvar Módulo</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quill Editor CSS (CDN) -->
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<!-- Quill JS -->
<script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Inicializar Tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        })

        // Debug para verificar se o evento está sendo disparado
        console.log('DOM carregado');

        // Inicializar modal
        const modulesModal = new bootstrap.Modal(document.getElementById('modulesModal'));

        // Função para carregar módulos
        async function loadModules(courseId) {
            console.log('Carregando módulos para curso:', courseId);
            try {
                const response = await fetch('?route=courses/modules&id=' + courseId);
                const responseText = await response.text();

                let data;
                try {
                    data = JSON.parse(responseText);
                } catch (e) {
                    console.error('Erro ao parsear JSON:', e);
                    console.error('Resposta recebida:', responseText);
                    throw new Error('Resposta inválida do servidor');
                }

                if (data.success) {
                    document.querySelector('#modulesModal .course-title').textContent = data.course.title;
                    document.getElementById('moduleCourseId').value = courseId;

                    // Limpar e preencher lista de módulos
                    const modulesList = document.getElementById('modulesList');
                    modulesList.innerHTML = '';

                    if (data.modules.length === 0) {
                        modulesList.innerHTML = '<div class="p-4 text-center text-muted">Nenhum módulo cadastrado ainda.</div>';
                    }

                    data.modules.forEach(module => {
                        const item = document.createElement('div');
                        item.className = 'list-group-item d-flex justify-content-between align-items-center py-3';
                        item.innerHTML = `
                        <div class="d-flex align-items-center">
                            <span class="badge bg-light text-dark border me-3 rounded-pill" style="width:30px;height:30px;display:flex;align-items:center;justify-content:center;">${module.order_number || module.order_index}</span>
                            <div>
                                <h6 class="mb-0 fw-bold">${module.title}</h6>
                                <small class="text-muted text-truncate" style="max-width: 300px; display:inline-block;">${module.description || ''}</small>
                            </div>
                        </div>
                        <div>
                            <button type="button" class="btn btn-sm btn-light text-primary border me-1 edit-module" 
                                    data-id="${module.id}">
                                <i class="fas fa-pencil-alt"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-light text-danger border delete-module" 
                                    data-id="${module.id}">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    `;
                        modulesList.appendChild(item);
                    });

                    // Esconder formulário e mostrar lista
                    const moduleFormDiv = document.getElementById('moduleForm');
                    moduleFormDiv.classList.add('d-none');
                    modulesList.classList.remove('d-none');
                    const addModuleBtn = document.getElementById('addModuleBtn');
                    addModuleBtn.classList.remove('d-none');

                    modulesModal.show();
                } else {
                    throw new Error(data.message || 'Erro ao carregar módulos');
                }
            } catch (error) {
                console.error('Erro:', error);
                alert('Erro ao carregar módulos: ' + error.message);
            }
        }

        // Expor globalmente para ser chamado no onclick
        window.loadModules = loadModules;

        // Inicializar Quill Principal
        var quill = new Quill('#editor-container', {
            theme: 'snow',
            placeholder: 'Escreva o conteúdo do curso aqui...',
            modules: {
                toolbar: [
                    [{ 'header': [1, 2, 3, false] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    ['link', 'blockquote', 'code-block'],
                    [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                    ['clean']
                ]
            }
        });

        const courseForm = document.getElementById('courseForm');
        const saveCourseBtn = document.getElementById('saveCourse');
        const addCourseModal = new bootstrap.Modal(document.getElementById('addCourseModal'));

        saveCourseBtn.addEventListener('click', async function () {
            if (!courseForm.checkValidity()) {
                courseForm.reportValidity();
                return;
            }

            try {
                const formData = new FormData(courseForm);
                const courseId = formData.get('course_id');
                const url = courseId ? '?route=courses/update' : '?route=courses/create';

                // Atualizar o conteúdo do Quill antes de enviar
                const content = quill.root.innerHTML;
                formData.set('content', content);

                const response = await fetch(url, {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();
                if (data.success) {
                    // Fechar modal e recarregar
                    addCourseModal.hide();
                    window.location.reload();
                } else {
                    alert('Erro: ' + data.message);
                }
            } catch (error) {
                console.error('Erro:', error);
                alert('Erro ao processar requisição');
            }
        });

        // Carregar dados do curso para edição
        window.loadCourseData = async function (courseId) {
            try {
                const response = await fetch('?route=courses/get&id=' + courseId);
                const data = await response.json();

                if (data.success) {
                    document.getElementById('courseId').value = data.id;
                    document.getElementById('title').value = data.title;
                    document.getElementById('category').value = data.category_id;
                    document.getElementById('level').value = data.level;
                    document.getElementById('duration').value = data.duration_hours;
                    document.getElementById('description').value = data.description;
                    quill.root.innerHTML = data.content;

                    // Atualizar título do modal
                    document.querySelector('#addCourseModal .modal-title').textContent = 'Editar Curso';

                    // Abrir o modal
                    addCourseModal.show();
                } else {
                    alert('Erro ao carregar dados do curso: ' + (data.message || 'Erro desconhecido'));
                }
            } catch (error) {
                console.error('Erro:', error);
                alert('Erro ao carregar dados do curso');
            }
        };

        document.querySelectorAll('.edit-course').forEach(button => {
            button.addEventListener('click', async function () {
                try {
                    const courseId = this.dataset.id;
                    await loadCourseData(courseId);
                } catch (error) {
                    console.error('Erro:', error);
                    alert('Erro ao carregar dados do curso');
                }
            });
        });

        document.querySelectorAll('.delete-course').forEach(button => {
            button.addEventListener('click', async function () {
                if (confirm('Tem certeza que deseja excluir este curso?')) {
                    try {
                        const courseId = this.dataset.id;
                        const response = await fetch(`?route=courses/delete/${courseId}`, {
                            method: 'POST'
                        });

                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }

                        const data = await response.json();
                        if (data.success) {
                            location.reload();
                        } else {
                            alert('Erro ao excluir o curso: ' + data.message);
                        }
                    } catch (error) {
                        console.error('Erro:', error);
                        alert('Erro ao excluir o curso: ' + error.message);
                    }
                }
            });
        });

        // Inicializar modal de módulos (Eventos já delegados)
        const moduleFormDiv = document.getElementById('moduleForm');
        const moduleFormElement = moduleFormDiv.querySelector('form');
        const addModuleBtn = document.getElementById('addModuleBtn');
        const cancelModuleBtn = document.getElementById('cancelModuleBtn');

        // Inicializar editor Quill para módulos
        const moduleQuill = new Quill('#moduleEditor', {
            theme: 'snow',
            modules: {
                toolbar: [
                    ['bold', 'italic', 'underline'],
                    ['link'],
                    [{ 'list': 'ordered' }, { 'list': 'bullet' }]
                ]
            }
        });

        // Mostrar formulário de novo módulo
        addModuleBtn.addEventListener('click', () => {
            moduleFormElement.reset();
            document.getElementById('moduleId').value = '';
            moduleQuill.root.innerHTML = '';
            document.getElementById('modulesList').classList.add('d-none');
            addModuleBtn.classList.add('d-none');
            moduleFormDiv.classList.remove('d-none');
        });

        // Cancelar edição de módulo
        cancelModuleBtn.addEventListener('click', () => {
            moduleFormDiv.classList.add('d-none');
            document.getElementById('modulesList').classList.remove('d-none');
            addModuleBtn.classList.remove('d-none');
        });

        // Salvar módulo
        moduleFormElement.addEventListener('submit', async (e) => {
            e.preventDefault();

            const formData = new FormData(moduleFormElement);
            const moduleId = formData.get('module_id');

            // Adicionar o conteúdo do editor
            formData.set('content', moduleQuill.root.innerHTML);

            try {
                const response = await fetch(
                    '?route=courses/' + (moduleId ? 'updateModule' : 'createModule'),
                    {
                        method: 'POST',
                        body: formData
                    }
                );

                const responseText = await response.text();

                let data;
                try {
                    data = JSON.parse(responseText);
                } catch (e) {
                    console.error('Erro ao parsear resposta:', e);
                    throw new Error('Resposta inválida do servidor');
                }

                if (data.success) {
                    // Reload local
                    loadModules(formData.get('course_id'));
                } else {
                    throw new Error(data.message || 'Erro ao processar requisição');
                }
            } catch (error) {
                console.error('Erro:', error);
                alert('Erro ao salvar módulo: ' + error.message);
            }
        });

        // Editar módulo (Delegado)
        document.getElementById('modulesList').addEventListener('click', async (e) => {
            const editBtn = e.target.closest('.edit-module');
            if (!editBtn) return;

            try {
                const response = await fetch('?route=courses/getModule&id=' + editBtn.dataset.id);
                const data = await response.json();

                if (data.success) {
                    document.getElementById('moduleId').value = data.module.id;
                    document.getElementById('moduleTitle').value = data.module.title;
                    document.getElementById('moduleDescription').value = data.module.description;
                    document.getElementById('moduleOrder').value = data.module.order_number || data.module.order_index;
                    moduleQuill.root.innerHTML = data.module.content;

                    document.getElementById('modulesList').classList.add('d-none');
                    addModuleBtn.classList.add('d-none');
                    moduleFormDiv.classList.remove('d-none');
                }
            } catch (error) {
                console.error('Erro:', error);
                alert('Erro ao carregar dados do módulo');
            }
        });

        // Excluir módulo (Delegado)
        document.getElementById('modulesList').addEventListener('click', async (e) => {
            const deleteBtn = e.target.closest('.delete-module');
            if (!deleteBtn) return;

            if (confirm('Tem certeza que deseja excluir este módulo?')) {
                try {
                    const response = await fetch('?route=courses/deleteModule', {
                        method: 'POST',
                        body: JSON.stringify({ id: deleteBtn.dataset.id })
                    });

                    const data = await response.json();
                    if (data.success) {
                        loadModules(document.getElementById('moduleCourseId').value);
                    } else {
                        alert('Erro: ' + data.message);
                    }
                } catch (error) {
                    console.error('Erro:', error);
                    alert('Erro ao excluir módulo');
                }
            }
        });
    });
</script>

<?php
// ---------------------------------------------------------
// FIM DO OUTPUT BUFFERING E INCLUSÃO DO BASE
// ---------------------------------------------------------
$content = ob_get_clean();
$pageTitle = "Gerenciar Cursos";
require_once TEMPLATES_PATH . '/base.php';
?>