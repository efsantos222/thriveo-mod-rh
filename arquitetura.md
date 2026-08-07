# arquitetura.md — Arquitetura Técnica da Plataforma Thriveo

---

## Visão Geral

Thriveo é uma plataforma **multi-tenant monolítica modular**: cada módulo de RH é uma aplicação PHP independente hospedada no mesmo servidor, compartilhando apenas o design system global e a convenção de banco de dados.

```
┌─────────────────────────────────────────────────────────┐
│                    thriveo.com.br                       │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌────────┐  │
│  │ entrev   │  │  board   │  │  clima   │  │  pdi   │  │
│  │  PHP 8   │  │  PHP 8   │  │  PHP 8   │  │  PHP 8 │  │
│  │ MySQL    │  │ MySQL    │  │ MySQL    │  │ MySQL  │  │
│  └──────────┘  └──────────┘  └──────────┘  └────────┘  │
│                    assets/ (Design System)              │
│  ┌─────────────────┐  ┌────────────────────────────┐   │
│  │ PromptMaster    │  │ ic/backend (FastAPI)        │   │
│  │ React 19 + Vite │  │ Python 3.7+ + Uvicorn      │   │
│  └─────────────────┘  └────────────────────────────┘   │
└─────────────────────────────────────────────────────────┘
           │                    │
    ┌──────┴──────┐    ┌────────┴────────┐
    │  OpenAI API │    │ Anthropic Claude │
    │   GPT-4o    │    │   Sonnet 4      │
    └─────────────┘    └─────────────────┘
```

---

## Stack por Camada

### Infraestrutura
| Componente | Tecnologia |
|-----------|-----------|
| Servidor web | Apache (Hostgator shared hosting) |
| PHP | 8.0+ (mínimo 7.4) |
| Banco de dados | MySQL 8.0 (Hostgator) |
| Deploy | FTP + CPanel |
| Domínio | thriveo.com.br / proftest.com.br |

### Backend (PHP)
| Padrão | Detalhe |
|--------|--------|
| Arquitetura | Procedimental + OOP em classes específicas |
| Roteamento | `?r=` query param (sem mod_rewrite) |
| Auth | PHP Session + JWT (localStorage) |
| DB Access | PDO com prepared statements |
| Multi-tenancy | `company_id` em todas as tabelas |

### Frontend
| Componente | Tecnologia |
|-----------|-----------|
| Linguagem | Vanilla JS (ES6+) |
| HTTP | Fetch API (sem jQuery) |
| Estilos | CSS3 + CSS Variables + Glassmorphism |
| Fonts | Google Fonts (Inter, Outfit, Plus Jakarta Sans) |
| Exceção | PromptMaster usa React 19 + TypeScript |

### IA / Machine Learning
| Provedor | Modelo | Integração |
|---------|--------|-----------|
| OpenAI | GPT-4o | REST API (`api.openai.com/v1/chat/completions`) |
| Anthropic | claude-sonnet-4-20250514 | REST API (SDK PHP) |
| Google | Gemini | @google/genai npm package |

---

## Fluxo de Autenticação

```
Usuário
  │
  ├─ Login direto ──────────────────────────────────────────►  PHP Session
  │                                                              └── company_id, user_id
  │
  └─ OAuth (GitHub / LinkedIn)
         │
         ▼
      oauth.php ──► GitHub API / LinkedIn API
         │
         ▼
      Valida usuário ──► Cria/atualiza registro no MySQL
         │
         ▼
      Gera JWT ──────────────────────────────────────────────►  localStorage (frontend)
         │                                                         └── JWT com payload:
         │                                                              user_id, company_id,
         │                                                              email, roles
         ▼
      PHP Session também criada (dual auth para compatibilidade)
```

**Validação de rota protegida:**
```php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit;
}
// Validar company_id em toda query
$stmt = $pdo->prepare("SELECT * FROM tabela WHERE company_id = ?");
$stmt->execute([$_SESSION['company_id']]);
```

---

## Fluxo de Dados — Módulo com IA (ex: hardskill)

