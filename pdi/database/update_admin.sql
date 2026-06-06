-- Adicionar colunas is_admin e is_manager
ALTER TABLE users
ADD COLUMN is_admin BOOLEAN DEFAULT FALSE,
ADD COLUMN is_manager BOOLEAN DEFAULT FALSE,
ADD COLUMN manager_id INT NULL,
ADD COLUMN active BOOLEAN DEFAULT TRUE,
ADD FOREIGN KEY (manager_id) REFERENCES users(id);

-- Atualizar o usuário admin existente
UPDATE users 
SET is_admin = TRUE, 
    is_manager = TRUE 
WHERE role = 'admin' 
OR email = 'admin@example.com';

-- Se não existir um admin, criar um
INSERT INTO users (name, email, password_hash, is_admin, is_manager, active)
SELECT 'Administrador', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', TRUE, TRUE, TRUE
WHERE NOT EXISTS (
    SELECT 1 FROM users WHERE email = 'admin@example.com'
);
