# MLPT System

O MLPT System é uma plataforma completa para gestão de talentos, recrutamento e desenvolvimento profissional, integrando recursos avançados de Inteligência Artificial.

## 🚀 Funcionalidades Principais

### Gestão Administrativa
- **Dashboard Interativo**: Visão geral de métricas e status do sistema.
- **Gestão de Usuários e Empresas**: Controle de acesso multi-nível (Super Admin, Admin de Empresa, Usuário).
- **Recrutamento**:
  - Gestão de Vagas (CRUD).
  - Banco de Talentos.
  - Integração com Google Custom Search API para busca automatizada de candidatos.

### Desenvolvimento e Avaliação
- **PDI (Plano de Desenvolvimento Individual)**: Ferramentas para criação e acompanhamento de planos de carreira.
- **Análise de Vídeo com IA**: Avaliação automatizada de candidatos via análise de vídeo utilizando GPT-4o Vision, focada em:
  - Comunicação Verbal e Não-Verbal.
  - Competências Técnicas e Comportamentais.
  - Indicadores de Risco.
- **Chatbots de Treinamento**: Módulos de Onboarding e Offboarding treinados com base de conhecimento da empresa.
- **Pulse Vote**: Sistema de votação e feedback rápido.

## 🛠️ Tecnologias Utilizadas

- **Backend**: PHP (Vanilla)
- **Frontend**: HTML5, CSS3, JavaScript
- **Banco de Dados**: MySQL
- **Integrações de IA**: OpenAI API (GPT-4o / Vision)
- **Busca**: Google Custom Search API

## ⚙️ Instalação e Configuração

1. **Banco de Dados**:
   - Crie um banco de dados MySQL.
   - Importe o arquivo `database.sql` para estruturar as tabelas.
   - Se necessário, execute `update_schema.sql` para garantir as últimas atualizações de estrutura.

2. **Configuração da Conexão**:
   - Verifique e configure as credenciais de banco de dados no arquivo `config/database.php`.

3. **Configuração Inicial**:
   - Execute o script `setup_admin.php` para criar o usuário administrador inicial.
   - **Login Padrão**:
     - E-mail: `ezequiel.santos@gmail.com`
     - Senha: `Kyew1802`

## 📂 Estrutura de Diretórios

- `/admin`: Painel administrativo e scripts de gestão.
- `/app`: Aplicação principal para usuários finais.
- `/assets`: Recursos estáticos (CSS, JS, Imagens).
- `/config`: Arquivos de configuração do sistema.
- `/includes`: Componentes reutilizáveis (Header, Sidebar, Auth).
- `/uploads`: Diretório para armazenamento de arquivos e vídeos.

## 📝 Notas Adicionais

- O sistema utiliza autenticação via sessão PHP.
- Certifique-se de que o diretório `/uploads` possui permissões de escrita.
- Para funcionalidades de IA, é necessário configurar as chaves da API da OpenAI no painel administrativo.
