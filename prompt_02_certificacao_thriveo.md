# Prompt 02 — Módulo de Certificação e Validação de Candidatos
> **Plataforma:** Thriveo  
> **Stack:** PHP MVC · JavaScript · HTML5 · CSS3 · MySQL  
> **Objetivo:** Sistema completo de certificação em 4 dimensões (Perfil Verificado, Hard Skills, Soft Skills, Reputação) com selo Thriveo Elite, geração de certificado PDF e página pública de verificação

---

## CONTEXTO DO PROJETO

Você é um desenvolvedor full-stack sênior contratado para construir o 
MÓDULO DE CERTIFICAÇÃO E VALIDAÇÃO DE CANDIDATOS da plataforma Thriveo 
(thriveo.com.br). A plataforma usa PHP (backend MVC), JavaScript (vanilla/jQuery), 
HTML5, CSS3 e MySQL. Candidatos já se cadastram via OAuth (GitHub + LinkedIn) 
e existem tabelas `candidatos` e `empresas` no banco.

A Thriveo é uma plataforma de RH especializada em TI. A certificação é o 
diferencial competitivo que transforma perfis autodeclarados em talentos 
VALIDADOS E CONFIÁVEIS para as empresas contratantes. Um candidato certificado 
recebe um selo visível no perfil e nas listagens de vagas.

---

## VISÃO GERAL DO MÓDULO

O sistema de certificação tem 4 dimensões de validação, aplicadas em sequência.
Um candidato pode ser certificado parcialmente (1–3 selos) ou totalmente 
(Certificação Thriveo Completa). Cada dimensão gera um "selo" independente.

```
┌─────────────────────────────────────────────────────────────┐
│          CERTIFICAÇÃO THRIVEO — 4 DIMENSÕES                 │
├──────────────┬──────────────┬──────────────┬────────────────┤
│  D1: Perfil  │  D2: Hard    │  D3: Soft    │  D4: Reputação │
│  Verificado  │  Skills      │  Skills      │  Profissional  │
│              │  Técnicas    │  & Fit       │                │
├──────────────┼──────────────┼──────────────┼────────────────┤
│ Gratuito     │ Gratuito 1x  │ Gratuito 1x  │ Gratuito       │
│ automático   │ Pago p/ mais │ Pago p/ mais │ (referências)  │
└──────────────┴──────────────┴──────────────┴────────────────┘
```

---

## DIMENSÃO 1 — PERFIL VERIFICADO (D1)

### Objetivo
Validar automaticamente a autenticidade e completude do perfil do candidato 
com base nos dados já importados do GitHub e LinkedIn.

### Critérios para obter o Selo D1 — "Perfil Verificado"

O sistema calcula um Score de Completude (0–100) com os seguintes pesos:

```
DADOS PESSOAIS (20 pontos):
  - Foto de perfil real (não avatar padrão): 5 pts
  - Nome completo (mínimo 2 palavras): 5 pts
  - Cidade e Estado preenchidos: 5 pts
  - Telefone ou e-mail verificado: 5 pts

GITHUB (30 pontos):
  - Conta com mais de 6 meses: 10 pts
  - Mínimo 5 repositórios públicos: 10 pts
  - Pelo menos 1 commit nos últimos 90 dias: 5 pts
  - README no perfil preenchido: 5 pts

LINKEDIN (30 pontos):
  - URL personalizada do LinkedIn: 5 pts
  - Mínimo 2 experiências profissionais cadastradas: 10 pts
  - Mínimo 1 formação acadêmica: 5 pts
  - Mais de 50 conexões: 5 pts
  - Bio/About preenchida com mínimo 100 caracteres: 5 pts

CONSISTÊNCIA (20 pontos):
  - Cargo do LinkedIn bate com área declarada na plataforma: 10 pts
  - Stack declarada aparece nos repositórios GitHub: 10 pts
```

**Regras de concessão:**
- Score >= 70 → Selo D1 concedido automaticamente
- Score entre 40–69 → Perfil "Em validação" com orientações de melhoria
- Score < 40 → Perfil "Incompleto" com checklist de ações

### Interface — Painel do Candidato (área logada)

