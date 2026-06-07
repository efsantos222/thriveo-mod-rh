<?php
// modulosrs/matchcv/config.php
// Bridge to main system

$rootPath = dirname(dirname(__DIR__));
require_once $rootPath . '/config.php';

// Fetch API Key from main system settings
$openai_key = '';
try {
    $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'openai_api_key' LIMIT 1");
    $stmt->execute();
    $row = $stmt->fetch();
    if ($row) {
        $openai_key = $row['setting_value'];
    }
} catch (Exception $e) {
    // Fallback or ignore
}

// If key not found in DB, use the hardcoded one provided as fallback
if (empty($openai_key)) {
    $openai_key = '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';
}

define('OPENAI_API_KEY', $openai_key);
define('OPENAI_API_URL', 'https://api.openai.com/v1/chat/completions');
?>
