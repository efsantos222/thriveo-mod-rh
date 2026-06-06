<?php
require_once __DIR__ . '/wp-load.php';
global $wpdb;
$vagas = $wpdb->get_results("SELECT id, empresa_id, titulo, status, tipo_publicacao FROM {$wpdb->prefix}thriveo_vagas", ARRAY_A);
print_r($vagas);
$assinaturas = $wpdb->get_results("SELECT id, empresa_id, status FROM {$wpdb->prefix}thriveo_pagbank_assinaturas", ARRAY_A);
print_r($assinaturas);
$cotas = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}thriveo_cotas_mensais", ARRAY_A);
print_r($cotas);
$companies = $wpdb->get_results("SELECT id, wp_user_id, company_name FROM {$wpdb->prefix}thriveo_ai_companies", ARRAY_A);
print_r($companies);
