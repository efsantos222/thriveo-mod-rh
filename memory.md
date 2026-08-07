# memory.md — Memória de Desenvolvimento Thriveo

Registro cronológico de decisões técnicas, lições aprendidas e contexto acumulado do projeto.

---

## Contexto do Projeto

- **Repositório:** `efsantos222/thriveo-mod-rh`
- **Dono:** Ezequiel Santos (`ezequiel.santos@gmail.com`)
- **Hospedagem:** Hostgator (shared hosting, Apache, PHP 8.0+)
- **Domínio principal:** `thriveo.com.br`
- **Domínio auxiliar:** `proftest.com.br`
- **Plataforma educacional:** `brilhamente.thriveo.com.br`

---

## Histórico de Decisões Técnicas

### PHP Vanilla em vez de Framework
**Decisão:** Usar PHP procedimental puro (sem Laravel, Symfony, etc.)
**Razão:** Compatibilidade com Hostgator shared hosting. Frameworks pesados exigem Composer, configurações de servidor e performance que shared hosting não garante.
**Consequência:** Código mais verboso, mas 100% compatível com o ambiente de produção.

### Roteamento sem mod_rewrite
**Decisão:** Usar `?r=` como parâmetro de rota (padrão implementado no `pdi`)
**Razão:** mod_rewrite no Hostgator é instável/inconsistente. O `pdi` implementou fallback: `$_GET['r']` → `$_SERVER['PATH_INFO']` → strip de `SCRIPT_NAME`.
**Status:** Padrão a ser adotado nos novos módulos.

### Multi-banco de dados (um por módulo)
**Decisão:** Cada módulo tem seu próprio banco `efsantos_[modulo]`
**Razão:** Isolamento, independência de deploy, facilidade de backup por módulo.
**Convenção:** User = banco = `efsantos_[modulo]`, senha padrão `Kyew1802`.

### JWT + PHP Session (dual auth)
**Decisão:** JWT armazenado em `localStorage` + sessão PHP server-side
**Razão:** OAuth (GitHub/LinkedIn) retorna JWT. PHP session mantém estado server-side para compatibilidade com módulos legados.
**Implementação:** `oauth.php` na raiz, `class-oauth-handler.php` no plugin WordPress.

### Vanilla JS (sem jQuery)
**Decisão:** Usar Vanilla JS + Fetch API em todos os módulos
**Razão:** Performance em shared hosting, sem dependência externa, menor bundle.
**Exceção:** PromptMaster usa React 19 + Vite 6 (app standalone separado).

### Design System Glassmorphism
**Decisão:** CSS Variables + glassmorphism como linguagem visual unificada
**Razão:** Visual moderno e consistente entre módulos sem overhead de framework CSS.
**Arquivos:** `assets/css/` (global), cada módulo pode ter `assets/css/style.css` local.
**Bug conhecido:** CSS variables mal escritas (`var(-navy)` em vez de `var(--navy)`) causaram bugs no Firefox/Edge — corrigido no walkthrough.

### Chave API no banco de dados (icadmpes)
**Decisão:** `icadmpes` armazena a chave OpenAI no banco (tabela `api_keys`) em vez do config.php
**Razão:** Melhor prática de segurança — chave não commitada no repositório.
**Status:** Este padrão deve ser adotado nos demais módulos.

---

## Módulos — Status e Notas

### `entrev` — Recrutamento
- Entry point: `index.php`
- Auth: GitHub OAuth + LinkedIn OAuth
- IA: OpenAI GPT-4o para roteiros de entrevista
- DB: `efsantos_entrev` — tabelas: companies, users, jobs, candidates, interview_scripts
- URLs: `https://brilhamente.thriveo.com.br/entrev/`

### `hardskill` — Testes Técnicos
- Diagnose files presentes: `check_error.php`, `debug_admin.php`, `diagnose_users.php`, `fix_settings_table.php`
- Indica histórico de problemas de configuração e debug em produção
- DB: `efsantos_hardskill` (inferido)

