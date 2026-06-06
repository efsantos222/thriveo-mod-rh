<?php
// 5w2h/import_actions.php
require_once 'includes/config.php';

// Ações extraídas do sistema-5w2h.jsx
$initial_actions = [
    [
        "what" => "Unificar plataformas de RH sob marca ProfTest",
        "why" => "Eliminar canibalização e confusão de marca entre Thriveo, TestProf e ProfTest",
        "where" => "ProfTest (proftest.com.br)",
        "when_start" => "2026-04-01",
        "when_end" => "2026-06-30",
        "who" => "Ezequiel + Equipe de Produto",
        "how" => "Consolidar funcionalidades, redirecionar domínios, migrar dados",
        "how_much" => "R$ 15.000",
        "category" => "proftest",
        "priority" => "critica",
        "status" => "pendente",
        "progress" => 0,
        "notes" => "Fase 1 do Plano Estratégico — consolidação e especialização",
    ],
    [
        "what" => "Estruturar consultoria MAIA como serviço formal",
        "why" => "Monetizar expertise em IA e Transformação Digital com metodologia proprietária",
        "where" => "ezequiel.pro.br + ProfTest",
        "when_start" => "2026-03-15",
        "when_end" => "2026-05-15",
        "who" => "Ezequiel",
        "how" => "Criar página de serviços, definir pacotes Flash/Standard/Enterprise, material comercial",
        "how_much" => "R$ 5.000",
        "category" => "consultoria",
        "priority" => "alta",
        "status" => "andamento",
        "progress" => 35,
        "notes" => "Metodologia MAIA já documentada. Falta criar material comercial e página web.",
    ],
    [
        "what" => "Preparar e vender Workshop IA Generativa",
        "why" => "Gerar receita imediata (R$ 15.000+) e captar leads para programa de 90 dias",
        "where" => "Empresas da região do ABC / presencial + remoto",
        "when_start" => "2026-04-01",
        "when_end" => "2026-04-30",
        "who" => "Ezequiel",
        "how" => "Usar material do workshop1_ia_generativa, prospectar empresas, agendar sessões",
        "how_much" => "R$ 2.000 (materiais e logística)",
        "category" => "workshops",
        "priority" => "alta",
        "status" => "pendente",
        "progress" => 0,
        "notes" => "Material completo já desenvolvido. Foco na prospecção comercial.",
    ],
    [
        "what" => "Implementar modelo de precificação na ProfTest",
        "why" => "Converter visitantes em clientes pagantes com pricing transparente",
        "where" => "proftest.com.br",
        "when_start" => "2026-05-01",
        "when_end" => "2026-05-31",
        "who" => "Ezequiel + Equipe de Produto",
        "how" => "Criar planos Básico (R$ 499), Profissional (R$ 999), Enterprise + taxa de sucesso 15%",
        "how_much" => "R$ 3.000",
        "category" => "proftest",
        "priority" => "media",
        "status" => "pendente",
        "progress" => 0,
        "notes" => "Fase 2 — Integração e Geração de Receita",
    ],
    [
        "what" => "Monetizar portal saocaetanodosul.net com vagas locais",
        "why" => "Criar nova fonte de receita e fortalecer presença local da ProfTest",
        "where" => "saocaetanodosul.net",
        "when_start" => "2026-07-01",
        "when_end" => "2026-09-30",
        "who" => "Ezequiel + Equipe de Vendas",
        "how" => "Integrar anúncios de vagas TI com ProfTest, contatar empresas locais do ABC",
        "how_much" => "R$ 5.000",
        "category" => "portais",
        "priority" => "baixa",
        "status" => "pendente",
        "progress" => 0,
        "notes" => "Fase 3 — Expansão do Ecossistema (6-12 meses)",
    ],
];

try {
    // 1. Encontrar o ID do usuário Ezequiel
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
    $stmt->execute(['ezequiel.santos@gmail.com']);
    $user = $stmt->fetch();

    if (!$user) {
        die("Erro: Usuário administrador não encontrado no banco de dados. Execute o SQL de criação primeiro.");
    }

    $user_id = $user['id'];
    $imported = 0;

    // 2. Limpar ações existentes do usuário (opcional, para recomeçar limpo)
    $pdo->prepare("DELETE FROM actions WHERE user_id = ?")->execute([$user_id]);

    // 3. Inserir as novas ações
    $sql = "INSERT INTO actions (user_id, what, why, `where`, who, when_start, when_end, how, how_much, category, priority, status, progress, notes) 
            VALUES (:user_id, :what, :why, :where, :who, :when_start, :when_end, :how, :how_much, :category, :priority, :status, :progress, :notes)";

    $stmt = $pdo->prepare($sql);

    foreach ($initial_actions as $action) {
        $action['user_id'] = $user_id;
        $stmt->execute($action);
        $imported++;
    }

    echo "Sucesso! $imported ações foram importadas para o usuário ezequiel.santos@gmail.com.";

} catch (PDOException $e) {
    echo "Erro durante a importação: " . $e->getMessage();
}
?>