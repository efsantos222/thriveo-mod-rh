<?php
// includes/sidebar.php

$role = $_SESSION['role'] ?? 'employee';

// Determine root path for links
$inSubDir = (
    strpos($_SERVER['PHP_SELF'], '/modules/')  !== false ||
    strpos($_SERVER['PHP_SELF'], '/admin/')    !== false ||
    strpos($_SERVER['PHP_SELF'], '/zbb/')      !== false ||
    strpos($_SERVER['PHP_SELF'], '/modulostd/') !== false ||
    strpos($_SERVER['PHP_SELF'], '/modulosrs/') !== false ||
    strpos($_SERVER['PHP_SELF'], '/modulosrh/') !== false
);
$rootPath = $inSubDir ? '../' : '';

// Injeta Font Awesome e Toast uma única vez por página
if (!defined('FONT_AWESOME_LOADED')) {
    define('FONT_AWESOME_LOADED', true);
    echo '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" integrity="sha512-Avb2QiuDEEvB4bZJYdft2mNjVShBftLdPG8FJ0V7irTLQ8Uo0qcPxh4Plh7eqjmsF+q7e7UjJHVPIBuH0jjw==" crossorigin="anonymous" referrerpolicy="no-referrer">';
    echo '<script src="' . $rootPath . 'assets/js/toast.js" defer></script>';
}

function isCurrentPage($page, $isDir = false) {
    if ($isDir) {
        return strpos($_SERVER['PHP_SELF'], $page) !== false;
    }
    return basename($_SERVER['PHP_SELF']) == $page;
}

// Renders a collapsible section with icons
function renderSection($title, $icon, $items, $isOpenByDefault = false) {
    global $rootPath;

    $visibleItems = array_filter($items, function($item) {
        return !(isset($item['hidden']) && $item['hidden']);
    });

    if (empty($visibleItems)) return;

    $hasActive = false;
    foreach ($visibleItems as $item) {
        if (isset($item['active']) && $item['active']) {
            $hasActive = true;
            break;
        }
    }

    $collapsedClass = ($hasActive || $isOpenByDefault) ? '' : 'collapsed';

    echo '<div class="sidebar-section ' . $collapsedClass . '">';
    echo '  <p class="sidebar-section-title" onclick="toggleSection(this)">';
    echo '    <span class="section-title__icon"><i class="fa-solid ' . htmlspecialchars($icon) . '"></i></span>';
    echo '    <span class="section-title__text">' . htmlspecialchars($title) . '</span>';
    echo '    <span class="section-title__arrow"><i class="fa-solid fa-chevron-down"></i></span>';
    echo '  </p>';
    echo '  <div class="sidebar-section-content">';

    foreach ($visibleItems as $item) {
        $activeClass  = (isset($item['active'])  && $item['active'])  ? 'active'  : '';
        $dangerClass  = (isset($item['danger'])  && $item['danger'])  ? 'danger'  : '';
        $itemIcon     = isset($item['icon']) ? $item['icon'] : 'fa-circle-dot';
        $label        = $item['label'];
        $tooltip      = 'data-tooltip="' . htmlspecialchars($label) . '"';

        echo '    <a href="' . $rootPath . $item['link'] . '" class="menu-item ' . $activeClass . ' ' . $dangerClass . '" ' . $tooltip . '>';
        echo '      <span class="menu-item__icon"><i class="fa-solid ' . htmlspecialchars($itemIcon) . '"></i></span>';
        echo '      <span class="menu-item__text">' . htmlspecialchars($label) . '</span>';
        echo '    </a>';
    }

    echo '  </div>';
    echo '</div>';
}

// Avatar inicial
$userName    = htmlspecialchars($_SESSION['name'] ?? 'Usuário');
$userInitial = mb_strtoupper(mb_substr(strip_tags($userName), 0, 1));
?>

