<?php
session_start();

// Verificar se está logado como superadmin
if (!isset($_SESSION['superadmin_authenticated']) || !$_SESSION['superadmin_authenticated']) {
    header('Location: superadmin_login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    
    switch ($action) {
        case 'add_selecionador':
            addSelecionador();
            break;
            
        case 'reset_senha':
            resetSenha();
            break;
            
        case 'delete_selecionador':
            deleteSelecionador();
            break;
            
        case 'add_candidato':
            $nome = $_POST['nome'];
            $email = $_POST['email'];
            $cargo = $_POST['cargo'];
            $selecionador_email = $_POST['selecionador_email'];
            $observacoes = $_POST['observacoes'];
            $senha = $_POST['senha'];
            
            // Validar dados
            if (empty($nome) || empty($email) || empty($cargo) || empty($selecionador_email) || empty($senha)) {
                header('Location: superadmin_panel.php?tab=candidatos&error=missing_fields');
                exit;
            }
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                header('Location: superadmin_panel.php?tab=candidatos&error=invalid_email');
                exit;
            }
            
            if (strlen($senha) < 6) {
                header('Location: superadmin_panel.php?tab=candidatos&error=invalid_password');
                exit;
            }
            
            // Carregar dados do selecionador
            $selecionador_nome = '';
            $admins_file = 'resultados/admins.csv';
            if (file_exists($admins_file)) {
                $fp = fopen($admins_file, 'r');
                while (($data = fgetcsv($fp)) !== FALSE) {
                    if ($data[1] === $selecionador_email) {
                        $selecionador_nome = $data[0];
                        break;
                    }
                }
                fclose($fp);
            }
            
            if ($selecionador_nome) {
                $candidatos_file = 'resultados/candidatos.csv';
                
                // Verificar se o email já existe
                if (file_exists($candidatos_file)) {
                    $fp = fopen($candidatos_file, 'r');
                    while (($data = fgetcsv($fp)) !== FALSE) {
                        if ($data[4] === $email) {
                            fclose($fp);
                            header('Location: superadmin_panel.php?tab=candidatos&error=email_exists');
                            exit;
                        }
                    }
                    fclose($fp);
                }
                
                // Adicionar novo candidato
                $fp = fopen($candidatos_file, 'a');
                fputcsv($fp, [
                    date('Y-m-d H:i:s'),
                    $selecionador_nome,
                    $selecionador_email,
                    $nome,
                    $email,
                    password_hash($senha, PASSWORD_DEFAULT),
                    $cargo,
                    $observacoes
                ]);
                fclose($fp);
                
                // Enviar e-mail com as credenciais
                $to = $email;
                $subject = "Credenciais de Acesso - Sistema DISC";
                $message = "Olá {$nome},\n\n";
                $message .= "Suas credenciais de acesso para o Sistema DISC foram criadas:\n\n";
                $message .= "E-mail: {$email}\n";
                $message .= "Senha: {$senha}\n\n";
                $message .= "Por favor, acesse o sistema e complete sua avaliação DISC.\n";
                $message .= "Recomendamos que você altere sua senha após o primeiro acesso.\n\n";
                $message .= "Atenciosamente,\nEquipe Sistema DISC";
                $headers = "From: noreply@sistemadisc.com";
                
                mail($to, $subject, $message, $headers);
                
                header('Location: superadmin_panel.php?tab=candidatos&success=add');
                exit;
            }
            break;
            
        case 'delete_candidato':
            $email = $_POST['email'];
            $candidatos_file = 'resultados/candidatos.csv';
            $temp_file = 'resultados/temp_candidatos.csv';
            
            $fp_read = fopen($candidatos_file, 'r');
            $fp_write = fopen($temp_file, 'w');
            
            // Copiar cabeçalho
            fputcsv($fp_write, fgetcsv($fp_read));
            
            while (($data = fgetcsv($fp_read)) !== FALSE) {
                if ($data[4] !== $email) {
                    fputcsv($fp_write, $data);
                } else {
                    // Excluir arquivos relacionados
                    $prefix = str_replace(['@', '.'], '_', $email);
                    $files_to_delete = [
                        'resultados/' . $prefix . '_avaliacao.csv',
                        'resultados/' . $prefix . '_grafico.png'
                    ];
                    
                    foreach ($files_to_delete as $file) {
                        if (file_exists($file)) {
                            unlink($file);
                        }
                    }
                }
            }
            
            fclose($fp_read);
            fclose($fp_write);
            
            unlink($candidatos_file);
            rename($temp_file, $candidatos_file);
            
            header('Location: superadmin_panel.php?tab=candidatos&success=delete');
            exit;
            break;
            
        case 'edit_candidato':
            $email_original = $_POST['email_original'];
            $nome = $_POST['nome'];
            $email = $_POST['email'];
            $cargo = $_POST['cargo'];
            $selecionador_email = $_POST['selecionador_email'];
            $observacoes = $_POST['observacoes'];
            
            // Carregar dados do selecionador
            $selecionador_nome = '';
            $admins_file = 'resultados/admins.csv';
            if (file_exists($admins_file)) {
                $fp = fopen($admins_file, 'r');
                while (($data = fgetcsv($fp)) !== FALSE) {
                    if ($data[1] === $selecionador_email) {
                        $selecionador_nome = $data[0];
                        break;
                    }
                }
                fclose($fp);
            }
            
            if ($selecionador_nome) {
                $candidatos_file = 'resultados/candidatos.csv';
                $temp_file = 'resultados/candidatos_temp.csv';
                
                $fp_read = fopen($candidatos_file, 'r');
                $fp_write = fopen($temp_file, 'w');
                
                // Copiar cabeçalho
                fputcsv($fp_write, fgetcsv($fp_read));
                
                while (($data = fgetcsv($fp_read)) !== FALSE) {
                    if ($data[4] === $email_original) {
                        $data[1] = $selecionador_nome;
                        $data[2] = $selecionador_email;
                        $data[3] = $nome;
                        $data[4] = $email;
                        $data[6] = $cargo;
                        $data[7] = $observacoes;
                        
                        // Se o email mudou, renomear os arquivos de resultado
                        if ($email !== $email_original) {
                            $old_prefix = str_replace(['@', '.'], '_', $email_original);
                            $new_prefix = str_replace(['@', '.'], '_', $email);
                            
                            $files_to_rename = [
                                'resultados/' . $old_prefix . '_avaliacao.csv' => 'resultados/' . $new_prefix . '_avaliacao.csv',
                                'resultados/' . $old_prefix . '_grafico.png' => 'resultados/' . $new_prefix . '_grafico.png'
                            ];
                            
                            foreach ($files_to_rename as $old_file => $new_file) {
                                if (file_exists($old_file)) {
                                    rename($old_file, $new_file);
                                }
                            }
                        }
                    }
                    fputcsv($fp_write, $data);
                }
                
                fclose($fp_read);
                fclose($fp_write);
                
                unlink($candidatos_file);
                rename($temp_file, $candidatos_file);
                
                header('Location: superadmin_panel.php?tab=candidatos&success=edit');
                exit;
            }
            break;
            
        case 'change_candidate_password':
            $email = $_POST['email'];
            $nova_senha = $_POST['nova_senha'];
            
            if (strlen($nova_senha) >= 6) {
                $candidatos_file = 'resultados/candidatos.csv';
                $temp_file = 'resultados/candidatos_temp.csv';
                $senha_alterada = false;
                $nome_candidato = '';
                
                $fp_read = fopen($candidatos_file, 'r');
                $fp_write = fopen($temp_file, 'w');
                
                // Copiar cabeçalho
                fputcsv($fp_write, fgetcsv($fp_read));
                
                while (($data = fgetcsv($fp_read)) !== FALSE) {
                    if ($data[4] === $email) {
                        $nome_candidato = $data[3];
                        $data[5] = password_hash($nova_senha, PASSWORD_DEFAULT);
                        $senha_alterada = true;
                    }
                    fputcsv($fp_write, $data);
                }
                
                fclose($fp_read);
                fclose($fp_write);
                
                if ($senha_alterada) {
                    unlink($candidatos_file);
                    rename($temp_file, $candidatos_file);
                    
                    // Enviar e-mail com a nova senha
                    $to = $email;
                    $subject = "Nova Senha - Sistema DISC";
                    $message = "Olá {$nome_candidato},\n\n";
                    $message .= "Sua senha foi alterada pelo administrador do sistema.\n\n";
                    $message .= "Nova senha: {$nova_senha}\n\n";
                    $message .= "Por favor, faça login com esta nova senha.\n";
                    $message .= "Recomendamos que você altere esta senha após o primeiro acesso.\n\n";
                    $message .= "Atenciosamente,\nEquipe Sistema DISC";
                    $headers = "From: noreply@sistemadisc.com";
                    
                    mail($to, $subject, $message, $headers);
                    
                    header('Location: superadmin_panel.php?tab=candidatos&success=password');
                    exit;
                } else {
                    unlink($temp_file);
                }
            }
            break;
            
        case 'add_candidato_mbti':
            $nome = $_POST['nome'];
            $email = $_POST['email'];
            $cargo = $_POST['cargo'];
            $selecionador_email = $_POST['selecionador_email'];
            $observacoes = $_POST['observacoes'];
            $senha = $_POST['senha'];
            
            // Validar dados
            if (empty($nome) || empty($email) || empty($cargo) || empty($selecionador_email) || empty($senha)) {
                header('Location: superadmin_panel.php?tab=mbti&error=missing_fields');
                exit;
            }
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                header('Location: superadmin_panel.php?tab=mbti&error=invalid_email');
                exit;
            }
            
            if (strlen($senha) < 6) {
                header('Location: superadmin_panel.php?tab=mbti&error=invalid_password');
                exit;
            }
            
            // Carregar dados do selecionador
            $selecionador_nome = '';
            $admins_file = 'resultados/admins.csv';
            if (file_exists($admins_file)) {
                $fp = fopen($admins_file, 'r');
                while (($data = fgetcsv($fp)) !== FALSE) {
                    if ($data[1] === $selecionador_email) {
                        $selecionador_nome = $data[0];
                        break;
                    }
                }
                fclose($fp);
            }
            
            if ($selecionador_nome) {
                $candidatos_file = 'resultados/candidatos_mbti.csv';
                
                // Verificar se o email já existe
                if (file_exists($candidatos_file)) {
                    $fp = fopen($candidatos_file, 'r');
                    while (($data = fgetcsv($fp)) !== FALSE) {
                        if ($data[4] === $email) {
                            fclose($fp);
                            header('Location: superadmin_panel.php?tab=mbti&error=email_exists');
                            exit;
                        }
                    }
                    fclose($fp);
                }
                
                // Adicionar novo candidato
                $fp = fopen($candidatos_file, 'a');
                fputcsv($fp, [
                    date('Y-m-d H:i:s'),
                    $selecionador_nome,
                    $selecionador_email,
                    $nome,
                    $email,
                    password_hash($senha, PASSWORD_DEFAULT),
                    $cargo,
                    $observacoes
                ]);
                fclose($fp);
                
                // Enviar e-mail com as credenciais
                $to = $email;
                $subject = "Credenciais de Acesso - Sistema MBTI";
                $message = "Olá {$nome},\n\n";
                $message .= "Suas credenciais de acesso para o Sistema MBTI foram criadas:\n\n";
                $message .= "E-mail: {$email}\n";
                $message .= "Senha: {$senha}\n\n";
                $message .= "Por favor, acesse o sistema e complete sua avaliação MBTI.\n";
                $message .= "Recomendamos que você altere sua senha após o primeiro acesso.\n\n";
                $message .= "Atenciosamente,\nEquipe Sistema MBTI";
                $headers = "From: noreply@sistemambti.com";
                
                mail($to, $subject, $message, $headers);
                
                header('Location: superadmin_panel.php?tab=mbti&success=add');
                exit;
            }
            break;
            
        case 'delete_candidato_mbti':
            $email = $_POST['email'];
            $candidatos_file = 'resultados/candidatos_mbti.csv';
            $temp_file = 'resultados/temp_candidatos_mbti.csv';
            
            $fp_read = fopen($candidatos_file, 'r');
            $fp_write = fopen($temp_file, 'w');
            
            // Copiar cabeçalho
            fputcsv($fp_write, fgetcsv($fp_read));
            
            while (($data = fgetcsv($fp_read)) !== FALSE) {
                if ($data[4] !== $email) {
                    fputcsv($fp_write, $data);
                } else {
                    // Excluir arquivos relacionados
                    $prefix = str_replace(['@', '.'], '_', $email);
                    $files_to_delete = [
                        'resultados/' . $prefix . '_avaliacao_mbti.csv',
                        'resultados/' . $prefix . '_grafico_mbti.png'
                    ];
                    
                    foreach ($files_to_delete as $file) {
                        if (file_exists($file)) {
                            unlink($file);
                        }
                    }
                }
            }
            
            fclose($fp_read);
            fclose($fp_write);
            
            unlink($candidatos_file);
            rename($temp_file, $candidatos_file);
            
            header('Location: superadmin_panel.php?tab=mbti&success=delete');
            exit;
            break;
            
        case 'edit_candidato_mbti':
            $email_original = $_POST['email_original'];
            $nome = $_POST['nome'];
            $email = $_POST['email'];
            $cargo = $_POST['cargo'];
            $selecionador_email = $_POST['selecionador_email'];
            $observacoes = $_POST['observacoes'];
            
            // Carregar dados do selecionador
            $selecionador_nome = '';
            $admins_file = 'resultados/admins.csv';
            if (file_exists($admins_file)) {
                $fp = fopen($admins_file, 'r');
                while (($data = fgetcsv($fp)) !== FALSE) {
                    if ($data[1] === $selecionador_email) {
                        $selecionador_nome = $data[0];
                        break;
                    }
                }
                fclose($fp);
            }
            
            if ($selecionador_nome) {
                $candidatos_file = 'resultados/candidatos_mbti.csv';
                $temp_file = 'resultados/candidatos_mbti_temp.csv';
                
                $fp_read = fopen($candidatos_file, 'r');
                $fp_write = fopen($temp_file, 'w');
                
                // Copiar cabeçalho
                fputcsv($fp_write, fgetcsv($fp_read));
                
                while (($data = fgetcsv($fp_read)) !== FALSE) {
                    if ($data[4] === $email_original) {
                        $data[1] = $selecionador_nome;
                        $data[2] = $selecionador_email;
                        $data[3] = $nome;
                        $data[4] = $email;
                        $data[6] = $cargo;
                        $data[7] = $observacoes;
                        
                        // Se o email mudou, renomear os arquivos de resultado
                        if ($email !== $email_original) {
                            $old_prefix = str_replace(['@', '.'], '_', $email_original);
                            $new_prefix = str_replace(['@', '.'], '_', $email);
                            
                            $files_to_rename = [
                                'resultados/' . $old_prefix . '_avaliacao_mbti.csv' => 'resultados/' . $new_prefix . '_avaliacao_mbti.csv',
                                'resultados/' . $old_prefix . '_grafico_mbti.png' => 'resultados/' . $new_prefix . '_grafico_mbti.png'
                            ];
                            
                            foreach ($files_to_rename as $old_file => $new_file) {
                                if (file_exists($old_file)) {
                                    rename($old_file, $new_file);
                                }
                            }
                        }
                    }
                    fputcsv($fp_write, $data);
                }
                
                fclose($fp_read);
                fclose($fp_write);
                
                unlink($candidatos_file);
                rename($temp_file, $candidatos_file);
                
                header('Location: superadmin_panel.php?tab=mbti&success=edit');
                exit;
            }
            break;
            
        case 'change_candidate_password_mbti':
            $email = $_POST['email'];
            $nova_senha = $_POST['nova_senha'];
            
            if (strlen($nova_senha) >= 6) {
                $candidatos_file = 'resultados/candidatos_mbti.csv';
                $temp_file = 'resultados/candidatos_mbti_temp.csv';
                $senha_alterada = false;
                $nome_candidato = '';
                
                $fp_read = fopen($candidatos_file, 'r');
                $fp_write = fopen($temp_file, 'w');
                
                // Copiar cabeçalho
                fputcsv($fp_write, fgetcsv($fp_read));
                
                while (($data = fgetcsv($fp_read)) !== FALSE) {
                    if ($data[4] === $email) {
                        $nome_candidato = $data[3];
                        $data[5] = password_hash($nova_senha, PASSWORD_DEFAULT);
                        $senha_alterada = true;
                    }
                    fputcsv($fp_write, $data);
                }
                
                fclose($fp_read);
                fclose($fp_write);
                
                if ($senha_alterada) {
                    unlink($candidatos_file);
                    rename($temp_file, $candidatos_file);
                    
                    // Enviar e-mail com a nova senha
                    $to = $email;
                    $subject = "Nova Senha - Sistema MBTI";
                    $message = "Olá {$nome_candidato},\n\n";
                    $message .= "Sua senha foi alterada pelo administrador do sistema.\n\n";
                    $message .= "Nova senha: {$nova_senha}\n\n";
                    $message .= "Por favor, faça login com esta nova senha.\n";
                    $message .= "Recomendamos que você altere esta senha após o primeiro acesso.\n\n";
                    $message .= "Atenciosamente,\nEquipe Sistema MBTI";
                    $headers = "From: noreply@sistemambti.com";
                    
                    mail($to, $subject, $message, $headers);
                    
                    header('Location: superadmin_panel.php?tab=mbti&success=password');
                    exit;
                } else {
                    unlink($temp_file);
                }
            }
            break;
    }
}

