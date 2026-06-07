<?php
require_once 'config.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

$company_id = $_SESSION['company_id'];
$stmt = $pdo->prepare("SELECT * FROM companies WHERE id = ?");
$stmt->execute([$company_id]);
$company = $stmt->fetch();

$sub = getCompanySubscription();

// Pricing tiers based on employees (R$ 2.00 per employee/month * 12 months = R$ 24.00 per employee/year)
$tiers = [
    ['max' => 50, 'price' => 50 * 2 * 12, 'name' => 'Plano Start'],
    ['max' => 150, 'price' => 150 * 2 * 12, 'name' => 'Plano Growth'],
    ['max' => 300, 'price' => 300 * 2 * 12, 'name' => 'Plano Business'],
    ['max' => 1000, 'price' => 1000 * 2 * 12, 'name' => 'Plano Enterprise'],
    ['max' => 999999, 'price' => 5000 * 2 * 12, 'name' => 'Plano Corporate'] // Example for large companies
];

$my_tier = null;
foreach ($tiers as $tier) {
    if ($company['employee_count'] <= $tier['max']) {
        $my_tier = $tier;
        break;
    }
}

$mercado_pago_token = 'APP_USR-227931852663399-031509-381b83217eb0aa63d710fc5a35eacb88-3268408628'; // Test Token
$public_key = 'APP_USR-ac5a0383-29d2-4ad3-a5f2-e87e5e78ef95';

// If payment success
if (isset($_GET['status']) && $_GET['status'] === 'success') {
    // In a real app, you'd verify the payment via IPN/Webhook.
    // For this demo/task, we will assume success if they come back from MP.
    $next_year = date('Y-m-d H:i:s', strtotime('+1 year'));
    $stmt = $pdo->prepare("UPDATE companies SET subscription_status = 'active', subscription_expires_at = ?, trial_ends_at = NULL WHERE id = ?");
    $stmt->execute([$next_year, $company_id]);
    header("Location: dashboard.php?payment=success");
    exit;
}

// Create Mercado Pago Preference
$preference_id = null;
if ($my_tier) {
    $url = "https://api.mercadopago.com/checkout/preferences";
    $data = [
        "items" => [
            [
                "title" => "Assinatura Anual - " . $my_tier['name'],
                "quantity" => 1,
                "unit_price" => (float)$my_tier['price'],
                "currency_id" => "BRL"
            ]
        ],
        "back_urls" => [
            "success" => (isset($_SERVER['HTTPS']) ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . "?status=success",
            "failure" => (isset($_SERVER['HTTPS']) ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . "?status=failure",
            "pending" => (isset($_SERVER['HTTPS']) ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . "?status=pending"
        ],
        "auto_return" => "approved",
        "external_reference" => "COMPANY_ID_" . $company_id
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $mercado_pago_token",
        "Content-Type: application/json"
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if (curl_errno($ch)) {
        $mp_error = 'Erro cURL: ' . curl_error($ch);
    } elseif ($http_code >= 400) {
        $mp_error = 'Erro API Mercado Pago (HTTP ' . $http_code . '): ' . $response;
    }
    
    $result = json_decode($response, true);
    $preference_id = $result['id'] ?? null;
    curl_close($ch);
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Assinatura - TestProf Engaja</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://sdk.mercadopago.com/js/v2"></script>
</head>
<body>
    <div class="app-layout">
        <?php include 'includes/sidebar.php'; ?>

        <main class="main-content">
            <h1 style="margin-bottom: 2rem;">Configuração da Assinatura</h1>

            <?php if ($sub['is_expired']): ?>
                <div style="background: #fef2f2; color: #b91c1c; padding: 1rem; border-radius: 0.5rem; margin-bottom: 2rem; border: 1px solid rgba(239, 68, 68, 0.3);">
                    <strong>Atenção:</strong> Seu período de teste expirou. Para continuar utilizando o sistema, por favor, realize o pagamento abaixo.
                </div>
            <?php else: ?>
                <div style="background: #f0fdf4; color: #15803d; padding: 1rem; border-radius: 0.5rem; margin-bottom: 2rem; border: 1px solid rgba(16, 185, 129, 0.3);">
                    Você está no período de teste. Restam <strong><?php echo $sub['days_left']; ?> dias</strong>.
                </div>
            <?php endif; ?>

            <div class="card" style="max-width: 600px; margin: 0 auto;">
                <h2 style="margin-bottom: 1.5rem; text-align: center;">Plano Selecionado</h2>
                <div style="background: #f8fafc; padding: 2rem; border-radius: 1rem; text-align: center; border: 1px solid var(--glass-border);">
                    <h3 style="color: var(--secondary-color); font-size: 1.5rem; margin-bottom: 0.5rem;"><?php echo $my_tier['name']; ?></h3>
                    <p style="color: var(--text-muted); margin-bottom: 1.5rem;">Baseado na sua equipe de <?php echo $company['employee_count']; ?> empregados.</p>
                    <div style="font-size: 2.5rem; font-weight: 700; color: var(--slate-900); margin-bottom: 2rem;">
                        R$ <?php echo number_format($my_tier['price'], 2, ',', '.'); ?> <span style="font-size: 1rem; font-weight: 400; color: var(--text-muted);">/ ano</span>
                    </div>

                    <div id="wallet_container"></div>
                </div>
                
                <p style="margin-top: 2rem; color: var(--text-muted); font-size: 0.85rem; text-align: center;">
                    Pagamento processado com segurança via Mercado Pago.
                </p>
            </div>
        </main>
    </div>

    <script>
        const mp = new MercadoPago('<?php echo $public_key; ?>', {
            locale: 'pt-BR'
        });

        <?php if ($preference_id): ?>
        mp.bricks().create("wallet", "wallet_container", {
            initialization: {
                preferenceId: '<?php echo $preference_id; ?>',
                redirectMode: 'modal'
            },
        });
        <?php endif; ?>
    </script>
</body>
</html>
