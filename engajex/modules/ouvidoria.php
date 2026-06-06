<?php
require_once '../config.php';
require_once '../includes/flash_toast.php';

if (!isLoggedIn()) {
    header("Location: ../login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$companyId = $_SESSION['company_id'] ?? null;
$message = '';
$isReceiver = false;

// Check if current user is the configured Ombudsman
if ($companyId) {
    $stmt = $pdo->prepare("SELECT ombudsman_user_id FROM companies WHERE id = ?");
    $stmt->execute([$companyId]);
    $ombudsmanId = $stmt->fetchColumn();
    if ($ombudsmanId == $userId) {
        $isReceiver = true;
    }
}

// Handle Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$isReceiver) { // Receivers generally don't report to themselves, but could allow it. Let's stick to standard user.
    $type = $_POST['type'];
    $text = $_POST['message'];
    $anon = isset($_POST['anonymous']) ? 1 : 0;

    try {
        $stmt = $pdo->prepare("INSERT INTO ombudsman_reports (company_id, sender_id, type, message, is_anonymous) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$companyId, $anon ? null : $userId, $type, $text, $anon]);
        $message = "Sua mensagem foi enviada com segurança.";
    } catch (PDOException $e) {
        $message = "Erro ao enviar.";
    }
}

// Handle List (If Receiver)
$reports = [];
if ($isReceiver) {
    if (isset($_GET['status']) && $_GET['status'] == 'completed') {
        // filter logic if implemented
    }

    $stmt = $pdo->prepare("SELECT * FROM ombudsman_reports WHERE company_id = ? ORDER BY created_at DESC");
    $stmt->execute([$companyId]);
    $reports = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Ouvidoria - TestProf Engaja</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .app-layout {
            display: flex;
            height: 100vh;
            overflow: hidden;
        }


        .main-content {
            flex: 1;
            padding: 2rem;
            overflow-y: auto;
            background: var(--bg-color);
        }

        .report-card {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--glass-border);
            padding: 1.5rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
        }

        .type-badge {
            padding: 0.2rem 0.6rem;
            border-radius: 4px;
            font-size: 0.8rem;
            text-transform: uppercase;
            font-weight: 600;
        }

        .type-denuncia {
            background: rgba(239, 68, 68, 0.2);
            color: #fca5a5;
        }

        .type-reclamacao {
            background: rgba(245, 158, 11, 0.2);
            color: #fcd34d;
        }

        .type-sugestao {
            background: rgba(59, 130, 246, 0.2);
            color: #93c5fd;
        }
    </style>
</head>

<body>
    <div class="app-layout">
        <?php include '../includes/sidebar.php'; ?>

        <main class="main-content">
            <?php if ($isReceiver): ?>
                <!-- VIEW FOR OMBUDSMAN -->
                <h1 style="margin-bottom: 2rem;">Relatórios Recebidos</h1>
                <p style="color: var(--text-muted); margin-bottom: 2rem;">Área restrita e confidencial.</p>

                <?php if (empty($reports)): ?>
                    <p style="color: var(--text-muted);">Nenhum relatório recebido.</p>
                <?php else: ?>
                    <?php foreach ($reports as $rpt): ?>
                        <div class="report-card">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 1rem;">
                                <span class="type-badge type-<?php echo $rpt['type']; ?>">
                                    <?php echo ucfirst($rpt['type']); ?>
                                </span>
                                <span style="font-size: 0.8rem; color: var(--text-muted);">
                                    <?php echo date('d/m/Y H:i', strtotime($rpt['created_at'])); ?>
                                </span>
                            </div>
                            <p style="white-space: pre-wrap; margin-bottom: 1rem;">
                                <?php echo htmlspecialchars($rpt['message']); ?>
                            </p>
                            <div
                                style="font-size: 0.8rem; color: var(--text-muted); border-top: 1px solid var(--glass-border); padding-top: 0.5rem;">
                                Enviado por:
                                <?php echo $rpt['is_anonymous'] ? '<span style="color: #fca5a5;">Anônimo</span>' : 'Usuário Identificado (ID: ' . $rpt['sender_id'] . ')'; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

            <?php else: ?>
                <!-- VIEW FOR EMPLOYEES -->
                <h1 style="margin-bottom: 2rem;">Ouvidoria</h1>

                <?php if ($message): flashToast($message, 'success'); endif; ?>

                <div class="card" style="max-width: 600px;">
                    <p style="margin-bottom: 1.5rem; color: var(--text-muted);">Este é um canal seguro para denúncias,
                        reclamações ou sugestões. Você pode optar pelo anonimato.</p>

                    <form method="POST">
                        <div class="form-group">
                            <label class="form-label">Tipo de Mensagem</label>
                            <select name="type" class="form-control" required>
                                <option value="denuncia">Denúncia</option>
                                <option value="reclamacao">Reclamação</option>
                                <option value="sugestao">Sugestão</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Mensagem</label>
                            <textarea name="message" class="form-control" rows="6" required
                                placeholder="Descreva a situação com detalhes..."></textarea>
                        </div>

                        <div class="form-group" style="display: flex; align-items: center; gap: 0.5rem;">
                            <input type="checkbox" id="anon" name="anonymous" value="1">
                            <label for="anon" style="cursor: pointer;">Desejo enviar anonimamente</label>
                        </div>

                        <button type="submit" class="btn btn-primary" style="margin-top: 1rem;">Enviar Relatório</button>
                    </form>
                </div>
            <?php endif; ?>
        </main>
    </div>
</body>

</html>