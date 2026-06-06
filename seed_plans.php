<?php
require 'wp-load.php';
global $wpdb;

$table = $wpdb->prefix . 'thriveo_pagbank_planos';

$planos = [
    ['nome' => 'Básico', 'slug' => 'basico', 'preco_mensal' => 99.00, 'pagbank_plan_id' => 'PLN_BASICO_SANDBOX', 'descricao' => '1 Vaga Adicional'],
    ['nome' => 'Profissional', 'slug' => 'profissional', 'preco_mensal' => 199.00, 'pagbank_plan_id' => 'PLN_PRO_SANDBOX', 'descricao' => '3 Vagas Adicionais + Destaque'],
    ['nome' => 'Enterprise', 'slug' => 'enterprise', 'preco_mensal' => 499.00, 'pagbank_plan_id' => 'PLN_ENT_SANDBOX', 'descricao' => 'Vagas Ilimitadas + Triagem IA Avançada']
];

foreach ($planos as $p) {
    if (!$wpdb->get_var($wpdb->prepare("SELECT id FROM $table WHERE slug=%s", $p['slug']))) {
        $wpdb->insert($table, $p);
        echo "Plano {$p['nome']} inserido.\n";
    } else {
        echo "Plano {$p['nome']} já existe.\n";
    }
}
