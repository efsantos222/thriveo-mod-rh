<?php
require_once 'config.php';
require_once 'db.php';

// Function to check if user has completed test
function hasCompletedTest($candidateId, $testType) {
    $db = getDbConnection();
    $stmt = $db->prepare("
        SELECT COUNT(*) FROM test_results 
        WHERE candidate_id = ? AND test_type = ?
    ");
    $stmt->execute([$candidateId, $testType]);
    return $stmt->fetchColumn() > 0;
}

// Function to redirect user to correct page
function redirectToCorrectPage($testType, $candidateId) {
    if (hasCompletedTest($candidateId, $testType)) {
        header('Location: ' . getResultsPath($testType) . '?candidate_id=' . $candidateId);
    } else {
        header('Location: ' . getTestPath($testType));
    }
    exit;
}
