# Sinergy Cult - Sistema de Gestão de Cultura Organizacional com IA

Sistema desenvolvido para Hostgator (PHP + MySQL).

## Instalação

### 1. Banco de Dados
- Crie um banco de dados no painel da Hostgator com o nome: `efsantos_cult`
- Crie um usuário `efsantos_cult` com a senha `Kyew1802`.
- Adicione o usuário ao banco com todas as permissões.

Alternativamente, se desejar alterar as credenciais, edite o arquivo `config/db.php`.

### 2. Instalação das Tabelas
- Suba todos os arquivos para a pasta pública (ex: `public_html/testprof-cult` ou raiz).
- Acesse pelo navegador: `http://seudominio.com.br/install.php`
- Se aparecer "Tabelas criadas com sucesso", a instalação foi concluída.
- **Importante:** Apague ou renomeie o arquivo `install.php` após o uso por segurança.

### 3. Acesso Inicial
- **Login Superadmin:** `ezequiel.santos@gmail.com`
- **Senha:** `Kyew1802`

### 4. Configuração Inicial
1. Faça login como Superadmin.
2. Vá em "Configurações" e insira sua Chave API do OpenAI (sk-...).
3. Vá em "Empresas" e cadastre a primeira empresa e seu Gestor.

### 5. Fluxo de Uso
1. **Gestor (Manager):**
   - Recebe acesso do Superadmin.
   - Cadastra a "Identidade Organizacional" (Missão, Visão, etc).
   - Cadastra Usuários (Colaboradores).
   - Cria Pesquisas e adiciona Perguntas.
   - Aguarda respostas.
   - Gera o Relatório de Cultura (IA).

2. **Usuário (Colaborador):**
   - Faz login com credenciais criadas pelo Gestor.
   - Responde as pesquisas disponíveis.

Suporte: ezequiel.santos@gmail.com
