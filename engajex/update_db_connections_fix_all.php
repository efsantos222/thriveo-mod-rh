<?php
require_once 'config.php';

try {
    echo "<h2>Iniciando Correção do Banco de Dados - Módulo Conexões</h2>";

    // 1. Create Tables if not exist (Base Structure)
    $sqlParticipants = "
    CREATE TABLE IF NOT EXISTS `connections_participants` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `company_id` int(11) NOT NULL,
      `name` varchar(255) NOT NULL,
      `status` enum('pending', 'completed') DEFAULT 'pending',
      `created_at` timestamp DEFAULT current_timestamp(),
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";
    $pdo->exec($sqlParticipants);
    echo "✅ Tabela 'connections_participants' verificada.<br>";

    $sqlItems = "
    CREATE TABLE IF NOT EXISTS `connections_items` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `company_id` int(11) NOT NULL,
      `content` text NOT NULL,
      `is_active` boolean DEFAULT true,
      `created_at` timestamp DEFAULT current_timestamp(),
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";
    $pdo->exec($sqlItems);
    echo "✅ Tabela 'connections_items' verificada.<br>";

    $sqlConfig = "
    CREATE TABLE IF NOT EXISTS `connections_config` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `company_id` int(11) NOT NULL,
      `config_key` varchar(50) NOT NULL,
      `config_value` varchar(255) NOT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";
    $pdo->exec($sqlConfig);
    echo "✅ Tabela 'connections_config' verificada.<br>";


    // 2. Upgrade Tables (Add columns for v2 functionality)

    // Check game_type in Items
    $stmt = $pdo->query("SHOW COLUMNS FROM connections_items LIKE 'game_type'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE connections_items ADD COLUMN game_type VARCHAR(20) NOT NULL DEFAULT 'lightning'");
        $pdo->exec("ALTER TABLE connections_items ADD INDEX idx_game_type (game_type)");
        echo "✅ Coluna 'game_type' adicionada em 'connections_items'.<br>";
    }

    // Check game_type in Config
    $stmt = $pdo->query("SHOW COLUMNS FROM connections_config LIKE 'game_type'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE connections_config ADD COLUMN game_type VARCHAR(20) NOT NULL DEFAULT 'lightning'");

        // Update Indexes
        try {
            // Drop old unique key if exists
            $pdo->exec("DROP INDEX comp_key ON connections_config");
        } catch (Exception $e) { /* Ignore if index doesn't exist */
        }

        try {
            // Add new unique key
            $pdo->exec("CREATE UNIQUE INDEX comp_key ON connections_config (company_id, config_key, game_type)");
        } catch (Exception $e) { /* Ignore if index already exists/fails */
        }

        echo "✅ Tabela 'connections_config' atualizada para v2.<br>";
    }


    // 3. Seed Default Data (Populate games if empty)
    $companyId = $_SESSION['company_id'] ?? 1;

    function seedIfEmpty($pdo, $companyId, $type, $contents)
    {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM connections_items WHERE company_id = ? AND game_type = ?");
        $stmt->execute([$companyId, $type]);
        if ($stmt->fetchColumn() == 0) {
            $insert = $pdo->prepare("INSERT INTO connections_items (company_id, content, game_type) VALUES (?, ?, ?)");
            foreach ($contents as $c) {
                $insert->execute([$companyId, $c, $type]);
            }
            echo "✅ Dados padrão inseridos para jogo: $type<br>";
        }
    }

    // Lightning Defaults
    seedIfEmpty($pdo, $companyId, 'lightning', [
        "Fale sobre sua experiência profissional anterior.",
        "Comente sobre sua atuação (projetos, clientes, etc).",
        "Conte sobre sua formação acadêmica.",
        "Qual seu hobby favorito fora do trabalho?",
        "Um fato curioso sobre você."
    ]);

    // Story Defaults
    seedIfEmpty($pdo, $companyId, 'story', [
        "Era uma vez em um projeto secreto...",
        "De repente, o prazo foi encurtado e...",
        "Mas a equipe teve uma ideia genial...",
        "Infelizmente, faltou café na copa...",
        "No final, entregamos com sucesso porque..."
    ]);

    // Mystery Defaults
    seedIfEmpty($pdo, $companyId, 'mystery', [
        "Se você fosse um software, qual seria?",
        "Qual superpoder ajudaria no seu trabalho?",
        "Qual a tecnologia do futuro na sua opinião?",
        "Dica de ouro para produtividade?",
        "Maior desafio que já superou."
    ]);

    // Emoji Defaults
    seedIfEmpty($pdo, $companyId, 'emoji', [
        "Seu humor hoje em 3 emojis",
        "Seu fim de semana em emojis",
        "Sua função na empresa em emojis",
        "Seu filme favorito em emojis",
        "Sua reação a um bug em produção"
    ]);

    echo "<h3>Concluído com Sucesso!</h3>";
    echo "<a href='modules/connections.php'>Voltar para Conexões Virtuais</a>";

} catch (PDOException $e) {
    echo "❌ Erro Fatal: " . $e->getMessage();
}
?>