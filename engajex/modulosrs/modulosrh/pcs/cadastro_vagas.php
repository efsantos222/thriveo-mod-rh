<?php
// Página para cadastro e controle de vagas
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cargo_id = $_POST['cargo_id'];
    $status = $_POST['status'];
    $orcamento = $_POST['orcamento'];
    $historico = $_POST['historico'];

    $stmt = $pdo->prepare("INSERT INTO vagas (cargo_id, status, orcamento, historico_movimentacoes) VALUES (?, ?, ?, ?)");
    $stmt->execute([$cargo_id, $status, $orcamento, $historico]);

    echo "Vaga cadastrada com sucesso!";
}

// Exemplo de listagem de cargos para seleção
$cargos = $pdo->query("SELECT id, nome FROM cargos")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro e Controle de Vagas</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h1 class="mt-5">Cadastro e Controle de Vagas</h1>
        <form action="cadastro_vagas.php" method="POST">
            <div class="form-group">
                <label for="cargo_id">Cargo</label>
                <select class="form-control" id="cargo_id" name="cargo_id">
                    <?php foreach ($cargos as $cargo): ?>
                    <option value="<?= $cargo['id'] ?>"><?= $cargo['nome'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="status">Status</label>
                <select class="form-control" id="status" name="status">
                    <option>Ocupada</option>
                    <option>Aberta</option>
                    <option>Extinta</option>
                </select>
            </div>
            <div class="form-group">
                <label for="orcamento">Orçamento</label>
                <input type="number" class="form-control" id="orcamento" name="orcamento" step="0.01" required>
            </div>
            <div class="form-group">
                <label for="historico">Histórico de Movimentações</label>
                <textarea class="form-control" id="historico" name="historico" rows="3"></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Cadastrar</button>
        </form>
    </div>
</body>
</html>
