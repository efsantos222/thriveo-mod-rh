<?php
if (isset($_POST['dados']) && isset($_POST['nome'])) {
    $dados = $_POST['dados'];
    $nomeRespondente = preg_replace("/[^a-zA-Z0-9]+/", "", $_POST['nome']);
    $nomeArquivo = "csv/" . $nomeRespondente . "_avaliacao.csv";

    file_put_contents($nomeArquivo, $dados);
    echo "Arquivo salvo com sucesso.";
} else {
    echo "Nenhum dado recebido.";
}
?>
