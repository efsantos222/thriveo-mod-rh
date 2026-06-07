# Thriveo ZBB - Sistema de Orçamento Base Zero

Sistema Web para gestão de Orçamento Base Zero (ZBB), inteligência competitiva e análise de despesas com suporte de IA.

## 🚀 Funcionalidades Principais

*   **Dashboard Executivo**: KPIs de aderência, economia, variação e gráficos de evolução.
*   **Gestão de Orçamento (ZBB)**:
    *   Visualização e edição de pacotes de despesas.
    *   Classificação por Centro de Custo, Categoria e Natureza (Fixa, Variável, Discricionária).
    *   Workflow de aprovação (Rascunho -> Pendente -> Aprovado).
*   **Importação Inteligente**: Upload de planilhas (.csv) via drag-and-drop com detecção automática de delimitadores e mapeamento de colunas.
*   **Inteligência Artificial (OpenAI)**:
    *   **Análise de Mercado**: Geração de relatórios competitivos e estratégicos por setor/país.
    *   **Defesa de Orçamento**: Sugestão de justificativas técnicas para despesas baseadas em dados históricos.
*   **Relatórios**: Geração de PDFs e exportação para Excel (CSV) de relatórios executivos, análise vertical e performance.
*   **Gestão de Usuários**: Níveis de acesso e controle por empresa.

## 🛠️ Tecnologias Utilizadas

*   **Backend**: PHP 7.4+ (Sem frameworks pesados, foco em performance e simplicidade).
*   **Frontend**: HTML5, CSS3 (Vanilla), JavaScript (ES6+).
*   **Banco de Dados**: MySQL / MariaDB.
*   **Integrações**: OpenAI API (GPT-4o).
*   **Bibliotecas**: Chart.js (Gráficos).

## 📂 Estrutura do Projeto

```
/thriveo-zbb
├── api/                  # Endpoints JSON para AJAX (Importação, Análise IA, Relatórios)
├── assets/
│   └── css/              # Estilos (style.css)
├── config/               # Conexão com banco de dados (db.php)
├── database/             # Scripts SQL (schema.sql)
├── includes/             # Componentes reutilizáveis (sidebar.php)
├── budget.php            # Tela principal de Orçamento
├── dashboard.php         # Painel de Controle e KPIs
├── intelligence.php      # Módulo de Inteligência de Mercado
├── reports.php           # Central de Relatórios
├── login.php             # Autenticação
├── install.php           # Script de instação inicial
└── index.php             # Redirecionamento
```

## ⚙️ Instalação e Configuração

1.  **Configurar Banco de Dados**:
    *   Crie um banco de dados MySQL (ex: `thriveo_zbb`).
    *   Edite o arquivo `config/db.php` com suas credenciais:
        ```php
        $host = 'localhost';
        $dbname = 'thriveo_zbb';
        $username = 'seu_usuario';
        $password = 'sua_senha';
        ```

2.  **Instalar Tabelas e Dados Iniciais**:
    *   Acesse `install.php` pelo navegador (ex: `http://localhost/thriveo-zbb/install.php`).
    *   Este script criará todas as tabelas e o usuário administrador padrão.

3.  **Acesso Inicial**:
    *   **Email**: `ezequiel.santos@gmail.com`
    *   **Senha**: `Kyew1802`

## 🧩 Uso do Sistema

### Importação de Dados
Acesse a tela **Orçamento ZBB** e utilize o botão "Importar Excel".
O arquivo CSV deve conter as colunas na seguinte ordem (separadas por vírgula ou ponto e vírgula):
1.  Centro de Custo
2.  Categoria
3.  Natureza (Fixa, Variável)
4.  Valor Referência (Ano Anterior)
5.  Valor Atual (Solicitado)
6.  Justificativa

### IA e Configurações
A chave da API da OpenAI é configurada automaticamente no banco de dados (`system_settings`) durante a instalação. Para alterar, edite a tabela ou crie uma tela de configurações administrativas.

## 📄 Licença
Desenvolvido para Thriveo. Todos os direitos reservados.
