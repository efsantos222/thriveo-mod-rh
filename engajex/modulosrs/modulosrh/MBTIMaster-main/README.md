# Sistema de Análise MBTI

Sistema completo para análise de personalidade MBTI (Myers-Briggs Type Indicator) com importação de planilhas Excel, cálculos estatísticos por Z-score e perfis detalhados offline para todos os 16 tipos de personalidade.

## Funcionalidades

### Importação de Dados
- Upload de planilhas Excel (.xlsx, .xls) via drag-and-drop
- Processamento de 60 questões MBTI por participante
- Validação automática dos dados importados

### Análise Estatística
- Cálculo de Z-scores para as 4 dimensões MBTI:
  - **E/I** - Extroversão / Introversão
  - **S/N** - Sensação / Intuição
  - **T/F** - Pensamento / Sentimento
  - **J/P** - Julgamento / Percepção
- Determinação automática do tipo MBTI
- Classificação de intensidade (Leve, Moderada, Clara, Muito Clara)

### Perfis de Personalidade
Análise completa offline para todos os 16 tipos MBTI:
- **Características Gerais** - Descrição detalhada do tipo
- **Pontos Fortes** - Qualidades e habilidades naturais
- **Oportunidades de Melhoria** - Áreas para desenvolvimento
- **Pontos a Desenvolver** - Sugestões práticas de crescimento
- **Sugestões de Carreira** - Profissões compatíveis com o perfil

### Visualização e Exportação
- Tabela interativa com todos os resultados
- Visualização individual de perfis ("Ver Perfil")
- Exportação para Excel com formatação profissional
- Exportação para JSON
- Geração de relatórios completos

## Estrutura do Projeto

```
├── client/                    # Frontend React
│   └── src/
│       ├── pages/
│       │   ├── mbti-analysis.tsx    # Página principal de análise
│       │   └── profile-analysis.tsx # Visualização de perfil individual
│       ├── lib/
│       │   ├── mbti-calculations.ts # Cálculos Z-score
│       │   └── mbti-profiles.ts     # Dados dos 16 perfis MBTI
│       └── components/              # Componentes UI
├── server/                    # Backend Node.js/Express
│   ├── routes.ts              # Rotas da API
│   └── storage.ts             # Interface de armazenamento
└── shared/
    └── schema.ts              # Tipos e schemas compartilhados
```

## Tecnologias Utilizadas

### Frontend
- React com TypeScript
- Vite (build tool)
- Tailwind CSS + Shadcn/ui
- TanStack Query (gerenciamento de estado)
- SheetJS (processamento Excel)
- Wouter (roteamento)

### Backend
- Node.js com Express
- TypeScript
- Drizzle ORM (preparado para PostgreSQL)
- Zod (validação)

## Como Usar

1. Acesse a página de **Análise MBTI**
2. Arraste ou selecione uma planilha Excel com os dados
3. O sistema processa automaticamente e exibe os resultados
4. Clique em **"Ver Perfil"** para análise individual detalhada
5. Use os botões de exportação para baixar os resultados

### Formato da Planilha

A planilha Excel deve conter:
- Coluna de identificação do participante
- Coluna com nome do participante
- 60 colunas com respostas das questões MBTI (valores numéricos)

## Integração com WordPress

### Opção 1 - Shortcode Simples

Adicione ao `functions.php`:

```php
function incorporar_analise_mbti() {
    return '<iframe src="SUA_URL_AQUI" width="100%" height="800" frameborder="0" style="border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);"></iframe>';
}
add_shortcode('analise_mbti', 'incorporar_analise_mbti');
```

Use o shortcode: `[analise_mbti]`

### Opção 2 - Shortcode com Parâmetros

```php
function incorporar_analise_mbti($atts) {
    $atts = shortcode_atts(array(
        'altura' => '800',
        'largura' => '100%'
    ), $atts);
    
    return sprintf(
        '<div style="width: %s; margin: 20px auto;">
            <iframe src="SUA_URL_AQUI" 
                    width="100%%" 
                    height="%s" 
                    frameborder="0" 
                    style="border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            </iframe>
         </div>',
        esc_attr($atts['largura']),
        esc_attr($atts['altura'])
    );
}
add_shortcode('mbti_analise', 'incorporar_analise_mbti');
```

Use: `[mbti_analise altura="600" largura="90%"]`

### Opção 3 - Widget no Dashboard

```php
function adicionar_widget_mbti() {
    wp_add_dashboard_widget(
        'mbti_analise_widget',
        'Análise MBTI',
        'exibir_widget_mbti'
    );
}

function exibir_widget_mbti() {
    echo '<iframe src="SUA_URL_AQUI" width="100%" height="400" frameborder="0"></iframe>';
}

add_action('wp_dashboard_setup', 'adicionar_widget_mbti');
```

**Nota:** Substitua `SUA_URL_AQUI` pela URL da aplicação após o deploy.

## Armazenamento de Dados

### Atual (Desenvolvimento)
- Armazenamento em memória
- Dados perdidos ao reiniciar o servidor
- Ideal para testes e demonstrações

### Produção (Opcional)
- PostgreSQL via Neon Database
- Persistência permanente dos dados
- Configuração via variável de ambiente `DATABASE_URL`

## Os 16 Tipos MBTI

| Tipo | Nome | Descrição |
|------|------|-----------|
| ISTJ | O Inspetor | Responsável e confiável |
| ISFJ | O Protetor | Dedicado e caloroso |
| INFJ | O Conselheiro | Perspicaz e idealista |
| INTJ | O Arquiteto | Estratégico e independente |
| ISTP | O Virtuoso | Prático e observador |
| ISFP | O Aventureiro | Flexível e charmoso |
| INFP | O Mediador | Idealista e empático |
| INTP | O Lógico | Inovador e analítico |
| ESTP | O Empresário | Enérgico e perceptivo |
| ESFP | O Animador | Espontâneo e entusiasta |
| ENFP | O Ativista | Criativo e sociável |
| ENTP | O Inovador | Inteligente e curioso |
| ESTJ | O Executivo | Organizado e dedicado |
| ESFJ | O Provedor | Atencioso e social |
| ENFJ | O Protagonista | Carismático e inspirador |
| ENTJ | O Comandante | Ousado e estratégico |

## Licença

Este projeto foi desenvolvido para fins de análise psicológica organizacional e de pesquisa.

---

Desenvolvido com React, TypeScript e Node.js
