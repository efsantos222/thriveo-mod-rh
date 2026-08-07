# CLAUDE.md — Guia de Desenvolvimento Thriveo

Este arquivo instrui o Claude Code sobre convenções, padrões e fluxos de trabalho deste repositório.

---

## Visão Geral do Projeto

**Thriveo** é uma plataforma SaaS multi-tenant de RH com 20+ módulos PHP hospedados em shared hosting (Hostgator). Cada módulo é uma aplicação PHP independente com seu próprio banco MySQL, compartilhando apenas o design system global em `assets/`.

---

## Estrutura de Diretórios

```
thriveo-mod-rh/
├── assets/css/          # Design System global (CSS variables, glassmorphism)
├── assets/js/           # Scripts globais compartilhados
├── [modulo]/
│   ├── config/db.php    # Credenciais do banco (padrão)
│   ├── config/config.php# Chaves de API, URLs base
│   ├── install.php      # Instalador (DELETAR após uso)
│   ├── database.sql     # Schema do módulo
│   └── index.php        # Entry point
├── PromptMaster-main/   # App React (isolado, Node.js)
├── disc/                # Módulo Python (isolado)
└── ic/backend/          # FastAPI Python (isolado)
```

---

## Padrões de Código

### PHP
- Versão mínima: **PHP 7.4**, preferir **PHP 8.0+**
- Estilo: Procedimental com OOP em `includes/` e `src/`
- Banco: **PDO** com prepared statements — nunca concatenar SQL
- Charset: `utf8mb4` em todas as conexões
- Sessões: `session_start()` no topo de páginas protegidas
- Senhas: `password_hash()` / `password_verify()` — nunca MD5/SHA1
- Output: `htmlspecialchars()` em todo dado exibido ao usuário

```php
// Conexão padrão PDO
$pdo = new PDO("mysql:host=localhost;dbname=efsantos_[modulo];charset=utf8mb4", 
               "efsantos_[modulo]", "Kyew1802",
               [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
```

### Roteamento (sem mod_rewrite)
O Hostgator tem limitações com mod_rewrite. Usar o padrão do `pdi`:
```php
// 1. Query string (funciona sempre)
$route = $_GET['r'] ?? null;
// 2. PATH_INFO (quando configurado)
if (!$route && isset($_SERVER['PATH_INFO'])) $route = $_SERVER['PATH_INFO'];
// 3. Fallback: strip do SCRIPT_NAME
if (!$route) {
    $requestUri = $_SERVER['REQUEST_URI'];
    $scriptName = dirname($_SERVER['SCRIPT_NAME']);
    $route = '/' . ltrim(substr($requestUri, strlen($scriptName)), '/');
    $route = strtok($route, '?');
}
$route = $route ?: '/';
```

### JavaScript
- Vanilla JS — sem jQuery
- Fetch API para AJAX
- JWT armazenado em `localStorage`
- Sem frameworks frontend (exceto PromptMaster que usa React)

### CSS
- Usar variáveis CSS do Design System: `var(--primary)`, `var(--navy)`, etc.
- Glassmorphism: `backdrop-filter: blur()` + `rgba()` backgrounds
- Mobile-first, responsivo
- **Bug conhecido:** `var(-navy)` (com um traço) quebra no Firefox/Edge — sempre usar `var(--navy)` (dois traços)

---

## Bancos de Dados

**Convenção de nomenclatura:**
- Database: `efsantos_[modulo]`
- User: `efsantos_[modulo]`
- Password: `Kyew1802` (alguns usam `Kyew@1802`)
- Host: `localhost` (mlpt usa `127.0.0.1`)

**Todos os módulos têm `company_id`** para isolamento multi-tenant.

