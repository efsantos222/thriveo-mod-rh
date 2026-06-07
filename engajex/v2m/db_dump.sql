-- Criação das Tabelas do Sistema V2MOM Intelligence

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Tabela `administradores`
-- (Caso já exista, o script PHP verificou, mas aqui está o DDL)
--
CREATE TABLE IF NOT EXISTS `administradores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `senha_hash` varchar(255) NOT NULL,
  `data_cadastro` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tabela `empresas`
--
CREATE TABLE IF NOT EXISTS `empresas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `razao_social` varchar(255) NOT NULL,
  `cnpj` varchar(20) DEFAULT NULL,
  `segmento` varchar(100) DEFAULT NULL,
  `porte` varchar(50) DEFAULT NULL,
  `data_cadastro` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('ativo','inativo') DEFAULT 'ativo',
  PRIMARY KEY (`id`),
  UNIQUE KEY `cnpj` (`cnpj`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tabela `responsaveis`
--
CREATE TABLE IF NOT EXISTS `responsaveis` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_empresa` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `senha_hash` varchar(255) NOT NULL,
  `cargo` varchar(100) DEFAULT NULL,
  `data_cadastro` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('ativo','inativo') DEFAULT 'ativo',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `id_empresa` (`id_empresa`),
  CONSTRAINT `responsaveis_ibfk_1` FOREIGN KEY (`id_empresa`) REFERENCES `empresas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tabela `configuracoes_ia`
--
CREATE TABLE IF NOT EXISTS `configuracoes_ia` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_empresa` int(11) NOT NULL,
  `api_key_openai` varchar(255) DEFAULT NULL,
  `modelo_preferido` varchar(50) DEFAULT 'gpt-4o',
  `temperatura` decimal(3,2) DEFAULT 0.70,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_empresa` (`id_empresa`),
  CONSTRAINT `configuracoes_ia_ibfk_1` FOREIGN KEY (`id_empresa`) REFERENCES `empresas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tabela `v2mom`
--
CREATE TABLE IF NOT EXISTS `v2mom` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_empresa` int(11) NOT NULL,
  `id_responsavel` int(11) NOT NULL,
  `versao` varchar(50) DEFAULT '1.0',
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('rascunho','ativo','arquivado') DEFAULT 'rascunho',
  PRIMARY KEY (`id`),
  KEY `id_empresa` (`id_empresa`),
  KEY `id_responsavel` (`id_responsavel`),
  CONSTRAINT `v2mom_ibfk_1` FOREIGN KEY (`id_empresa`) REFERENCES `empresas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `v2mom_ibfk_2` FOREIGN KEY (`id_responsavel`) REFERENCES `responsaveis` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tabela `visoes`
--
CREATE TABLE IF NOT EXISTS `visoes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_v2mom` int(11) NOT NULL,
  `descricao` text DEFAULT NULL,
  `prazo` varchar(100) DEFAULT NULL,
  `tipo` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_v2mom` (`id_v2mom`),
  CONSTRAINT `visoes_ibfk_1` FOREIGN KEY (`id_v2mom`) REFERENCES `v2mom` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tabela `valores`
--
CREATE TABLE IF NOT EXISTS `valores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_v2mom` int(11) NOT NULL,
  `descricao` text DEFAULT NULL,
  `prioridade` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_v2mom` (`id_v2mom`),
  CONSTRAINT `valores_ibfk_1` FOREIGN KEY (`id_v2mom`) REFERENCES `v2mom` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tabela `metodos`
--
CREATE TABLE IF NOT EXISTS `metodos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_v2mom` int(11) NOT NULL,
  `descricao` text DEFAULT NULL,
  `responsavel` varchar(255) DEFAULT NULL,
  `prazo` varchar(100) DEFAULT NULL,
  `status` enum('nao_iniciado','em_andamento','concluido','atrasado') DEFAULT 'nao_iniciado',
  PRIMARY KEY (`id`),
  KEY `id_v2mom` (`id_v2mom`),
  CONSTRAINT `metodos_ibfk_1` FOREIGN KEY (`id_v2mom`) REFERENCES `v2mom` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tabela `obstaculos`
--
CREATE TABLE IF NOT EXISTS `obstaculos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_v2mom` int(11) NOT NULL,
  `descricao` text DEFAULT NULL,
  `impacto` enum('alto','medio','baixo') DEFAULT 'medio',
  `solucao_proposta` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_v2mom` (`id_v2mom`),
  CONSTRAINT `obstaculos_ibfk_1` FOREIGN KEY (`id_v2mom`) REFERENCES `v2mom` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tabela `metricas`
--
CREATE TABLE IF NOT EXISTS `metricas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_v2mom` int(11) NOT NULL,
  `descricao` text DEFAULT NULL,
  `meta` varchar(255) DEFAULT NULL,
  `unidade` varchar(50) DEFAULT NULL,
  `periodicidade` varchar(50) DEFAULT NULL,
  `valor_atual` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_v2mom` (`id_v2mom`),
  CONSTRAINT `metricas_ibfk_1` FOREIGN KEY (`id_v2mom`) REFERENCES `v2mom` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tabela `interacoes_ia`
--
CREATE TABLE IF NOT EXISTS `interacoes_ia` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_v2mom` int(11) DEFAULT NULL,
  `tipo_interacao` varchar(100) DEFAULT NULL,
  `prompt` text DEFAULT NULL,
  `resposta` text DEFAULT NULL,
  `tokens_usados` int(11) DEFAULT NULL,
  `data_hora` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `id_v2mom` (`id_v2mom`),
  CONSTRAINT `interacoes_ia_ibfk_1` FOREIGN KEY (`id_v2mom`) REFERENCES `v2mom` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tabela `logs_acesso`
--
CREATE TABLE IF NOT EXISTS `logs_acesso` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) DEFAULT NULL,
  `tipo_usuario` enum('admin','responsavel') DEFAULT NULL,
  `acao` varchar(255) DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `data_hora` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

COMMIT;
