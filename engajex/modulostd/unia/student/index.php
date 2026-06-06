<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit;
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Aluno | Thriveo Unia</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <div class="container" style="padding-top: 5rem;">
        <h1>Área do Aluno</h1>
        <p>Acesse seus cursos e certificações.</p>
        <div class="card" style="margin-top: 2rem;">
            <h3>Em Desenvolvimento</h3>
            <p>Aqui o aluno poderá assistir às aulas, realizar provas e baixar certificados.</p>
            <br>
            <a href="../back_to_system.php" class="btn btn-primary">Voltar ao Sistema</a>
            <a href="../logout.php" class="btn btn-outline">Sair</a>
        </div>
    </div>
</body>

</html>