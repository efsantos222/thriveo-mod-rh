<?php
session_start();

// Verifica se foi solicitada a desconexão (action=disconnect)
if (isset($_GET['action']) && $_GET['action'] === 'disconnect') {
    session_destroy();
    header("Location: index.php");
    exit;
}

// Função para exibir o formulário de parâmetros de conexão e tabela
function exibirFormularioConexao($mensagem = '') {
    ?>
    <!DOCTYPE html>
    <html lang="pt-BR">
    <head>
        <meta charset="utf-8">
        <title>Configuração de Conexão MySQL</title>
        <style>
            body { font-family: Arial, sans-serif; }
            .container { width: 50%; margin: 20px auto; }
            .form-group { margin-bottom: 10px; }
            label { display: inline-block; width: 150px; }
            input { width: 100%; padding: 8px; }
            button { padding: 10px 20px; }
            .mensagem { color: red; }
        </style>
    </head>
    <body>
        <div class="container">
            <h2>Informe os Parâmetros de Conexão com o MySQL</h2>
            <?php if($mensagem): ?>
                <p class="mensagem"><?php echo htmlspecialchars($mensagem); ?></p>
            <?php endif; ?>
            <form method="post">
                <div class="form-group">
                    <label for="host">Host:</label>
                    <input type="text" id="host" name="host" required placeholder="localhost">
                </div>
                <div class="form-group">
                    <label for="db">Nome do Banco:</label>
                    <input type="text" id="db" name="db" required>
                </div>
                <div class="form-group">
                    <label for="user">Usuário:</label>
                    <input type="text" id="user" name="user" required>
                </div>
                <div class="form-group">
                    <label for="pass">Senha:</label>
                    <input type="password" id="pass" name="pass" required>
                </div>
                <div class="form-group">
                    <label for="charset">Charset:</label>
                    <input type="text" id="charset" name="charset" value="utf8mb4">
                </div>
                <div class="form-group">
                    <label for="table">Tabela:</label>
                    <input type="text" id="table" name="table" placeholder="Informe a tabela ou deixe em branco para usar a primeira">
                </div>
                <button type="submit" name="connect">Conectar</button>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Se os parâmetros de conexão ainda não estiverem na sessão e o formulário não tiver sido enviado, exibe o formulário.
if (!isset($_SESSION['db_params']) && !isset($_POST['connect'])) {
    exibirFormularioConexao();
}

// Se o formulário foi enviado, armazena os dados na sessão.
if (isset($_POST['connect'])) {
    $host    = trim($_POST['host']);
    $db      = trim($_POST['db']);
    $user    = trim($_POST['user']);
    $pass    = trim($_POST['pass']);
    $charset = trim($_POST['charset']) ?: 'utf8mb4';
    // O campo "table" é opcional
    $table   = trim($_POST['table']) ?: null;

    $_SESSION['db_params'] = compact('host', 'db', 'user', 'pass', 'charset', 'table');
} else {
    extract($_SESSION['db_params']);
}

// Cria o DSN e define as opções do PDO
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

// Tenta estabelecer a conexão
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    unset($_SESSION['db_params']);
    exibirFormularioConexao("Erro na conexão: " . $e->getMessage());
}

// Define a tabela a ser utilizada:
// Se a URL não informar a tabela, verifica se há uma tabela definida na sessão a partir do formulário;
// caso contrário, utiliza a primeira tabela disponível.
$table = $_GET['table'] ?? ($_SESSION['db_params']['table'] ?? null);
if (!$table) {
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_NUM);
    if (count($tables) > 0) {
        $table = $tables[0][0];
    } else {
        die("Nenhuma tabela encontrada no banco de dados.");
    }
}

// Extrai os metadados da tabela usando DESCRIBE
$stmt = $pdo->prepare("DESCRIBE `$table`");
$stmt->execute();
$fields = $stmt->fetchAll();

// Determina a chave primária (suporta um único campo primário)
$primaryKey = null;
foreach ($fields as $field) {
    if ($field['Key'] === 'PRI') {
        $primaryKey = $field['Field'];
        break;
    }
}
if (!$primaryKey) {
    die("A tabela '$table' não possui uma chave primária definida.");
}

// === Processamento de ações ===

// Exclusão: quando a URL contém ?action=delete e um valor para a chave primária
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['pk'])) {
    $pk = $_GET['pk'];
    $stmt = $pdo->prepare("DELETE FROM `$table` WHERE `$primaryKey` = ?");
    if ($stmt->execute([$pk])) {
        header("Location: index.php?table=" . urlencode($table));
        exit;
    } else {
        echo "<p style='color:red;'>Erro ao excluir o registro.</p>";
    }
}

// Atualização: processamento do formulário de edição
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['updateRecord'])) {
    $pk = $_POST[$primaryKey];
    $setClause = [];
    $values = [];
    foreach ($fields as $field) {
        $fieldName = $field['Field'];
        if ($fieldName == $primaryKey) continue;
        if (isset($_POST[$fieldName])) {
            $setClause[] = "`$fieldName` = ?";
            $values[] = $_POST[$fieldName];
        }
    }
    $values[] = $pk;
    $sql = "UPDATE `$table` SET " . implode(", ", $setClause) . " WHERE `$primaryKey` = ?";
    $stmt = $pdo->prepare($sql);
    if ($stmt->execute($values)) {
        header("Location: index.php?table=" . urlencode($table));
        exit;
    } else {
        echo "<p style='color:red;'>Erro ao atualizar o registro.</p>";
    }
}

