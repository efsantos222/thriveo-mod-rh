<?php

$nome = $_GET['nome'] ?? ''; // Ou $_POST['nome'], dependendo do método que você usar

// Caminho para a pasta onde os arquivos CSV são armazenados
$pastaCsv = 'csv/';

$nomeExiste = false;

// Lista todos os arquivos CSV na pasta
foreach (glob($pastaCsv . "*.csv") as $arquivo) {
    $conteudo = file_get_contents($arquivo);
    // Verifica se o nome existe no arquivo
    if (strpos($conteudo, $nome) !== false) {
        $nomeExiste = true;
        break;
    }
}

// Retorna uma resposta JSON
header('Content-Type: application/json');
echo json_encode(['nomeExiste' => $nomeExiste]);