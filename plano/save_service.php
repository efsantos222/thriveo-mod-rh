<?php
require_once 'includes/auth.php';
checkLogin();
require_once 'config/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $companyId = getCompanyId();
    $id = isset($_POST['id']) ? $_POST['id'] : null;

    // Basic Info
    $name = $_POST['name'];
    $description = $_POST['description'];
    $revenue_model = $_POST['revenue_model'];
    $target_audience = $_POST['target_audience'];
    $key_resources = $_POST['key_resources'];

    // Y-Axis Scores (Maturity)
    $score_standardization = intval($_POST['score_standardization']);
    $score_scalability = intval($_POST['score_scalability']);
    $score_margin = intval($_POST['score_margin']);
    $score_automation = intval($_POST['score_automation']);

    $maturity_avg = ($score_standardization + $score_scalability + $score_margin + $score_automation) / 4.0;

    // X-Axis Scores (Synergy)
    $score_lead_gen = intval($_POST['score_lead_gen']);
    $score_relationship = intval($_POST['score_relationship']);
    $score_cross_sell = intval($_POST['score_cross_sell']);
    $score_brand_value = intval($_POST['score_brand_value']);

    $synergy_avg = ($score_lead_gen + $score_relationship + $score_cross_sell + $score_brand_value) / 4.0;

    if ($id) {
        // Enforce Company Ownership on Update
        $stmt = $pdo->prepare("UPDATE portfolio_services SET 
            name = ?, description = ?, revenue_model = ?, target_audience = ?, key_resources = ?, 
            score_standardization = ?, score_scalability = ?, score_margin = ?, score_automation = ?, maturity_avg = ?,
            score_lead_gen = ?, score_relationship = ?, score_cross_sell = ?, score_brand_value = ?, synergy_avg = ?
            WHERE id = ? AND company_id = ?");
        $stmt->execute([
            $name,
            $description,
            $revenue_model,
            $target_audience,
            $key_resources,
            $score_standardization,
            $score_scalability,
            $score_margin,
            $score_automation,
            $maturity_avg,
            $score_lead_gen,
            $score_relationship,
            $score_cross_sell,
            $score_brand_value,
            $synergy_avg,
            $id,
            $companyId
        ]);
    } else {
        // Enforce Company Ownership on Insert
        $stmt = $pdo->prepare("INSERT INTO portfolio_services (
            company_id, name, description, revenue_model, target_audience, key_resources, 
            score_standardization, score_scalability, score_margin, score_automation, maturity_avg,
            score_lead_gen, score_relationship, score_cross_sell, score_brand_value, synergy_avg
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $companyId,
            $name,
            $description,
            $revenue_model,
            $target_audience,
            $key_resources,
            $score_standardization,
            $score_scalability,
            $score_margin,
            $score_automation,
            $maturity_avg,
            $score_lead_gen,
            $score_relationship,
            $score_cross_sell,
            $score_brand_value,
            $synergy_avg
        ]);
        $id = $pdo->lastInsertId();
    }

    header("Location: view_service.php?id=" . $id . "&just_saved=true");
    exit();
}
?>