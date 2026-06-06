<?php
session_start();
require_once 'pages_config.php';

// Função para exibir o formulário de conexão
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
            .disconnect-btn {
                text-align: right;
                margin-bottom: 10px;
            }
            .disconnect-btn a {
                text-decoration: none;
                padding: 5px 10px;
                background-color: #ff4444;
                color: white;
                border-radius: 3px;
            }
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

// Verifica se foi solicitada a desconexão (action=disconnect)
if (isset($_GET['action']) && $_GET['action'] === 'disconnect') {
    session_destroy();
    header("Location: index3.php");
    exit;
}

// Se os parâmetros de conexão ainda não estiverem na sessão e o formulário não tiver sido enviado, exibe o formulário
if (!isset($_SESSION['db_params']) && !isset($_POST['connect'])) {
    exibirFormularioConexao();
}

// Se o formulário foi enviado, armazena os dados na sessão
if (isset($_POST['connect'])) {
    $host    = trim($_POST['host']);
    $db      = trim($_POST['db']);
    $user    = trim($_POST['user']);
    $pass    = trim($_POST['pass']);
    $charset = trim($_POST['charset']) ?: 'utf8mb4';
    $table   = trim($_POST['table']) ?: null;

    $_SESSION['db_params'] = compact('host', 'db', 'user', 'pass', 'charset', 'table');
} else if (isset($_SESSION['db_params'])) {
    extract($_SESSION['db_params']);
} else {
    exibirFormularioConexao();
}

