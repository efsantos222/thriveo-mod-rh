CREATE TABLE IF NOT EXISTS portfolio_services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    revenue_model VARCHAR(255),
    target_audience VARCHAR(255),
    key_resources TEXT,
    
    -- Eixo Y: Maturidade Operacional (1-10)
    score_standardization INT DEFAULT 0,
    score_scalability INT DEFAULT 0,
    score_margin INT DEFAULT 0,
    score_automation INT DEFAULT 0,
    maturity_avg DECIMAL(4,2) DEFAULT 0.00,
    
    -- Eixo X: Sinergia Estratégica (1-10)
    score_lead_gen INT DEFAULT 0,
    score_relationship INT DEFAULT 0,
    score_cross_sell INT DEFAULT 0,
    score_brand_value INT DEFAULT 0,
    synergy_avg DECIMAL(4,2) DEFAULT 0.00,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
