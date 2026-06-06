<?php
/**
 * ═══════════════════════════════════════════════════
 *  Thriveo — OAuth Handler
 *  GitHub + LinkedIn
 *  Arquivo: oauth.php
 * ═══════════════════════════════════════════════════
 *
 *  INSTALAÇÃO:
 *  1. Copie oauth.php para a raiz do seu projeto
 *  2. Configure as constantes abaixo com suas credenciais
 *  3. Crie a tabela no MySQL com o SQL no final deste arquivo
 *  4. Aponte as callbacks nas plataformas para:
 *     GitHub   → https://thriveo.com.br/oauth.php?action=github_callback
 *     LinkedIn → https://thriveo.com.br/oauth.php?action=linkedin_callback
 */

session_start();

// ─── CONFIGURAÇÕES — altere antes de subir em produção ────────────────────────

define('GITHUB_CLIENT_ID',       'SEU_GITHUB_CLIENT_ID');
define('GITHUB_CLIENT_SECRET',   'SEU_GITHUB_CLIENT_SECRET');
define('GITHUB_REDIRECT_URI',    'https://thriveo.com.br/oauth.php?action=github_callback');

define('LINKEDIN_CLIENT_ID',     'SEU_LINKEDIN_CLIENT_ID');
define('LINKEDIN_CLIENT_SECRET', 'SEU_LINKEDIN_CLIENT_SECRET');
define('LINKEDIN_REDIRECT_URI',  'https://thriveo.com.br/oauth.php?action=linkedin_callback');

define('APP_FRONTEND_URL',       'https://thriveo.com.br/login.html');
define('APP_DASHBOARD_URL',      'https://thriveo.com.br/dashboard.html');
define('JWT_SECRET',             'TROQUE_ISTO_POR_UMA_CHAVE_LONGA_E_ALEATORIA');

// ─── BANCO DE DADOS ───────────────────────────────────────────────────────────

define('DB_HOST', 'localhost');
define('DB_NAME', 'thriveo');
define('DB_USER', 'root');
define('DB_PASS', '');

function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO(
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
            DB_USER, DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
             PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
        );
    }
    return $pdo;
}

// ─── HEADERS ─────────────────────────────────────────────────────────────────

header('Access-Control-Allow-Origin: ' . APP_FRONTEND_URL);
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// ─── ROTEADOR ─────────────────────────────────────────────────────────────────

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'github_start':      githubStart();      break;
    case 'github_callback':   githubCallback();   break;
    case 'linkedin_start':    linkedinStart();    break;
    case 'linkedin_callback': linkedinCallback(); break;
    case 'me':                getMe();            break;
    case 'logout':            logout();           break;
    default:
        jsonResponse(['error' => 'Ação inválida'], 404);
}

// ═══════════════════════════════════════════════════════════════════════════════
//  GITHUB
// ═══════════════════════════════════════════════════════════════════════════════

function githubStart(): void {
    $state = bin2hex(random_bytes(16));
    $_SESSION['oauth_state']    = $state;
    $_SESSION['oauth_provider'] = 'github';

    $url = 'https://github.com/login/oauth/authorize?' . http_build_query([
        'client_id'    => GITHUB_CLIENT_ID,
        'redirect_uri' => GITHUB_REDIRECT_URI,
        'scope'        => 'read:user user:email read:org',
        'state'        => $state,
    ]);

    header('Location: ' . $url);
    exit;
}

