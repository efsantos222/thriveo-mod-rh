# Prompt 01 — Interface de Candidatos + Gestão de Vagas
> **Plataforma:** Thriveo / ProfTest  
> **Stack:** PHP MVC · JavaScript · HTML5 · CSS3 · MySQL  
> **Objetivo:** Painel de visualização de candidatos cadastrados via GitHub/LinkedIn + sistema de publicação de vagas com modelo freemium (1 gratuita/mês, demais pagas)

---

## CONTEXTO DO PROJETO

Você é um desenvolvedor full-stack sênior contratado para construir dois módulos 
integrados em uma plataforma de recrutamento especializado em TI já existente. 
A plataforma usa PHP (backend MVC), JavaScript (vanilla/jQuery), HTML5, CSS3 e 
MySQL. Já existe um sistema de login via OAuth com GitHub e LinkedIn que popula 
a tabela `candidatos` no banco de dados.

---

## MÓDULO 1 — PAINEL DE CANDIDATOS CADASTRADOS

### Objetivo
Criar uma interface administrativa (área logada) para visualizar, filtrar, 
buscar e gerenciar os candidatos que se cadastraram via GitHub e/ou LinkedIn.

### Especificações Técnicas

#### Página: /admin/candidatos ou /empresa/talentos

**Layout — Cards + Tabela alternável**
- Header com título "Banco de Talentos", contador total de candidatos 
  e botão de alternância entre visualização em cards e tabela.
- Barra de busca full-text que filtra em tempo real por: nome, cargo, 
  tecnologias, cidade.
- Filtros laterais ou em chips horizontais:
  - Origem do cadastro: GitHub | LinkedIn | Ambos
  - Área de atuação: Backend | Frontend | Full-Stack | DevOps | 
    Data/IA | Mobile | UX/Design | Outro
  - Nível: Júnior | Pleno | Sênior | Especialista
  - Disponibilidade: Imediato | 15 dias | 30 dias | Não informado
  - Stack principal: lista dinâmica gerada do banco (PHP, Python, 
    React, Node, Java, etc.)
  - Cidade/Estado
- Paginação: 20 candidatos por página com navegação.

**Card de Candidato**  
Cada card deve exibir:
- Avatar (foto do GitHub/LinkedIn ou iniciais em fallback)
- Nome completo
- Cargo/título profissional
- Localização (cidade, estado)
- Stack principal (até 5 tags coloridas por categoria)
- Origem: ícone GitHub e/ou LinkedIn com link clicável
- Nota de match (se aplicável, calculada posteriormente por IA)
- Botão "Ver Perfil" → abre modal ou página de detalhe
- Botão "Convidar para Vaga" → dispara modal de seleção de vaga ativa

**Modal / Página de Detalhe do Candidato**
- Todas as informações do cadastro: bio, experiência, formação, skills, links
- Repositórios GitHub em destaque (nome, linguagem, stars)
- Histórico profissional importado do LinkedIn
- Timeline de candidaturas anteriores nesta plataforma
- Campo de anotações privadas para a empresa (salvo em tabela separada)
- Status: Disponível | Em processo | Contratado | Inativo

### Backend (PHP)
Criar arquivo: `controllers/CandidatosController.php`

```php
// Endpoints necessários:
GET  /api/candidatos              → lista paginada com filtros (query string)
GET  /api/candidatos/{id}         → perfil completo
PUT  /api/candidatos/{id}/status  → atualizar status
POST /api/candidatos/{id}/nota    → salvar anotação da empresa
GET  /api/candidatos/export/csv   → exportar lista filtrada
```

Consulta SQL base:
```sql
SELECT 
  c.id, c.nome, c.email, c.foto_url, c.cargo_atual, c.cidade, c.estado,
  c.nivel_experiencia, c.disponibilidade, c.origem_cadastro,
  c.github_username, c.linkedin_url, c.bio, c.skills_json,
  c.github_repos_json, c.linkedin_experiencia_json,
  c.criado_em, c.ultimo_acesso
FROM candidatos c
WHERE c.ativo = 1
  AND (c.nome LIKE :busca OR c.cargo_atual LIKE :busca OR c.skills_json LIKE :busca)
  AND (:origem = '' OR c.origem_cadastro = :origem)
  AND (:nivel = '' OR c.nivel_experiencia = :nivel)
  AND (:area = '' OR c.area_atuacao = :area)
ORDER BY c.criado_em DESC
LIMIT :limit OFFSET :offset
```

