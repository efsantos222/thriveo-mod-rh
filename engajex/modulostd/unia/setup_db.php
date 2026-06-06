<?php
require_once 'config/db.php';

try {
    // 1. Tabela de Empresas
    $pdo->exec("CREATE TABLE IF NOT EXISTS companies (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        cnpj VARCHAR(20),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // 2. Tabela de Usuários
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        company_id INT,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role ENUM('admin', 'coordinator', 'instructor', 'student') NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE SET NULL
    )");

    // 3. Tabela de Configurações (Chaves API)
    $pdo->exec("CREATE TABLE IF NOT EXISTS system_settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        setting_key VARCHAR(50) NOT NULL UNIQUE,
        setting_value TEXT,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");

    // 4. Tabela de Cursos
    $pdo->exec("CREATE TABLE IF NOT EXISTS courses (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        coordinator_id INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (coordinator_id) REFERENCES users(id) ON DELETE SET NULL
    )");

    // 5. Tabela de Módulos/Conteúdo
    $pdo->exec("CREATE TABLE IF NOT EXISTS course_modules (
        id INT AUTO_INCREMENT PRIMARY KEY,
        course_id INT NOT NULL,
        title VARCHAR(255) NOT NULL,
        content TEXT,
        type ENUM('text', 'video', 'pdf', 'quiz') DEFAULT 'text',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
    )");

    // 6. Tabela de Turmas/Eventos (Treinamentos)
    $pdo->exec("CREATE TABLE IF NOT EXISTS events (
        id INT AUTO_INCREMENT PRIMARY KEY,
        course_id INT NOT NULL,
        instructor_id INT,
        title VARCHAR(255),
        start_date DATETIME,
        end_date DATETIME,
        location VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
        FOREIGN KEY (instructor_id) REFERENCES users(id) ON DELETE SET NULL
    )");

    // 7. Matrículas
    $pdo->exec("CREATE TABLE IF NOT EXISTS enrollments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        course_id INT NOT NULL,
        status ENUM('active', 'completed', 'dropped') DEFAULT 'active',
        enrolled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
    )");

    // 8. Certificados
    $pdo->exec("CREATE TABLE IF NOT EXISTS certificates (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        course_id INT NOT NULL,
        issued_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        code VARCHAR(255) UNIQUE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
    )");

    // Create default Admin user
    $adminEmail = 'ezequiel.santos@gmail.com';
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$adminEmail]);

    if ($stmt->rowCount() == 0) {
        // Hash the password
        $passwordHash = password_hash('Kyew1802', PASSWORD_DEFAULT);

        // Insert Admin
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->execute(['Administrador', $adminEmail, $passwordHash, 'admin']);
        echo "Administrador padrão criado com sucesso.\n";
    }

    // Insert Default API Settings if not exists
    $stmt = $pdo->prepare("INSERT IGNORE INTO system_settings (setting_key, setting_value) VALUES ('openai_api_key', ''), ('openai_model', 'gpt-4o'), ('openai_temperature', '0.5')");
    $stmt->execute();

    echo "Banco de dados configurado com sucesso!";

} catch (PDOException $e) {
    echo "Erro ao configurar banco de dados: " . $e->getMessage();
}
?>