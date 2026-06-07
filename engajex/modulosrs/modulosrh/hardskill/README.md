# TestProf Hardskill

Sistema de Recrutamento Tech & Testes Técnicos com Inteligência Artificial.

O **TestProf Hardskill** é uma plataforma desenvolvida para facilitar a avaliação técnica de candidatos. O sistema permite que recrutadores criem testes personalizados (manualmente ou gerados por IA), atribuam a candidatos e corrijam as respostas automaticamente utilizando o modelo GPT-4o da OpenAI.

## 🚀 Funcionalidades Principais

### 👤 Administrador
- **Dashboard Geral**: Visão geral de recrutadores, candidatos e testes.
- **Gerenciar Recrutadores**: Cadastro e controle de acesso para recrutadores.
- **Configurações do Sistema**: Configuração segura da API Key da OpenAI.

### 💼 Recrutador
- **Geração de Testes com IA**: Criação automática de testes técnicos complexos baseados na descrição da vaga e nível de senioridade (Júnior, Pleno, Sênior).
- **Gestão de Candidatos**: Cadastro de candidatos e acompanhamento de status.
- **Atribuição de Testes**: Envio de links de prova para candidatos específicos.
- **Correção Automática (AI)**: Análise detalhada das respostas do candidato (incluindo código e dissertativas) com sugestão de nota e feedback geraado por IA (GPT-4o).
- **Correção Manual**: Interface para revisão humana e ajuste de notas.

### 👨‍💻 Candidato
- **Área do Candidato**: Acesso aos testes atribuídos.
- **Realização de Prova**: Interface focada e responsiva para responder questões de múltipla escolha, texto e análise de cenário.

## 🛠️ Tecnologias Utilizadas

- **Backend**: PHP 7.4+ (Nativo, sem frameworks pesados).
- **Banco de Dados**: MySQL.
- **Frontend**: HTML5, CSS3 Moderno (Variáveis CSS, Flexbox, Grid), JavaScript (Vanilla).
- **AI Integration**: OpenAI API (GPT-4o).
- **Design**: Responsivo, com foco em acessibilidade e tipografia legível (Inter Font).

## 📦 Instalação e Deploy

Este sistema foi projetado para rodar em hospedagens compartilhadas (ex: Hostgator) e suporta instalação em subdiretórios.

### 1. Configuração do Banco de Dados
1. Crie um banco de dados MySQL e um usuário.
2. Importe o arquivo `database.sql` incluído na raiz do projeto para criar as tabelas e o usuário admin inicial.

### 2. Configuração da Aplicação
Edite o arquivo `config/database.php` com as credenciais do seu banco e a URL da aplicação:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'seu_banco');
define('DB_USER', 'seu_usuario');
define('DB_PASS', 'sua_senha');

// Importante: Defina a URL exata onde o sistema está instalado
define('APP_URL', 'https://seusite.com.br/hardskill');
```

### 3. Configuração de Servidor (.htaccess)
O sistema utiliza um roteamento personalizado compatível com subdiretórios. O arquivo `.htaccess` na raiz já está configurado para redirecionar requisições para o `index.php`.

Caso instale em uma pasta diferente de `/hardskill`, ajuste a linha `RewriteBase` no `.htaccess`:

```apache
RewriteBase /nome-da-sua-pasta/
```

## 🔑 Acesso Inicial

**Administrador Padrão:**
- **Email:** `admin@testprof.com.br`
- **Senha:** `admin123` (Recomendado alterar após o primeiro login via banco de dados ou criar funcionalidade de troca).

## 🤖 Configuração da IA

Para habilitar as funcionalidades de IA (Geração de Testes e Correção Automática):
1. Logue com o usuário Administrador.
2. Vá em **Configurações**.
3. Insira sua chave de API da OpenAI (`sk-...`).

## 📁 Estrutura de Pastas

- `config/`: Configurações de banco de dados.
- `src/`: Lógica do sistema (Controllers e Helpers).
- `templates/`: Arquivos de visualização (HTML/PHP).
- `public/`: Assets estáticos (CSS, Imagens).
- `index.php`: Roteador principal da aplicação.

---
Desenvolvido para **TestProf**.
