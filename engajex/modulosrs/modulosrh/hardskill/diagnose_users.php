<?php
ini_set('display_errors', 1);
require_once __DIR__ . '/config/database.php';
$pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);

echo "<h1>Gerenciamento de Usuários (Diagnóstico)</h1>";

// Reset de Senha
if (isset($_POST['reset_id'])) {
    $id = $_POST['reset_id'];
    $newPass = $_POST['new_pass'];
    if (!empty($newPass)) {
        $hash = password_hash($newPass, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$hash, $id]);
        echo "<p style='color:green'>Senha do usuário ID $id atualizada para '$newPass'!</p>";
    }
}

// Testar Login
if (isset($_POST['test_email'])) {
    $email = $_POST['test_email'];
    $pass = $_POST['test_pass'];
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    echo "<h3>Teste de Login:</h3>";
    if (!$user) {
        echo "<p style='color:red'>Usuário não encontrado.</p>";
    } else {
        if (password_verify($pass, $user['password'])) {
            echo "<p style='color:green'><strong>SENHA CORRETA!</strong> O hash coincide.</p>";
        } else {
            echo "<p style='color:red'><strong>SENHA INCORRETA!</strong> O hash no banco não bate com a senha digitada.</p>";
            echo "<p>Hash no banco: " . $user['password'] . "</p>";
        }
    }
    echo "<hr>";
}

// Listar Usuários
$users = $pdo->query("SELECT id, name, email, role FROM users")->fetchAll(PDO::FETCH_ASSOC);
?>

<table border="1" cellpadding="10">
    <tr>
        <th>ID</th>
        <th>Nome</th>
        <th>Email</th>
        <th>Role</th>
        <th>Ações</th>
    </tr>
    <?php foreach ($users as $u): ?>
        <tr>
            <td><?= $u['id'] ?></td>
            <td><?= htmlspecialchars($u['name']) ?></td>
            <td><?= htmlspecialchars($u['email']) ?></td>
            <td><?= $u['role'] ?></td>
            <td>
                <form method="POST" style="display:inline">
                    <input type="hidden" name="reset_id" value="<?= $u['id'] ?>">
                    <input type="text" name="new_pass" placeholder="Nova Senha" required>
                    <button type="submit">Resetar</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
</table>

<h3>Testar Validar Senha</h3>
<form method="POST">
    Email: <input type="text" name="test_email" required><br>
    Senha: <input type="text" name="test_pass" required><br>
    <button type="submit">Verificar</button>
</form>