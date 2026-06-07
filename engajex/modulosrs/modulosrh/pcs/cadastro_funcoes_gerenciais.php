<?php
// Página para cadastro de funções gerenciais
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = $_POST['nome'];
    $nivel = $_POST['nivel'];
    $descricao = $_POST['descricao'];
    $experiencia = $_POST['experiencia'];
    $formacao = $_POST['formacao'];
    $faixa_salarial = $_POST['faixa_salarial'];

    $stmt = $pdo->prepare("INSERT INTO funcoes_gerenciais (nome, nivel, descricao, experiencia, formacao, faixa_salarial) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$nome, $nivel, $descricao, $experiencia, $formacao, $faixa_salarial]);

    echo "Função gerencial cadastrada com sucesso!";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Funções Gerenciais</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h1 class="mt-5">Cadastro de Funções Gerenciais</h1>
        <form action="cadastro_funcoes_gerenciais.php" method="POST">
            <div class="form-group">
                <label for="nome">Nome da Função</label>
                <input type="text" class="form-control" id="nome" name="nome" required>
            </div>
            <div class="form-group">
                <label for="nivel">Nível</label>
                <select class="form-control" id="nivel" name="nivel">
                    <option>Coordenador</option>
                    <option>Gerente</option>
                    <option>Diretor</option>
                </select>
            </div>
            <div class="form-group">
                <label for="descricao">Descrição</label>
                <textarea class="form-control" id="descricao" name="descricao" rows="3" required></textarea>
            </div>
            <div class="form-group">
                <label for="experiencia">Experiência (anos)</label>
                <input type="number" class="form-control" id="experiencia" name="experiencia" required>
            </div>
            <div class="form-group">
                <label for="formacao">Formação Acadêmica</label>
                <input type="text" class="form-control" id="formacao" name="formacao" required>
            </div>
            <div class="form-group">
                <label for="faixa_salarial">Faixa Salarial</label>
                <input type="number" class="form-control" id="faixa_salarial" name="faixa_salarial" step="0.01" required>
            </div>
            <button type="submit" class="btn btn-primary">Cadastrar</button>
        </form>
    </div>
</body>
</html>
