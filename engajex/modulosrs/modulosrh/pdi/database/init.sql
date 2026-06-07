-- Use o banco de dados
USE efsantos_pdi;

-- Limpar dados existentes
DELETE FROM course_enrollments;
DELETE FROM courses;
DELETE FROM course_categories;
DELETE FROM pdi_competencies;
DELETE FROM pdis;
DELETE FROM competencies;

-- Criar usuário administrador padrão apenas se não existir
INSERT INTO users (
    name,
    email,
    password_hash,
    role,
    created_at,
    updated_at
) VALUES (
    'Administrador',
    'admin@proftest.com.br',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- senha: admin123
    'admin',
    NOW(),
    NOW()
);

-- Inserir competências padrão
INSERT IGNORE INTO competencies (name, description) VALUES 
('Liderança', 'Capacidade de influenciar e guiar equipes'),
('Comunicação', 'Habilidade de se expressar clara e efetivamente'),
('Gestão de Projetos', 'Capacidade de planejar e executar projetos'),
('Inovação', 'Capacidade de propor e implementar novas ideias'),
('Trabalho em Equipe', 'Habilidade de colaborar e trabalhar em grupo');

-- Inserir categorias de cursos
INSERT IGNORE INTO course_categories (name, description) VALUES
('Liderança', 'Cursos focados em desenvolvimento de liderança'),
('Comunicação', 'Cursos para melhorar habilidades de comunicação'),
('Gestão', 'Cursos sobre gestão e administração');

-- Criar cursos de exemplo
SET @cat_lideranca = (SELECT id FROM course_categories WHERE name = 'Liderança');
SET @cat_comunicacao = (SELECT id FROM course_categories WHERE name = 'Comunicação');
SET @cat_gestao = (SELECT id FROM course_categories WHERE name = 'Gestão');

INSERT IGNORE INTO courses (title, category_id, level, description, content) VALUES
('Fundamentos de Liderança', @cat_lideranca, 'basic', 'Curso introdutório sobre liderança', 'Módulo 1: Introdução à Liderança\nMódulo 2: Estilos de Liderança');

INSERT IGNORE INTO courses (title, category_id, level, description, content) VALUES
('Comunicação Efetiva', @cat_comunicacao, 'basic', 'Aprenda a se comunicar melhor', 'Módulo 1: Comunicação Verbal\nMódulo 2: Comunicação Não-verbal');

INSERT IGNORE INTO courses (title, category_id, level, description, content) VALUES
('Gestão de Equipes', @cat_gestao, 'intermediate', 'Gestão eficiente de equipes', 'Módulo 1: Formação de Equipes\nMódulo 2: Motivação');