**Card de Status de Certificação:**
- Medidor circular de progresso (gauge chart) mostrando o Score de Completude
- Lista de checklist com o que está completo (✅) e o que falta (❌)
- Para cada item faltante: texto explicativo e botão de ação (ex: "Atualizar perfil", 
  "Conectar GitHub")
- Badge animado ao atingir D1: "🎖️ Perfil Verificado pela Thriveo"

### Backend PHP

```php
// Arquivo: controllers/CertificacaoController.php

class CertificacaoController {

    public function calcularScoreD1($candidato_id): array {
        // Buscar dados do candidato + GitHub JSON + LinkedIn JSON
        // Aplicar pesos conforme tabela acima
        // Retornar: score, itens_completos, itens_pendentes, selo_concedido
    }

    public function concederSeloD1($candidato_id): void {
        // INSERT em certificacoes com tipo='D1', score, data_concessao
        // UPDATE candidatos SET selo_d1 = 1, score_perfil = :score
        // Disparar notificação (e-mail + notificação interna)
    }

    public function recalcularTodos(): void {
        // CRON JOB: rodar toda noite às 02h
        // Para cada candidato ativo, recalcular score D1
        // Conceder ou revogar selos automaticamente
    }
}
```

---

## DIMENSÃO 2 — HARD SKILLS TÉCNICAS (D2)

### Objetivo
Validar as competências técnicas declaradas pelo candidato por meio de 
testes práticos gerados dinamicamente.

### Trilhas de Certificação Técnica disponíveis

```
LINGUAGENS DE PROGRAMAÇÃO:
  PHP Certified Developer (Básico / Intermediário / Avançado)
  Python Developer (Básico / Intermediário / Avançado)
  JavaScript / TypeScript Developer
  Java Developer
  C# / .NET Developer

FRAMEWORKS & FRONTEND:
  React Developer
  Vue.js Developer
  Angular Developer
  Laravel Developer
  Node.js / Express Developer

BANCO DE DADOS:
  MySQL / MariaDB Specialist
  PostgreSQL Specialist
  MongoDB Developer
  Redis & Caching

INFRAESTRUTURA & DEVOPS:
  Git & Versionamento
  Docker & Containers
  CI/CD Pipelines
  AWS / Azure / GCP Fundamentals
  Linux Administration

DADOS & IA:
  SQL para Análise de Dados
  Python para Data Science
  Machine Learning Fundamentals
  Prompt Engineering & LLMs
```

### Formato dos Testes

Cada trilha tem 3 níveis, cada nível com 20 questões:

```
NÍVEL BÁSICO (20 questões, 30 min):
  - 10 questões teóricas (múltipla escolha)
  - 5 questões de completar código
  - 5 questões de identificar erro no código (debug)
  Aprovação: >= 70% de acerto

NÍVEL INTERMEDIÁRIO (20 questões, 45 min):
  - 5 questões teóricas
  - 10 questões de completar/corrigir código
  - 3 desafios de lógica com código real
  - 2 questões de arquitetura/boas práticas
  Aprovação: >= 75% de acerto

NÍVEL AVANÇADO (20 questões, 60 min):
  - 3 questões teóricas
  - 8 questões de código complexo
  - 5 cenários reais de problema/solução
  - 4 questões de performance e otimização
  Aprovação: >= 80% de acerto
```

### Geração de Questões com IA

```php
// Usar API Claude/OpenAI para gerar questões dinamicamente

function gerarQuestaoIA($trilha, $nivel, $topico): array {
    $prompt = "
    Gere uma questão de teste técnico para certificação de {$trilha} 
    nível {$nivel} sobre o tópico: {$topico}.
    
    Responda APENAS em JSON com esta estrutura:
    {
      'enunciado': 'texto da questão',
      'tipo': 'multipla_escolha|completar_codigo|debug|cenario',
      'codigo_base': 'código se aplicável (null se não)',
      'alternativas': ['A) ...', 'B) ...', 'C) ...', 'D) ...'],
      'resposta_correta': 'A',
      'explicacao': 'por que esta é a resposta correta',
      'dificuldade': 1-5,
      'topico': '{$topico}'
    }
    
    A questão deve:
    - Ser realista, baseada em situações do dia a dia do desenvolvedor
    - Testar conhecimento prático, não decoreba
    - Ter código em português nos comentários
    - Evitar questões ambíguas
    ";
    
    // Chamar API → parsear JSON → salvar em banco (cache 30 dias)
    // Pool mínimo de 100 questões por trilha/nível antes de ir ao ar
}
```

