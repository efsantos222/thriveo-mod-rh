<?php
// ─── config.php ───────────────────────────────────────────────────────────────
// ATENÇÃO: Este arquivo contém a chave de API.
// NÃO faça commit deste arquivo em repositórios públicos.
// Adicione "config.php" ao seu .gitignore.

// ── Chave da API Anthropic (fica APENAS no servidor, nunca exposta ao browser) ──
define('ANTHROPIC_API_KEY', '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');

// ── Modelo padrão ──
define('CLAUDE_MODEL', 'claude-sonnet-4-20250514');

// ── Máximo de tokens por resposta ──
define('MAX_TOKENS', 2048);

// ── Segurança: bloqueia acesso direto a este arquivo ──
if (basename($_SERVER['PHP_SELF']) === 'config.php') {
    http_response_code(403);
    die('Acesso negado.');
}
