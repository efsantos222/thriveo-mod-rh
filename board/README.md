# Proftest Board - Sistema de Gestão Inteligente

Este sistema foi desenvolvido para hospedagem compartilhada (Hostgator) utilizando PHP, MySQL, HTML, CSS e Javascript.

## Funcionalidades Principais

### 1. Papel: Superadmin (Dono da Plataforma)
- **Gestão de Empresas**: Cria, edita e exclui empresas que utilizarão o sistema.
- **Configuração Global**: Gerencia a API Key do OpenAI (ChatGPT) usada por todas as empresas.

### 2. Papel: Administrador da Empresa (Cliente)
- **Gestão de Usuários**: Cadastra e gerencia os colaboradores da sua empresa.
- **Cultura & Identidade**: Define Missão, Visão e Valores para alinhar a IA.
- **Treinamento da IA**:
  - Ensina o chat com regras específicas (ex: "Política de Home Office", "Benefícios").
  - O conteúdo inserido aqui é usado automaticamente pelos assistentes virtuais para responder dúvidas.
- **Pesquisas**: Cria formulários para os colaboradores responderem.

### 3. Papel: Usuário (Colaborador)
- **AI Onboarding Buddy**: Assistente virtual treinado com a cultura da empresa para tirar dúvidas nos primeiros 90 dias.
- **Offboarding Inteligente**: Entrevista de desligamento conduzida por IA para capturar feedback honesto.
- **Knowledge Transfer**: IA entrevista quem está saindo para salvar conhecimentos críticos.
- **Pesquisas**: Responde aos formulários enviados pela empresa.

---

## Estrutura de Arquivos para Upload
Envie estas pastas e arquivos para o diretório `/public_html/board` (ou raiz) do seu servidor:

- 📁 **`api/`**: Lógica de comunicação com o ChatGPT (`chat.php`).
- 📁 **`assets/`**: Estilos (`css/style.css`) e recursos visuais.
- 📁 **`config/`**: Conexão com banco de dados.
- 📁 **`includes/`**: Menu lateral e trechos de código reutilizáveis.
- 📁 **`pages/`**: Todas as telas (Dashboard, Setup, Cadastros).
- 📄 **`database.sql`**: Esquema do banco de dados.
- 📄 **`index.php`**: Página inicial (Landing Page).
- 📄 **`login.php`**: Tela de login.
- 📄 **`logout.php`**: Script de saída.

---

## Guia de Instalação (Hostgator)

1. **Banco de Dados**:
   - Crie um banco de dados MySQL e um usuário no CPanel do Hostgator.
   - Credenciais configuradas no código:
     - **Banco**: `efsantos_board`
     - **Usuário**: `efsantos_board`
     - **Senha**: `Kyew1802`
   - *Se mudar essas credenciais no CPanel, lembre-se de atualizar o arquivo `config/config.php`.*

2. **Upload**:
   - Suba todos os arquivos citados acima via FTP ou Gerenciador de Arquivos.

3. **Instalação Automática**:
   - Acesse no navegador: `https://seu-dominio.com/board/pages/setup.php`
   - Se ver a mensagem "Setup concluído!", as tabelas e o usuário Superadmin foram criados.
   - **Segurança**: Renomeie ou apague o arquivo `setup.php` após o sucesso.

4. **Acesso Inicial**:
   - **Login Superadmin**: `ezequiel.santos@gmail.com`
   - **Senha**: `Kyew1802`

---

## Como Configurar (Passo a Passo)

### 1. Configuração Inicial (Superadmin)
1. Faça login com o e-mail do Superadmin.
2. No menu lateral, vá em **Configurações (API)**.
3. Cole sua chave da OpenAI (`sk-...`) e salve.
4. Vá em **Empresas** e crie a primeira empresa.
   - Defina um **Responsável** (Admin da Empresa) no momento da criação.

### 2. Treinamento da IA (Admin da Empresa)
1. Faça logout e entre com o e-mail do Responsável que você acabou de criar.
2. Vá em **Cultura & Identidade** e preencha Missão, Visão e Valores.
3. Vá em **Treinamento IA** (Novo!).
4. Adicione itens para ensinar a IA.
   - Ex: Título: "Plano de Saúde", Conteúdo: "O plano é Unimed, sem coparticipação...".
   - A IA lerá isso automaticamente e saberá responder quando um funcionário perguntar "Como funciona o plano de saúde?".

## Tecnologias
- **Backend**: PHP 7.4+ (Nativo, sem frameworks pesados).
- **Frontend**: HTML5, CSS3, Javascript.
- **Banco de Dados**: MySQL.
- **IA**: OpenAI API (cURL).
