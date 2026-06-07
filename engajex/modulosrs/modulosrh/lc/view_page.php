<?php
session_start();
require_once 'pages_config.php';

if (!isset($_GET['id'])) {
    die("ID da página não fornecido");
}

$page = getPage($_GET['id']);
if (!$page) {
    die("Página não encontrada");
}

// Recupera os parâmetros de conexão da sessão
if (!isset($_SESSION['db_params'])) {
    header("Location: index3.php");
    exit;
}

extract($_SESSION['db_params']);

try {
    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];

    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // Constrói a consulta SQL apenas com os campos selecionados
    $fields = implode(', ', array_map(function($field) {
        return "`$field`";
    }, $page['fields']));
    
    $stmt = $pdo->query("SELECT $fields FROM `{$page['table']}`");
    $data = $stmt->fetchAll();

} catch (\PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title><?php echo htmlspecialchars($page['name']); ?></title>
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
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .btn {
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 4px;
            color: white;
            background-color: #007bff;
        }
        .btn-back {
            background-color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><?php echo htmlspecialchars($page['name']); ?></h1>
            <a href="index3.php" class="btn btn-back">Voltar</a>
        </div>
        
        <?php if (!empty($data)): ?>
            <table>
                <thead>
                    <tr>
                        <?php foreach ($page['fields'] as $field): ?>
                            <th><?php echo htmlspecialchars($field); ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data as $row): ?>
                        <tr>
                            <?php foreach ($page['fields'] as $field): ?>
                                <td><?php echo htmlspecialchars($row[$field]); ?></td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Nenhum registro encontrado.</p>
        <?php endif; ?>
    </div>
</body>
</html>