function githubCallback(): void {
    // Validação CSRF
    $code  = $_GET['code']  ?? '';
    $state = $_GET['state'] ?? '';

    if (empty($code) || $state !== ($_SESSION['oauth_state'] ?? '')) {
        redirectWithError('state_mismatch');
    }

    // Troca code por access_token
    $tokenResp = httpPost('https://github.com/login/oauth/access_token', [
        'client_id'     => GITHUB_CLIENT_ID,
        'client_secret' => GITHUB_CLIENT_SECRET,
        'code'          => $code,
        'redirect_uri'  => GITHUB_REDIRECT_URI,
    ], ['Accept: application/json']);

    $token = json_decode($tokenResp, true);

    if (empty($token['access_token'])) {
        redirectWithError('token_error');
    }

    $accessToken = $token['access_token'];

    // Busca perfil público
    $profile = json_decode(httpGet('https://api.github.com/user', $accessToken, 'github'), true);

    // Busca e-mail (pode estar oculto no perfil principal)
    $emails = json_decode(httpGet('https://api.github.com/user/emails', $accessToken, 'github'), true);
    $primaryEmail = '';
    foreach ($emails as $e) {
        if ($e['primary'] && $e['verified']) {
            $primaryEmail = $e['email'];
            break;
        }
    }

    // Busca organizações públicas
    $orgs = json_decode(httpGet('https://api.github.com/user/orgs', $accessToken, 'github'), true);
    $orgNames = array_column($orgs ?? [], 'login');

    // Busca repositórios (top 10 por estrelas)
    $repos = json_decode(httpGet(
        'https://api.github.com/user/repos?sort=stargazers_count&per_page=10',
        $accessToken, 'github'
    ), true);

    $topRepos = [];
    foreach (($repos ?? []) as $r) {
        $topRepos[] = [
            'name'        => $r['name'],
            'description' => $r['description'],
            'stars'       => $r['stargazers_count'],
            'language'    => $r['language'],
            'url'         => $r['html_url'],
        ];
    }

    // Monta dados normalizados
    $userData = [
        'provider'         => 'github',
        'provider_id'      => (string)$profile['id'],
        'name'             => $profile['name'] ?? $profile['login'],
        'email'            => $primaryEmail ?: ($profile['email'] ?? ''),
        'avatar'           => $profile['avatar_url'] ?? '',
        'username'         => $profile['login'],
        'bio'              => $profile['bio'] ?? '',
        'location'         => $profile['location'] ?? '',
        'company'          => $profile['company'] ?? '',
        'blog'             => $profile['blog'] ?? '',
        'public_repos'     => $profile['public_repos'] ?? 0,
        'followers'        => $profile['followers'] ?? 0,
        'profile_url'      => $profile['html_url'],
        'organizations'    => implode(',', $orgNames),
        'top_repos'        => json_encode($topRepos),
        'access_token'     => $accessToken,
    ];

    $user = upsertUser($userData);
    $jwt  = generateJWT($user['id'], $user['email']);

    unset($_SESSION['oauth_state'], $_SESSION['oauth_provider']);

    // Redireciona para o frontend com token
    header('Location: ' . APP_DASHBOARD_URL . '?token=' . urlencode($jwt) . '&provider=github');
    exit;
}

// ═══════════════════════════════════════════════════════════════════════════════
//  LINKEDIN
// ═══════════════════════════════════════════════════════════════════════════════

function linkedinStart(): void {
    $state = bin2hex(random_bytes(16));
    $_SESSION['oauth_state']    = $state;
    $_SESSION['oauth_provider'] = 'linkedin';

    $url = 'https://www.linkedin.com/oauth/v2/authorization?' . http_build_query([
        'response_type' => 'code',
        'client_id'     => LINKEDIN_CLIENT_ID,
        'redirect_uri'  => LINKEDIN_REDIRECT_URI,
        'scope'         => 'openid profile email',
        'state'         => $state,
    ]);

    header('Location: ' . $url);
    exit;
}

