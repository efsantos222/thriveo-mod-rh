<?php
// Configurações do banco de dados
define('DB_HOST', 'localhost');
define('DB_NAME', 'efsantos_disc');
define('DB_USER', 'efsantos_disc');
define('DB_PASS', 'Kyew1802');

// Test paths configuration
define('DISC_TEST_PATH', 'take_test.php?type=disc');
define('MBTI_TEST_PATH', 'mbti/test.php');
define('BIGFIVE_TEST_PATH', 'test_bigfive.php');
define('JSS_TEST_PATH', 'test_jss.php');
define('DISC_RESULTS_PATH', 'view_result.php');
define('MBTI_RESULTS_PATH', 'mbti/view_results.php');
define('BIGFIVE_RESULTS_PATH', 'view_results_bigfive.php');
define('JSS_RESULTS_PATH', 'view_results_jss.php');

// Function to get test path based on type
function getTestPath($testType) {
    switch ($testType) {
        case 'mbti':
            return MBTI_TEST_PATH;
        case 'bigfive':
            return BIGFIVE_TEST_PATH;
        case 'jss':
            return JSS_TEST_PATH;
        default:
            return DISC_TEST_PATH;
    }
}

// Function to get results path based on type
function getResultsPath($testType) {
    switch ($testType) {
        case 'mbti':
            return MBTI_RESULTS_PATH;
        case 'bigfive':
            return BIGFIVE_RESULTS_PATH;
        case 'jss':
            return JSS_RESULTS_PATH;
        default:
            return DISC_RESULTS_PATH;
    }
}
