-- Criação do Banco de Dados
CREATE DATABASE IF NOT EXISTS efsantos_talen
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE efsantos_talen;

-- ==========================================
-- TABELAS DE AUTENTICAÇÃO E CONTROLE DE ACESSO
-- ==========================================

-- Tabela de Papéis (Roles)
CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    description VARCHAR(255)
);

-- Inserindo os papéis solicitados
INSERT INTO roles (name, description) VALUES 
('Administrador', 'Cadastra empresas e responsáveis, possui controle total do sistema'),
('Empresa', 'Entidade contratante, publica vagas'),
('Responsável por Empresa', 'Pode acessar os dados de candidatos e gerenciar vagas da sua empresa'),
('Candidato', 'Usuário que completa seu perfil profissional e busca oportunidades');

-- Tabela de Usuários
CREATE TABLE users (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    role_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE RESTRICT
);


-- ==========================================
-- TABELAS DE EMPRESAS E RECRUTAMENTO
-- ==========================================

-- Tabela de Empresas
CREATE TABLE companies (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    document_cnpj VARCHAR(20) UNIQUE, 
    registered_by_admin_id BIGINT, -- ID do Administrador que cadastrou a empresa
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (registered_by_admin_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Relação entre Usuários (Responsáveis) e Empresas
CREATE TABLE company_managers (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT NOT NULL,
    company_id BIGINT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    UNIQUE(user_id, company_id)
);

-- Tabela de Vagas
-- A regra de negócio "1 vaga por mês grátis, acima disso é paga" deve ser garantida no backend da aplicação 
-- validando vagas criadas no mês atual pela 'company_id' com 'is_paid' = false.
CREATE TABLE jobs (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    is_paid BOOLEAN DEFAULT FALSE, -- Identifica se esta vaga foi publicada como vaga paga
    payment_status ENUM('FREE', 'PENDING', 'PAID') DEFAULT 'FREE', 
    status ENUM('DRAFT', 'ACTIVE', 'CLOSED') DEFAULT 'ACTIVE',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NULL,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
);


-- ==========================================
-- TABELAS DE PERFIL DE CANDIDATOS 
-- (Mapeamento do cadastro.html para continuar o módulo "Completar Perfil Profissional")
-- ==========================================

-- Tabela do Perfil Principal do Candidato
CREATE TABLE candidate_profiles (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT NOT NULL UNIQUE,
    headline VARCHAR(255), -- Ex: "AI Engineer · LLMs · RAG"
    bio TEXT,
    location VARCHAR(255),
    current_company VARCHAR(255),
    years_experience INT DEFAULT 0,
    specialties VARCHAR(500), -- Armazenado como CSV ou string (ex: "LLM, RAG, NLP")
    linkedin_url VARCHAR(255),
    github_url VARCHAR(255),
    is_available BOOLEAN DEFAULT TRUE,
    is_public BOOLEAN DEFAULT TRUE,
    completeness_pct INT DEFAULT 0, -- Porcentagem de perfil completo
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tabela de Habilidades (Skills)
CREATE TABLE candidate_skills (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    candidate_id BIGINT NOT NULL,
    name VARCHAR(100) NOT NULL,
    level INT CHECK (level >= 1 AND level <= 5), -- Níveis 1 a 5, conforme as estrelinhas no HTML
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (candidate_id) REFERENCES candidate_profiles(id) ON DELETE CASCADE
);

-- Controle de visualização de perfis por Responsáveis de Empresas
CREATE TABLE candidate_profile_views (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    candidate_id BIGINT NOT NULL,
    manager_id BIGINT NOT NULL, -- O usuário "Responsável por Empresa" que visualizou
    viewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (candidate_id) REFERENCES candidate_profiles(id) ON DELETE CASCADE,
    FOREIGN KEY (manager_id) REFERENCES users(id) ON DELETE CASCADE
);
