<?php
require_once '../config/config.php';
require_once '../config/database.php';
checkAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['csvFile'])) {
    $_SESSION['error'] = "Nenhum arquivo foi enviado.";
    redirect('/admin/respondentes.php');
}

$file = $_FILES['csvFile'];

// Validar arquivo
if ($file['error'] !== UPLOAD_ERR_OK) {
    $_SESSION['error'] = "Erro no upload do arquivo.";
    redirect('/admin/respondentes.php');
}

if ($file['type'] !== 'text/csv') {
    $_SESSION['error'] = "O arquivo deve estar no formato CSV.";
    redirect('/admin/respondentes.php');
}

// Processar arquivo
try {
    $db = Database::getInstance()->getConnection();
    $db->beginTransaction();

    $handle = fopen($file['tmp_name'], 'r');
    $count = 0;
    $errors = [];

    while (($data = fgetcsv($handle)) !== false) {
        $email = trim($data[0] ?? '');
        
        // Validar e-mail
        if (!validateEmail($email)) {
            $errors[] = "Linha " . ($count + 1) . ": E-mail inválido - " . htmlspecialchars($email);
            continue;
        }

        // Gerar código único
        $codigo = generateUniqueCode();
        $tentativas = 0;
        
        // Garantir que o código seja único
        while ($tentativas < 5) {
            try {
                $stmt = $db->prepare("INSERT INTO respondentes (email, codigo_unico) VALUES (?, ?)");
                $stmt->execute([$email, $codigo]);
                $count++;
                break;
            } catch (PDOException $e) {
                if ($e->getCode() == '23000') { // Duplicate entry
                    $codigo = generateUniqueCode();
                    $tentativas++;
                } else {
                    throw $e;
                }
            }
        }
        
        if ($tentativas == 5) {
            $errors[] = "Linha " . ($count + 1) . ": Não foi possível gerar um código único para - " . htmlspecialchars($email);
        }
    }

    fclose($handle);
    
    if (empty($errors)) {
        $db->commit();
        $_SESSION['success'] = "Importação concluída com sucesso. {$count} respondentes importados.";
    } else {
        $db->rollBack();
        $_SESSION['error'] = "Erros durante a importação:<br>" . implode("<br>", $errors);
    }

} catch (Exception $e) {
    if (isset($db)) {
        $db->rollBack();
    }
    $_SESSION['error'] = "Erro durante a importação: " . $e->getMessage();
}

redirect('/admin/respondentes.php');
