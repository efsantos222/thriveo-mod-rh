# V2MOM Intelligence

Sistema de planejamento estratégico completo baseado no método V2MOM (Vision, Values, Methods, Obstacles, Measures) com integração de Inteligência Artificial para potencializar a visão executiva.

## 🚀 Funcionalidades

### 🏢 Painel Administrativo (SaaS)
- Gestão completa de Empresas e Usuários.
- Configuração centralizada de chaves de API para IA.
- Monitoramento de logs de acesso e uso.

### 🎯 V2MOM Builder (Aplicação)
Módulo estruturado para construção do planejamento estratégico:
1.  **Vision (Visão)**: Definição do objetivo macro com análise de IA.
2.  **Values (Valores)**: Estabelecimento de princípios corporativos.
3.  **Methods (Métodos)**: Planos de ação com responsáveis e prazos.
4.  **Obstacles (Obstáculos)**: Gestão de riscos, identificação de impacto (Alto/Médio/Baixo) e planos de mitigação.
5.  **Measures (Métricas)**: Definição de KPIs, metas e unidades de medida.
6.  **Assistente de IA**: Sugestões inteligentes contextuais para todas as etapas acima (ex: "Sugerir obstáculos com base na visão e métodos").

### 📊 Execução & Acompanhamento
- **Rastreamento em Tempo Real**: Atualização de status de métodos (Não Iniciado, Em Andamento, Concluído).
- **Monitoramento de KPIs**: Atualização de valores atuais de métricas com barras de progresso visuais.

### 📑 Relatórios de Performance
- **Dashboard Unificado**: Visão consolidada de todo o V2MOM.
- **Análise Executiva (IA)**: O sistema atua como um "Chief of Staff", analisando o progresso atual e gerando insights estratégicos automáticos.
- **Exportação**: Layout otimizado para impressão e geração de PDF.

## 🛠 Tecnologias

- **Backend**: PHP 8.x (Estruturado, PDO)
- **Frontend**: HTML5, CSS3 (Vanilla + Glassmorphism UI), JavaScript
- **Banco de Dados**: MySQL
- **Integração IA**: API OpenAI (GPT-4o / GPT-3.5-turbo)

## 📦 Instalação

### 1. Configuração do Banco de Dados

Certifique-se de que o servidor MySQL está rodando.

1. Crie o banco de dados e o usuário (se não tiver acesso root para o script automático):
   ```sql
   CREATE DATABASE efsantos_v2m;
   CREATE USER 'efsantos_v2m'@'localhost' IDENTIFIED BY 'Kyew1802';
   GRANT ALL PRIVILEGES ON efsantos_v2m.* TO 'efsantos_v2m'@'localhost';
   FLUSH PRIVILEGES;
   ```

2. Execute o script de instalação (ou acesse via navegador):
   - Terminal: `php setup_database.php`
   - Isso criará todas as tabelas e o administrador padrão.

### 2. Configuração do Servidor Web

Este projeto foi desenhado para rodar em servidores Apache ou Nginx com PHP.

- Para rodar localmente (ambiente de desenvolvimento):
  ```bash
  php -S localhost:8080
  ```
- Acesse: `http://localhost:8080`

### 3. Acesso Inicial

**Administrador do Sistema:**
- **URL**: `/admin/login.php`
- **Email**: `ezequiel.santos@gmail.com`
- **Senha**: `Kyew1802`

**Usuário (Empresa):**
- Deve ser criado através do Painel Administrativo.
- Acesse `/app/login.php` com as credenciais geradas.

## 🤖 Configuração da IA

Para que as funcionalidades de IA funcionem:
1. Faça login como **Administrador**.
2. Vá em **Configuração IA** (`admin/config_ia.php`).
3. Insira a `API Key` da OpenAI para a empresa desejada.

## 📂 Estrutura de Pastas

```
/
├── admin/          # Painel Administrativo (Gestão SaaS)
├── app/            # Aplicação do Cliente
│   ├── dashboard.php      # Visão Geral
│   ├── v2mom_builder.php  # Construtor do V2MOM (5 Etapas)
│   ├── execution.php      # Módulo de Execução e Status
│   └── reports.php        # Relatórios e Análise IA
├── assets/         # CSS, JS e Imagens
├── includes/       # Classes e Utilitários (AI Helper)
├── config.php      # Conexão com Banco de Dados
├── index.php       # Landing Page Pública
└── setup_database.php # Script de Instalação e Migração
```

## 🔐 Notas de Segurança

- O arquivo `config.php` contém credenciais de banco. Em produção, mova-o para fora da raiz pública ou proteja via `.htaccess`.
- Senhas são armazenadas utilizando `password_hash()` (Bcrypt).
- A proteção contra injeção de SQL é garantida pelo uso de Prepared Statements (PDO).