try {
    // Cria o DSN e define as opções do PDO
    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];

    // Tenta estabelecer a conexão
    $pdo = new PDO($dsn, $user, $pass, $options);

    // Define a tabela a ser utilizada
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

    // Processamento da criação de página
    if (isset($_GET['action']) && $_GET['action'] === 'create_page' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['pageName']) && isset($_POST['fields']) && !empty($_POST['fields'])) {
            savePage(
                $_POST['pageName'],
                $_POST['fields'],
                $table
            );
            header("Location: index3.php");
            exit;
        }
    }

    // Processamento da exclusão de página
    if (isset($_GET['action']) && $_GET['action'] === 'delete_page' && isset($_GET['id'])) {
        deletePage($_GET['id']);
        header("Location: index3.php");
        exit;
    }

    // Extrai os metadados da tabela
    $stmt = $pdo->prepare("DESCRIBE `$table`");
    $stmt->execute();
    $fields = $stmt->fetchAll();

    // Determina a chave primária
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

    // Processamento das ações CRUD
    $message = '';
    
    // DELETE
    if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['pk'])) {
        $pk = $_GET['pk'];
        $stmt = $pdo->prepare("DELETE FROM `$table` WHERE `$primaryKey` = ?");
        if ($stmt->execute([$pk])) {
            $message = "Registro excluído com sucesso!";
        } else {
            $message = "Erro ao excluir o registro.";
        }
    }

    // UPDATE
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
        $pk = $_POST[$primaryKey];
        $setClause = [];
        $values = [];
        
        foreach ($fields as $field) {
            $fieldName = $field['Field'];
            if ($fieldName !== $primaryKey && isset($_POST[$fieldName])) {
                $setClause[] = "`$fieldName` = ?";
                $values[] = $_POST[$fieldName];
            }
        }
        
        $values[] = $pk; // Adiciona o valor da chave primária para a cláusula WHERE
        
        if (!empty($setClause)) {
            $sql = "UPDATE `$table` SET " . implode(", ", $setClause) . " WHERE `$primaryKey` = ?";
            $stmt = $pdo->prepare($sql);
            if ($stmt->execute($values)) {
                $message = "Registro atualizado com sucesso!";
            } else {
                $message = "Erro ao atualizar o registro.";
            }
        }
    }

    // CREATE
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create'])) {
        $columns = [];
        $placeholders = [];
        $values = [];
        
        foreach ($fields as $field) {
            $fieldName = $field['Field'];
            if ($field['Extra'] !== 'auto_increment' && isset($_POST[$fieldName]) && $_POST[$fieldName] !== '') {
                $columns[] = "`$fieldName`";
                $placeholders[] = "?";
                $values[] = $_POST[$fieldName];
            }
        }
        
        if (!empty($columns)) {
            $sql = "INSERT INTO `$table` (" . implode(", ", $columns) . ") VALUES (" . implode(", ", $placeholders) . ")";
            $stmt = $pdo->prepare($sql);
            if ($stmt->execute($values)) {
                $message = "Registro criado com sucesso!";
            } else {
                $message = "Erro ao criar o registro.";
            }
        }
    }

    // READ - Busca os dados para exibição
    $editData = null;
    if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['pk'])) {
        $stmt = $pdo->prepare("SELECT * FROM `$table` WHERE `$primaryKey` = ?");
        $stmt->execute([$_GET['pk']]);
        $editData = $stmt->fetch();
    }

    // Busca todos os registros para a listagem
    $stmt = $pdo->query("SELECT * FROM `$table`");
    $data = $stmt->fetchAll();

    // Interface do usuário
    ?>
    <!DOCTYPE html>
    <html lang="pt-BR">
    <head>
        <meta charset="utf-8">
        <title>Gerenciamento de Tabela MySQL</title>
        <style>
            body { 
                font-family: Arial, sans-serif; 
                padding: 20px;
                background-color: #f5f5f5;
            }
            .container { 
                width: 95%; 
                margin: 0 auto;
                background-color: white;
                padding: 20px;
                border-radius: 8px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }
            table { 
                width: 100%; 
                border-collapse: collapse; 
                margin-top: 20px;
                background-color: white;
            }
            th, td { 
                padding: 12px 8px; 
                text-align: left; 
                border: 1px solid #ddd;
            }
            th { 
                background-color: #f8f9fa;
                font-weight: bold;
            }
            tr:nth-child(even) { 
                background-color: #f9f9f9;
            }
            tr:hover {
                background-color: #f5f5f5;
            }
            .actions { 
                display: flex; 
                gap: 10px;
            }
            .actions a, button { 
                text-decoration: none; 
                padding: 6px 12px; 
                border-radius: 4px; 
                color: white;
                border: none;
                cursor: pointer;
                font-size: 14px;
            }
            .edit { 
                background-color: #4CAF50;
            }
            .delete { 
                background-color: #f44336;
            }
            .disconnect-btn {
                text-align: right;
                margin-bottom: 20px;
            }
            .disconnect-btn a {
                text-decoration: none;
                padding: 8px 15px;
                background-color: #ff4444;
                color: white;
                border-radius: 4px;
                font-weight: bold;
            }
            .form-container {
                background-color: #f8f9fa;
                padding: 20px;
                border-radius: 4px;
                margin-top: 20px;
            }
            .form-group {
                margin-bottom: 15px;
            }
            .form-group label {
                display: block;
                margin-bottom: 5px;
                font-weight: bold;
            }
            .form-group input {
                width: 100%;
                padding: 8px;
                border: 1px solid #ddd;
                border-radius: 4px;
                box-sizing: border-box;
            }
            .btn-primary {
                background-color: #007bff;
                color: white;
                padding: 8px 16px;
                border: none;
                border-radius: 4px;
                cursor: pointer;
            }
            .btn-primary:hover {
                background-color: #0056b3;
            }
            .message {
                padding: 10px;
                margin-bottom: 20px;
                border-radius: 4px;
                background-color: #d4edda;
                color: #155724;
                border: 1px solid #c3e6cb;
            }
            .error {
                background-color: #f8d7da;
                color: #721c24;
                border: 1px solid #f5c6cb;
            }
            .pages-section {
                margin-top: 20px;
                padding: 20px;
                background-color: #f8f9fa;
                border-radius: 4px;
            }
            .pages-grid {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
                gap: 15px;
                margin-top: 15px;
            }
            .page-card {
                background: white;
                padding: 15px;
                border-radius: 4px;
                box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            }
            .page-card h4 {
                margin: 0 0 10px 0;
            }
            .page-card p {
                margin: 5px 0;
                font-size: 0.9em;
                color: #666;
            }
            .page-actions {
                margin-top: 10px;
                display: flex;
                gap: 10px;
            }
            .modal {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0,0,0,0.5);
                z-index: 1000;
            }
            .modal-content {
                position: relative;
                background-color: #fff;
                margin: 50px auto;
                padding: 20px;
                width: 80%;
                max-width: 600px;
                border-radius: 8px;
            }
            .close {
                position: absolute;
                right: 20px;
                top: 10px;
                font-size: 28px;
                cursor: pointer;
            }
            .field-list {
                margin: 15px 0;
                max-height: 300px;
                overflow-y: auto;
            }
            .field-item {
                padding: 8px;
                margin: 5px 0;
                background: #f8f9fa;
                border-radius: 4px;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="disconnect-btn">
                <a href="?action=disconnect">Desconectar</a>
            </div>
            
            <?php if ($message): ?>
                <div class="message"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <!-- Seção de Páginas Personalizadas -->
            <div class="pages-section">
                <h3>Páginas Personalizadas</h3>
                <button onclick="showCreatePageModal()" class="btn-primary">Criar Nova Página</button>
                
                <div class="pages-grid">
                    <?php foreach (getPages() as $customPage): ?>
                        <div class="page-card">
                            <h4><?php echo htmlspecialchars($customPage['name']); ?></h4>
                            <p>Tabela: <?php echo htmlspecialchars($customPage['table']); ?></p>
                            <p>Campos: <?php echo htmlspecialchars(implode(', ', $customPage['fields'])); ?></p>
                            <div class="page-actions">
                                <a href="view_page.php?id=<?php echo urlencode($customPage['id']); ?>" class="btn-primary">Ver</a>
                                <a href="?action=delete_page&id=<?php echo urlencode($customPage['id']); ?>" 
                                   class="delete" 
                                   onclick="return confirm('Tem certeza que deseja excluir esta página?')">Excluir</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <h2>Gerenciamento da Tabela: <?php echo htmlspecialchars($table); ?></h2>
            
            <?php if ($editData): ?>
                <!-- Formulário de Edição -->
                <div class="form-container">
                    <h3>Editar Registro</h3>
                    <form method="post">
                        <?php foreach ($fields as $field): ?>
                            <div class="form-group">
                                <label for="<?php echo htmlspecialchars($field['Field']); ?>">
                                    <?php echo htmlspecialchars($field['Field']); ?>:
                                </label>
                                <?php if ($field['Field'] === $primaryKey): ?>
                                    <input type="hidden" name="<?php echo htmlspecialchars($field['Field']); ?>" 
                                           value="<?php echo htmlspecialchars($editData[$field['Field']]); ?>">
                                    <input type="text" disabled 
                                           value="<?php echo htmlspecialchars($editData[$field['Field']]); ?>">
                                <?php else: ?>
                                    <input type="text" name="<?php echo htmlspecialchars($field['Field']); ?>" 
                                           value="<?php echo htmlspecialchars($editData[$field['Field']]); ?>"
                                           <?php echo ($field['Null'] === 'NO' ? 'required' : ''); ?>>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                        <button type="submit" name="update" class="btn-primary">Atualizar</button>
                        <a href="?" style="margin-left: 10px;">Cancelar</a>
                    </form>
                </div>
            <?php else: ?>
                <!-- Formulário de Criação -->
                <div class="form-container">
                    <h3>Novo Registro</h3>
                    <form method="post">
                        <?php foreach ($fields as $field): ?>
                            <?php if ($field['Extra'] !== 'auto_increment'): ?>
                                <div class="form-group">
                                    <label for="<?php echo htmlspecialchars($field['Field']); ?>">
                                        <?php echo htmlspecialchars($field['Field']); ?>:
                                    </label>
                                    <input type="text" name="<?php echo htmlspecialchars($field['Field']); ?>"
                                           <?php echo ($field['Null'] === 'NO' ? 'required' : ''); ?>>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                        <button type="submit" name="create" class="btn-primary">Criar</button>
                    </form>
                </div>
            <?php endif; ?>

            <!-- Tabela de Registros -->
            <?php if (!empty($data)): ?>
                <table>
                    <thead>
                        <tr>
                            <?php foreach ($fields as $field): ?>
                                <th><?php echo htmlspecialchars($field['Field']); ?></th>
                            <?php endforeach; ?>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data as $row): ?>
                            <tr>
                                <?php foreach ($fields as $field): ?>
                                    <td><?php echo htmlspecialchars($row[$field['Field']]); ?></td>
                                <?php endforeach; ?>
                                <td class="actions">
                                    <a href="?action=edit&pk=<?php echo urlencode($row[$primaryKey]); ?>" 
                                       class="edit">Editar</a>
                                    <a href="?action=delete&pk=<?php echo urlencode($row[$primaryKey]); ?>" 
                                       class="delete" 
                                       onclick="return confirm('Tem certeza que deseja excluir este registro?')">
                                        Excluir
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Nenhum registro encontrado.</p>
            <?php endif; ?>
        </div>

        <!-- Modal para criar nova página -->
        <div id="createPageModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="hideCreatePageModal()">&times;</span>
                <h2>Criar Nova Página</h2>
                <form method="post" action="?action=create_page">
                    <div class="form-group">
                        <label for="pageName">Nome da Página:</label>
                        <input type="text" id="pageName" name="pageName" required>
                    </div>
                    
                    <div class="field-list">
                        <h4>Selecione os campos:</h4>
                        <?php foreach ($fields as $field): ?>
                            <div class="field-item">
                                <label>
                                    <input type="checkbox" name="fields[]" value="<?php echo htmlspecialchars($field['Field']); ?>">
                                    <?php echo htmlspecialchars($field['Field']); ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <button type="submit" class="btn-primary">Criar Página</button>
                </form>
            </div>
        </div>

        <script>
            function showCreatePageModal() {
                document.getElementById('createPageModal').style.display = 'block';
            }
            
            function hideCreatePageModal() {
                document.getElementById('createPageModal').style.display = 'none';
            }
            
            // Fecha o modal se clicar fora dele
            window.onclick = function(event) {
                var modal = document.getElementById('createPageModal');
                if (event.target == modal) {
                    modal.style.display = 'none';
                }
            }
        </script>
    </body>
    </html>
    <?php

} catch (\PDOException $e) {
    unset($_SESSION['db_params']);
    exibirFormularioConexao("Erro na conexão: " . $e->getMessage());
}
