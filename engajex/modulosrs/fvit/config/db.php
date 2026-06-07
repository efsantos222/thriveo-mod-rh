<?php
// modulosrs/fvit/config/db.php
// This is now a bridge to the main system's database

$rootPath = dirname(dirname(dirname(__DIR__)));
require_once $rootPath . '/config.php';

// At this point, $pdo is the main database connection (efsantos_engaj)
// We'll use the same connection to keep things simple and integrated.

// We should also check if the necessary settings (openai_api_key) exist in the main settings table.
// If not, we might want to insert the default one from fvit (captured earlier).
try {
    $stmt = $pdo->prepare("SELECT 1 FROM settings WHERE setting_key = 'openai_api_key'");
    $stmt->execute();
    if (!$stmt->fetch()) {
        $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('openai_api_key', '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')");
        $stmt->execute();
    }
} catch (Exception $e) {
    // Table might not exist yet, or other issue - ignore for now as it's a bridge.
}
?>
