<?php
echo json_encode([
    'php'  => PHP_VERSION,
    'curl' => function_exists('curl_init') ? 'OK' : 'INDISPONIVEL',
    'path' => __FILE__,
]);
