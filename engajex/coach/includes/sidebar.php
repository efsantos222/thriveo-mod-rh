<nav class="sidebar">
    <div class="user-info">
        <h3><?php echo htmlspecialchars($_SESSION['user_name']); ?></h3>
        <p><?php echo htmlspecialchars($_SESSION['user_profile']); ?></p>
    </div>
    <ul class="nav-menu">
        <li><a href="../dashboard.php" <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'class="active"' : ''; ?>>Dashboard</a></li>
        <li><a href="../sessions/index.php" <?php echo strpos($_SERVER['PHP_SELF'], '/sessions/') !== false ? 'class="active"' : ''; ?>>Sessões</a></li>
        <li><a href="../questionnaires/index.php" <?php echo strpos($_SERVER['PHP_SELF'], '/questionnaires/') !== false ? 'class="active"' : ''; ?>>Questionários</a></li>
        <li><a href="../goals/index.php" <?php echo strpos($_SERVER['PHP_SELF'], '/goals/') !== false ? 'class="active"' : ''; ?>>Metas SMART</a></li>
        <li><a href="../feedback/index.php" <?php echo strpos($_SERVER['PHP_SELF'], '/feedback/') !== false ? 'class="active"' : ''; ?>>Feedback</a></li>
        <li><a href="../auth/logout.php">Sair</a></li>
    </ul>
</nav>
