<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once 'config.php';

try {
    // 1. Tabela Contratações
    $pdo->exec("CREATE TABLE IF NOT EXISTS rh_contratacoes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        company_id INT NOT NULL,
        mes VARCHAR(3),
        ano VARCHAR(4),
        mes_n INT,
        data_ref DATE,
        contratacao INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX(company_id),
        UNIQUE(company_id, mes_n, ano)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // 2. Tabela Demissões
    $pdo->exec("CREATE TABLE IF NOT EXISTS rh_demissoes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        company_id INT NOT NULL,
        mes VARCHAR(3),
        ano VARCHAR(4),
        mes_n INT,
        data_ref DATE,
        demissao_p INT DEFAULT 0,
        demissao_e INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX(company_id),
        UNIQUE(company_id, mes_n, ano)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // 3. Tabela Efetivo
    $pdo->exec("CREATE TABLE IF NOT EXISTS rh_efetivo (
        id INT AUTO_INCREMENT PRIMARY KEY,
        company_id INT NOT NULL,
        mes VARCHAR(3),
        ano VARCHAR(4),
        mes_n INT,
        data_ref DATE,
        clt INT DEFAULT 0,
        pj INT DEFAULT 0,
        coop INT DEFAULT 0,
        scp INT DEFAULT 0,
        estag INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX(company_id),
        UNIQUE(company_id, mes_n, ano)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // 4. Tabela Horas Extras
    $pdo->exec("CREATE TABLE IF NOT EXISTS rh_horasext (
        id INT AUTO_INCREMENT PRIMARY KEY,
        company_id INT NOT NULL,
        mes VARCHAR(3),
        ano VARCHAR(4),
        mes_n INT,
        data_ref DATE,
        he INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX(company_id),
        UNIQUE(company_id, mes_n, ano)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // 5. Tabela Folha Pagamento
    $pdo->exec("CREATE TABLE IF NOT EXISTS rh_folhapag (
        id INT AUTO_INCREMENT PRIMARY KEY,
        company_id INT NOT NULL,
        mes VARCHAR(3),
        ano VARCHAR(4),
        mes_n INT,
        data_ref DATE,
        custo DECIMAL(15,2) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX(company_id),
        UNIQUE(company_id, mes_n, ano)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // 6. Tabela Humor
    $pdo->exec("CREATE TABLE IF NOT EXISTS rh_humor (
        id INT AUTO_INCREMENT PRIMARY KEY,
        company_id INT NOT NULL,
        mes VARCHAR(3),
        ano VARCHAR(4),
        mes_n INT,
        data_ref DATE,
        mfeliz INT DEFAULT 0,
        feliz INT DEFAULT 0,
        neutro INT DEFAULT 0,
        triste INT DEFAULT 0,
        mtriste INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX(company_id),
        UNIQUE(company_id, mes_n, ano)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // 7. Tabela Feedback (Fixed redundant numbering in prompt)
    $pdo->exec("CREATE TABLE IF NOT EXISTS rh_feedback (
        id INT AUTO_INCREMENT PRIMARY KEY,
        company_id INT NOT NULL,
        mes VARCHAR(3),
        ano VARCHAR(4),
        mes_n INT,
        data_ref DATE,
        feedback INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX(company_id),
        UNIQUE(company_id, mes_n, ano)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // 8. Tabela R&S Tempo (Fixed numbering)
    $pdo->exec("CREATE TABLE IF NOT EXISTS rh_rstempo (
        id INT AUTO_INCREMENT PRIMARY KEY,
        company_id INT NOT NULL,
        mes VARCHAR(3),
        ano VARCHAR(4),
        mes_n INT,
        data_ref DATE,
        vagas INT DEFAULT 0,
        tempo DECIMAL(10,2) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX(company_id),
        UNIQUE(company_id, mes_n, ano)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // 9. Tabela R&S Vagas
    $pdo->exec("CREATE TABLE IF NOT EXISTS rh_rsvagas (
        id INT AUTO_INCREMENT PRIMARY KEY,
        company_id INT NOT NULL,
        mes VARCHAR(3),
        ano VARCHAR(4),
        mes_n INT,
        data_ref DATE,
        abertas INT DEFAULT 0,
        fechadas INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX(company_id),
        UNIQUE(company_id, mes_n, ano)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // 10. Tabela R&S Desconto (Economia)
    $pdo->exec("CREATE TABLE IF NOT EXISTS rh_rsdesc (
        id INT AUTO_INCREMENT PRIMARY KEY,
        company_id INT NOT NULL,
        mes VARCHAR(3),
        ano VARCHAR(4),
        mes_n INT,
        data_ref DATE,
        orcado DECIMAL(15,2) DEFAULT 0,
        contratado DECIMAL(15,2) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX(company_id),
        UNIQUE(company_id, mes_n, ano)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // 11. Tabela Realocação
    $pdo->exec("CREATE TABLE IF NOT EXISTS rh_realoc (
        id INT AUTO_INCREMENT PRIMARY KEY,
        company_id INT NOT NULL,
        mes VARCHAR(3),
        ano VARCHAR(4),
        mes_n INT,
        data_ref DATE,
        entrada INT DEFAULT 0,
        realoc INT DEFAULT 0,
        desligado INT DEFAULT 0,
        saldo INT DEFAULT 0,
        total INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX(company_id),
        UNIQUE(company_id, mes_n, ano)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // 12. Tabela Capacitação
    $pdo->exec("CREATE TABLE IF NOT EXISTS rh_capacita (
        id INT AUTO_INCREMENT PRIMARY KEY,
        company_id INT NOT NULL,
        mes VARCHAR(3),
        ano VARCHAR(4),
        mes_n INT,
        data_ref DATE,
        cumpriu INT DEFAULT 0,
        parcial INT DEFAULT 0,
        sreg INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX(company_id),
        UNIQUE(company_id, mes_n, ano)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // Permissions Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS rh_permissions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        company_id INT NOT NULL,
        user_id INT NOT NULL,
        can_view BOOLEAN DEFAULT TRUE,
        can_edit BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE(company_id, user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    echo "Todas as tabelas de Indicadores RH foram criadas com sucesso!";

} catch (PDOException $e) {
    echo "Erro ao criar tabelas: " . $e->getMessage();
}
?>