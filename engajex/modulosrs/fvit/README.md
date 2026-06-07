# Proftest Formata - Sistema Inteligente de Currículos

Sistema corporativo para formatação e padronização de currículos utilizando Inteligência Artificial (OpenAI GPT-4o), com foco em conformidade com a LGPD e otimização de processos de RH.

## 🚀 Funcionalidades

### 🌟 Para o Responsável (Usuário)
*   **Upload de PDF**: Extração automática de texto de arquivos PDF.
*   **Inteligência Artificial**: Reescrita e formatação automática de currículos para um padrão profissional.
*   **Personalização Drag & Drop**: Ordene as seções do currículo (Dados Pessoais, Resumo, Experiência, etc.) apenas arrastando os itens.
*   **Controles de Privacidade**: 
    *   Ocultar contatos pessoais (LGPD).
    *   Simplificar nome do candidato (Ex: "João Silva" -> "João S.").
    *   Remover referências a fotos.
*   **Exportação**: Geração de PDF pronto para impressão/envio.

### 🛡️ Para o Administrador
*   **Gestão de Usuários**: Criação e controle de acesso (Admin/Responsável).
*   **Gestão de Empresas**: Organização de múltiplos clientes/departamentos.
*   **Integração API**: Configuração segura da chave OpenAI diretamente pelo painel.

## 🛠️ Tecnologias Utilizadas

*   **Backend**: PHP 7.4+ (Sem frameworks pesados, leve e rápido).
*   **Frontend**: HTML5, CSS3 Moderno (Glassmorphism, HSL), JavaScript Puro.
*   **Banco de Dados**: MySQL.
*   **Bibliotecas Externas**:
    *   `PDF.js` (Mozilla) - Para leitura de PDFs no navegador.
    *   `SortableJS` - Para funcionalidade de arrastar e soltar.
*   **IA**: OpenAI API (Modelo GPT-4o).

## ⚙️ Instalação

### Pré-requisitos
*   Servidor Web (Apache/Nginx).
*   PHP 7.4 ou superior com extensão `pdo_mysql` e `curl`.
*   MySQL 5.7 ou superior.

### Passo a Passo

1.  **Arquivos**:
    Faça o upload de todos os arquivos para a pasta pública do seu servidor (ex: `public_html/cv-sistema`).

2.  **Configuração de Banco de Dados**:
    Edite o arquivo `config/db.php` com as credenciais do seu servidor MySQL:
    ```php
    $host = 'localhost';
    $dbname = 'nome_do_banco';
    $username = 'seu_usuario';
    $password = 'sua_senha';
    ```

3.  **Instalação Automática**:
    Acesse o script de instalação pelo navegador na primeira execução para criar as tabelas e o usuário administrador:
    `http://seu-dominio.com/install/setup.php`

4.  **Acesso Inicial**:
    *   **Login**: `ezequiel.santos@gmail.com`
    *   **Senha**: `Kyew@1802`

5.  **Configuração da API**:
    Faça login como Administrador, vá em "Configurações API" e insira sua chave da OpenAI (`sk-...`).

## 📁 Estrutura de Arquivos

```text
/
├── index.php             # Página de Login e Landing Page
├── dashboard.php         # Painel Principal (Roteador)
├── api/
│   └── process_cv.php    # Proxy da API OpenAI + Regras de Prompt
├── config/
│   └── db.php            # Conexão MySQL
├── views/
│   ├── formatter_panel.php # Ferramenta de Currículo (Frontend)
│   ├── admin_users.php     # Gestão de Usuários
│   └── ...
└── assets/               # CSS e Recursos Estáticos
```

## 🔒 Segurança e LGPD

*   O sistema não armazena os currículos processados no banco de dados para garantir a privacidade.
*   A chave da API é armazenada de forma segura no banco de dados.
*   Funcionalidades nativas para anonimização de dados sensíveis antes da geração do documento final.

---
Desenvolvido para **Proftest**.