function linkedinCallback(): void {
    $code  = $_GET['code']  ?? '';
    $state = $_GET['state'] ?? '';
    $error = $_GET['error'] ?? '';

    if (!empty($error)) {
        redirectWithError('linkedin_denied');
    }

    if (empty($code) || $state !== ($_SESSION['oauth_state'] ?? '')) {
        redirectWithError('state_mismatch');
    }

    // Troca code por access_token
    $tokenResp = httpPost('https://www.linkedin.com/oauth/v2/accessToken', [
        'grant_type'    => 'authorization_code',
        'code'          => $code,
        'redirect_uri'  => LINKEDIN_REDIRECT_URI,
        'client_id'     => LINKEDIN_CLIENT_ID,
        'client_secret' => LINKEDIN_CLIENT_SECRET,
    ]);

    $token = json_decode($tokenResp, true);

    if (empty($token['access_token'])) {
        redirectWithError('token_error');
    }

    $accessToken = $token['access_token'];

    // Busca perfil via OpenID Connect userinfo
    $profile = json_decode(httpGet(
        'https://api.linkedin.com/v2/userinfo',
        $accessToken, 'linkedin'
    ), true);

    $userData = [
        'provider'     => 'linkedin',
        'provider_id'  => $profile['sub'] ?? '',
        'name'         => ($profile['given_name'] ?? '') . ' ' . ($profile['family_name'] ?? ''),
        'email'        => $profile['email'] ?? '',
        'avatar'       => $profile['picture'] ?? '',
        'username'     => '',
        'bio'          => '',
        'location'     => $profile['locale']['country'] ?? '',
        'company'      => '',
        'blog'         => '',
        'public_repos' => 0,
        'followers'    => 0,
        'profile_url'  => 'https://linkedin.com',
        'organizations'=> '',
        'top_repos'    => json_encode([]),
        'access_token' => $accessToken,
    ];

    $user = upsertUser($userData);
    $jwt  = generateJWT($user['id'], $user['email']);

    unset($_SESSION['oauth_state'], $_SESSION['oauth_provider']);

    header('Location: ' . APP_DASHBOARD_URL . '?token=' . urlencode($jwt) . '&provider=linkedin');
    exit;
}

// ═══════════════════════════════════════════════════════════════════════════════
//  ENDPOINTS DE DADOS
// ═══════════════════════════════════════════════════════════════════════════════

function getMe(): void {
    header('Content-Type: application/json');
    $user = requireAuth();

    // Remove dados sensíveis
    unset($user['access_token']);

    // Decodifica top_repos para JSON
    if (!empty($user['top_repos'])) {
        $user['top_repos'] = json_decode($user['top_repos'], true);
    }

    jsonResponse(['user' => $user]);
}

function logout(): void {
    session_destroy();
    header('Content-Type: application/json');
    jsonResponse(['message' => 'Logout realizado com sucesso']);
}

// ═══════════════════════════════════════════════════════════════════════════════
//  BANCO DE DADOS
// ═══════════════════════════════════════════════════════════════════════════════

function upsertUser(array $data): array {
    $db = db();

    // Verifica se já existe pelo provider + provider_id
    $stmt = $db->prepare('SELECT * FROM users WHERE provider = ? AND provider_id = ?');
    $stmt->execute([$data['provider'], $data['provider_id']]);
    $existing = $stmt->fetch();

    if ($existing) {
        // Atualiza dados
        $stmt = $db->prepare('
            UPDATE users SET
                name = ?, email = ?, avatar = ?, username = ?, bio = ?,
                location = ?, company = ?, blog = ?, public_repos = ?,
                followers = ?, profile_url = ?, organizations = ?,
                top_repos = ?, access_token = ?, updated_at = NOW()
            WHERE id = ?
        ');
        $stmt->execute([
            $data['name'], $data['email'], $data['avatar'], $data['username'],
            $data['bio'], $data['location'], $data['company'], $data['blog'],
            $data['public_repos'], $data['followers'], $data['profile_url'],
            $data['organizations'], $data['top_repos'], $data['access_token'],
            $existing['id']
        ]);

        $stmt = $db->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$existing['id']]);
        return $stmt->fetch();
    }

    // Insere novo usuário
    $stmt = $db->prepare('
        INSERT INTO users (
            provider, provider_id, name, email, avatar, username, bio,
            location, company, blog, public_repos, followers, profile_url,
            organizations, top_repos, access_token, created_at, updated_at
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW()
        )
    ');
    $stmt->execute([
        $data['provider'], $data['provider_id'], $data['name'], $data['email'],
        $data['avatar'], $data['username'], $data['bio'], $data['location'],
        $data['company'], $data['blog'], $data['public_repos'], $data['followers'],
        $data['profile_url'], $data['organizations'], $data['top_repos'],
        $data['access_token']
    ]);

    $id   = $db->lastInsertId();
    $stmt = $db->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$id]);
    return $stmt->fetch();
}

// ═══════════════════════════════════════════════════════════════════════════════
//  JWT
// ═══════════════════════════════════════════════════════════════════════════════