---

## MÓDULO 2 — GESTÃO DE VAGAS PARA EMPRESAS

### Objetivo
Permitir que empresas cadastradas publiquem vagas com modelo freemium: 
1 vaga gratuita por mês, vagas adicionais pagas.

### Regras de Negócio

```
PLANO GRATUITO:
- 1 vaga ativa por mês (mês calendário)
- Vaga fica ativa por 30 dias
- Sem destaque na listagem
- Sem acesso à lista de candidatos que se candidataram
- Sem filtros avançados de busca de talentos

PLANO PAGO (por vaga adicional):
- R$ 99,00 por vaga adicional/mês
- Vaga fica ativa por 30 dias
- Destaque "PATROCINADA" na listagem pública
- Acesso à lista completa de candidatos interessados
- Botão "Buscar Talentos" ativo (proativo, não só receber candidaturas)
- Badge de empresa verificada
```

### Páginas e Fluxos

#### /empresa/vagas — Dashboard de Vagas da Empresa

**Header do Dashboard:**
- Nome da empresa + logo
- Status do plano: "Você usou X de 1 vaga gratuita este mês"
- Barra de progresso visual do cota mensal
- Botão "Publicar Nova Vaga" (destaque primário)

**Lista de Vagas:**  
Tabela com colunas:
- Título da vaga
- Status: Rascunho | Ativa | Expirada | Pausada
- Candidaturas recebidas (número, clicável se plano pago)
- Data de publicação / Expira em (countdown)
- Tipo: Gratuita | Paga
- Ações: Ver | Editar | Pausar | Duplicar | Excluir

#### /empresa/vagas/nova — Formulário de Publicação de Vaga

```
Seção 1 — Informações Básicas
- Título da Vaga *
- Área de Atuação * (select: Backend, Frontend, Full-Stack, DevOps, 
  Data/IA, Mobile, UX/Design, Gestão Tech, Outro)
- Nível * (Júnior, Pleno, Sênior, Especialista, Não especificado)
- Regime * (CLT, PJ, Freelancer, Estágio)
- Modalidade * (Presencial, Híbrido, Remoto)
- Cidade / Estado (obrigatório se Presencial ou Híbrido)

Seção 2 — Detalhes da Vaga
- Descrição da vaga * (textarea rico, mínimo 100 caracteres)
- Responsabilidades (textarea, bullet points sugeridos)
- Requisitos obrigatórios * (textarea)
- Requisitos desejáveis (textarea)
- Stack tecnológica * (tags input: adicionar tecnologias)

Seção 3 — Remuneração e Benefícios  
- Faixa salarial: [De R$ ___] [Até R$ ___] ou "A combinar" (checkbox)
- Benefícios (checkboxes múltiplos: VR, VA, VT, Plano de Saúde, 
  Gympass, PLR, Stock Options, Outros)
- Bônus / Comissão (campo texto livre)

Seção 4 — Processo Seletivo
- Etapas do processo (drag-and-drop reordenável): 
  Triagem de CV | Teste Técnico | Entrevista RH | 
  Entrevista Técnica | Proposta | Outros (campo customizável)
- Prazo para candidatura (date picker)
- Como se candidatar: Pelo sistema | Link externo | E-mail

Seção 5 — Revisão e Publicação
- Preview da vaga como o candidato verá
- Aviso de cota: SE empresa já usou 1 vaga gratuita → mostrar bloco 
  de cobrança ANTES de publicar
- Botão "Salvar Rascunho" | Botão "Publicar" (ou "Publicar e Pagar")
```

### Lógica de Cobrança (PHP)

