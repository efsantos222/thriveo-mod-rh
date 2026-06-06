<?php
session_start();

// Inicializa o array de páginas se não existir
if (!isset($_SESSION['custom_pages'])) {
    $_SESSION['custom_pages'] = [];
}

// Função para salvar uma nova configuração de página
function savePage($name, $fields, $table) {
    $page = [
        'id' => uniqid(),
        'name' => $name,
        'table' => $table,
        'fields' => $fields,
        'created_at' => date('Y-m-d H:i:s')
    ];
    $_SESSION['custom_pages'][] = $page;
    return $page['id'];
}

// Função para obter todas as páginas
function getPages() {
    return $_SESSION['custom_pages'] ?? [];
}

// Função para obter uma página específica
function getPage($id) {
    foreach ($_SESSION['custom_pages'] as $page) {
        if ($page['id'] === $id) return $page;
    }
    return null;
}

// Função para deletar uma página
function deletePage($id) {
    foreach ($_SESSION['custom_pages'] as $key => $page) {
        if ($page['id'] === $id) {
            unset($_SESSION['custom_pages'][$key]);
            $_SESSION['custom_pages'] = array_values($_SESSION['custom_pages']);
            return true;
        }
    }
    return false;
}
?>
