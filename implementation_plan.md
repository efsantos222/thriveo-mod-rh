# Integração do Módulo "Completar Perfil Profissional"

O objetivo é conectar o fluxo de login via LinkedIn/GitHub (OAuth) existente no plugin à interface do Dashboard (`cadastro.html`), permitindo que os usuários completem seus perfis após a autenticação.

## Proposed Changes

### Plugin - Rotas e Banco de Dados (Backend)
- **`thriveo-ai-jobs.php`**
  - Adicionar o include da nova classe `Thriveo_AI_Jobs_Dashboard`.
- **`includes/class-dashboard.php`** (Novo)
  - Criar uma classe que intercepta a URL `/?thriveo_dashboard=1` via a action `template_redirect`.
  - Quando a URL é acessada, renderiza o arquivo de template contendo a estrutura de `cadastro.html`.
- **`includes/class-oauth-handler.php`**
  - Adicionar novas actions para as chamadas em AJAX (`wp_ajax_thriveo_save_profile`) para salvar no banco os dados do perfil preenchidos no dashboard (Name, Headline, Bio, etc).

### Plugin - Frontend (HTML/JS)
- **`templates/dashboard.php`** (Novo)
  - Será criado incorporando inteiramente o design e o código do arquivo `cadastro.html`.
  - Modificar a tag `<script>` interna para validar o JWT armazenado no `localStorage`, carregar os dados reais do backend (via API) e injetá-los na interface (substituindo o "Mock" e a "Carla Souza").
  - Adicionar o disparo das rotas AJAX no PHP para os botões "Salvar" no perfil.
- **`includes/elementor/class-hero-widget.php`**
  - A interface do widget terá o botão "Completar Perfil Profissional" atualizado para redirecionar o usuário para `/?thriveo_dashboard=1`.
- **`assets/js/hero.js`**
  - Mudar o evento do botão "Completar Perfil Profissional" (remover o `alert`) e redirecionar adequadamente para a tela de dashboard, levando consigo o contexto atual de autenticação (o JWT mantido em `localStorage` assegura a continuidade).

---
## Verification Plan

### Automated Tests
- Não há configuração de testes automatizados (PHPUnit/Jest) presente no ambiente inicialmente. A validação recairá em testes manuais e checagem de logs do servidor.

### Manual Verification
1. Abrir a página principal do site via link local ou dev mode.
2. Realizar o login com GitHub ou LinkedIn.
3. Clicar no botão "Completar Perfil Profissional".
4. Verificar se a tela baseada em `cadastro.html` (Dashboard Thriveo) é carregada com sucesso, mantendo todo seu estilo (CSS original).
5. Preencher campos como "Bio" e "Headline", alterar dados e clicar em "Salvar".
6. Recarregar a página `/thriveo_dashboard=1` e validar se os dados continuam lá (confirmando que foram persistidos na tabela do banco MySQL do WordPress).
