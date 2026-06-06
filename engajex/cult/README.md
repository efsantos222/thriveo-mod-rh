# Sinergy Cult - Sistema de Gestão de Cultura Organizacional com IA

## Sobre
O **Sinergy Cult** é uma plataforma web desenvolvida para a **Proftest** que utiliza Inteligência Artificial para analisar a cultura organizacional de empresas. O sistema confronta a Identidade Organizacional (definida pela gestão) com a percepção real dos colaboradores (coletada via pesquisas de clima), gerando relatórios de correções de rota e insights estratégicos.

## Funcionalidades Principais

### 🚀 Landing Page & Acesso
- **Design Premium**: Interface moderna com glassmorphism e animações suaves.
- **Login Unificado**: Página inicial serve como Landing Page institucional e portal de acesso.
- **Conformidade LGPD**: Seção dedicada explicando o tratamento e anonimização de dados.
- **Solicitação de Acesso**: Instruções claras para cadastro corporativo (validação manual).

### 👥 Papéis de Usuário (ACL)
1. **Superadmin**
   - Gestão total do sistema.
   - Cadastro e exclusão de Empresas.
   - Configuração da **OpenAI API Key**.
   - Criação de gestores para cada empresa.
   
2. **Gestor (Manager)**
   - **Identidade Organizacional**: Cadastro de Propósito, Missão, Visão, Princípios e Valores.
   - **Gestão de Equipe**: Cadastro de colaboradores (apenas e-mails corporativos).
   - **Pesquisas**: Criação de questionários personalizados (Texto, Escala, Binário).
   - **Relatório AI**: Geração de análise cultural profunda usando GPT-4/GPT-3.5.

3. **Colaborador (User)**
   - Interface simplificada ("Minha Área").
   - Resposta segura e anônima às pesquisas disponíveis.

## Tecnologias
- **Backend/Frontend**: PHP Nativo (Estruturado/MVC-like), HTML5.
- **Estilo**: CSS Puro (Vanilla) com design system moderno (Variáveis CSS, CSS Grid/Flexbox).
- **Banco de Dados**: MySQL.
- **IA**: Integração com OpenAI API (cURL).

## Instalação (Hostgator / Compartilhada)

### 1. Banco de Dados
- Crie um banco de dados: `efsantos_cult`
- Usuário do banco: `efsantos_cult`
- Senha do banco: `Kyew1802`
- *Caso altere, atualize o arquivo `config/db.php`.*

### 2. Configuração
1. Suba os arquivos para a pasta pública (ex: `public_html/cult`).
2. Acesse `seusite.com/install.php` para criar as tabelas automaticamente.
3. Apague o arquivo `install.php` após o uso.

### 3. Primeiro Acesso
- **URL**: `https://testprof.com.br/cult`
- **Login Superadmin**: `ezequiel.santos@gmail.com`
- **Senha**: `Kyew1802`

### 4. Pós-Instalação
1. Logue como Superadmin.
2. Vá em **Configurações** e insira sua chave da OpenAI.
3. Cadastre a primeira empresa cliente.

## Estrutura de Arquivos
- `assets/`: CSS e imagens.
- `config/`: Conexão com banco e sessão (`db.php`).
- `includes/`: Componentes reutilizáveis (Header, Footer).
- `install.php`: Script de setup inicial.
- `*.php`: Paginas do sistema (Controllers + Views).

---
Desenvolvido por **Proftest** - 2025.
