# Proftest Board - Sistema de Gestão Inteligente

Este sistema foi desenvolvido para hospedagem compartilhada (Hostgator) utilizando PHP, MySQL, HTML, CSS e Javascript.

## Funcionalidades
- **Superadmin**: Gestão de empresas e chave API OpenAI.
- **Empresas**: Gestão de usuários, cultura organizacional e pesquisas.
- **Usuários**: AI Onboarding, Offboarding, Knowledge Transfer e Pesquisas.
- **IA**: Integração com OpenAI (ChatGPT) para assistentes virtuais.

## Instalação no Hostgator

1. **Upload de Arquivos**: Envie todos os arquivos para a pasta `public_html` ou subpasta desejada.
2. **Banco de Dados**:
   - Crie um banco de dados MySQL e um usuário no CPanel do Hostgator.
   - Use as credenciais fornecidas no pedido:
     - Banco: `efsantos_board`
     - Usuário: `efsantos_board`
     - Senha: `Kyew1802`
3. **Configuração**:
   - O arquivo `config/config.php` já está configurado com essas credenciais padrão. Se mudar, edite este arquivo.
4. **Instalação Automática**:
   - Acesse `http://seudominio.com/pages/setup.php` no navegador.
   - Isso criará as tabelas e o usuário Superadmin.
   - **Importante**: Apague o arquivo `pages/setup.php` após o uso por segurança.

## Acesso Superadmin
- **Login**: `ezequiel.santos@gmail.com`
- **Senha**: `Kyew1802`

## Primeiros Passos
1. Faça login como Superadmin.
2. Vá em **Configurações** e insira sua **OpenAI API Key**.
3. Crie uma nova Empresa em **Empresas**.
4. Faça logout e entre com a conta de admin da empresa criada.
5. Defina a **Cultura & Identidade** da empresa para calibrar a IA.
6. Cadastre usuários.

## Tecnologias
- PHP 7.4+ (Sem frameworks pesados, ideal para shared hosting)
- MySQL
- Vanilla CSS/JS