header('Location: superadmin_panel.php?error=1');
exit;

function addSelecionador() {
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];
    
    // Validações
    if (empty($nome) || empty($email) || empty($senha)) {
        header('Location: superadmin_panel.php?error=missing_fields');
        exit;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header('Location: superadmin_panel.php?error=invalid_email');
        exit;
    }
    
    if (strlen($senha) < 6) {
        header('Location: superadmin_panel.php?error=invalid_password');
        exit;
    }
    
    $resultados_dir = 'resultados';
    $admins_file = $resultados_dir . '/admins.csv';
    
    // Criar diretório resultados se não existir
    if (!file_exists($resultados_dir)) {
        mkdir($resultados_dir, 0777, true);
    }
    
    // Verificar se o e-mail já existe
    if (file_exists($admins_file)) {
        $fp = fopen($admins_file, 'r');
        if ($fp !== false) {
            fgetcsv($fp); // Pular cabeçalho
            while (($data = fgetcsv($fp)) !== FALSE) {
                if ($data[1] === $email) {
                    fclose($fp);
                    header('Location: superadmin_panel.php?error=email_exists');
                    exit;
                }
            }
            fclose($fp);
        }
    }
    
    // Criar arquivo com cabeçalho se não existir
    if (!file_exists($admins_file)) {
        $fp = fopen($admins_file, 'w');
        if ($fp !== false) {
            fputcsv($fp, ['Nome', 'Email', 'Senha', 'Data Cadastro']);
            fclose($fp);
        }
    }
    
    // Adicionar novo selecionador
    $fp = fopen($admins_file, 'a');
    if ($fp !== false) {
        fputcsv($fp, [
            $nome,
            $email,
            password_hash($senha, PASSWORD_DEFAULT),
            date('Y-m-d H:i:s')
        ]);
        fclose($fp);
        
        // Enviar e-mail com as credenciais
        $to = $email;
        $subject = "Credenciais de Acesso - Sistema DISC/MBTI";
        $message = "Olá {$nome},\n\n";
        $message .= "Suas credenciais de acesso para o Sistema DISC/MBTI foram criadas:\n\n";
        $message .= "E-mail: {$email}\n";
        $message .= "Senha: {$senha}\n\n";
        $message .= "Por favor, acesse o sistema para gerenciar seus candidatos.\n";
        $message .= "Recomendamos que você altere sua senha no primeiro acesso.\n\n";
        $message .= "Atenciosamente,\nEquipe Sistema DISC/MBTI";
        $headers = "From: noreply@sistemadisc.com";
        
        mail($to, $subject, $message, $headers);
        
        header('Location: superadmin_panel.php?success=add_selecionador');
        exit;
    }
    
    header('Location: superadmin_panel.php?error=file_error');
    exit;
}