function generateJWT(int $userId, string $email): string {
    $header  = base64url_encode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
    $payload = base64url_encode(json_encode([
        'sub'   => $userId,
        'email' => $email,
        'iat'   => time(),
        'exp'   => time() + (7 * 24 * 3600), // 7 dias
    ]));
    $sig = base64url_encode(hash_hmac('sha256', "$header.$payload", JWT_SECRET, true));
    return "$header.$payload.$sig";
}

function verifyJWT(string $token): ?array {
    $parts = explode('.', $token);
    if (count($parts) !== 3) return null;

    [$header, $payload, $sig] = $parts;
    $expected = base64url_encode(hash_hmac('sha256', "$header.$payload", JWT_SECRET, true));

    if (!hash_equals($expected, $sig)) return null;

    $data = json_decode(base64url_decode($payload), true);
    if ($data['exp'] < time()) return null;

    return $data;
}

function requireAuth(): array {
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    $token = str_replace('Bearer ', '', $authHeader);

    if (empty($token)) {
        jsonResponse(['error' => 'Token ausente'], 401);
        exit;
    }

    $payload = verifyJWT($token);
    if (!$payload) {
        jsonResponse(['error' => 'Token inválido ou expirado'], 401);
        exit;
    }

    $stmt = db()->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$payload['sub']]);
    $user = $stmt->fetch();

    if (!$user) {
        jsonResponse(['error' => 'Usuário não encontrado'], 404);
        exit;
    }

    return $user;
}

// ═══════════════════════════════════════════════════════════════════════════════
//  HELPERS HTTP
// ═══════════════════════════════════════════════════════════════════════════════

function httpPost(string $url, array $data, array $headers = []): string {
    $defaultHeaders = ['Content-Type: application/x-www-form-urlencoded'];
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => http_build_query($data),
        CURLOPT_HTTPHEADER     => array_merge($defaultHeaders, $headers),
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_TIMEOUT        => 15,
    ]);
    $response = curl_exec($ch);
    curl_close($ch);
    return $response ?: '';
}

function httpGet(string $url, string $token, string $provider = 'github'): string {
    $headers = ['Authorization: Bearer ' . $token];
    if ($provider === 'github') {
        $headers[] = 'Accept: application/vnd.github+json';
        $headers[] = 'User-Agent: Thriveo-App/1.0';
        // GitHub usa "token" ao invés de "Bearer"
        $headers[0] = 'Authorization: token ' . $token;
    }

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_TIMEOUT        => 15,
    ]);
    $response = curl_exec($ch);
    curl_close($ch);
    return $response ?: '{}';
}

// ─── UTILS ───────────────────────────────────────────────────────────────────

function base64url_encode(string $data): string {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function base64url_decode(string $data): string {
    return base64_decode(strtr($data, '-_', '+/') . str_repeat('=', (4 - strlen($data) % 4) % 4));
}

function jsonResponse(array $data, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

function redirectWithError(string $err): void {
    header('Location: ' . APP_FRONTEND_URL . '?error=' . $err);
    exit;
}

/*
 * ═══════════════════════════════════════════════════════════════════════════════
 *  SQL — Execute no seu banco MySQL para criar a tabela de usuários
 * ═══════════════════════════════════════════════════════════════════════════════
 *
 * CREATE TABLE IF NOT EXISTS users (
 *   id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 *   provider      ENUM('github','linkedin') NOT NULL,
 *   provider_id   VARCHAR(100) NOT NULL,
 *   name          VARCHAR(200),
 *   email         VARCHAR(200),
 *   avatar        VARCHAR(500),
 *   username      VARCHAR(100),
 *   bio           TEXT,
 *   location      VARCHAR(200),
 *   company       VARCHAR(200),
 *   blog          VARCHAR(500),
 *   public_repos  INT DEFAULT 0,
 *   followers     INT DEFAULT 0,
 *   profile_url   VARCHAR(500),
 *   organizations TEXT,
 *   top_repos     JSON,
 *   access_token  TEXT,
 *   created_at    DATETIME,
 *   updated_at    DATETIME,
 *   UNIQUE KEY uq_provider (provider, provider_id)
 * ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
 *
 */