### Interface — Área do Candidato

**Página /candidato/certificacoes:**
- Grid de trilhas disponíveis com ícones coloridos por categoria
- Para cada trilha: status (Não iniciado / Em progresso / Aprovado / Reprovado)
- Ao clicar: modal com detalhes do nível, tempo estimado, data de validade do 
  certificado (12 meses), regras de reaprovação (aguardar 7 dias)
- Botão "Iniciar Teste" → redireciona para ambiente de teste

**Ambiente de Teste /candidato/teste/{trilha}/{nivel}:**
- Layout fullscreen sem distrações
- Barra de progresso: "Questão 8 de 20"
- Timer regressivo em destaque (vermelho nos últimos 5 minutos)
- Questões uma por vez com animação de transição
- Para questões de código: editor com syntax highlighting (CodeMirror ou Ace Editor)
- Não pode voltar para questão anterior (configurável por trilha)
- Auto-submit ao zerar o timer
- Anti-cola: detectar troca de aba (registrar no log, avisar 2x, cancelar na 3ª)

**Resultado imediato após o teste:**
- Tela de resultado com animação: aprovado (confetti) ou reprovado (feedback)
- Score obtido + percentual por categoria
- Revisão das questões erradas com explicação (apenas após aprovação no nível básico)
- Se aprovado: gerar certificado PDF automaticamente
- Se reprovado: mostrar áreas para estudar e data de liberação para reteste

### Modelo de Monetização D2

```
GRATUITO:
  - 1 teste por trilha (nível básico) gratuitamente
  - Certificado básico válido por 6 meses

PAGO — Plano Certificação Pro (R$ 49,90/mês):
  - Testes ilimitados em todas as trilhas e níveis
  - Reteste imediato (sem carência de 7 dias)
  - Certificados válidos por 12 meses
  - Badge "Pro Certified" no perfil
  - Histórico completo de desempenho com analytics

PAGO — Trilha Avulsa (R$ 19,90/trilha):
  - Acesso a todos os 3 níveis de uma trilha específica
  - Válido por 90 dias
```

---

## DIMENSÃO 3 — SOFT SKILLS & FIT CULTURAL (D3)

### Objetivo
Mapear as competências comportamentais e o perfil de trabalho do candidato 
por meio de avaliações psicométricas padronizadas.

### Avaliações Disponíveis

**Avaliação 1 — Perfil Comportamental DISC Simplificado (20 min)**  
Baseado em metodologia DISC adaptada. 30 situações profissionais, o candidato 
escolhe como reagiria em uma escala de 1–5. Resultado: perfil predominante 
(Dominante / Influente / Estável / Consciente) com relatório detalhado.

**Avaliação 2 — Inteligência Emocional no Trabalho (15 min)**  
25 cenários de conflito e pressão no ambiente tech. Mede: autoconsciência, 
autocontrole, empatia, motivação, habilidades sociais.

**Avaliação 3 — Fit com Cultura de Empresas Tech (10 min)**  
Questionário de valores e preferências: estilo de liderança, autonomia, 
comunicação, ritmo de trabalho, feedback. Gera um "Mapa de Fit Cultural" 
que empresas podem cruzar com o perfil da própria equipe.

**Avaliação 4 — Perfil de Trabalho Remoto (10 min)**  
Avalia: disciplina, comunicação assíncrona, gestão de tempo, proatividade. 
Gera selo "Remote Ready" se pontuação >= 80%.

### Interface

**Tela de avaliação:** Progresso visual, questões com escala Likert ou 
escolha de situação, sem timer (ritmo do candidato), salvamento automático 
de progresso a cada 5 questões.

