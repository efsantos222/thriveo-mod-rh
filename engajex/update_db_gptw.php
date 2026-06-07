<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once 'config.php';

try {
    echo "<h2>Criando Tabelas GPTW...</h2>";

    // Helper to create standard GPTW structured tables (Dimensao, Visao Emp, Visao Area, GPTW Nac)
    function createStandardGptwTable($pdo, $tableName)
    {
        $sql = "CREATE TABLE IF NOT EXISTS $tableName (
            id INT AUTO_INCREMENT PRIMARY KEY,
            company_id INT NOT NULL,
            mes VARCHAR(3),
            ano VARCHAR(4),
            mes_n INT,
            data_ref DATE,
            dimensao VARCHAR(255),
            visao_empresa INT DEFAULT 0,
            visao_area INT DEFAULT 0,
            gptw_nacional INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX(company_id),
            INDEX(ano),
            INDEX(mes_n)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        $pdo->exec($sql);
        echo "Tabela <b>$tableName</b> criada/verificada.<br>";
    }

    // 1. Tabela geral_gptw
    createStandardGptwTable($pdo, 'geral_gptw');

    // 2. Tabela respeito_gptw
    createStandardGptwTable($pdo, 'respeito_gptw');

    // 3. Tabela demais_gptw (Structure differs slightly: Metrica column instead of GPTW Nac? No, user listed distinct columns)
    // User requested: Mês, Ano, MesN, Data, Dimensão, Métrica, Visão Empresa, Visão Área.
    $pdo->exec("CREATE TABLE IF NOT EXISTS demais_gptw (
        id INT AUTO_INCREMENT PRIMARY KEY,
        company_id INT NOT NULL,
        mes VARCHAR(3),
        ano VARCHAR(4),
        mes_n INT,
        data_ref DATE,
        dimensao VARCHAR(255),
        metrica VARCHAR(255),
        visao_empresa INT DEFAULT 0,
        visao_area INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX(company_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "Tabela <b>demais_gptw</b> criada/verificada.<br>";

    // 4. Tabela credibilidade_gptw
    createStandardGptwTable($pdo, 'credibilidade_gptw');

    // 5. Tabela imparcialidade_gptw
    createStandardGptwTable($pdo, 'imparcialidade_gptw');

    // 6. Tabela orgulho_gptw
    createStandardGptwTable($pdo, 'orgulho_gptw');

    // 7. Tabela camaradagem_gptw
    createStandardGptwTable($pdo, 'camaradagem_gptw');

    // 8. Tabela adicionais_gptw
    createStandardGptwTable($pdo, 'adicionais_gptw');

    // 9. Tabela bench_gptw
    createStandardGptwTable($pdo, 'bench_gptw');

    // 10. Tabela enps_gptw
    // User: Mês, Ano, MesN, Data, Visão Empresa
    $pdo->exec("CREATE TABLE IF NOT EXISTS enps_gptw (
        id INT AUTO_INCREMENT PRIMARY KEY,
        company_id INT NOT NULL,
        mes VARCHAR(3),
        ano VARCHAR(4),
        mes_n INT,
        data_ref DATE,
        visao_empresa INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX(company_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "Tabela <b>enps_gptw</b> criada/verificada.<br>";

    // 11 (A). Tabela lidera_gptw
    // User: Mês, Ano, MesN, Data, Visão Empresa, Sig (char)
    $pdo->exec("CREATE TABLE IF NOT EXISTS lidera_gptw (
        id INT AUTO_INCREMENT PRIMARY KEY,
        company_id INT NOT NULL,
        mes VARCHAR(3),
        ano VARCHAR(4),
        mes_n INT,
        data_ref DATE,
        visao_empresa INT DEFAULT 0,
        sig VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX(company_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "Tabela <b>lidera_gptw</b> criada/verificada.<br>";

    // 11 (B). Tabela ivr_gptw (Innovation)
    // User: Mês, Ano, MesN, Data, Etapa (char)
    $pdo->exec("CREATE TABLE IF NOT EXISTS ivr_gptw (
        id INT AUTO_INCREMENT PRIMARY KEY,
        company_id INT NOT NULL,
        mes VARCHAR(3),
        ano VARCHAR(4),
        mes_n INT,
        data_ref DATE,
        etapa VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX(company_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "Tabela <b>ivr_gptw</b> criada/verificada.<br>";

    // 11 (C). Tabela particip_gptw
    // User: Mês, Ano, MesN, Data, convidados, respostas, comentários
    $pdo->exec("CREATE TABLE IF NOT EXISTS particip_gptw (
        id INT AUTO_INCREMENT PRIMARY KEY,
        company_id INT NOT NULL,
        mes VARCHAR(3),
        ano VARCHAR(4),
        mes_n INT,
        data_ref DATE,
        convidados INT DEFAULT 0,
        respostas INT DEFAULT 0,
        comentarios INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX(company_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "Tabela <b>particip_gptw</b> criada/verificada.<br>";

    // 12 (A). Tabela reunioes_gptw
    // User: nenhuma, uma, duas, tres, maisqtres
    $pdo->exec("CREATE TABLE IF NOT EXISTS reunioes_gptw (
        id INT AUTO_INCREMENT PRIMARY KEY,
        company_id INT NOT NULL,
        mes VARCHAR(3),
        ano VARCHAR(4),
        mes_n INT,
        data_ref DATE,
        nenhuma INT DEFAULT 0,
        uma INT DEFAULT 0,
        duas INT DEFAULT 0,
        tres INT DEFAULT 0,
        maisqtres INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX(company_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "Tabela <b>reunioes_gptw</b> criada/verificada.<br>";

    // 12 (B). Tabela melhorar_gptw
    // User: caracteristica (char), contagem (int)
    $pdo->exec("CREATE TABLE IF NOT EXISTS melhorar_gptw (
        id INT AUTO_INCREMENT PRIMARY KEY,
        company_id INT NOT NULL,
        mes VARCHAR(3),
        ano VARCHAR(4),
        mes_n INT,
        data_ref DATE,
        caracteristica TEXT,
        contagem INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX(company_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "Tabela <b>melhorar_gptw</b> criada/verificada.<br>";

    // 13. Tabela manter_gptw
    // User: caracteristica (char), contagem (int)
    $pdo->exec("CREATE TABLE IF NOT EXISTS manter_gptw (
        id INT AUTO_INCREMENT PRIMARY KEY,
        company_id INT NOT NULL,
        mes VARCHAR(3),
        ano VARCHAR(4),
        mes_n INT,
        data_ref DATE,
        caracteristica TEXT,
        contagem INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX(company_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "Tabela <b>manter_gptw</b> criada/verificada.<br>";

    echo "<br><h3>Sucesso! Todas as tabelas GPTW foram processadas.</h3>";

} catch (PDOException $e) {
    echo "Erro ao criar tabelas: " . $e->getMessage();
}
?>