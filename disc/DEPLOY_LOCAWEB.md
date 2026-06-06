# Guia de Deploy - Servidor Dedicado Locaweb

Este documento contém instruções específicas para implantar a Aplicação Web DISC no seu servidor dedicado Locaweb, acessível via `https://thriveo.com.br/disc`.

## 📋 Pré-requisitos Completados

Já realizamos as seguintes adaptações no código:
1.  **`web_app.py`**: O sistema foi atualizado para usar rotas dinâmicas (`url_for`). Isso permite que ele funcione corretamente dentro do subdiretório `/disc` sem quebrar links do JavaScript.
2.  **`passenger_wsgi.py`**: Criamos o arquivo de entrada padrão exigido pelo Phusion Passenger (usado pela Locaweb no cPanel) para aplicações Python.

## 🚀 Passo a Passo para Deploy

### 1. Upload dos Arquivos

Envie todos os arquivos desta pasta para o diretório da sua aplicação no servidor. Normalmente, você criará uma pasta fora do `public_html` para segurança, ou dentro, dependendo da configuração.

Sugerimos a estrutura: `/home/seu-usuario/disc_app`

### 2. Configuração no cPanel ("Setup Python App")

1.  Acesse o cPanel do seu domínio `thriveo.com.br`.
2.  Procure por **"Setup Python App"** (Configurar Aplicação Python).
3.  Clique em **"Create Application"**.
4.  Preencha os campos:
    *   **Python Version**: Escolha 3.7 ou superior (recomendado 3.9+).
    *   **Application root**: O caminho da pasta onde você enviou os arquivos (ex: `disc_app`).
    *   **Application URL**: Selecione o domínio `thriveo.com.br` e digite `disc` no campo de caminho. A URL final deve ficar `thriveo.com.br/disc`.
    *   **Application startup file**: `passenger_wsgi.py` (O sistema deve detectar automaticamente se deixado em branco, mas é bom especificar).
    *   **Application entry point**: `application` (Isso é importante, pois definimos `application` no `passenger_wsgi.py`).
5.  Clique em **"Create"**.

### 3. Instalação de Dependências

Após criar a aplicação no cPanel:
1.  O cPanel mostrará um "Command for entering to virtual environment" (Comando para entrar no ambiente virtual), algo como `source /home/seu-usuario/virtualenv/disc_app/3.9/bin/activate`.
2.  Acesse o terminal (via SSH ou Terminal do cPanel).
3.  Execute o comando de ativação copiado.
4.  Navegue até a pasta da aplicação: `cd /home/seu-usuario/disc_app`.
5.  Instale as dependências:
    ```bash
    pip install -r requirements.txt
    ```

### 4. Reiniciar a Aplicação

1.  Volte à tela "Setup Python App" no cPanel.
2.  Na lista de aplicações, encontre sua app DISC.
3.  Clique em **"Restart"**.

## ✅ Teste

Acesse `https://thriveo.com.br/disc`.
O sistema deve carregar a página inicial do teste DISC.
Tente:
1.  Responder algumas perguntas.
2.  Verificar se as perguntas passam para a próxima sem erro.
3.  Gerar o resultado final.

## 🔧 Troubleshoot (Resolução de Problemas)

*   **Erro 404 ou 500**: Verifique os logs de erro no cPanel ou no arquivo `passenger.log` (se disponível).
*   **Página carrega, mas teste não inicia**: Verifique se o console do navegador (F12) mostra erros de JavaScript. Se houver erros 404 em `/api/questions`, significa que a configuração de URL base não foi detectada corretamente. A mudança que fizemos para usar `{{ url_for(...) }}` deve prevenir isso, desde que o servidor web passe o `SCRIPT_NAME` correto (padrão no Locaweb).
*   **Permissões**: Certifique-se de que os arquivos `.py` têm permissão 644 e pastas 755.

---
**Observação**: O arquivo `web_app.py` continua sendo capaz de rodar localmente com `python web_app.py` para testes rápidos.
