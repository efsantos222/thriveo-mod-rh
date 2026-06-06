# SysManager AI · Agentes Corporativos
## Guia de Deploy em Hospedagem Compartilhada (cPanel/Hostinger)

---

## 📁 Arquivos do projeto

```
agentes_sysmanager/
├── index.php        ← Página principal (HTML + PHP)
├── api.php          ← Proxy seguro para Anthropic API
├── config.php       ← ⚠️ Chave da API (EDITAR ANTES DO DEPLOY)
├── agents.js        ← Dados de todos os agentes
├── app.js           ← Lógica da aplicação
├── style.css        ← Visual completo
├── .htaccess        ← Segurança e cache
└── README.md        ← Este arquivo
```

---

## ⚙️ Passo a Passo para Deploy

### 1. Edite o config.php
Abra o arquivo `config.php` e substitua:
```php
define('ANTHROPIC_API_KEY', 'sk-ant-COLOQUE_SUA_CHAVE_AQUI');
```
Pela sua chave real da Anthropic. Obtenha em: https://console.anthropic.com/

### 2. Faça upload pelo cPanel
- Acesse o **File Manager** do cPanel
- Navegue até `public_html/` (ou subpasta desejada)
- Crie uma pasta (ex: `agentes/`)
- Faça upload de **todos os arquivos** desta pasta

### 3. Configure as permissões
No File Manager do cPanel:
- `config.php` → permissão **600** (somente leitura pelo servidor)
- Demais arquivos → permissão **644**
- `.htaccess` → permissão **644**

### 4. Acesse pelo browser
Acesse: `https://seudominio.com.br/agentes/`

---

## 🔒 Segurança

- A **API Key da Anthropic fica APENAS no servidor** (config.php)
- O browser nunca vê ou recebe a chave
- O .htaccess bloqueia acesso direto ao config.php
- Todas as chamadas à API passam pelo api.php no servidor

---

## 📱 Tela cheia em dispositivos móveis

O sistema funciona em tela cheia no iPhone/Android:
1. Abra no Safari (iOS) ou Chrome (Android)
2. Toque em **Compartilhar** → **Adicionar à Tela de Início**
3. O app abrirá em tela cheia como aplicativo nativo

---

## 🛠️ Requisitos do servidor

- PHP 7.4+ (a maioria das hospedagens já inclui)
- Extensão cURL habilitada (padrão em todos os hosts)
- HTTPS (obrigatório para a API funcionar)

---

## ❓ Problemas comuns

**Erro "Acesso negado"**
→ Verifique as permissões do config.php (deve ser 600)

**Erro de API**
→ Verifique se a chave no config.php está correta

**Tela em branco**
→ Verifique se o PHP está habilitado no servidor

**CORS error no console**
→ O api.php já resolve isso. Verifique se os arquivos foram upados corretamente.