### `softskill` — Avaliação Comportamental
- Estrutura MVC: `config/`, `public/`, `sql/`, `src/`, `views/`
- Schema SQL em `sql/schema.sql`
- DB: `efsantos_softskill`

### `disc` — Perfil DISC (Python)
- Standalone Python 3.7+ — único módulo não-PHP
- CLI nativo sem dependências externas
- Flask opcional para modo web
- `passenger_wsgi.py` para deploy WSGI no Hostgator
- Status: Migração em andamento para integração na plataforma PHP

### `board` — Onboarding Cultural
- BASE_URL: `https://proftest.com.br/board`
- Assistente de IA treinado na cultura da empresa
- DB: `efsantos_board` — tabelas: empresas, users, settings, company_intellect, chat_sessions

### `clima` — Pesquisa de Clima
- BASE_URL: `https://proftest.com.br/clima`
- Arquivos de diagnóstico: `test_connection.php`, `generate_hash.php`
- DB: `efsantos_clima`

### `cult` — Cultura Organizacional
- Tem `install.php` (deve ser deletado em produção)
- DB: `efsantos_cult`

### `connections` — Jogos de Integração
- **Sem banco de dados** — usa JSON em `storage/`
- Único módulo sem MySQL

### `coach` — Coaching & Mentoria
- Estrutura completa: `api/`, `auth/`, `sessions/`, `goals/`, `feedback/`, `questionnaires/`
- DB: `efsantos_coach`

### `ic` — Inteligência Competitiva
- Arquitetura híbrida: frontend PHP + backend Python FastAPI
- Backend em `ic/backend/` (porta 8000)
- DB: `efsantos_ic`

### `pdi` — Desenvolvimento Individual
- **Módulo mais sofisticado** do repositório (812 KB)
- Roteamento robusto compatível com Hostgator
- Estrutura: `src/`, `public/`, `templates/`, `logs/`, `scripts/`
- DB: `efsantos_pdi` — tabelas: users, pdis, competencies, companies, system_settings

### `perform` — Performance
- BASE_URL: `https://proftest.com.br`
- DB: `efsantos_testeslog` (nome diferente do padrão — legado)
- Estrutura: `admin/`, `aplicador/`, `candidato/`

### `mlpt` — Liderança
- Host DB usa `127.0.0.1` (não `localhost`) — Hostgator específico
- DB: `efsantos_mlpt`
- Tabelas: pulse_surveys, pulse_responses, sentiment_analysis

### `agentes` — Agentes de IA
- Usa Anthropic Claude Sonnet 4 (`claude-sonnet-4-20250514`)
- MAX_TOKENS: 2048
- Chave API em `agentes/config.php` — ⚠️ commitada no repo

### `matchcv` — Matching de CV
- Usa `proxy.php` — protege chave de API via server-side (boa prática)
- Chave OpenAI em `matchcv/config.php` — ⚠️ commitada no repo

### `icadmpes` — IC Administrativo
- Chave OpenAI armazenada no banco de dados (tabela `api_keys`) — melhor prática
- DB: `efsantos_ip`
- `setup_db.php` disponível

### `5w2h` — Plano de Ação
- Tem `import_actions.php` — suporte a importação de dados
- DB: `efsantos_5w2h`

### `unia` — LMS / Treinamentos
- Módulo completo de aprendizagem: cursos, módulos, eventos, inscrições, certificados
- Admin default: `ezequiel.santos@gmail.com` / `Kyew1802`

### `pcs` — Gestão de Cargos
- DB: `efsantos_gestaocargos`
- Estrutura: `index.html`, `dashboard.html`, `cadastrar_cargo.php`

### `PromptMaster-main` — Gestão de Prompts (React)
- App totalmente isolado (Node.js + Vite)
- Descrição do `metadata.json`: "Sistema profissional de geração e gerenciamento de prompts para IA com controle de acesso via pagamento Pix"
- Firebase integrado (autenticação e/ou storage)
- Stack: React 19.2, Vite 6.2, TypeScript 5.8, @google/genai 1.30, Firebase 12.6, uuid 13