// Inserção: processamento do formulário de novo registro
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['insertRecord'])) {
    $columns = [];
    $placeholders = [];
    $values = [];
    foreach ($fields as $field) {
        $fieldName = $field['Field'];
        if (stripos($field['Extra'], 'auto_increment') !== false) continue;
        if (isset($_POST[$fieldName]) && $_POST[$fieldName] !== '') {
            $columns[] = "`$fieldName`";
            $placeholders[] = '?';
            $values[] = $_POST[$fieldName];
        }
    }
    if (!empty($columns)) {
        $sql = "INSERT INTO `$table` (" . implode(", ", $columns) . ") VALUES (" . implode(", ", $placeholders) . ")";
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute($values)) {
            echo "<p style='color:green;'>Registro inserido com sucesso!</p>";
        } else {
            echo "<p style='color:red;'>Erro ao inserir registro.</p>";
        }
    }
}

// Verifica se está em modo de edição (GET action=edit)
$editMode = false;
$editData = null;
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['pk'])) {
    $editMode = true;
    $pk = $_GET['pk'];
    $stmt = $pdo->prepare("SELECT * FROM `$table` WHERE `$primaryKey` = ?");
    $stmt->execute([$pk]);
    $editData = $stmt->fetch();
    if (!$editData) {
        die("Registro não encontrado para edição.");
    }
}

// Consulta os registros para exibição (se não estiver no modo de edição)
$stmt = $pdo->query("SELECT * FROM `$table`");
$data = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <title>Plataforma Low-Code: <?php echo htmlspecialchars($table); ?></title>
  <style>
      body { font-family: Arial, sans-serif; }
      .container { width: 80%; margin: 20px auto; }
      table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }
      th, td { padding: 8px; border: 1px solid #ddd; text-align: left; }
      th { background-color: #f4f4f4; }
      .form-group { margin-bottom: 10px; }
      label { display: inline-block; width: 150px; }
      input[type="text"], input[type="number"] { width: 300px; padding: 5px; }
      button { padding: 10px 20px; }
      .actions a { margin-right: 5px; text-decoration: none; }
      .disconnect { float: right; margin-top: 10px; }
  </style>
</head>
<body>
<div class="container">
  <!-- Botão de Desconectar -->
  <div class="disconnect">
      <a href="?action=disconnect" style="text-decoration:none; font-weight:bold;">Desconectar</a>
  </div>

  <h2>Registros da Tabela: <?php echo htmlspecialchars($table); ?></h2>

  <?php if(!$editMode): ?>
    <?php if (!empty($data)): ?>
      <table>
          <thead>
              <tr>
                  <?php 
                  // Cabeçalho dinâmico – inclui uma coluna para as ações
                  foreach (array_keys($data[0]) as $col): ?>
                      <th><?php echo htmlspecialchars($col); ?></th>
                  <?php endforeach; ?>
                  <th>Ações</th>
              </tr>
          </thead>
          <tbody>
              <?php foreach ($data as $row): ?>
                  <tr>
                      <?php foreach ($row as $value): ?>
                          <td><?php echo htmlspecialchars($value); ?></td>
                      <?php endforeach; ?>
                      <td class="actions">
                          <a href="?table=<?php echo urlencode($table); ?>&action=edit&pk=<?php echo urlencode($row[$primaryKey]); ?>">Editar</a>
                          <a href="?table=<?php echo urlencode($table); ?>&action=delete&pk=<?php echo urlencode($row[$primaryKey]); ?>" 
                             onclick="return confirm('Deseja realmente excluir este registro?');">Excluir</a>
                      </td>
                  </tr>
              <?php endforeach; ?>
          </tbody>
      </table>
    <?php else: ?>
      <p>Nenhum registro encontrado na tabela.</p>
    <?php endif; ?>

    <h2>Inserir Novo Registro</h2>
    <form method="post">
      <?php foreach ($fields as $field): 
            // Ignora campos auto-incremento
            if (stripos($field['Extra'], 'auto_increment') !== false) continue;
      ?>
          <div class="form-group">
              <label><?php echo htmlspecialchars($field['Field']); ?>:</label>
              <input type="text" name="<?php echo htmlspecialchars($field['Field']); ?>" placeholder="Digite o valor">
          </div>
      <?php endforeach; ?>
      <button type="submit" name="insertRecord">Inserir</button>
    </form>
  <?php else: // Modo de edição ?>
    <h2>Editar Registro (<?php echo htmlspecialchars($editData[$primaryKey]); ?>)</h2>
    <form method="post">
      <!-- Campo oculto para a chave primária -->
      <input type="hidden" name="<?php echo htmlspecialchars($primaryKey); ?>" value="<?php echo htmlspecialchars($editData[$primaryKey]); ?>">
      <?php foreach ($fields as $field):
              $fieldName = $field['Field'];
              if (stripos($field['Extra'], 'auto_increment') !== false) continue;
              $value = $editData[$fieldName] ?? '';
      ?>
          <div class="form-group">
              <label><?php echo htmlspecialchars($fieldName); ?>:</label>
              <input type="text" name="<?php echo htmlspecialchars($fieldName); ?>" value="<?php echo htmlspecialchars($value); ?>">
          </div>
      <?php endforeach; ?>
      <button type="submit" name="updateRecord">Atualizar</button>
      <a href="index.php?table=<?php echo urlencode($table); ?>">Cancelar</a>
    </form>
  <?php endif; ?>
</div>
</body>
</html>
