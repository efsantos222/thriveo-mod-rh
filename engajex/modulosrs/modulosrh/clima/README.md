# Sistema de Pesquisa de Clima Semanal

Sistema desenvolvido para gerenciar pesquisas de clima semanais, permitindo o cadastro de respondentes, administração de perguntas e geração de relatórios.

## Requisitos do Sistema

- PHP 7.4+
- MySQL 5.7+
- Servidor Apache
- mod_rewrite habilitado

## Estrutura do Projeto

```
/
├── admin/           # Módulo administrativo
├── assets/         # Arquivos CSS, JS e imagens
├── config/         # Configurações do sistema
├── includes/       # Classes e funções compartilhadas
└── public/         # Interface pública para respondentes
```

## Instalação

1. Clone o repositório
2. Configure o arquivo `config/database.php` com suas credenciais
3. Importe o arquivo `database.sql` no seu MySQL
4. Configure o Apache para apontar para a pasta `public`

## Funcionalidades

- Gestão de administradores
- Cadastro e importação de respondentes
- Criação e gestão de pesquisas
- Geração de relatórios em CSV
- Sistema de códigos únicos para respondentes

## Segurança

- Proteção contra SQL Injection
- Proteção contra XSS
- Senhas criptografadas
- Validação de sessões
