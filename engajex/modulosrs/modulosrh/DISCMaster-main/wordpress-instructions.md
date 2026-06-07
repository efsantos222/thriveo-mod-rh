# Como Incorporar o Sistema DISC no WordPress

## Passo 1: Preparar o Aplicativo
1. Faça deploy da aplicação DISC no Replit
2. Anote a URL gerada (ex: `https://seu-app.replit.app`)

## Passo 2: Instalar no WordPress
1. Copie todo o conteúdo do arquivo `wordpress-integration.php`
2. Cole no final do arquivo `functions.php` do seu tema ativo
3. Salve as alterações

## Passo 3: Configurar
1. No painel do WordPress, vá em **Configurações > DISC Analyzer**
2. Digite a URL do seu aplicativo DISC
3. Clique em "Salvar alterações"

## Formas de Usar

### 1. Shortcode Simples (Mais Fácil)
Cole em qualquer post ou página:
```
[disc_analyzer_v2]
```

### 2. Shortcode com Parâmetros
```
[disc_analyzer_v2 width="100%" height="900px"]
```

### 3. Shortcode Completo
```
[disc_analyzer server_url="https://sua-url.replit.app" width="100%" height="800px"]
```

### 4. No Código PHP (Para Desenvolvedores)
No template do seu tema:
```php
<?php embed_disc_analyzer('https://sua-url.replit.app'); ?>
```

### 5. Widget no Sidebar
1. Vá em **Aparência > Widgets**
2. Adicione o widget "DISC Analyzer" ao sidebar desejado
3. Configure título e altura

## Parâmetros Disponíveis

- **server_url**: URL do seu aplicativo DISC
- **width**: Largura do sistema (padrão: 100%)
- **height**: Altura do sistema (padrão: 800px)

## Exemplo de Uso
Para criar uma página dedicada ao DISC:

1. Crie uma nova página no WordPress
2. Digite o título: "Análise de Perfil DISC"
3. No conteúdo, cole apenas: `[disc_analyzer_v2]`
4. Publique a página

## Customização Visual

O sistema já vem com estilos responsivos. Para personalizar ainda mais, adicione CSS customizado em **Aparência > Personalizar > CSS Adicional**:

```css
.disc-analyzer-container {
    border-radius: 15px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    margin: 30px 0;
}

.disc-analyzer-container iframe {
    border-radius: 15px;
}
```

## Suporte
- O sistema funciona em qualquer tema WordPress
- É responsivo e se adapta a dispositivos móveis
- Não interfere com outros plugins
- Pode ser usado múltiplas vezes na mesma página