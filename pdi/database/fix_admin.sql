-- Desativar verificação de chaves estrangeiras
SET FOREIGN_KEY_CHECKS=0;

-- Atualizar todos os usuários existentes para não-admin
UPDATE users SET is_admin = 0, is_manager = 0;

-- Criar ou atualizar o usuário admin
INSERT INTO users (name, email, password_hash, is_admin, is_manager, active)
VALUES ('Administrador', 'admin@example.com', 'password', 1, 1, 1)
ON DUPLICATE KEY UPDATE
    name = 'Administrador',
    password_hash = 'password',
    is_admin = 1,
    is_manager = 1,
    active = 1;

-- Reativar verificação de chaves estrangeiras
SET FOREIGN_KEY_CHECKS=1;