**Relatório do candidato (visível para ele):**
- Gráfico radar com as 5 dimensões avaliadas
- Texto interpretativo gerado por IA baseado no perfil
- "Seus pontos fortes para posições em TI:"
- "Áreas de desenvolvimento recomendadas:"
- Sugestão de tipos de empresa e cultura onde se encaixa melhor

**Relatório para empresas (visível mediante plano pago):**
- Versão resumida do perfil comportamental
- Score de fit com a vaga específica (se empresa preencher perfil de cultura)
- Sem revelar respostas individuais, apenas o perfil consolidado
- Badge no perfil público: "✅ Soft Skills Avaliadas pela Thriveo"

### Modelo de Monetização D3

```
GRATUITO para candidatos: todas as 4 avaliações
PAGO para empresas: 
  - Plano básico: ver perfil comportamental resumido (incluso na vaga paga)
  - Plano Pro empresa: ver relatório completo + cruzamento de fit 
    com perfil da equipe (R$ 299/mês adicional)
```

---

## DIMENSÃO 4 — REPUTAÇÃO PROFISSIONAL (D4)

### Objetivo
Validar experiências anteriores e coletar referências verificadas de ex-gestores 
e colegas de trabalho.

### Fluxo de Validação de Referências

**Candidato solicita referência:**
1. No painel, candidato clica em "Solicitar Referência"
2. Preenche: nome do referente, cargo, empresa, e-mail, período de convívio, 
   tipo de relação (gestor direto / colega / cliente / mentorado)
3. Sistema envia e-mail automático para o referente com link único e seguro

**Referente responde o formulário (sem login necessário):**

```
Token de acesso único, expira em 7 dias.
Link: /referencias/responder/{token_unico}

Formulário para o referente:
  Confirma que trabalhou com [nome do candidato]? (Sim/Não)
  Período aproximado: [mês/ano início] a [mês/ano fim]
  Cargo do candidato nessa época: ____________
  Avalie de 1-5 as seguintes competências:
    - Qualidade técnica do trabalho
    - Comunicação e colaboração
    - Cumprimento de prazos e responsabilidade
    - Proatividade e iniciativa
    - Adaptabilidade a mudanças
  Recomendaria este profissional? (Sim / Sim com ressalvas / Não)
  Comentário livre (opcional, max 300 chars)
```

**Processamento:**
- Mínimo 2 referências confirmadas → Selo D4 "Referências Verificadas"
- Score de reputação calculado pela média ponderada das avaliações
- Referências de gestores diretos têm peso 2x maior que colegas
- Candidato não vê respostas individuais, apenas o score geral e quantas 
  referências confirmaram

**Regras anti-fraude:**
- E-mail do referente não pode ser o mesmo do candidato
- Não pode ser e-mail de domínio gratuito genérico se cargo for de empresa 
  (validar domínio corporativo via DNS MX lookup)
- IP do referente registrado e comparado com IP do candidato (alerta se igual)
- Verificar se o domínio do e-mail do referente existe há mais de 1 ano

---

## CERTIFICAÇÃO COMPLETA — SELO THRIVEO ELITE

Candidato que obtém todos os 4 selos recebe o **Selo Thriveo Elite** com 
destaque especial nas listagens:

```
┌────────────────────────────────────────────┐
│  ⭐ THRIVEO ELITE CERTIFIED               │
│  Perfil Verificado · Hard Skills · Soft   │
│  Skills · Referências · Score: 94/100     │
│  Válido até: 12/2026                       │
└────────────────────────────────────────────┘
```

**Benefícios para o candidato Elite:**
- Aparece primeiro nas buscas das empresas (antes de não-certificados)
- Badge dourado no perfil público
- Acesso a vagas exclusivas marcadas como "Elite Only"
- Relatório anual de evolução do perfil
- Carta de certificação PDF oficial da Thriveo (para usar no LinkedIn)

---

## CERTIFICADO PDF GERADO AUTOMATICAMENTE

Para cada trilha aprovada, gerar certificado PDF com:

```
Layout:
- Logo Thriveo centralizado no topo
- "CERTIFICAMOS QUE"
- [Nome completo do candidato] em destaque
- "concluiu com êxito a certificação"
- [Nome da Trilha] — [Nível: Básico/Intermediário/Avançado]
- Score obtido: [XX]%
- Data de emissão e data de validade
- Código de verificação único (UUID)
- QR Code que leva para: thriveo.com.br/verificar/{codigo}
- Assinatura digital: "Thriveo — Plataforma de Talentos em TI"
- Rodapé: "Este certificado pode ser verificado em thriveo.com.br/verificar"

Tecnologia: gerar com FPDF ou TCPDF (PHP)
Salvar em: /storage/certificados/{candidato_id}/{codigo}.pdf
```

**Página pública de verificação** `/verificar/{codigo}`:
- Qualquer pessoa pode verificar a autenticidade do certificado
- Exibe: nome do candidato, trilha, nível, data, status (válido/expirado)
- Útil para recrutadores externos e para o candidato colocar no LinkedIn

---

## BANCO DE DADOS — NOVAS TABELAS

```sql
-- Certificações concedidas
CREATE TABLE certificacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    candidato_id INT NOT NULL,
    tipo ENUM('D1','D2','D3','D4','ELITE') NOT NULL,
    subtipo VARCHAR(100),             -- ex: 'PHP_INTERMEDIARIO', 'DISC', 'D1'
    score DECIMAL(5,2),
    status ENUM('ativo','expirado','revogado') DEFAULT 'ativo',
    codigo_verificacao VARCHAR(36) UNIQUE NOT NULL,  -- UUID
    pdf_path VARCHAR(500),
    concedido_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expira_em TIMESTAMP,
    metadata_json JSON,
    INDEX idx_candidato (candidato_id),
    INDEX idx_codigo (codigo_verificacao)
);

-- Banco de questões dos testes técnicos
CREATE TABLE questoes_tecnicas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    trilha VARCHAR(50) NOT NULL,
    nivel ENUM('basico','intermediario','avancado') NOT NULL,
    topico VARCHAR(100) NOT NULL,
    tipo ENUM('multipla_escolha','completar_codigo','debug','cenario') NOT NULL,
    enunciado TEXT NOT NULL,
    codigo_base TEXT,
    alternativas_json JSON NOT NULL,
    resposta_correta CHAR(1) NOT NULL,
    explicacao TEXT NOT NULL,
    dificuldade TINYINT NOT NULL,     -- 1-5
    gerada_por_ia TINYINT(1) DEFAULT 1,
    ativa TINYINT(1) DEFAULT 1,
    vezes_usada INT DEFAULT 0,
    taxa_acerto DECIMAL(5,2),
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_trilha_nivel (trilha, nivel),
    INDEX idx_ativa (ativa)
);

-- Sessões de teste (tentativas)
CREATE TABLE sessoes_teste (
    id INT AUTO_INCREMENT PRIMARY KEY,
    candidato_id INT NOT NULL,
    trilha VARCHAR(50) NOT NULL,
    nivel VARCHAR(20) NOT NULL,
    status ENUM('iniciada','em_andamento','concluida','cancelada','timeout') DEFAULT 'iniciada',
    questoes_json JSON,
    respostas_json JSON,
    score_obtido DECIMAL(5,2),
    aprovado TINYINT(1),
    tempo_inicio TIMESTAMP,
    tempo_fim TIMESTAMP,
    tempo_limite_min INT DEFAULT 30,
    trocas_de_aba INT DEFAULT 0,      -- anti-cola
    ip_candidato VARCHAR(45),
    INDEX idx_candidato (candidato_id),
    INDEX idx_trilha (trilha, nivel)
);

-- Avaliações de Soft Skills
CREATE TABLE avaliacoes_softskills (
    id INT AUTO_INCREMENT PRIMARY KEY,
    candidato_id INT NOT NULL,
    tipo ENUM('DISC','IE','FIT_CULTURAL','REMOTE_READY') NOT NULL,
    status ENUM('iniciada','concluida') DEFAULT 'iniciada',
    respostas_json JSON,
    resultado_json JSON,
    perfil_predominante VARCHAR(50),
    score_geral DECIMAL(5,2),
    concluido_em TIMESTAMP,
    INDEX idx_candidato_tipo (candidato_id, tipo)
);

-- Referências profissionais
CREATE TABLE referencias_profissionais (
    id INT AUTO_INCREMENT PRIMARY KEY,
    candidato_id INT NOT NULL,
    nome_referente VARCHAR(150) NOT NULL,
    cargo_referente VARCHAR(100),
    empresa_referente VARCHAR(150),
    email_referente VARCHAR(200) NOT NULL,
    tipo_relacao ENUM('gestor_direto','colega','cliente','mentorado') NOT NULL,
    periodo_inicio VARCHAR(7),
    periodo_fim VARCHAR(7),
    token_resposta VARCHAR(64) UNIQUE NOT NULL,
    token_expira_em TIMESTAMP,
    status ENUM('enviado','respondido','expirado','invalido') DEFAULT 'enviado',
    scores_json JSON,
    recomendaria ENUM('sim','sim_ressalvas','nao'),
    comentario TEXT,
    ip_referente VARCHAR(45),
    respondido_em TIMESTAMP,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_candidato (candidato_id),
    INDEX idx_token (token_resposta)
);

-- Log de verificações de certificados
CREATE TABLE log_verificacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo_verificacao VARCHAR(36) NOT NULL,
    ip_consultor VARCHAR(45),
    user_agent TEXT,
    consultado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

---

## ESTRUTURA DE ARQUIVOS

```
/controllers/
  CertificacaoController.php     ← orquestrador principal
  TestesTecnicosController.php   ← sessões de teste, sorteio, correção
  SoftSkillsController.php       ← avaliações comportamentais
  ReferenciasController.php      ← envio e recebimento de referências
  CertificadoController.php      ← geração de PDF + verificação pública

