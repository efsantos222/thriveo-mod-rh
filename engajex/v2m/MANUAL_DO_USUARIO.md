# Manual do Usuário - V2MOM Intelligence

Bem-vindo ao **V2MOM Intelligence**, o sistema completo de planejamento estratégico baseado na metodologia V2MOM (Vision, Values, Methods, Obstacles, Measures) e potencializado por Inteligência Artificial.

Este manual guiará você através das funcionalidades do sistema, divididas entre o **Painel Administrativo** (para gestores da plataforma) e o **Aplicativo da Empresa** (para criação e gestão do planejamento).

---

## 1. Acesso ao Sistema

O sistema possui dois portais de acesso distintos:

*   **Portal do Administrador (SaaS):** Destinado à gestão da plataforma, empresas e usuários.
    *   **URL:** `/admin/login.php`
*   **Portal da Empresa (App):** Destinado à criação e acompanhamento do V2MOM.
    *   **URL:** `/app/login.php`

---

## 2. Painel Administrativo

O painel administrativo é o centro de controle do sistema SaaS. Apenas super-administradores têm acesso a esta área.

### Principais Funcionalidades:

1.  **Dashboard Geral:**
    *   Visão macro de quantas empresas e usuários estão cadastrados.
    *   Monitoramento de logs de acesso recentes para segurança.

2.  **Gestão de Empresas (`/admin/empresas.php`):**
    *   **Cadastrar Nova Empresa:** Insira o nome, CNPJ (opcional) e, fundamentalmente, a **Chave de API da OpenAI (API Key)**.
    *   *Nota:* A chave de API é necessária para que as funcionalidades de IA funcionem dentro do ambiente daquela empresa.
    *   **Editar/Excluir:** Gerencie os dados das empresas existentes.

3.  **Gestão de Responsáveis (`/admin/responsaveis.php`):**
    *   Crie usuários de acesso para as empresas cadastradas.
    *   Vincule cada "Responsável" a uma "Empresa" específica.
    *   O login e senha criados aqui serão usados pelo cliente no **Portal da Empresa**.

4.  **Configuração de IA (`/admin/config_ia.php`):**
    *   Área dedicada para configurar e testar as integrações com a OpenAI.
    *   Assegure-se de que a chave da API esteja ativa e com créditos para evitar erros no uso do assistente.

---

## 3. Aplicativo da Empresa (V2MOM)

É aqui que o planejamento estratégico acontece. O usuário (Responsável) fará login com as credenciais fornecidas pelo administrador.

### Módulo 1: V2MOM Builder (Construtor)

O sistema guia o usuário por 5 etapas estruturadas para construir o plano. Em cada etapa, o **Assistente de IA** pode oferecer sugestões baseadas no contexto inserido anteriormente.

1.  **Vision (Visão):**
    *   Defina o objetivo macro da empresa. Onde vocês querem chegar?
    *   *Exemplo:* "Ser a plataforma líder em educação financeira no Brasil até 2026."

2.  **Values (Valores):**
    *   Quais princípios guiarão a jornada?
    *   Liste valores como "Transparência", "Inovação", "Foco no Cliente".

3.  **Methods (Métodos):**
    *   Como vocês atingirão a visão?
    *   Defina planos de ação claros.
    *   Atribua **Responsáveis** e **Prazos** para cada método.

4.  **Obstacles (Obstáculos):**
    *   O que pode atrapalhar o caminho?
    *   Identifique riscos e classifique o impacto (Alto/Médio/Baixo).
    *   Crie planos de mitigação para cada obstáculo.

5.  **Measures (Métricas):**
    *   Como medir o sucesso?
    *   Defina KPIs (Indicadores-Chave de Desempenho).
    *   Estabeleça a **Meta** e a **Unidade de Medida** (%, R$, #).

### Módulo 2: Assistente de IA (`/app/advisor.php`)

*   Acesse o "Advisor" no menu lateral para conversar um Chatbot especialista em estratégia.
*   Peça sugestões de melhoria, ideias de métricas ou análise de riscos.
*   O Advisor utiliza o contexto da sua empresa para dar respostas personalizadas.

### Módulo 3: Execução & Acompanhamento (`/app/execution.php`)

Após planejar, é hora de executar.

*   **Atualizar Status:** Marque os Métodos como "Não Iniciado", "Em Andamento" ou "Concluído".
*   **Monitorar Métricas:** Atualize o "Valor Atual" dos seus KPIs. O sistema mostrará barras de progresso visuais.
*   Use esta tela em reuniões semanais de acompanhamento.

### Módulo 4: Relatórios e Análise (`dashboard.php` e `reports.php`)

*   **Dashboard:** Visão rápida do progresso geral do V2MOM (% de conclusão).
*   **Relatórios:**
    *   Gera uma visualização consolidada de todo o plano.
    *   **Análise de IA:** Clique para solicitar que a IA analise o progresso atual e aponte desvios ou sugestões de correção de rota.
    *   Opção de imprimir ou salvar como PDF para apresentações.

---

## 4. Dicas de Uso

*   **Seja Específico:** Ao preencher a Visão e os Métodos, quanto mais detalhes você der, melhores serão as sugestões da IA.
*   **Revisão Constante:** O V2MOM é um documento vivo. Volte ao menu "Execução" regularmente para manter os dados atualizados.
*   **Segurança:** Nunca compartilhe sua senha de administrador ou as chaves de API com pessoas não autorizadas.
