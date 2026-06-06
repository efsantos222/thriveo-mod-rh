# Relatório de Memória de Desenvolvimento: Thriveo

Este documento serve como um ponto central para recuperar o histórico, a arquitetura e o estado atual do ecossistema **Thriveo**, desenvolvido ao longo dos últimos meses.

## 🏗️ Visão Geral da Arquitetura

O projeto Thriveo é composto por três pilares principais integrados:

1.  **Thriveo AI Jobs (WordPress Plugin)**:
    *   **Core**: Um plugin personalizado para WordPress que utiliza Elementor para criar uma interface moderna de busca de vagas e talentos.
    *   **Autenticação**: Integração OAuth com GitHub e LinkedIn via `admin-ajax.php`.
    *   **Dashboard**: Uma interface de usuário premium (`/?thriveo_dashboard=1`) para completar perfis profissionais, integrada ao banco de dados WordPress.
    *   **Banco de Dados**: Tabelas personalizadas `wp_thriveo_ai_candidates`, `wp_thriveo_ai_companies` e `wp_thriveo_ai_jobs`.

2.  **Thriveo WhatsApp Chatbot (PHP)**:
    *   **Motor**: Sistema baseado em PHP que utiliza a API da OpenAI para respostas inteligentes.
    *   **Otimização**: Módulo de `RespostasPersonalizadas.php` para tratar perguntas frequentes (Preços, Suporte, OutSystems) sem custo de IA.
    *   **Persistência**: Armazenamento de logs e conversas em diretórios locais para contexto contínuo.

3.  **GPTW Dashboard 2026**:
    *   **Tecnologia**: Aplicação standalone em HTML5/Vanilla JS/CSS3.
    *   **Funcionalidade**: Processamento de planilhas Excel (`GPTW2026.xlsx`) no navegador usando a biblioteca SheetJS para análise de resultados de clima organizacional.

---

## 📅 Linha do Tempo e Marcos Recentes

### Maio 2026
*   **Chatbot WhatsApp**: Finalização da lógica de negócio e integração de respostas fixas para reduzir custos. Configuração de webhooks com a Meta.

### Abril 2026
*   **Dashboard GPTW**: Criação de uma ferramenta de visualização de dados com filtros hierárquicos (Alocação, Gênero, Idade) e design em Dark Mode/Glassmorphism.

### Março 2026
*   **Refinamento de UI (8-9/Mar)**: Otimização do dashboard do candidato. Correção de bugs de sintaxe CSS para compatibilidade com Firefox/Edge e implementação do salvamento de perfil via AJAX.
*   **Lançamento do Plugin (7/Mar)**: Criação da estrutura base do plugin `thriveo-ai-jobs`, migrações de banco de dados e widget de Hero para Elementor.
*   **V2MOM (1/Mar)**: Conversão do planejamento estratégico V2MOM de React para HTML estático para portabilidade.

---

## 📁 Arquivos de "Memória" no Workspace

Se precisar de detalhes técnicos específicos, consulte estes arquivos na raiz do projeto (alguns podem levar tempo para sincronizar via OneDrive):

*   **[progresso_thriveo_marzo_10.md](file:///Users/ezequielfsantos/Library/CloudStorage/OneDrive-Pessoal/Área de Trabalho/Ezequiel/thriveo/sistemas/thriveo_brihome/progresso_thriveo_marzo_10.md)**: O diário de bordo mais detalhado até março.
*   **[arquitetura_thriveo_mermaid.md](file:///Users/ezequielfsantos/Library/CloudStorage/OneDrive-Pessoal/Área de Trabalho/Ezequiel/thriveo/sistemas/thriveo_brihome/arquitetura_thriveo_mermaid.md)**: Diagrama de fluxo de dados e estrutura de componentes.
*   **[README.md](file:///Users/ezequielfsantos/Library/CloudStorage/OneDrive-Pessoal/Área de Trabalho/Ezequiel/thriveo/sistemas/thriveo_brihome/README.md)**: Visão geral de alto nível.
*   **[walkthrough.md](file:///Users/ezequielfsantos/Library/CloudStorage/OneDrive-Pessoal/Área de Trabalho/Ezequiel/thriveo/sistemas/thriveo_brihome/walkthrough.md)**: Resumo das últimas implementações de backend.

---

## 🛠️ Comandos Úteis e Links
*   **Dashboard Candidato**: `https://seusite.com.br/?thriveo_dashboard=1`
*   **Webhook Bot**: `/webhook/webhook.php`
*   **Admin WordPress**: `/wp-admin/admin.php?page=thriveo-candidates`

> [!TIP]
> Para recuperar o contexto de qualquer funcionalidade específica, você pode me pedir para analisar um dos arquivos `.zip` de backup na pasta raiz (ex: `thriveo-ai-jobs-hr-modules-v1.8.2.zip`), que contêm as versões estáveis de cada etapa.