function resetSenha() {
    $email = $_POST['email'];
    $nova_senha = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8);
    
    $selecionadores_file = 'resultados/selecionadores.csv';
    $temp_file = 'resultados/temp_selecionadores.csv';
    
    if (!file_exists($selecionadores_file)) {
        $_SESSION['error'] = 'Arquivo de selecionadores não encontrado';
        return;
    }
    
    $fp_read = fopen($selecionadores_file, 'r');
    $fp_write = fopen($temp_file, 'w');
    
    // Copiar cabeçalho
    fputcsv($fp_write, fgetcsv($fp_read));
    
    // Atualizar senha do selecionador
    while (($data = fgetcsv($fp_read)) !== FALSE) {
        if ($data[1] === $email) {
            $data[2] = password_hash($nova_senha, PASSWORD_DEFAULT);
            
            // Enviar e-mail com a nova senha
            $subject = 'Nova Senha - Sistema DISC';
            $body = "Olá {$data[0]},<br><br>";
            $body .= "Sua senha foi resetada. Sua nova senha é: <strong>{$nova_senha}</strong><br>";
            $body .= "Por favor, altere sua senha após o primeiro acesso.<br><br>";
            $body .= "Atenciosamente,<br>Sistema de Avaliação DISC";
            
            $headers = 'From: sistema@seudominio.com' . "\r\n" .
                'Reply-To: sistema@seudominio.com' . "\r\n" .
                'Content-Type: text/html; charset=UTF-8' . "\r\n" .
                'X-Mailer: PHP/' . phpversion();
            
            mail($email, $subject, $body, $headers);
        }
        fputcsv($fp_write, $data);
    }
    
    fclose($fp_read);
    fclose($fp_write);
    
    unlink($selecionadores_file);
    rename($temp_file, $selecionadores_file);
    
    $_SESSION['success'] = 'Senha resetada com sucesso. Nova senha enviada por e-mail.';
}

