# Thriveo — Ecossistema Unificado de RH com IA

**Thriveo** é uma plataforma multi-tenant de Gestão de Pessoas que consolida mais de 20 módulos especializados — recrutamento, avaliação comportamental, clima organizacional, desenvolvimento individual, coaching, inteligência competitiva e muito mais — em um único ecossistema integrado e orientado por inteligência artificial.

> Domínio principal: [thriveo.com.br](https://thriveo.com.br)

---

## Índice

- [Visão Geral](#visão-geral)
- [Módulos](#módulos)
  - [Recrutamento & Seleção](#recrutamento--seleção)
  - [Cultura & Clima Organizacional](#cultura--clima-organizacional)
  - [Desenvolvimento & Performance](#desenvolvimento--performance)
  - [Inteligência & Analytics](#inteligência--analytics)
  - [Ferramentas Especializadas](#ferramentas-especializadas)
- [Stack Tecnológica](#stack-tecnológica)
- [Arquitetura](#arquitetura)
- [Instalação](#instalação)
- [Variáveis de Ambiente & APIs](#variáveis-de-ambiente--apis)
- [Acesso Administrativo](#acesso-administrativo)
- [Segurança](#segurança)
- [Roadmap](#roadmap)

---

## Visão Geral

| Característica | Detalhe |
|---|---|
| Tipo | Plataforma SaaS multi-tenant |
| Arquitetura | Monolítica modular |
| Módulos ativos | 20+ |
| Isolamento de dados | `company_id` por tenant |
| Hierarquia de acesso | Super Admin → Empresa Admin → Usuário → Candidato |
| Hospedagem | Shared Hosting (Hostgator / Apache) |
| Idioma principal | Português (BR) |

---

## Módulos

### Recrutamento & Seleção

#### `entrev` — Sistema Inteligente de Recrutamento
Gerencia vagas, candidatos e processos seletivos end-to-end com geração automática de roteiros de entrevista via OpenAI GPT-4o.

- Publicação e gerenciamento de vagas
- Cadastro e triagem de candidatos
- Roteiros de entrevista gerados por IA
- Autenticação OAuth (GitHub / LinkedIn)

**Stack:** PHP 7.4+, MySQL, OpenAI API

---

#### `hardskill` — Avaliação de Competências Técnicas
Geração automática de testes técnicos e correção inteligente por IA, com dashboard para recrutadores.

- Testes personalizados gerados por GPT-4o
- Correção e pontuação automáticas
- Relatórios por candidato e por vaga

**Stack:** PHP 7.4+, MySQL, OpenAI API

---

#### `softskill` — Avaliação Comportamental (DISC / MBTI)
Administração de avaliações comportamentais com feedback gerado por IA e painel do recrutador.

**Stack:** PHP 8.0+, MySQL, OpenAI API

---

#### `disc` — Perfil DISC Standalone
Módulo autônomo de perfilamento comportamental com 20 questões, disponível via CLI ou interface web.

**Stack:** Python 3.7+ (Flask opcional para modo web)

```bash
# Modo CLI
python disc/main.py

# Modo Web (requer Flask)
cd disc && pip install -r requirements.txt && python web_app.py
```

---

#### `matchcv` — Matching de Currículos
Cruzamento inteligente de currículos com requisitos de vagas. Usa `proxy.php` para proteger a chave de API no server-side.

**Stack:** PHP, OpenAI API

---

### Cultura & Clima Organizacional

#### `board` — Onboarding Cultural & AI Buddy
Assistente de onboarding treinado na cultura da empresa, além de entrevistas de offboarding e transferência de conhecimento.

**Stack:** PHP 7.4+, MySQL, OpenAI API

---

#### `clima` — Pesquisa de Clima Semanal
Envio e coleta de pesquisas semanais de clima organizacional com geração de relatórios automatizados.

**Stack:** PHP 7.4+, MySQL

---

#### `cult` — Análise de Cultura Organizacional
Definição de identidade organizacional (Missão, Visão, Valores) e análise de lacunas culturais via IA.

**Stack:** PHP 8.0+, MySQL, OpenAI API

---

#### `connections` — Jogos Virtuais de Integração
Dinâmicas de integração de equipes: icebreakers, apresentações relâmpago, storytelling com emojis, mystery box. **Único módulo sem banco de dados** — armazena estado em JSON em `storage/`.

**Stack:** PHP 7.0+, JSON

---

### Desenvolvimento & Performance

#### `pdi` — Plano de Desenvolvimento Individual
Gestão de PDIs com acompanhamento de cursos, metas e notificações. Módulo mais sofisticado do repositório, com roteamento robusto compatível com Hostgator.

**Stack:** PHP 8.0+, MySQL

---

#### `perform` — Gestão de Performance
Avaliações de desempenho, coleta de feedbacks e relatórios por colaborador.

**Stack:** PHP 7.4+, MySQL

---

#### `coach` — Coaching & Mentoria
Gestão de sessões de coaching, feedback 360°, metas SMART e hierarquia de master coaches.

**Stack:** PHP 7.4+, MySQL

---

#### `mlpt` — Liderança & Gestão de Times
Avaliações de liderança, pulse surveys e análise de sentimento de equipes.

**Stack:** PHP 7.4+, MySQL

---

#### `plano` — Planejamento de Serviços
Planejamento estratégico de serviços e acompanhamento de execução.

**Stack:** PHP 7.4+, MySQL

---

#### `5w2h` — Plano de Ação
Ferramenta de resolução de problemas estruturada no framework 5W2H (O quê / Por quê / Onde / Quem / Quando / Como / Quanto).

**Stack:** PHP, MySQL

---

### Inteligência & Analytics

#### `ic` — Inteligência Competitiva
Análise de mercado, análise de sentimento e web scraping com backend em Python FastAPI e frontend em PHP.

- Análise de sentimento via GPT-4
- Web scraping de fontes públicas
- Dashboard de inteligência de mercado

**Stack:** PHP (frontend), Python 3.7+ + FastAPI (backend), OpenAI API

```bash
cd ic/backend
pip install -r requirements.txt
python main.py
# API disponível em: http://localhost:8000
```

---

#### `aval_comp` — Avaliação Competitiva
Análise e benchmarking competitivo com processamento de CSV.

**Stack:** PHP, CSV

---

### Ferramentas Especializadas

#### `PromptMaster-main` — Gestão de Prompts de IA
Interface React para criação, teste e gerenciamento de prompts de IA com controle de acesso via pagamento Pix e integração ao Google Gemini.

**Stack:** React 19, Vite 6, TypeScript, Google Gemini API, Firebase

```bash
cd PromptMaster-main
npm install
npm run dev
# Disponível em: http://localhost:5173
```

---

#### `agentes` — Agentes Corporativos de IA
Sistema multi-agente para fluxos de trabalho corporativos usando Anthropic Claude Sonnet 4.

**Stack:** PHP, Anthropic Claude API (`claude-sonnet-4-20250514`)

---

#### `unia` — LMS / Treinamentos
Plataforma de aprendizagem completa com cursos, módulos, eventos, inscrições e emissão de certificados.

**Stack:** PHP, MySQL

---

#### `lc` — Cursos & Aprendizagem
Gestão e entrega de cursos internos.

**Stack:** PHP

---

#### `pcs` — Gestão de Cargos e Salários
Estruturação e administração de cargos e tabelas salariais.

**Stack:** PHP, MySQL (`efsantos_gestaocargos`)

---

#### `engajex` — Engajamento de Colaboradores
Acompanhamento de engajamento e satisfação dos colaboradores.

**Stack:** PHP

---

#### `fvit` — Visão de Futuro
Ferramenta de planejamento estratégico orientada para o futuro.

**Stack:** PHP 7.4+, MySQL

---

## Stack Tecnológica

### Backend
| Tecnologia | Uso |
|---|---|
| PHP 8.0+ / 7.4+ | Core de todos os módulos |
| MySQL 5.7+ / 8.0+ | Banco de dados relacional |
| Python 3.7+ | Módulos DISC e IC |
| FastAPI + Uvicorn | Backend do módulo IC |
| Apache (Hostgator) | Servidor web — roteamento via `?r=` (sem mod_rewrite) |

### Frontend
| Tecnologia | Uso |
|---|---|
| HTML5 / CSS3 / Vanilla JS | Base de todos os módulos |
| CSS Variables + Glassmorphism | Design System unificado |
| React 19 + Vite 6 + TypeScript | PromptMaster |
| Google Fonts (Inter, Outfit) | Tipografia |

### Integrações Externas
| API / Serviço | Módulos |
|---|---|
| OpenAI GPT-4o | entrev, hardskill, softskill, cult, board, matchcv, ic |
| Anthropic Claude Sonnet 4 | agentes |
| Google Gemini | PromptMaster |
| GitHub OAuth | entrev (autenticação de candidatos) |
| LinkedIn OAuth | entrev (autenticação de candidatos) |
| Mercado Pago | Cobrança / assinaturas |
| Meta WhatsApp API | Chatbot (em desenvolvimento) |

---

## Arquitetura

```
thriveo-mod-rh/
├── index.php               # Landing page principal (Brilhamente)
├── login.html              # Autenticação de usuários
├── oauth.php               # Handler OAuth (GitHub / LinkedIn)
├── assets/
│   ├── css/                # Estilos globais (Design System)
│   └── js/                 # Scripts globais
├── entrev/                 # Módulo de recrutamento
├── hardskill/              # Avaliação técnica
├── softskill/              # Avaliação comportamental
├── disc/                   # Perfil DISC (Python)
├── board/                  # Onboarding cultural
├── clima/                  # Pesquisa de clima
├── cult/                   # Cultura organizacional
├── connections/            # Jogos de integração (JSON, sem DB)
├── pdi/                    # Desenvolvimento individual
├── perform/                # Performance
├── coach/                  # Coaching & mentoria
├── mlpt/                   # Liderança & times
├── ic/                     # Inteligência competitiva
│   └── backend/            # FastAPI (Python, porta 8000)
├── PromptMaster-main/      # Gestão de prompts (React + Vite)
├── agentes/                # Agentes de IA (Anthropic)
├── 5w2h/                   # Plano de ação
├── matchcv/                # Matching de currículos
├── unia/                   # LMS / Treinamentos
├── pcs/                    # Gestão de cargos
├── lc/                     # Cursos
├── engajex/                # Engajamento
└── fvit/                   # Visão de futuro
```

**Fluxo de autenticação:**
1. Usuário autentica via OAuth ou formulário
2. JWT armazenado em `localStorage` + sessão PHP (dual auth)
3. Toda query valida `company_id` para isolamento multi-tenant
4. Chamadas à IA passam por `proxy.php` ou handler server-side

> Ver `arquitetura.md` para diagramas detalhados de fluxo de dados e multi-tenancy.

---

## Instalação

### Pré-requisitos

- Apache com `mod_rewrite` habilitado (ou usar roteamento `?r=`)
- PHP 8.0+ (mínimo 7.4)
- MySQL 5.7+ ou 8.0+
- Node.js 18+ (apenas para PromptMaster)
- Python 3.7+ (apenas para módulos `disc` e `ic`)

### Hospedagem Compartilhada (Hostgator)

1. **Criar banco de dados** via CPanel (padrão: `efsantos_[modulo]`)
2. **Upload dos arquivos** via FTP para `public_html/` ou subdiretório
3. **Executar o instalador** de cada módulo:
   ```
   https://seu-dominio.com.br/[modulo]/install.php
   # ou
   https://seu-dominio.com.br/[modulo]/setup.php
   ```
4. **⚠️ Deletar `install.php` / `setup.php`** imediatamente após a instalação

### Desenvolvimento Local

```bash
# Módulos PHP (servidor embutido — apenas para dev)
php -S localhost:8000 -t .

# PromptMaster (React)
cd PromptMaster-main
npm install
npm run dev

# Módulo DISC (Python)
cd disc
python main.py --demo

# Backend IC (Python / FastAPI)
cd ic/backend
pip install -r requirements.txt
python main.py
```

### Configuração por Módulo

Cada módulo possui arquivos de configuração no padrão:

```
[modulo]/config/db.php       # Credenciais do banco
[modulo]/config/config.php   # Chaves de API, URLs
```

---

## Variáveis de Ambiente & APIs

| Variável | Onde configurar | Descrição |
|---|---|---|
| `OPENAI_API_KEY` | `config.php` de cada módulo | Chave GPT-4o |
| `ANTHROPIC_API_KEY` | `agentes/config.php` | Chave Claude API |
| `GEMINI_API_KEY` | `PromptMaster-main/.env.local` | Chave Google Gemini |
| `DB_HOST` / `DB_NAME` / `DB_USER` / `DB_PASS` | `config/db.php` de cada módulo | Credenciais MySQL |

> **Nunca commite chaves de API ou senhas no repositório.** Use variáveis de ambiente ou arquivos `.env` fora do controle de versão.

---

## Acesso Administrativo

**Super Admin (plataforma unificada):**

```
URL: /?thriveo_pla_admin=1
Usuário: ezequiel_santos
E-mail: ezequiel.santos@gmail.com
```

> Altere as credenciais padrão imediatamente em ambientes de produção.

---

## Segurança

- Senhas armazenadas com hash (`password_hash` bcrypt)
- Validação de sessão em todas as rotas protegidas
- PDO com prepared statements em todas as queries
- Sanitização de outputs com `htmlspecialchars()` para prevenir XSS
- Isolamento multi-tenant via `company_id` em todas as queries
- Conformidade com **LGPD**
- Instale e delete os arquivos `install.php` / `setup.php` após uso

---

## Roadmap

| Módulo | Status |
|---|---|
| `entrev` — Recrutamento com IA | ✅ Ativo |
| `hardskill` — Testes técnicos | ✅ Ativo |
| `softskill` — Avaliação comportamental | ✅ Ativo |
| `disc` — Perfil DISC | 🔄 Migração em andamento |
| `board` — Onboarding cultural | ✅ Ativo |
| `clima` — Pesquisa de clima | ✅ Ativo |
| `cult` — Cultura organizacional | ✅ Ativo |
| `coach` — Coaching & mentoria | ✅ Ativo |
| `ic` — Inteligência competitiva | ✅ Ativo |
| `mlpt` — Liderança & times | ✅ Ativo |
| `PromptMaster` — Gestão de prompts | ✅ Ativo |
| `agentes` — Agentes corporativos de IA | ✅ Ativo |
| `unia` — LMS / Treinamentos | ✅ Ativo |
| `pdi` — Desenvolvimento individual | 🔄 Em desenvolvimento |
| `zbb` — Gestão orçamentária | 🗺️ Roadmap |
| Integração WhatsApp (Meta API) | 🔄 Em desenvolvimento |

---

> Documentação técnica detalhada: [`arquitetura.md`](./arquitetura.md) | [`memory.md`](./memory.md) | [`CLAUDE.md`](./CLAUDE.md)

© 2026 Thriveo — Inteligência em Gestão de Pessoas.