<!-- Overlay para mobile -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<aside class="sidebar" id="sidebar">

    <div class="sidebar-header">
        <a href="<?php echo $rootPath; ?>dashboard.php" class="sidebar-logo-link">
            <img src="http://thriveo.com.br/wp-content/uploads/2026/03/logo_thriveo_branco_transparente.png"
                 alt="Thriveo" class="sidebar-logo-img">
            <i class="fa-solid fa-rocket sidebar-logo-icon"></i>
        </a>

        <div class="sidebar-user-info">
            <span class="sidebar-user-avatar"><?php echo $userInitial; ?></span>
            <span class="sidebar-user">Olá, <?php echo $userName; ?></span>
        </div>

        <button class="sidebar-toggle" id="sidebarToggle" title="Recolher menu">
            <i class="fa-solid fa-angles-left"></i>
        </button>
    </div>

    <nav class="sidebar-nav">
        <?php
        // ── Principal ────────────────────────────────────────
        renderSection('Principal', 'fa-house', [
            ['label' => 'Dashboard',                 'icon' => 'fa-gauge',        'link' => 'dashboard.php',       'active' => isCurrentPage('dashboard.php')],
            ['label' => 'Meu Perfil',                'icon' => 'fa-circle-user',  'link' => 'profile.php',         'active' => isCurrentPage('profile.php')],
            ['label' => 'Configurações da Empresa',  'icon' => 'fa-gear',         'link' => 'company_settings.php','active' => isCurrentPage('company_settings.php'), 'hidden' => !in_array($role, ['responsible','company_admin','admin'])],
            ['label' => 'Assinatura',                'icon' => 'fa-credit-card',  'link' => 'subscribe.php',       'active' => isCurrentPage('subscribe.php'),        'hidden' => !in_array($role, ['responsible','company_admin','admin'])],
        ], true);

        // ── Gestão ───────────────────────────────────────────
        if (in_array($role, ['responsible', 'company_admin', 'admin'])) {
            renderSection('Gestão', 'fa-briefcase', [
                ['label' => 'Pessoas & Gerentes',      'icon' => 'fa-users',              'link' => 'manage_employees.php',          'active' => isCurrentPage('manage_employees.php')],
                ['label' => 'Alocação de Times',       'icon' => 'fa-sitemap',            'link' => 'allocation.php',                'active' => isCurrentPage('allocation.php')],
                ['label' => 'Ouvidoria Config',        'icon' => 'fa-shield-halved',      'link' => 'ouvidoria_config.php',          'active' => isCurrentPage('ouvidoria_config.php')],
                ['label' => 'Turnover',                'icon' => 'fa-arrow-right-from-bracket', 'link' => 'turnover.php',            'active' => isCurrentPage('turnover.php')],
                ['label' => 'V2MOM (Estratégia)',      'icon' => 'fa-bullseye',           'link' => 'modules/v2mom.php',             'active' => isCurrentPage('v2mom.php')],
                ['label' => 'Inteligência Competitiva','icon' => 'fa-binoculars',         'link' => 'modules/competitive_intel.php', 'active' => isCurrentPage('competitive_intel.php')],
                ['label' => 'Dashboard RH',            'icon' => 'fa-chart-bar',          'link' => 'modules/rh_dashboard.php',      'active' => isCurrentPage('rh_dashboard.php')],
                ['label' => 'Input Indicadores RH',    'icon' => 'fa-arrow-up-from-bracket','link'=> 'modules/rh_indicators_input.php','active'=> isCurrentPage('rh_indicators_input.php')],
                ['label' => 'GPTW (Indicadores)',      'icon' => 'fa-trophy',             'link' => 'modules/gptw_dashboard.php',    'active' => isCurrentPage('gptw_dashboard.php')],
            ]);
        }

        // ── Financeiro ───────────────────────────────────────
        if (in_array($role, ['responsible', 'company_admin', 'admin', 'manager'])) {
            renderSection('Financeiro', 'fa-wallet', [
                ['label' => 'Orçamento ZBB', 'icon' => 'fa-coins', 'link' => 'zbb/dashboard.php', 'active' => isCurrentPage('/zbb/', true)],
            ]);
        }

        // ── Enquetes ─────────────────────────────────────────
        renderSection('Enquetes', 'fa-square-poll-horizontal', [
            ['label' => 'Pesquisa de Clima',       'icon' => 'fa-cloud-sun',  'link' => 'modules/climate.php',       'active' => isCurrentPage('climate.php')],
            ['label' => 'Melhores Lugares (GPTW)', 'icon' => 'fa-star',       'link' => 'modules/mlpt_maturity.php', 'active' => isCurrentPage('mlpt_maturity.php')],
        ]);

        // ── Engajamento ──────────────────────────────────────
        renderSection('Engajamento', 'fa-heart', [
            ['label' => 'Canal de Ouvidoria', 'icon' => 'fa-inbox',               'link' => 'modules/ouvidoria.php',    'active' => isCurrentPage('ouvidoria.php')],
            ['label' => 'Feedback',           'icon' => 'fa-comments',            'link' => 'modules/feedback.php',    'active' => isCurrentPage('feedback.php')],
            ['label' => '1:1 Meetings',       'icon' => 'fa-handshake',           'link' => 'modules/one_on_one.php',  'active' => isCurrentPage('one_on_one.php')],
            ['label' => 'Celebrações',        'icon' => 'fa-champagne-glasses',   'link' => 'modules/celebrations.php','active' => isCurrentPage('celebrations.php')],
            ['label' => 'Registro de Humor',  'icon' => 'fa-face-smile',          'link' => 'modules/mood.php',        'active' => isCurrentPage('mood.php')],
            ['label' => 'Gamificação',        'icon' => 'fa-gamepad',             'link' => 'modules/gamification.php','active' => isCurrentPage('gamification.php')],
        ]);

        // ── Desenvolvimento ──────────────────────────────────
        renderSection('Desenvolvimento', 'fa-rocket', [
            ['label' => 'Avaliação de Desempenho',  'icon' => 'fa-chart-line',        'link' => 'modules/competencies.php',    'active' => isCurrentPage('competencies.php')],
            ['label' => 'Meetup',                   'icon' => 'fa-people-group',      'link' => 'modules/meetup.php',          'active' => isCurrentPage('meetup.php')],
            ['label' => 'Cultura da Empresa',       'icon' => 'fa-landmark',          'link' => 'modules/board_culture.php',   'active' => isCurrentPage('board_culture.php')],
            ['label' => 'Onboarding & Offboarding', 'icon' => 'fa-door-open',         'link' => 'modules/board_onboarding.php','active' => isCurrentPage('board_onboarding.php')],
            ['label' => 'Base de Conhecimento',     'icon' => 'fa-book',              'link' => 'modules/board_knowledge.php', 'active' => isCurrentPage('board_knowledge.php')],
            ['label' => 'Coaching & Assessments',   'icon' => 'fa-graduation-cap',    'link' => 'modules/coach.php',           'active' => isCurrentPage('coach.php')],
        ]);

        // ── T&D ──────────────────────────────────────────────
        renderSection('T&D', 'fa-chalkboard-user', [
            ['label' => 'Thriveo UniA', 'icon' => 'fa-school', 'link' => 'modulostd/unia/dashboard.php', 'active' => isCurrentPage('/modulostd/unia/', true)],
        ]);

        // ── Recrutamento e Seleção ───────────────────────────
        renderSection('Recrutamento e Seleção', 'fa-user-tie', [
            ['label' => 'Proftest Formata (FVIT)',  'icon' => 'fa-crosshairs',    'link' => 'modulosrs/fvit/dashboard.php',        'active' => isCurrentPage('/modulosrs/fvit/', true)],
            ['label' => 'Match CV (IA)',             'icon' => 'fa-file-lines',    'link' => 'modulosrs/matchcv/index.php',         'active' => isCurrentPage('/modulosrs/matchcv/', true)],
            ['label' => 'Avaliação SoftSkill',      'icon' => 'fa-brain',         'link' => 'modulosrs/softskill/public/index.php','active' => isCurrentPage('/modulosrs/softskill/', true)],
            ['label' => 'Roteiro de Entrevista (IA)','icon' => 'fa-clipboard-list','link'=> 'modulosrh/entrev/dashboard.php',       'active' => isCurrentPage('/modulosrh/entrev/', true)],
        ]);

        // ── Dinâmicas ────────────────────────────────────────
        renderSection('Dinâmicas', 'fa-wand-magic-sparkles', [
            ['label' => 'Conexões Virtuais',  'icon' => 'fa-network-wired',      'link' => 'modules/connections.php', 'active' => isCurrentPage('connections.php')],
            ['label' => 'História da Empresa','icon' => 'fa-clock-rotate-left',  'link' => 'modules/history.php',     'active' => isCurrentPage('history.php')],
            ['label' => 'Bingo Corporativo',  'icon' => 'fa-dice',               'link' => 'modules/bingo.php',       'active' => isCurrentPage('bingo.php')],
        ]);
        ?>

        <!-- Logout -->
        <div class="sidebar-section sidebar-section--logout">
            <a href="<?php echo $rootPath; ?>logout.php"
               class="menu-item danger"
               data-tooltip="Sair">
                <span class="menu-item__icon"><i class="fa-solid fa-right-from-bracket"></i></span>
                <span class="menu-item__text">Sair</span>
            </a>
        </div>
    </nav>
