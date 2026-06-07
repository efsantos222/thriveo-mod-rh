<?php
$pasta = 'csv/';
$dados = [];

foreach (new DirectoryIterator($pasta) as $fileInfo) {
    if ($fileInfo->isDot() || $fileInfo->getExtension() !== 'csv') continue;
    $conteudo = array_count_values(str_getcsv(file_get_contents($fileInfo->getPathname()), ','));
    $dados[$fileInfo->getFilename()] = [
        'Aguia' => $conteudo['I'] ?? 0,
        'Gato' => $conteudo['C'] ?? 0,
        'Tubarao' => $conteudo['A'] ?? 0,
        'Lobo' => $conteudo['O'] ?? 0,
    ];
}

header('Content-Type: application/json');
echo json_encode($dados);