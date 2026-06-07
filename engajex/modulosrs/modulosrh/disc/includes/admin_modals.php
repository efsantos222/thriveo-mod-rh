<?php
// Array com os tipos de teste e suas categorias
$test_types = [
    'disc' => ['D', 'I', 'S', 'C'],
    'mbti' => ['E/I', 'S/N', 'T/F', 'J/P'],
    'bigfive' => ['O', 'C', 'E', 'A', 'N'],
    'jss' => ['PAY', 'PRO', 'SUP', 'BEN', 'REW', 'OPR', 'COW', 'NAT', 'COM']
];

// Gerar modais para cada tipo de teste
foreach ($test_types as $type => $categories) {
    $type_upper = strtoupper($type);
    $type_title = $type === 'jss' ? 'JSS' : ucfirst($type);
?>
    <!-- Modal Adicionar Questão <?php echo $type_title; ?> -->
    <div class="modal fade" id="add<?php echo ucfirst($type); ?>QuestionModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-plus-circle"></i>
                        Adicionar Questão <?php echo $type_title; ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_<?php echo $type; ?>_question">
                        
                        <div class="mb-3">
                            <label for="<?php echo $type; ?>_question_text" class="form-label">Texto da Questão</label>
                            <textarea class="form-control" id="<?php echo $type; ?>_question_text" name="question_text" rows="3" required></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="<?php echo $type; ?>_category" class="form-label">Categoria</label>
                            <select class="form-select" id="<?php echo $type; ?>_category" name="category" required>
                                <option value="">Selecione...</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category; ?>"><?php echo $category; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Adicionar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Editar Questão <?php echo $type_title; ?> -->
    <div class="modal fade" id="edit<?php echo ucfirst($type); ?>QuestionModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-pencil"></i>
                        Editar Questão <?php echo $type_title; ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit_<?php echo $type; ?>_question">
                        <input type="hidden" name="question_id" id="edit_<?php echo $type; ?>_question_id">
                        
                        <div class="mb-3">
                            <label for="edit_<?php echo $type; ?>_question_text" class="form-label">Texto da Questão</label>
                            <textarea class="form-control" id="edit_<?php echo $type; ?>_question_text" name="question_text" rows="3" required></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_<?php echo $type; ?>_category" class="form-label">Categoria</label>
                            <select class="form-select" id="edit_<?php echo $type; ?>_category" name="category" required>
                                <option value="">Selecione...</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category; ?>"><?php echo $category; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php } ?>

<!-- Modal Adicionar Avaliador(a) -->
<div class="modal fade" id="addSelectorModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-person-plus"></i>
                    Adicionar Avaliador(a)
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add_selector">
                    
                    <div class="mb-3">
                        <label for="selector_name" class="form-label">Nome</label>
                        <input type="text" class="form-control" id="selector_name" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="selector_email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="selector_email" name="email" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="selector_password" class="form-label">Senha</label>
                        <input type="password" class="form-control" id="selector_password" name="password" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Adicionar</button>
                </div>
            </form>
        </div>
    </div>
</div>