```php
// Verificar cota mensal antes de publicar
function verificarCotaMensal($empresa_id): array {
    $mes_atual = date('Y-m');
    
    $query = "SELECT COUNT(*) as total 
              FROM vagas 
              WHERE empresa_id = :empresa_id 
                AND DATE_FORMAT(criado_em, '%Y-%m') = :mes
                AND tipo_publicacao IN ('gratuita', 'paga')
                AND status != 'rascunho'";
    
    // Se total >= 1: retorna ['pode_gratis' => false, 'custo' => 99.00]
    // Se total == 0: retorna ['pode_gratis' => true, 'custo' => 0]
}
```

**Modal de Confirmação de Pagamento** (quando cota esgotada):
- Resumo: "Esta será sua 2ª vaga em [mês]. Custo: R$ 99,00"
- Métodos de pagamento: Pix | Cartão de Crédito | Boleto
- Integrar com gateway: Mercado Pago ou PagSeguro (configurável via .env)
- Após confirmação do pagamento: publicar vaga automaticamente
- Nota fiscal automática por e-mail (opcional, fase 2)

#### /vagas — Página Pública de Listagem de Vagas

**Para candidatos (não logados ou logados):**
- Cards de vagas com: logo da empresa, título, área, nível, localização, 
  modalidade, faixa salarial (ou "A combinar"), data de publicação
- Vagas pagas aparecem com badge "DESTAQUE" e topo da lista
- Filtros: área, nível, modalidade, cidade, faixa salarial, stack
- Busca full-text no título e descrição
- Botão "Candidatar-se" → requer login (redireciona para OAuth se não logado)

**Detalhe da Vaga** `/vagas/{id}-{slug}`:
- Todas as informações da vaga formatadas
- Seção "Sobre a Empresa" (logo, nome, site, tamanho, segmento)
- Etapas do processo seletivo (timeline visual)
- Botão "Candidatar-se" → registra candidatura na tabela `candidaturas`
- Compartilhar vaga: WhatsApp, LinkedIn, Copiar Link

---

## BANCO DE DADOS

```sql
-- Tabela de vagas
CREATE TABLE vagas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    empresa_id INT NOT NULL,
    titulo VARCHAR(200) NOT NULL,
    slug VARCHAR(220) NOT NULL UNIQUE,
    area_atuacao VARCHAR(50) NOT NULL,
    nivel VARCHAR(30) NOT NULL,
    regime VARCHAR(30) NOT NULL,
    modalidade VARCHAR(30) NOT NULL,
    cidade VARCHAR(100),
    estado VARCHAR(2),
    descricao TEXT NOT NULL,
    responsabilidades TEXT,
    requisitos_obrigatorios TEXT NOT NULL,
    requisitos_desejaveis TEXT,
    stack_json JSON,
    salario_min DECIMAL(10,2),
    salario_max DECIMAL(10,2),
    salario_a_combinar TINYINT(1) DEFAULT 0,
    beneficios_json JSON,
    etapas_processo_json JSON,
    prazo_candidatura DATE,
    como_candidatar ENUM('sistema','link_externo','email') DEFAULT 'sistema',
    link_externo VARCHAR(500),
    email_candidatura VARCHAR(200),
    status ENUM('rascunho','ativa','pausada','expirada') DEFAULT 'rascunho',
    tipo_publicacao ENUM('gratuita','paga') DEFAULT 'gratuita',
    pagamento_id VARCHAR(100),
    pagamento_status VARCHAR(30),
    visualizacoes INT DEFAULT 0,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    expira_em TIMESTAMP,
    INDEX idx_empresa (empresa_id),
    INDEX idx_status (status),
    INDEX idx_area (area_atuacao),
    FULLTEXT INDEX ft_busca (titulo, descricao)
);

-- Tabela de candidaturas
CREATE TABLE candidaturas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vaga_id INT NOT NULL,
    candidato_id INT NOT NULL,
    status ENUM('recebida','em_analise','aprovado','reprovado','contratado') DEFAULT 'recebida',
    mensagem_candidato TEXT,
    anotacao_empresa TEXT,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_candidatura (vaga_id, candidato_id)
);

-- Tabela de cotas mensais (controle freemium)
CREATE TABLE cotas_mensais (
    id INT AUTO_INCREMENT PRIMARY KEY,
    empresa_id INT NOT NULL,
    mes_ano VARCHAR(7) NOT NULL,
    vagas_gratuitas_usadas INT DEFAULT 0,
    vagas_pagas INT DEFAULT 0,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_empresa_mes (empresa_id, mes_ano)
);

-- Anotações privadas empresa sobre candidato
CREATE TABLE anotacoes_candidatos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    empresa_id INT NOT NULL,
    candidato_id INT NOT NULL,
    anotacao TEXT NOT NULL,
    autor_nome VARCHAR(100),
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_empresa_candidato (empresa_id, candidato_id)
);
```