---

## WordPress — Contexto Histórico

A plataforma começou como **plugin WordPress** (`thriveo-ai-jobs`):
- Versões: v1.6.8 → v1.8.2 (ZIPs presentes na raiz)
- Tabelas: `wp_thriveo_ai_candidates`, `wp_thriveo_ai_companies`, `wp_thriveo_ai_jobs`
- Auth: `/?thriveo_dashboard=1` (candidato), `/?thriveo_pla_admin=1` (admin)
- AJAX: `admin-ajax.php` com actions `thriveo_save_profile`, `thriveo_oauth_*`
- Backup XML: `thriveo.WordPress.2026-03-07.xml`

**Decisão de migração:** Abandonar o WordPress como base e construir PHP puro para cada módulo. O plugin continua existindo nos ZIPs como backup/referência.

---

## Linha do Tempo

| Data | Evento |
|------|--------|
| Mar 2026 | Lançamento do plugin WordPress Thriveo AI Jobs |
| Abr 2026 | Dashboard GPTW 2026 (HTML5 + SheetJS) |
| Mai 2026 | Chatbot WhatsApp (PHP + OpenAI webhook) |
| Jun 2026 | Migração para módulos PHP standalone |
| Ago 2026 | Refatoração da documentação (README, CLAUDE.md, memory.md, arquitetura.md) |

---

## Bugs e Workarounds Conhecidos

| Bug | Causa | Solução |
|-----|-------|--------|
| CSS quebrado no Firefox/Edge | `var(-navy)` em vez de `var(--navy)` | Corrigir typo do double-dash |
| CSS quebrado | Smart quotes em vez de aspas retas no CSS | Substituir `"` por `"` |
| mod_rewrite instável | Hostgator shared hosting | Usar `?r=` query param como fallback |
| OAuth AJAX falha | RewriteBase incorreto | Ajustar `.htaccess` RewriteBase por subdiretório |
| PDO charset | utf8 vs utf8mb4 | Usar sempre utf8mb4 na string de conexão |
| API key CORS | Chave exposta no frontend | Usar `proxy.php` server-side (implementado no matchcv) |
| mlpt não conecta | Host `localhost` vs `127.0.0.1` | mlpt usa `127.0.0.1` especificamente |

---

## Segurança — Pendências

- [ ] Mover chaves de API de `config.php` para variáveis de ambiente
- [ ] Rotacionar `ANTHROPIC_API_KEY` em `agentes/config.php` (exposta no repo)
- [ ] Rotacionar `OPENAI_API_KEY` em `matchcv/config.php` (exposta no repo)
- [ ] Confirmar que `install.php` / `setup.php` foram deletados em produção
- [ ] Criar `.env.example` com template das variáveis necessárias
- [ ] Auditar uploads em módulos que aceitam arquivos
- [ ] Adotar padrão do `icadmpes` (chave no banco) em todos os módulos

---

## Arquitetura de IA

| Provedor | Modelo | Módulos | Uso |
|---------|--------|---------|-----|
| OpenAI | GPT-4o | entrev, hardskill, softskill, cult, board, matchcv, ic | Geração de testes, feedbacks, análises |
| Anthropic | Claude Sonnet 4 (`claude-sonnet-4-20250514`) | agentes | Agentes corporativos multi-turn |
| Google | Gemini (via @google/genai) | PromptMaster | Geração e teste de prompts |

---

## Referências

- `arquitetura_thriveo_mermaid.md` — Diagrama Mermaid do fluxo de dados (legado)
- `implementation_plan.md` — Plano do perfil profissional (WordPress plugin)
- `walkthrough.md` — Registro de implementações e fixes
- `progresso_thriveo_marzo_10.md` — Progresso de março/2026
- `prompt_01_candidatos_vagas.md` — Prompt template para vagas
- `prompt_02_certificacao_thriveo.md` — Prompt template para certificações
- `arquitetura.md` — Arquitetura técnica atualizada (este projeto)
