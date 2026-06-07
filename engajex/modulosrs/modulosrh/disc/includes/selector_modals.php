<?php
// Array com os tipos de teste
$test_types = [
    'disc' => 'DISC',
    'mbti' => 'MBTI',
    'bigfive' => 'Big Five',
    'jss' => 'JSS'
];
?>

<!-- Modal Adicionar Candidato -->
<div class="modal fade" id="addCandidateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-person-plus"></i>
                    Adicionar Candidato
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="selector_panel.php" method="post">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add_candidate">
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Nome</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="cargo" class="form-label">Cargo</label>
                        <input type="text" class="form-control" id="cargo" name="cargo" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label d-block">Testes</label>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="tests[]" value="disc" id="test_disc">
                            <label class="form-check-label" for="test_disc">DISC</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="tests[]" value="mbti" id="test_mbti">
                            <label class="form-check-label" for="test_mbti">MBTI</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="tests[]" value="bigfive" id="test_bigfive">
                            <label class="form-check-label" for="test_bigfive">Big Five</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="tests[]" value="jss" id="test_jss">
                            <label class="form-check-label" for="test_jss">JSS</label>
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        Uma senha será gerada automaticamente e exibida após o cadastro.
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

<!-- Modal Editar Candidato -->
<div class="modal fade" id="editCandidateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-pencil"></i>
                    Editar Candidato
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="selector_panel.php" method="post">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit_candidate">
                    <input type="hidden" name="candidate_id" id="edit_candidate_id">
                    
                    <div class="mb-3">
                        <label for="edit_name" class="form-label">Nome</label>
                        <input type="text" class="form-control" id="edit_name" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="edit_email" name="email" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_cargo" class="form-label">Cargo</label>
                        <input type="text" class="form-control" id="edit_cargo" name="cargo" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label d-block">Testes</label>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="tests[]" value="disc" id="edit_test_disc">
                            <label class="form-check-label" for="edit_test_disc">DISC</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="tests[]" value="mbti" id="edit_test_mbti">
                            <label class="form-check-label" for="edit_test_mbti">MBTI</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="tests[]" value="bigfive" id="edit_test_bigfive">
                            <label class="form-check-label" for="edit_test_bigfive">Big Five</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="tests[]" value="jss" id="edit_test_jss">
                            <label class="form-check-label" for="edit_test_jss">JSS</label>
                        </div>
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

<!-- Modal Excluir Candidato -->
<div class="modal fade" id="deleteCandidateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-trash"></i>
                    Excluir Candidato
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="selector_panel.php" method="post">
                <div class="modal-body">
                    <input type="hidden" name="action" value="delete_candidate">
                    <input type="hidden" name="candidate_id" id="delete_candidate_id">
                    <p>Tem certeza que deseja excluir este candidato?</p>
                    <p class="text-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Esta ação não pode ser desfeita.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Excluir</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Alterar Senha do Candidato -->
<div class="modal fade" id="changePasswordModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-key"></i>
                    Alterar Senha do Candidato
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="selector_panel.php" method="post">
                <div class="modal-body">
                    <input type="hidden" name="action" value="change_password">
                    <input type="hidden" name="candidate_id" id="change_password_candidate_id">
                    
                    <div class="mb-3">
                        <label for="new_password" class="form-label">Nova Senha</label>
                        <input type="text" class="form-control" id="new_password" name="new_password" required 
                               minlength="6" maxlength="20" pattern="[a-zA-Z0-9]+" 
                               title="A senha deve conter entre 6 e 20 caracteres alfanuméricos">
                        <div class="form-text">A senha deve conter entre 6 e 20 caracteres alfanuméricos.</div>
                    </div>

                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        Certifique-se de informar a nova senha ao candidato de forma segura.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Alterar Senha</button>
                </div>
            </form>
        </div>
    </div>
</div>