---

## ESTRUTURA DE ARQUIVOS PHP (MVC)

```
/controllers/
  CandidatosController.php    ← listagem, filtros, perfil, anotações
  VagasController.php         ← CRUD vagas, controle de cota, publicação
  CandidaturasController.php  ← submeter, listar, atualizar status
  PagamentosController.php    ← integração gateway, webhook, confirmação

/models/
  Candidato.php
  Vaga.php
  Candidatura.php
  CotaMensal.php

/views/
  admin/
    candidatos/index.php      ← lista com cards/tabela
    candidatos/detalhe.php    ← perfil completo
  empresa/
    vagas/index.php           ← dashboard vagas
    vagas/form.php            ← formulário nova/editar vaga
    vagas/preview.php         ← preview antes de publicar
  publico/
    vagas/listagem.php        ← página pública
    vagas/detalhe.php         ← detalhe da vaga
  components/
    card-candidato.php
    card-vaga.php
    modal-pagamento.php
    filtros-candidatos.php

/assets/js/
  candidatos.js               ← busca, filtros, alternância view
  vagas-form.js               ← validação, tags input, drag-and-drop etapas
  pagamento.js                ← modal de pagamento, polling de status

/api/
  candidatos.php              ← endpoints REST
  vagas.php
  pagamentos/webhook.php      ← callback do gateway
```

---

## REQUISITOS NÃO FUNCIONAIS

1. **Segurança:** Todas as queries com PDO + prepared statements. 
   CSRF token em todos os formulários POST. Validação server-side obrigatória.

2. **Performance:** Paginação no banco (LIMIT/OFFSET). Cache de contadores 
   (total candidatos, vagas ativas) em tabela auxiliar ou Redis (opcional).
   Lazy loading de avatares.

3. **LGPD:** Botão "Solicitar exclusão de dados" no perfil do candidato. 
   Log de acesso: registrar qual empresa visualizou qual candidato e quando.
   Candidatos podem definir perfil como "oculto para empresas".

4. **UX Mobile:** Layout responsivo. Cards em coluna única no mobile. 
   Formulário de vaga em accordion para mobile.

5. **Feedback visual:** Loading spinners, toasts de sucesso/erro, 
   confirmações antes de ações destrutivas. Contador de caracteres nos textareas.

---

## DESIGN VISUAL

- Paleta: azul-marinho `#1a2e4a` como cor primária, verde-tech `#00c896` 
  como acento, cinza-claro `#f5f7fa` como fundo de cards.
- Tipografia: Inter ou Roboto (Google Fonts).
- Ícones: Font Awesome 6 ou Lucide Icons.
- Cards com shadow sutil, border-radius 12px, hover com elevação.
- Tags de stack coloridas por categoria: azul para linguagens, 
  laranja para frameworks, roxo para cloud/infra, verde para dados/IA.
- Status das vagas como badges coloridos: 
  verde=ativa, amarelo=pausada, cinza=expirada, azul=rascunho.

---

## ENTREGÁVEIS ESPERADOS

Gere o código completo e funcional para:
1. `CandidatosController.php` com todos os métodos
2. `VagasController.php` com controle de cota freemium
3. `views/admin/candidatos/index.php` com cards, filtros e busca em tempo real
4. `views/empresa/vagas/form.php` com formulário completo + validação JS
5. `views/publico/vagas/listagem.php` página pública
6. `assets/js/candidatos.js` com busca/filtro dinâmico via fetch API
7. `assets/js/vagas-form.js` com tags input e drag-and-drop
8. SQL completo das 4 tabelas com índices
9. `modal-pagamento.php` com lógica de exibição condicional

Gere arquivo por arquivo, completo, sem omitir código. 
Use comentários explicativos em português nas partes críticas da lógica.