function deleteSelecionador() {
    $email = $_POST['email'];
    
    $selecionadores_file = 'resultados/selecionadores.csv';
    $temp_file = 'resultados/temp_selecionadores.csv';
    
    if (!file_exists($selecionadores_file)) {
        $_SESSION['error'] = 'Arquivo de selecionadores não encontrado';
        return;
    }
    
    $fp_read = fopen($selecionadores_file, 'r');
    $fp_write = fopen($temp_file, 'w');
    
    // Copiar cabeçalho
    fputcsv($fp_write, fgetcsv($fp_read));
    
    // Copiar todos exceto o selecionador a ser excluído
    while (($data = fgetcsv($fp_read)) !== FALSE) {
        if ($data[1] !== $email) {
            fputcsv($fp_write, $data);
        }
    }
    
    fclose($fp_read);
    fclose($fp_write);
    
    unlink($selecionadores_file);
    rename($temp_file, $selecionadores_file);
    
    // Excluir também os candidatos deste selecionador
    $candidatos_file = 'resultados/candidatos.csv';
    if (file_exists($candidatos_file)) {
        $temp_file = 'resultados/temp_candidatos.csv';
        
        $fp_read = fopen($candidatos_file, 'r');
        $fp_write = fopen($temp_file, 'w');
        
        // Copiar cabeçalho
        fputcsv($fp_write, fgetcsv($fp_read));
        
        // Copiar todos exceto os candidatos do selecionador excluído
        while (($data = fgetcsv($fp_read)) !== FALSE) {
            if ($data[2] !== $email) {
                fputcsv($fp_write, $data);
            } else {
                // Excluir arquivo de resultado do candidato se existir
                $resultado_file = 'resultados/' . str_replace(['@', '.'], '_', $data[4]) . '_avaliacao.csv';
                if (file_exists($resultado_file)) {
                    unlink($resultado_file);
                }
            }
        }
        
        fclose($fp_read);
        fclose($fp_write);
        
        unlink($candidatos_file);
        rename($temp_file, $candidatos_file);
    }
    
    $_SESSION['success'] = 'Selecionador e seus candidatos excluídos com sucesso';
}