| Módulo | Database |
|--------|----------|
| entrev | efsantos_entrev |
| board | efsantos_board |
| cult | efsantos_cult |
| clima | efsantos_clima |
| softskill | efsantos_softskill |
| coach | efsantos_coach |
| ic | efsantos_ic |
| pdi | efsantos_pdi |
| mlpt | efsantos_mlpt |
| perform | efsantos_testeslog |
| plano | efsantos_plano |
| fvit | efsantos_fvit |
| pcs | efsantos_gestaocargos |
| 5w2h | efsantos_5w2h |
| icadmpes | efsantos_ip |

---

## APIs Externas

| API | Módulos | Configuração |
|-----|---------|-------------|
| OpenAI GPT-4o | entrev, hardskill, softskill, cult, board, matchcv, ic | `config.php` de cada módulo |
| Anthropic Claude Sonnet 4 | agentes/ | `agentes/config.php` — `ANTHROPIC_API_KEY` |
| Google Gemini | PromptMaster | `.env.local` — `VITE_GEMINI_API_KEY` |
| GitHub OAuth | entrev | Client ID/Secret no config |
| LinkedIn OAuth | entrev | Client ID/Secret no config |
| Mercado Pago | pagamentos | Credenciais no config |

> ⚠️ **ATENÇÃO:** Chaves de API estão commitadas em arquivos `config.php`. Não logar nem expor esses valores. Ao refatorar, mover para variáveis de ambiente.

---

## Módulos Especiais

### PromptMaster-main (React + Vite)
```bash
cd PromptMaster-main
npm install
npm run dev       # dev em localhost:5173
npm run build     # gera dist/
```
Stack: React 19, Vite 6, TypeScript 5.8, Google Gemini, Firebase.

### disc/ (Python)
```bash
python disc/main.py              # modo CLI (sem dependências)
cd disc && pip install -r requirements.txt && python web_app.py  # modo web
# Deploy Hostgator: passenger_wsgi.py
```

### ic/backend/ (FastAPI)
```bash
cd ic/backend
pip install -r requirements.txt
python main.py   # API em http://localhost:8000
```

---

## Fluxo de Instalação de Módulo

1. Criar database `efsantos_[modulo]` no CPanel
2. Upload dos arquivos via FTP
3. Acessar `https://dominio/[modulo]/install.php` ou `setup.php`
4. **Deletar o instalador imediatamente após uso**

---

## Segurança — Regras Obrigatórias

- **Nunca** concatenar variáveis em queries SQL — usar PDO prepared statements
- **Nunca** exibir dados sem `htmlspecialchars()` — prevenir XSS
- **Nunca** usar `eval()` ou `exec()` com input de usuário
- Validar `company_id` em toda query que acessa dados de tenant
- Arquivos de upload: validar tipo MIME, não apenas extensão
- Instaladores (`install.php`, `setup.php`) devem ser deletados após uso

---

## Domínios de Produção

| Domínio | Uso |
|---------|-----|
| `thriveo.com.br` | Plataforma principal |
| `proftest.com.br` | Módulos board, clima, perform |
| `brilhamente.thriveo.com.br` | Plataforma Brilhamente (educacional) |

---

## Comandos Git

```bash
# Branch de desenvolvimento atual
git checkout claude/brave-mayer-2RmYd

# Padrão de commit
git commit -m "feat(modulo): descrição clara da mudança"
git commit -m "fix(modulo): o que foi corrigido e por quê"
git commit -m "docs: atualiza README/CLAUDE/memory"

# Push
git push -u origin claude/brave-mayer-2RmYd
```

---

## O que NÃO fazer

- Não usar `mysqli_*` — usar PDO
- Não usar jQuery — usar Vanilla JS / Fetch API
- Não depender de `mod_rewrite` — usar `?r=` parameter
- Não criar `.htaccess` sem testar no Hostgator
- Não commitar chaves de API (já existem, não adicionar novas)
- Não deletar arquivos ZIP de versões — são backups de produção
- Não modificar `thriveo.WordPress.2026-03-07.xml` — backup do WordPress
