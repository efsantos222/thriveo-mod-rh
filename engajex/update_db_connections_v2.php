<?php
require_once 'config.php';

try {
    // 1. Add game_type column to Items table if not exists
    $stmt = $pdo->query("SHOW COLUMNS FROM connections_items LIKE 'game_type'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE connections_items ADD COLUMN game_type VARCHAR(20) NOT NULL DEFAULT 'lightning'");
        $pdo->exec("ALTER TABLE connections_items ADD INDEX idx_game_type (game_type)");
        echo "Coluna 'game_type' adicionada em 'connections_items'.<br>";
    }

    // 2. Add game_type column to Config table if not exists
    $stmt = $pdo->query("SHOW COLUMNS FROM connections_config LIKE 'game_type'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE connections_config ADD COLUMN game_type VARCHAR(20) NOT NULL DEFAULT 'lightning'");

        // Fix Unique Key to include game_type
        // First drop existing key if possible (ignoring error if not found)
        try {
            $pdo->exec("ALTER TABLE connections_config DROP INDEX comp_key");
        } catch (Exception $ex) {
        }

        $pdo->exec("ALTER TABLE connections_config ADD UNIQUE KEY comp_key (company_id, config_key, game_type)");
        echo "Tabela 'connections_config' atualizada para suportar múltiplos jogos.<br>";
    }

    // 3. Seed Default Items for the new games
    $companyId = $_SESSION['company_id'] ?? 1; // Fallback for script context

    // Helper to insert defaults if empty
    function seedItems($pdo, $companyId, $type, $items)
    {
        $check = $pdo->prepare("SELECT COUNT(*) FROM connections_items WHERE company_id = ? AND game_type = ?");
        $check->execute([$companyId, $type]);
        if ($check->fetchColumn() == 0) {
            $stmt = $pdo->prepare("INSERT INTO connections_items (company_id, content, game_type) VALUES (?, ?, ?)");
            foreach ($items as $item) {
                $stmt->execute([$companyId, $item, $type]);
            }
            echo "Itens padrão criados para: $type<br>";
        }
    }

    // Seed Data
    seedItems($pdo, $companyId, 'story', [
        "Era uma vez em um escritório muito distante...",
        "De repente, o servidor caiu e...",
        "Mas o estagiário teve uma ideia brilhante...",
        "Infelizmente, o café tinha acabado...",
        "No final, todos aprenderam que..."
    ]);

    seedItems($pdo, $companyId, 'mystery', [
        "Qual foi seu maior erro profissional e o que aprendeu?",
        "Se pudesse ter um superpoder no trabalho, qual seria?",
        "Qual tecnologia você acha que vai revolucionar nossa área?",
        "Uma dica de produtividade infalível?",
        "Qual o melhor conselho que já recebeu?"
    ]);

    seedItems($pdo, $companyId, 'emoji', [
        "Defina seu humor hoje usando 3 emojis",
        "Conte como foi seu fim de semana apenas com emojis",
        "Sua função na empresa em emojis",
        "O projeto dos seus sonhos em emojis",
        "Sua reação quando o código compila de primeira"
    ]);

    echo "Banco de dados atualizado com sucesso!";

} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
}
?>