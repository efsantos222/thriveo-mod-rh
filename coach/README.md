# Sinergy Coaching System

Sistema integrado de ferramentas de coaching para gerenciamento de sessões, avaliações, feedback e gestão corporativa.

O sistema está online em: [https://testprof.com.br/coach](https://testprof.com.br/coach)

## Funcionalidades Principais

- **Gestão de Sessões de Coaching**: Agendamento e histórico.
- **Ferramentas de Diagnóstico**: DISC e MBTI integrados.
- **Gestão de Metas SMART**: Acompanhamento de objetivos.
- **Sistema de Feedback 360°**: Avaliações contínuas.
- **Gestão Corporativa (Novo)**:
    - Cadastro de Empresas.
    - Perfil **Master Coach**: Gerencia seus próprios mentorados (Coachees) dentro de sua empresa.
    - Controle de Acesso hierárquico (Administrador > Master Coach > Coach/Coachee).
- **Landing Page & Login Moderno**: Interface responsiva e adequada à LGPD.

## Estrutura de Usuários

1. **Administrador**: Acesso total, gerenciamento de empresas e todos os usuários.
2. **Master Coach**: Gerencia apenas ou mentorados (Coachees) da sua própria empresa.
3. **Coach/Coachee**: Acesso às ferramentas de coaching e sessões.

## Credenciais de Acesso (Administrador Padrão)

- **Email**: `ezequiel.santos@gmail.com`
- **Senha**: `Kyew1802`

## Requisitos

- PHP 7.4+
- MySQL 5.7+
- Servidor Web (Apache/Nginx)

## Instalação e Atualização

1. **Clone o repositório**:
   ```bash
   git clone https://github.com/efsantos222/testprof-coach.git
   ```

2. **Banco de Dados**:
   - Para instalação limpa: Importe `database/schema.sql`.
   - Para atualização (versão anterior): Execute o script `update_schema.php` (via navegador ou CLI) para criar as tabelas de empresas e atualizar colunas.

3. **Configuração**:
   - Ajuste as credenciais do banco em `config/database.php`.

## Histórico de Mudanças

- **Dez/2025**:
    - Implementação do perfil Master Coach.
    - Módulo de Gestão de Empresas.
    - Redesign da tela de Login/Landing Page.
    - Ajustes de segurança e LGPD.