</aside>

<script>
(function () {
    const sidebar      = document.getElementById('sidebar');
    const toggleBtn    = document.getElementById('sidebarToggle');
    const overlay      = document.getElementById('sidebarOverlay');
    const STORAGE_KEY  = 'engajex_sidebar_collapsed';

    // ── Restaurar estado salvo ──────────────────────────────
    if (localStorage.getItem(STORAGE_KEY) === '1') {
        sidebar.classList.add('sidebar--collapsed');
    }

    // ── Toggle colapso (desktop) ────────────────────────────
    if (toggleBtn) {
        toggleBtn.addEventListener('click', function () {
            const isCollapsed = sidebar.classList.toggle('sidebar--collapsed');
            localStorage.setItem(STORAGE_KEY, isCollapsed ? '1' : '0');
        });
    }

    // ── Toggle mobile (hamburger externo) ──────────────────
    window.toggleSidebarMobile = function () {
        const isOpen = sidebar.classList.toggle('sidebar--mobile-open');
        overlay.classList.toggle('sidebar-overlay--visible', isOpen);
        document.body.style.overflow = isOpen ? 'hidden' : '';
    };

    if (overlay) {
        overlay.addEventListener('click', function () {
            sidebar.classList.remove('sidebar--mobile-open');
            overlay.classList.remove('sidebar-overlay--visible');
            document.body.style.overflow = '';
        });
    }

    // ── Accordion de seções ─────────────────────────────────
    window.toggleSection = function (element) {
        const section = element.parentElement;

        // Fechar outras seções (accordion)
        document.querySelectorAll('.sidebar-section').forEach(function (s) {
            if (s !== section && !s.classList.contains('sidebar-section--logout')) {
                s.classList.add('collapsed');
            }
        });

        section.classList.toggle('collapsed');
    };
})();
</script>
