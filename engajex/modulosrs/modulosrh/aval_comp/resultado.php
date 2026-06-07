<?php

$pastaCsv = 'csv/';
$animais = ['A' => 'Tubarão', 'C' => 'Gato', 'I' => 'Águia', 'O' => 'Lobo'];

if (!is_dir($pastaCsv)) {
    die("Pasta não encontrada: $pastaCsv");
}

$diretorio = opendir($pastaCsv);
if (!$diretorio) {
    die("Não foi possível abrir a pasta $pastaCsv");
}

echo "<html><body>";

while (($arquivo = readdir($diretorio)) !== false) {
    if (pathinfo($arquivo, PATHINFO_EXTENSION) === 'csv') {
        $caminhoCompleto = $pastaCsv . '/' . $arquivo;
        $handle = fopen($caminhoCompleto, 'r');
        if (!$handle) {
            echo "Não foi possível abrir o arquivo: $arquivo<br>";
            continue;
        }

        $respostas = ['A' => 0, 'C' => 0, 'I' => 0, 'O' => 0];
        fgetcsv($handle); // Pula a linha de cabeçalho

        while (($linha = fgetcsv($handle)) !== false) {
            if (isset($respostas[$linha[1]])) {
                $respostas[$linha[1]]++;
            }
        }

        fclose($handle);

        // Exibindo os resultados em uma tabela HTML
        echo "<h2>Resultado de $arquivo</h2>";
        echo "<table border='1'><tr><th>Animal</th><th>Quantidade</th></tr>";
        foreach ($respostas as $codigo => $quantidade) {
            echo "<tr><td>" . $animais[$codigo] . "</td><td>" . $quantidade . "</td></tr>";
        }
        echo "</table><br>";
        echo "<h4>Dominância - Lobo(O): Representa pessoas que são diretas, assertivas, e focadas em resultados. Eles preferem liderar e resolver problemas. São Lobos por sua liderança e determinação.</h4>";
        echo "<p>Tendência à Impaciência: Sua ênfase em resultados rápidos pode levá-los a serem impacientes com processos mais lentos e detalhados, potencialmente ignorando etapas importantes.
    	Possibilidade de Autoritarismo: Em sua busca por liderança e controle, podem tornar-se autoritários, o que pode afetar negativamente o moral da equipe e a colaboração.
    	Risco de Conflito: A abordagem direta e assertiva pode levar a conflitos, especialmente se percebida como insensível ou desconsiderada pelas necessidades dos outros.</p>";
        echo "<h4>Influência - Gato(C): São pessoas sociáveis, otimistas e persuasivas. Gostam de interagir e motivar os outros. São Gato, por sua agilidade social e charme.</h4>";
        echo "<p>Foco Excessivo em Popularidade: Pode levar a decisões baseadas mais em ser querido do que em ser eficaz, potencialmente comprometendo a qualidade das decisões.
    	Desatenção aos Detalhes: O entusiasmo por interações e ideias grandes pode resultar em uma negligência para com os detalhes e a organização necessária para a implementação eficaz.
    	Dificuldade em Lidar com Críticas: Pode haver uma tendência a levar críticas para o lado pessoal, dificultando o crescimento pessoal e profissional.</p>";
        echo "<h4>Estabilidade - Águia(I): Indivíduos calmos, pacientes e consistentes, que valorizam a segurança e a estabilidade. São Águia, por sua capacidade de fluir e se adaptar.</h4>";
        echo "<p>Resistência à Mudança: Um foco profundo na estabilidade pode torná-los resistentes a mudanças necessárias, limitando a inovação e a adaptação em ambientes dinâmicos.
    	Dificuldade em Tomada de Decisão Rápida: Em situações que exigem decisões rápidas e ação imediata, a calma e a precaução podem ser vistas como hesitação ou falta de iniciativa.
    	Tendência ao Conformismo: A preferência pela harmonia e estabilidade pode levar a um excesso de conformismo, evitando riscos que poderiam levar a melhorias significativas.</p>";
        echo "<h4>Conformidade - Tubarão(A): Pessoas que valorizam a qualidade, precisão, independência e competência. São analíticas e organizadas. São Tubarão, por sua precisão e foco em objetivos.</h4>";
        echo "<p>Perfeccionismo Paralisante: A busca constante por precisão e perfeição pode levar à paralisia analítica, onde a tomada de decisão é adiada indefinidamente em busca de uma solução perfeita.
    	Dificuldade em Trabalho em Equipe: A ênfase em independência e competência pode dificultar a colaboração eficaz, pois podem ter dificuldade em confiar nas contribuições e habilidades dos outros.
    	Resistência à Inovação: O foco em procedimentos estabelecidos e na qualidade pode torná-los resistentes a novas ideias e abordagens, potencialmente limitando a criatividade e inovação.</p>";
    }
}

echo "</body></html>";
closedir($diretorio);