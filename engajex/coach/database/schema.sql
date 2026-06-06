-- Coaching Tools System Database Schema

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- Tabela usuarios
CREATE TABLE IF NOT EXISTS usuarios (
    id_usuario INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    perfil ENUM('administrador', 'coach', 'coachee') NOT NULL DEFAULT 'coach',
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ultima_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela sessoes_coaching
CREATE TABLE IF NOT EXISTS sessoes_coaching (
    id_sessao INT PRIMARY KEY AUTO_INCREMENT,
    tipo VARCHAR(50) NOT NULL,
    id_coach INT NOT NULL,
    data_sessao DATE NOT NULL,
    hora_inicio TIME NOT NULL,
    hora_fim TIME NOT NULL,
    local VARCHAR(255),
    observacoes TEXT,
    status ENUM('agendada', 'concluida', 'cancelada') NOT NULL DEFAULT 'agendada',
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_coach) REFERENCES usuarios(id_usuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela participantes_sessao
CREATE TABLE IF NOT EXISTS participantes_sessao (
    id_sessao INT NOT NULL,
    id_participante INT NOT NULL,
    PRIMARY KEY (id_sessao, id_participante),
    FOREIGN KEY (id_sessao) REFERENCES sessoes_coaching(id_sessao),
    FOREIGN KEY (id_participante) REFERENCES usuarios(id_usuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela questionarios
CREATE TABLE IF NOT EXISTS questionarios (
    id_questionario INT PRIMARY KEY AUTO_INCREMENT,
    tipo ENUM('DISC', 'MBTI') NOT NULL,
    titulo VARCHAR(100) NOT NULL,
    descricao TEXT,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela respostas_questionario
CREATE TABLE IF NOT EXISTS respostas_questionario (
    id_resposta INT PRIMARY KEY AUTO_INCREMENT,
    id_usuario INT NOT NULL,
    id_questionario INT NOT NULL,
    resposta JSON NOT NULL,
    data_resposta TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario),
    FOREIGN KEY (id_questionario) REFERENCES questionarios(id_questionario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela metas
CREATE TABLE IF NOT EXISTS metas (
    id_meta INT PRIMARY KEY AUTO_INCREMENT,
    id_usuario INT NOT NULL,
    titulo VARCHAR(100) NOT NULL,
    categoria ENUM('pessoal', 'profissional', 'saude', 'financeiro') NOT NULL,
    criterios_smart JSON NOT NULL,
    data_inicio DATE NOT NULL,
    data_fim DATE NOT NULL,
    status ENUM('em_andamento', 'concluida', 'atrasada') NOT NULL DEFAULT 'em_andamento',
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ultima_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela marcos_meta
CREATE TABLE IF NOT EXISTS marcos_meta (
    id_marco INT PRIMARY KEY AUTO_INCREMENT,
    id_meta INT NOT NULL,
    descricao TEXT NOT NULL,
    concluido BOOLEAN NOT NULL DEFAULT FALSE,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_conclusao TIMESTAMP NULL,
    FOREIGN KEY (id_meta) REFERENCES metas(id_meta)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela feedback
CREATE TABLE IF NOT EXISTS feedback (
    id_feedback INT PRIMARY KEY AUTO_INCREMENT,
    id_remetente INT NOT NULL,
    id_destinatario INT NOT NULL,
    categoria ENUM('desempenho', 'comportamento', 'habilidades', 'objetivos') NOT NULL,
    tipo_feedback ENUM('positivo', 'construtivo', 'sugestao') NOT NULL,
    situacao TEXT NOT NULL,
    comportamento TEXT NOT NULL,
    impacto TEXT NOT NULL,
    sugestao TEXT,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_remetente) REFERENCES usuarios(id_usuario),
    FOREIGN KEY (id_destinatario) REFERENCES usuarios(id_usuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Inserir usuário administrador inicial
INSERT INTO usuarios (nome, email, senha, perfil) VALUES 
('Administrador', 'ezequiel.santos@gmail.com', 'Kyew1802', 'administrador')
ON DUPLICATE KEY UPDATE senha='Kyew1802', perfil='administrador';
