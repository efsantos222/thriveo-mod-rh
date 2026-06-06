# 🎮 Virtual Connections

Sistema de jogos de apresentação para reuniões virtuais em equipe.

## 📋 Resumo

Aplicação web em **PHP puro** (sem frameworks) que oferece jogos interativos para apresentações em equipe. Armazena dados em arquivos JSON (sem necessidade de banco de dados).

## ✨ Funcionalidades

### Jogos Disponíveis
- **⚡ Apresentação Relâmpago**: Apresentações rápidas com desafios e timer de 60 segundos
- **😀 Apresentação com Emoji**: Use emojis para contar sua história
- **📖 Construtor de Histórias**: Crie histórias colaborativas em equipe
- **🎁 Caixa Misteriosa**: Perguntas aleatórias para quebrar o gelo

### Recursos Administrativos
- Gerenciamento completo de itens dos jogos
- Edição inline de desafios e perguntas
- Reordenação via drag-and-drop ou botões
- Ordenação alfabética automática
- Visualização de sessões criadas

## 🚀 Instalação Rápida

1. **Upload**: Copie a pasta `php-app` para seu servidor web
2. **Permissões**: Configure permissões 777 na pasta `storage/`
3. **Acesse**: Abra `index.php` no navegador
4. **Pronto!** A aplicação está funcionando

## 🔐 Credenciais de Admin

- **Senha padrão**: `sysmanager25`
- Para alterar: edite `config.php` e modifique `ADMIN_PASSWORD`

## 📁 Estrutura

```
php-app/
├── index.php                 # Página inicial
├── config.php                # Configurações
├── INSTALACAO.txt            # Instruções detalhadas
├── assets/
│   └── css/
│       └── style.css         # Estilos globais
├── includes/
│   └── storage.php           # Sistema de armazenamento JSON
├── storage/                  # Dados em JSON (gerado automaticamente)
├── pages/
│   ├── create-session.php    # Criar sessão
│   ├── manage-session.php    # Gerenciar participantes
│   ├── admin-login.php       # Login admin
│   ├── admin-panel.php       # Painel administrativo
│   └── games/
│       ├── speed-intro.php   # Jogo 1
│       ├── emoji-intro.php   # Jogo 2
│       ├── story-builder.php # Jogo 3
│       └── mystery-box.php   # Jogo 4
```

## 💻 Requisitos Técnicos

- PHP 7.0 ou superior
- Permissões de escrita no servidor
- Sessões PHP habilitadas
- Módulo JSON habilitado

## 🎨 Personalização

- **Alterar cores**: Edite `assets/css/style.css`
- **Modificar tempo do timer**: Edite `pages/games/speed-intro.php` (const TIME_LIMIT)
- **Adicionar itens/perguntas**: Use o painel administrativo

## 🛡️ Segurança

- ✅ Pasta `storage/` protegida por `.htaccess`
- ✅ Headers de segurança configurados
- ✅ Validação de sessão administrativa
- ✅ Sanitização de inputs HTML

**Recomendações**:
- Altere a senha de admin padrão
- Use HTTPS em produção
- Faça backups regulares da pasta `storage/`

## 📖 Documentação Completa

Veja o arquivo `INSTALACAO.txt` para instruções detalhadas, solução de problemas e configurações avançadas.

## 🌐 Compatibilidade

- ✅ Navegadores modernos (Chrome, Firefox, Safari, Edge)
- ✅ Design responsivo para mobile
- ✅ PHP 7.0+ em Linux/Windows/Mac

## 🔧 Tecnologias

- PHP puro (sem frameworks)
- HTML5
- CSS3 (backdrop-filter, gradientes, animações)
- JavaScript vanilla (timers, drag-and-drop, interações)
- Armazenamento JSON (sem banco de dados)

## 📝 Backup e Restauração

**Backup**: Copie a pasta `storage/`

**Restauração**: Substitua a pasta `storage/` pelo backup

---

**Versão**: 1.0 | **Data**: Janeiro 2025
