# Sistema de Recrutamento Inteligente (ProfTest)

Este sistema foi desenvolvido para gerenciamento de processos seletivos e geração de roteiros de entrevista com Inteligência Artificial.

## Tecnologias
- PHP 7.4+
- MySQL
- HTML5 / CSS3 (Design Responsivo, Glassmorphism)
- JavaScript

## Funcionalidades
1.  **Superadmin**: Gestão de empresas e responsáveis. Configuração de API Key.
2.  **Responsáveis**: Gestão de vagas e candidatos. Geração de roteiros de entrevista via OpenAI.
3.  **Segurança**: Proteção de rotas, senhas hash, LGPD compliance notice.

## Instalação (Hostgator)

1.  **Upload**: Envie todos os arquivos para a pasta pública do seu servidor (ex: `public_html/entrv`).
2.  **Banco de Dados**:
    - Crie um banco de dados MySQL e um usuário no cPanel da Hostgator.
    - Edite o arquivo `config.php` com as credenciais do banco. (Já configurado com os dados fornecidos).
3.  **Instalação Automática**:
    - Acesse `https://testprof.com.br/entrv/setup.php` no navegador.
    - Isso criará as tabelas e o usuário Superadmin.
    - **IMPORTANTE**: Após ver a mensagem de sucesso, **apague o arquivo setup.php**.
4.  **Acesso**:
    - Página Inicial: `index.php`
    - Login: `login.php`
    - **Superadmin**: `ezequiel.santos@gmail.com` / `Kyew1802`

## Estrutura de Pastas
- `admin/`: Painel do Superadmin.
- `responsible/`: Painel dos Responsáveis (Empresas).
- `assets/`: Arquivos CSS e recursos visuais.
- `includes/`: (Implícito nos headers)
- `config.php`: Conexão com Banco de Dados.

## API OpenAI
A chave da API fornecida já está configurada no script de instalação. Se precisar trocar, acesse o painel Superadmin > Configurações.
