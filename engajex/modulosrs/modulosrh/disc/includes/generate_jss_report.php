<?php
require_once 'jpgraph/jpgraph.php';
require_once 'jpgraph/jpgraph_bar.php';
require_once 'jpgraph/jpgraph_line.php';

function generate_jss_report($email) {
    $results_file = 'resultados/' . str_replace(['@', '.'], '_', $email) . '_avaliacao_jss.csv';
    if (!file_exists($results_file)) {
        return false;
    }
    
    $data = array_map('str_getcsv', file($results_file))[0];
    
    // Extrair dados
    $freq_mean = (float)$data[2];
    $grav_mean = (float)$data[3];
    $stress_index = (float)$data[4];
    
    // Extrair frequências e gravidades individuais
    $frequencies = [];
    $severities = [];
    for ($i = 0; $i < 10; $i++) {
        $frequencies[] = (float)$data[5 + ($i * 2)];
        $severities[] = (float)$data[6 + ($i * 2)];
    }
    
    // Criar gráfico
    $graph = new Graph(800, 400, 'auto');
    $graph->SetScale("textlin", 0, 5);
    $graph->SetMargin(50, 30, 50, 120);
    
    // Configurar título e eixos
    $graph->title->Set('Perfil de Estresse no Trabalho (JSS)');
    $graph->xaxis->SetTitle('Situações', 'center');
    $graph->xaxis->SetLabelAngle(45);
    $graph->yaxis->SetTitle('Pontuação');
    
    // Adicionar barras para frequência
    $freq_plot = new BarPlot($frequencies);
    $freq_plot->SetLegend('Frequência');
    $freq_plot->SetFillColor('skyblue');
    
    // Adicionar barras para gravidade
    $sev_plot = new BarPlot($severities);
    $sev_plot->SetLegend('Gravidade');
    $sev_plot->SetFillColor('lightcoral');
    
    // Agrupar barras
    $group_plot = new GroupBarPlot(array($freq_plot, $sev_plot));
    $graph->Add($group_plot);
    
    // Adicionar rótulos no eixo X
    $situations = [
        "Prazos curtos",
        "Cobranças",
        "Sobrecarga",
        "Falta controle",
        "Conflitos",
        "Hora extra",
        "Falta clareza",
        "Falta apoio",
        "Críticas",
        "Reconhecimento"
    ];
    $graph->xaxis->SetTickLabels($situations);
    
    // Adicionar índice de estresse como texto
    $stress_text = sprintf('Índice de Estresse: %.2f / 5.00', $stress_index);
    $txt = new Text($stress_text);
    $txt->SetPos(0.5, 0.02, 'center', 'top');
    $txt->SetFont(FF_ARIAL, FS_BOLD, 12);
    $graph->AddText($txt);
    
    // Salvar gráfico
    $graph_file = 'resultados/' . str_replace(['@', '.'], '_', $email) . '_grafico_jss.png';
    $graph->Stroke($graph_file);
    
    return true;
}
