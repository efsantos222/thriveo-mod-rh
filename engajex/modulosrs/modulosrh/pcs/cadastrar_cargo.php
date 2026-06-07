<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = $_POST['nome'];
    $descricao = $_POST['descricao'];
    $nivel = $_POST['nivel'];
    $experiencia = $_POST['experiencia'];
    $formacao = $_POST['formacao'];
    $faixa_salarial = $_POST['faixa_salarial'];

    $stmt = $pdo->prepare("INSERT INTO cargos (nome, descricao, nivel, experiencia_minima, formacao_academica, faixa_salarial) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$nome, $descricao, $nivel, $experiencia, $formacao, $faixa_salarial]);

    echo "Cargo cadastrado com sucesso!";
}
?>
