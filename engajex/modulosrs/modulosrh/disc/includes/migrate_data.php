<?php
require_once 'config.php';

function importUsers() {
    $db = getDbConnection();
    
    // Import superadmin
    $stmt = $db->prepare("INSERT IGNORE INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->execute(['Recursos Humanos', 'recursoshumanos@sysmanager.com.br', password_hash('SysManager25', PASSWORD_DEFAULT), 'admin']);
    
    // Import selectors from CSV
    if (file_exists('../data/selectors.csv')) {
        $handle = fopen('../data/selectors.csv', 'r');
        fgetcsv($handle); // Skip header
        
        while (($data = fgetcsv($handle)) !== FALSE) {
            $stmt->execute([
                $data[0], // name
                $data[1], // email
                password_hash($data[2], PASSWORD_DEFAULT), // password
                'selector'
            ]);
        }
        fclose($handle);
    }
}

function importCandidates() {
    $db = getDbConnection();
    $stmt = $db->prepare("INSERT IGNORE INTO candidates (name, email, created_at) VALUES (?, ?, ?)");
    
    if (file_exists('../data/candidates.csv')) {
        $handle = fopen('../data/candidates.csv', 'r');
        fgetcsv($handle); // Skip header
        
        while (($data = fgetcsv($handle)) !== FALSE) {
            $stmt->execute([
                $data[0], // name
                $data[1], // email
                $data[2]  // created_at
            ]);
        }
        fclose($handle);
    }
}

function importTestQuestions() {
    $db = getDbConnection();
    $stmt = $db->prepare("INSERT INTO test_questions (test_type, question, type, options) VALUES (?, ?, ?, ?)");
    
    // Import DISC questions
    if (file_exists('../data/disc_questions.csv')) {
        $handle = fopen('../data/disc_questions.csv', 'r');
        fgetcsv($handle); // Skip header
        
        while (($data = fgetcsv($handle)) !== FALSE) {
            $options = [
                'D' => $data[1], // Dominância
                'I' => $data[2], // Influência
                'S' => $data[3], // Estabilidade
                'C' => $data[4]  // Conformidade
            ];
            
            $stmt->execute([
                'disc',
                $data[0], // question
                null,     // type not used for DISC
                json_encode($options)
            ]);
        }
        fclose($handle);
    }
    
    // Import RAC questions
    if (file_exists('../data/rac_questions.csv')) {
        $handle = fopen('../data/rac_questions.csv', 'r');
        fgetcsv($handle); // Skip header
        
        while (($data = fgetcsv($handle)) !== FALSE) {
            $options = [];
            for ($i = 2; $i <= 5; $i++) {
                $options[] = $data[$i];
            }
            
            $stmt->execute([
                'rac',
                $data[0], // question
                $data[1], // type (logico, verbal, numerico, espacial)
                json_encode($options)
            ]);
        }
        fclose($handle);
    }
}

function importTestResults() {
    $db = getDbConnection();
    $stmt = $db->prepare("
        INSERT INTO test_results (candidate_id, test_type, results, completed_at)
        SELECT c.id, ?, ?, ? FROM candidates c WHERE c.email = ?
    ");
    
    $files = [
        'disc' => '../data/disc_results.csv',
        'rac' => '../data/rac_results.csv'
    ];
    
    foreach ($files as $type => $file) {
        if (file_exists($file)) {
            $handle = fopen($file, 'r');
            fgetcsv($handle); // Skip header
            
            while (($data = fgetcsv($handle)) !== FALSE) {
                $stmt->execute([
                    $type,
                    json_encode($data[2]), // results
                    $data[3], // completed_at
                    $data[1]  // email
                ]);
            }
            fclose($handle);
        }
    }
}

try {
    echo "Iniciando migração...\n";
    
    echo "Importando usuários...\n";
    importUsers();
    
    echo "Importando candidatos...\n";
    importCandidates();
    
    echo "Importando questões dos testes...\n";
    importTestQuestions();
    
    echo "Importando resultados dos testes...\n";
    importTestResults();
    
    echo "Migração concluída com sucesso!\n";
} catch (Exception $e) {
    echo "Erro durante a migração: " . $e->getMessage() . "\n";
    exit(1);
}
