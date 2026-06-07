<?php
function renderMenu() {
    $current_page = basename($_SERVER['PHP_SELF']);
    $is_admin = isset($_SESSION['admin_authenticated']) && $_SESSION['admin_authenticated'];
    $is_superadmin = isset($_SESSION['superadmin_authenticated']) && $_SESSION['superadmin_authenticated'];
    
    if (!$is_admin && !$is_superadmin) {
        return; // Não mostrar menu para usuários não autenticados como admin/superadmin
    }
    ?>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="#">Sistema DISC/MBTI</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <?php if ($is_superadmin): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page === 'superadmin_panel.php' ? 'active' : ''; ?>" 
                           href="superadmin_panel.php">Painel Principal</a>
                    </li>
                    <?php endif; ?>
                    
                    <?php if ($is_admin || $is_superadmin): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            Candidatos
                        </a>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item <?php echo $current_page === 'view_candidates.php' ? 'active' : ''; ?>" 
                                   href="view_candidates.php">
                                    <i class="bi bi-people"></i> Candidatos DISC
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item <?php echo $current_page === 'view_candidates_mbti.php' ? 'active' : ''; ?>" 
                                   href="view_candidates_mbti.php">
                                    <i class="bi bi-people"></i> Candidatos MBTI
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item <?php echo $current_page === 'view_candidates_bigfive.php' ? 'active' : ''; ?>" 
                                   href="view_candidates_bigfive.php">
                                    <i class="bi bi-people"></i> Candidatos BigFive
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item <?php echo $current_page === 'view_candidates_jss.php' ? 'active' : ''; ?>" 
                                   href="view_candidates_jss.php">
                                    <i class="bi bi-people"></i> Candidatos JSS
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item <?php echo $current_page === 'register_candidate.php' ? 'active' : ''; ?>" 
                                   href="register_candidate.php">
                                    <i class="bi bi-person-plus"></i> Novo Candidato DISC
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item <?php echo $current_page === 'register_candidate_mbti.php' ? 'active' : ''; ?>" 
                                   href="register_candidate_mbti.php">
                                    <i class="bi bi-person-plus"></i> Novo Candidato MBTI
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item <?php echo $current_page === 'register_candidate_bigfive.php' ? 'active' : ''; ?>" 
                                   href="register_candidate_bigfive.php">
                                    <i class="bi bi-person-plus"></i> Novo Candidato BigFive
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item <?php echo $current_page === 'register_candidate_jss.php' ? 'active' : ''; ?>" 
                                   href="register_candidate_jss.php">
                                    <i class="bi bi-person-plus"></i> Novo Candidato JSS
                                </a>
                            </li>
                            <?php if ($is_superadmin): ?>
                            <li>
                                <a class="dropdown-item <?php echo $current_page === 'manage_questions.php' ? 'active' : ''; ?>" 
                                   href="manage_questions.php">
                                    <i class="bi bi-list-check"></i> Questões DISC
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item <?php echo $current_page === 'manage_questions_mbti.php' ? 'active' : ''; ?>" 
                                   href="manage_questions_mbti.php">
                                    <i class="bi bi-list-check"></i> Questões MBTI
                                </a>
                            </li>
                            <?php endif; ?>
                        </ul>
                    </li>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">
                            <i class="bi bi-box-arrow-right"></i> Sair
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <?php
}