/models/
  Certificacao.php
  QuestaoTecnica.php
  SessaoTeste.php
  AvaliacaoSoftSkill.php
  ReferenciaProfissional.php

/views/
  candidato/
    certificacoes/dashboard.php  ← painel geral com os 4 selos
    certificacoes/trilhas.php    ← lista de trilhas disponíveis
    teste/ambiente.php           ← tela fullscreen do teste
    teste/resultado.php          ← resultado imediato
    softskills/avaliacao.php     ← tela de avaliação comportamental
    softskills/resultado.php     ← radar chart com perfil
    referencias/solicitar.php    ← form para convidar referente
    referencias/status.php       ← lista de referências e status
  publico/
    verificar.php                ← página de verificação de certificado
  email/
    convite_referencia.php       ← template de e-mail para referente
    certificado_concedido.php    ← e-mail de parabéns ao candidato

/assets/js/
  teste-ambiente.js              ← timer, anti-cola, auto-submit
  softskills.js                  ← progressão da avaliação, auto-save
  certificacoes.js               ← gauge chart, animações de selo
  codemirror-config.js           ← editor de código nas questões

/lib/
  GeradorCertificadoPDF.php      ← TCPDF/FPDF para gerar o PDF
  GeradorQuestoesIA.php          ← integração com API de IA
  ValidadorReferencia.php        ← anti-fraude, DNS MX lookup
  ScoreCalculador.php            ← cálculo D1, D2, D3, D4 e Elite

/cron/
  recalcular_scores_d1.php       ← rodar nightly 02h
  expirar_certificacoes.php      ← checar e expirar certificados vencidos
  reenviar_referencias.php       ← reenviar e-mails sem resposta após 3 dias
