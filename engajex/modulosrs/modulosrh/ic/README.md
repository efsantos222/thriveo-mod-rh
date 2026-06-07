# Sistema de Inteligência Competitiva (Proftest IC)

## Visão Geral
Sistema de Inteligência Competitiva desenvolvido em PHP (Frontend/Admin) e Python/FastAPI (Engine de Inteligência).

## Funcionalidades
- **Landing Page**: Apresentação do sistema e conformidade LGPD.
- **Painel Administrativo**: Gerenciamento de Empresas, Usuários e Chaves de API.
- **Engine de IA (Backend)**: Estrutura para análise via GPT-4, Scraping e Análise de Sentimento.

## Instalação

### 1. Banco de Dados
Certifique-se de ter um banco de dados MySQL chamado `efsantos_ic` criado.
As credenciais configuradas em `config/db.php` são:
- Usuário: `efsantos_ic`
- Senha: `Kyew1802`

Para criar as tabelas e o usuário administrador padrão, acesse no navegador:
`http://seu-servidor/install.php`

**Credenciais de Administrador:**
- Email: `ezequiel.santos@gmail.com`
- Senha: `Kyew1802`

### 2. Backend Python (Inteligência)
O backend de IA está na pasta `backend/`. Para rodar:

```bash
cd backend
pip install -r requirements.txt
python main.py
```
O servidor rodará em `http://localhost:8000`.

## Estrutura de Arquivos
- `index.php`: Página inicial (Landing Page)
- `admin/`: Painel administrativo (Login, Dashboard)
- `assets/`: CSS e JS
- `config/`: Configuração de banco de dados
- `backend/`: API em Python (FastAPI)
