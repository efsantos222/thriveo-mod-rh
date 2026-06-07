<?php
// seed_tests.php
require_once 'modulosrs/softskill/config/database.php';

try {
    $db = new Database();
    $conn = $db->getConnection();

    // Clear existing (optional - maybe only for these types)
    // $conn->exec("DELETE FROM ss_questions");

    $questions = [
        // BIG FIVE (OCEAN)
        ['Big Five', 'Tenho facilidade em expressar minhas ideias para grupos de pessoas.', 'E'],
        ['Big Five', 'Costumo ser organizado e cumprir meus prazos rigorosamente.', 'C'],
        ['Big Five', 'Interesso-me por conceitos abstratos e novas experiências culturais.', 'O'],
        ['Big Five', 'Sinto-me angustiado ou preocupado com frequência.', 'N'],
        ['Big Five', 'Sou visto pelos outros como uma pessoa empática e colaboradora.', 'A'],
        ['Big Five', 'Prefiro ambientes tranquilos a festas ou grandes reuniões sociais.', 'E'],
        ['Big Five', 'Frequentemente busco novas formas de fazer as coisas (inovação).', 'O'],
        ['Big Five', 'Mantenho a calma mesmo sob pressão ou em situações difíceis.', 'N'],
        ['Big Five', 'Sou atencioso com as necessidades dos meus colegas.', 'A'],
        ['Big Five', 'A planejar minhas atividades, foco nos detalhes para evitar erros.', 'C'],

        // PDA (Personal Development Analysis)
        ['PDA', 'Tomo decisões rápidas, mesmo que envolvam riscos significativos.', 'Risco'],
        ['PDA', 'Foco muito mais no resultado final do que no processo para chegar lá.', 'Risco'],
        ['PDA', 'Tenho facilidade em convencer os outros sobre o meu ponto de vista.', 'Extrov'],
        ['PDA', 'Sou uma pessoa muito sociável e busco interação constante.', 'Extrov'],
        ['PDA', 'Prefiro trabalhar em um ritmo constante, sem mudanças bruscas.', 'Pacienc'],
        ['PDA', 'Consigo ouvir os outros pacientemente antes de dar minha opinião.', 'Pacienc'],
        ['PDA', 'Sinto-me desconfortável se as regras de um projeto não forem seguidas.', 'Norma'],
        ['PDA', 'Sou extremamente detalhista e reviso meu trabalho várias vezes.', 'Norma'],

        // Grit Scale (Resiliência)
        ['Grit Scale', 'Supero obstáculos com facilidade quando tenho um objetivo claro.', 'Resil'],
        ['Grit Scale', 'Mantenho o foco em projetos de longo prazo por meses ou anos.', 'Persev'],
        ['Grit Scale', 'Frequentemente me interesso por algo novo e abandono o que estava fazendo.', 'Persev'],
        ['Grit Scale', 'Sou um trabalhador diligente e nunca desisto de uma tarefa difícil.', 'Esforç'],
        ['Grit Scale', 'Já superei decepções importantes na vida para alcançar um sonho.', 'Resil'],
        ['Grit Scale', 'Minha determinação é maior do que o meu talento natural.', 'Garra'],

        // DISC (Complementares)
        ['DISC', 'Sinto satisfação em liderar projetos e assumir o controle.', 'D'],
        ['DISC', 'Gosto de ser o centro das atenções e animar o ambiente.', 'I'],
        ['DISC', 'Priorizo a harmonia do grupo acima da velocidade de execução.', 'S'],
        ['DISC', 'Sou muito cuidadoso com a precisão técnica das minhas tarefas.', 'C'],
        ['DISC', 'Reajo bem a desafios competitivos e pressão por resultados.', 'D'],
        ['DISC', 'Tenho facilidade em fazer novos amigos e conexões.', 'I'],
        ['DISC', 'Evito conflitos diretos sempre que possível.', 'S'],
        ['DISC', 'Gosto de seguir manuais e procedimentos padrão definidos.', 'C'],
        
        // MBTI (Complementares)
        ['MBTI', 'Recarrego minhas energias em momentos de solitude.', 'I'],
        ['MBTI', 'Confio mais em fatos concretos do que em intuições ou pressentimentos.', 'S'],
        ['MBTI', 'Tomo decisões baseadas na lógica e análise crítica, evitando emoções.', 'T'],
        ['MBTI', 'Gosto de ter minha rotina planejada e organizada com antecedência.', 'J'],
        ['MBTI', 'Sinto-me à vontade em ambientes dinâmicos onde o improviso é necessário.', 'P']
    ];

    $stmt = $conn->prepare("INSERT INTO ss_questions (test_type, question_text, dimension) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE question_text=VALUES(question_text)");

    foreach ($questions as $q) {
        $stmt->execute($q);
    }

    echo "Sucesso: Questões inseridas/atualizadas com sucesso!";

} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
?>