```

---

## PAINEL DO CANDIDATO — UX DA CERTIFICAÇÃO

**Página principal /candidato/certificacoes:**

```
┌─────────────────────────────────────────────────────────────┐
│  🏅 Sua Certificação Thriveo                               │
│  "Candidatos certificados recebem 3x mais contatos         │
│   de recrutadores"                                          │
├──────────┬──────────┬──────────┬──────────────────────────┤
│   D1     │   D2     │   D3     │   D4                      │
│ ✅ 87pts │ 🔒 Fazer │ ✅ Feito │ ⏳ 1/2 refs             │
│ Verificad│ testes   │ DISC ok  │  aguardando              │
│    o     │          │          │                           │
├──────────┴──────────┴──────────┴──────────────────────────┤
│  Seu score geral: ████████░░ 68/100   [Incompleto]        │
│  Faltam: D2 (testes técnicos) + 1 referência para D4      │
│  [▶ Ver o que falta]  [📤 Compartilhar no LinkedIn]       │
└─────────────────────────────────────────────────────────────┘
```

**Notificações automáticas:**
- E-mail quando referência é respondida
- E-mail 30 dias antes do certificado expirar
- Notificação in-app quando empresa visualiza o perfil certificado
- Push notification quando aprovado em teste técnico

---

## INTEGRAÇÃO COM O MÓDULO DE CANDIDATOS (Prompt 01)

- No card de candidato nas listagens, exibir badges dos selos obtidos 
  (ícones pequenos: D1✅ D2⭐ D3🧠 D4👥)
- Filtro "Apenas certificados" na busca de candidatos
- Filtro por nível de certificação: "Tem Hard Skills em React", 
  "Remote Ready", "Referências Verificadas"
- Ordenação padrão da busca: candidatos Elite > Certificados > Não-certificados
- Endpoint para empresa verificar certificação:  
  `GET /api/candidatos/{id}/certificacoes` → retorna selos ativos + scores

---

## REQUISITOS NÃO FUNCIONAIS

1. **Integridade dos testes:** Questões sorteadas aleatoriamente de um pool 
   mínimo de 60 questões por nível. Nenhuma questão repetida na mesma sessão.

2. **Validade temporal:** D2 expira em 12 meses. D1 e D3 expiram em 24 meses. 
   D4 expira em 18 meses.

3. **LGPD:** Dados das avaliações de soft skills são sensíveis. 
   Armazenar em coluna criptografada (AES-256). Candidato pode solicitar 
   exclusão completa dos resultados.

4. **Acessibilidade:** Testes devem funcionar sem mouse (teclado). 
   Contraste mínimo WCAG AA. Tempo extra configurável para candidatos com 
   necessidades especiais (flag na tabela candidatos).

5. **Performance:** Cache das questões por trilha/nível (Redis ou arquivo JSON). 
   Geração de PDF em job assíncrono (fila simples em tabela MySQL). 
   Página de verificação pública com cache de 1 hora.

---

## DESIGN VISUAL

- Selos com cores distintas por dimensão:  
  D1 = azul `#1a2e4a` · D2 = laranja-tech `#ff6b35` · D3 = roxo `#7c3aed` · D4 = verde `#00c896` · Elite = dourado `#f59e0b`
- Ícones SVG personalizados para cada selo (não usar emojis no código)
- Animação de "unlock" ao conquistar cada selo (CSS keyframe)
- Tela de teste em fundo escuro `#0f172a` para reduzir fadiga visual
- Editor de código com tema "Monokai" ou "One Dark"
- Certificado PDF com design limpo, fundo branco, marca d'água diagonal 
  sutil "THRIVEO CERTIFIED"

---

## ENTREGÁVEIS ESPERADOS

Gere o código completo e funcional para:
1. `CertificacaoController.php` — lógica completa dos 4 selos
2. `TestesTecnicosController.php` — sorteio, sessão, correção, anti-cola
3. `SoftSkillsController.php` — avaliação DISC + cálculo de perfil
4. `ReferenciasController.php` — envio, token, recebimento, anti-fraude
5. `GeradorCertificadoPDF.php` — certificado PDF completo com QR Code
6. `views/candidato/certificacoes/dashboard.php` — painel visual completo
7. `views/candidato/teste/ambiente.php` — tela fullscreen com timer e editor
8. `views/publico/verificar.php` — verificação pública de autenticidade
9. `assets/js/teste-ambiente.js` — timer, detecção de troca de aba, auto-submit
10. `assets/js/certificacoes.js` — gauge chart D1, animações de conquista
11. SQL completo das 6 novas tabelas com índices e comentários
12. `ScoreCalculador.php` — classe com toda lógica de pontuação D1 a Elite

Gere arquivo por arquivo, completo, sem omitir código.  
Use comentários explicativos em português nas partes críticas.  
Para o gerador de PDF, use TCPDF (disponível via Composer).