```
Recrutador                     PHP Backend                    OpenAI GPT-4o
    │                               │                               │
    ├─ POST /gerar-teste ──────────►│                               │
    │                               ├─ Valida sessão/permissão      │
    │                               ├─ Monta prompt com             │
    │                               │  contexto da vaga             │
    │                               ├─ POST /v1/chat/completions ──►│
    │                               │                               ├─ Gera questões
    │                               │◄─ JSON com questões ──────────┤
    │                               ├─ Salva no MySQL               │
    │◄─ HTML com teste renderizado ─┤                               │
```

---

## Mapa de Módulos e Bancos de Dados

```
Aplicação PHP              Database MySQL           Tabelas Principais
─────────────────────────────────────────────────────────────────────
entrev/          ──────►  efsantos_entrev     companies, users, jobs,
                                               candidates, interview_scripts

board/           ──────►  efsantos_board      empresas, users, settings,
                                               company_intellect, chat_sessions

clima/           ──────►  efsantos_clima      administradores, respondentes,
                                               perguntas, respostas

cult/            ──────►  efsantos_cult       (cultura, missão, visão, valores)

softskill/       ──────►  efsantos_softskill  users, settings, questions,
                                               test_assignments, answers

coach/           ──────►  efsantos_coach      usuarios, sessoes_coaching,
                                               participantes, questionarios,
                                               respostas, goals

hardskill/       ──────►  efsantos_hardskill  users, settings, tests,
                                               questions, test_assignments

ic/              ──────►  efsantos_ic         (análises competitivas)

pdi/             ──────►  efsantos_pdi        users, pdis, competencies,
                                               companies, system_settings

mlpt/            ──────►  efsantos_mlpt       companies, users, maturity_assessments,
                                               documents, pulse_surveys,
                                               pulse_responses, sentiment_analysis

perform/         ──────►  efsantos_testeslog  (avaliações de desempenho)

plano/           ──────►  efsantos_plano      portfolio_services

5w2h/            ──────►  efsantos_5w2h       companies, users, actions

pcs/             ──────►  efsantos_gestaocargos (cargos e salários)

fvit/            ──────►  efsantos_fvit       (visão de futuro estratégica)

unia/            ──────►  efsantos_unia       companies, users, courses,
                                               course_modules, events,
                                               enrollments, certificates

icadmpes/        ──────►  efsantos_ip         companies, users, api_keys,
                                               search_history

connections/     ──────►  (JSON em storage/)  sem banco de dados
```

---

## Módulos com Stacks Diferentes

### PromptMaster-main — React SPA

```
PromptMaster-main/
├── index.html
├── index.tsx          # Entry point React
├── App.tsx            # Componente raiz
├── types.ts           # TypeScript types
├── components/        # Componentes React
├── services/          # Google Gemini + Firebase calls
├── vite.config.ts
└── package.json

Dependências:
  react: ^19.2.0
  react-dom: ^19.2.0
  @google/genai: ^1.30.0
  firebase: ^12.6.0
  uuid: ^13.0.0
  vite: ^6.2.0
  typescript: ~5.8.2
```

### disc/ — Python CLI/Web

```
disc/
├── main.py            # CLI entry point (sem dependências externas)
├── web_app.py         # Flask web interface (opcional)
├── disc_data.py       # Dados do questionário
├── disc_results.py    # Cálculo de perfil
├── disc_test.py       # Testes
├── passenger_wsgi.py  # Deploy WSGI no Hostgator
├── install.sh
└── requirements.txt   # flask>=2.0.0, gunicorn>=20.0.0

Modos:
  CLI:  python main.py          (sem dependências)
  Web:  python web_app.py       (requer Flask)
  Prod: passenger_wsgi.py       (Hostgator WSGI)
```

### ic/backend/ — FastAPI

```
ic/backend/
├── main.py            # FastAPI app (porta 8000)
└── requirements.txt
    ├── fastapi
    ├── uvicorn
    ├── openai
    ├── beautifulsoup4
    ├── requests
    ├── textblob
    └── pydantic

Endpoints disponíveis em: http://localhost:8000
Frontend PHP se comunica via fetch() para http://localhost:8000
```

---

## Padrão de Roteamento PHP (Hostgator-safe)

O módulo `pdi` implementa o roteamento mais robusto, compatível com Hostgator:

```php
// 1. Tenta ?r= (query string — funciona sempre)
$route = $_GET['r'] ?? null;

// 2. Tenta PATH_INFO (quando configurado)
if (!$route && isset($_SERVER['PATH_INFO'])) {
    $route = $_SERVER['PATH_INFO'];
}

// 3. Fallback: strip do SCRIPT_NAME da REQUEST_URI
if (!$route) {
    $requestUri = $_SERVER['REQUEST_URI'];
    $scriptName = dirname($_SERVER['SCRIPT_NAME']);
    $route = '/' . ltrim(substr($requestUri, strlen($scriptName)), '/');
    $route = strtok($route, '?'); // remove query string
}

$route = $route ?: '/';
```

---

## Multi-Tenancy

Isolamento via `company_id` em todas as tabelas:

```sql
-- Exemplo de schema multi-tenant
CREATE TABLE pdis (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NOT NULL,          -- isolamento de tenant
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_company (company_id),
    FOREIGN KEY (company_id) REFERENCES companies(id)
);

-- Toda query filtra por company_id
SELECT * FROM pdis WHERE company_id = ? AND user_id = ?
```

**Hierarquia de roles:**
```
Super Admin (acesso a todos os tenants)
    └── Company Admin (acesso ao próprio tenant)
            └── Usuário / RH (acesso limitado)
                    └── Candidato (acesso apenas ao próprio perfil)
```

---

## Integrações de Pagamento e Comunicação

```
Mercado Pago Checkout Pro
  └── Webhooks de pagamento
  └── Controle de assinaturas por empresa

Meta WhatsApp API
  └── Webhook: /webhook/webhook.php
  └── Chatbot com OpenAI (em desenvolvimento)

GitHub OAuth
  └── Callback: oauth.php
  └── Scope: user:email, read:user

LinkedIn OAuth
  └── Callback: oauth.php
  └── Scope: r_emailaddress, r_liteprofile
```

---

## URLs Administrativas

| URL | Acesso |
|-----|-------|
| `/?thriveo_pla_admin=1` | Super Admin (plataforma unificada) |
| `/?thriveo_dashboard=1` | Dashboard do candidato |
| `/[modulo]/install.php` | Instalador do módulo (deletar após uso) |
| `/[modulo]/setup.php` | Setup alternativo (deletar após uso) |
| `/webhook/webhook.php` | Webhook WhatsApp |

---

## Diagrama de Dependências de IA

```
                    ┌──────────────────────────────────┐
                    │         Thriveo Platform         │
                    └──────────────────────────────────┘
                         │              │         │
               ┌─────────┘              │         └─────────┐
               ▼                        ▼                   ▼
        ┌─────────────┐         ┌──────────────┐    ┌──────────────┐
        │  OpenAI     │         │  Anthropic   │    │   Google     │
        │  GPT-4o     │         │  Claude      │    │   Gemini     │
        └─────────────┘         │  Sonnet 4    │    └──────────────┘
               │                └──────────────┘           │
        ┌──────┴──────┐                │             ┌─────┘
        │             │          ┌─────┘             │
        ▼             ▼          ▼                   ▼
   entrev/        board/     agentes/          PromptMaster/
   hardskill/     cult/      (multi-agent)     (prompt mgmt)
   softskill/     ic/
   matchcv/       clima/
```

---

## Histórico de Versões (WordPress Plugin)

| Versão | Arquivo ZIP | Mudanças |
|--------|-------------|----------|
| v1.6.8 | thriveo-ai-jobs-v1.6.8_RELATIVE_AJAX.zip | AJAX com caminhos relativos |
| v1.6.9 | thriveo-ai-jobs-v1.6.9_INCLUDE.zip | Inclusão de arquivos refatorada |
| v1.7.0 | thriveo-ai-jobs-v1.7.0_AUTH_GUARD.zip | Guard de autenticação |
| v1.7.1 | thriveo-ai-jobs-v1.7.1_API_WAF_BYPASS.zip | Bypass de WAF para API |
| v1.8.0 | thriveo-ai-jobs.zip | Versão estável |
| v1.8.1 | thriveo-ai-jobs-hr-modules-v1.8.1.zip | Módulos HR integrados |
| v1.8.2 | thriveo-ai-jobs-hr-modules-v1.8.2.zip | Correções v1.8.1 |