function addCandidato() {
    $selecionador_email = $_POST['selecionador_email'];
    $candidato_nome = trim($_POST['candidato_nome']);
    $candidato_email = trim($_POST['candidato_email']);
    
    // Buscar nome do selecionador
    $selecionadores_file = 'resultados/selecionadores.csv';
    $selecionador_nome = '';
    
    if (file_exists($selecionadores_file)) {
        $fp = fopen($selecionadores_file, 'r');
        fgetcsv($fp); // Pular cabeçalho
        while (($data = fgetcsv($fp)) !== FALSE) {
            if ($data[1] === $selecionador_email) {
                $selecionador_nome = $data[0];
                break;
            }
        }
        fclose($fp);
    }
    
    if (empty($selecionador_nome)) {
        $_SESSION['error'] = 'Selecionador não encontrado';
        return;
    }
    
    // Gerar senha para o candidato
    $senha = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8);
    
    // Salvar candidato
    $candidatos_file = 'resultados/candidatos.csv';
    $fp = fopen($candidatos_file, 'a');
    
    if (!file_exists($candidatos_file)) {
        fputcsv($fp, ['Data', 'Selecionador Nome', 'Selecionador Email', 'Candidato Nome', 'Candidato Email', 'Senha']);
    }
    
    fputcsv($fp, [
        date('Y-m-d H:i:s'),
        $selecionador_nome,
        $selecionador_email,
        $candidato_nome,
        $candidato_email,
        $senha
    ]);
    
    fclose($fp);
    
    // Enviar e-mail para o candidato
    $subject = 'Acesso para Avaliação DISC';
    $body = "Olá {$candidato_nome},<br><br>";
    $body .= "Você foi convidado para participar de uma avaliação DISC.<br>";
    $body .= "Sua senha de acesso é: <strong>{$senha}</strong><br>";
    $body .= "Acesse o link: http://seudominio.com/disc/login.php<br><br>";
    $body .= "Atenciosamente,<br>Sistema de Avaliação DISC";
    
    $headers = 'From: sistema@seudominio.com' . "\r\n" .
        'Reply-To: sistema@seudominio.com' . "\r\n" .
        'Content-Type: text/html; charset=UTF-8' . "\r\n" .
        'X-Mailer: PHP/' . phpversion();
    
    mail($candidato_email, $subject, $body, $headers);
    
    $_SESSION['success'] = 'Candidato adicionado com sucesso';
}

function deleteCandidato() {
    $email = $_POST['email'];
    
    $candidatos_file = 'resultados/candidatos.csv';
    $temp_file = 'resultados/temp_candidatos.csv';
    
    if (!file_exists($candidatos_file)) {
        $_SESSION['error'] = 'Arquivo de candidatos não encontrado';
        return;
    }
    
    $fp_read = fopen($candidatos_file, 'r');
    $fp_write = fopen($temp_file, 'w');
    
    // Copiar cabeçalho
    fputcsv($fp_write, fgetcsv($fp_read));
    
    // Copiar todos exceto o candidato a ser excluído
    while (($data = fgetcsv($fp_read)) !== FALSE) {
        if ($data[4] !== $email) {
            fputcsv($fp_write, $data);
        } else {
            // Excluir arquivo de resultado se existir
            $resultado_file = 'resultados/' . str_replace(['@', '.'], '_', $email) . '_avaliacao.csv';
            if (file_exists($resultado_file)) {
                unlink($resultado_file);
            }
        }
    }
    
    fclose($fp_read);
    fclose($fp_write);
    
    unlink($candidatos_file);
    rename($temp_file, $candidatos_file);
    
    $_SESSION['success'] = 'Candidato excluído com sucesso';
}
