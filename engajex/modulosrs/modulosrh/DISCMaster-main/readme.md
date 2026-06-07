# Sistema de Avaliação DISC

Sistema web para cálculo e análise de perfis comportamentais DISC (Dominância, Influência, Estabilidade, Conformidade) a partir de planilhas Excel.

## Funcionalidades

- **Upload de Planilhas Excel**: Processa arquivos .xlsx e .xls com questionários DISC
- **Cálculo Automático de Perfis**: Calcula os 4 perfis DISC para cada candidato
- **Análise Comportamental**: Análise detalhada de pontos fortes, áreas de melhoria e desenvolvimento
- **Análise de Equipe**: Avaliação da composição e dinâmica do grupo
- **Múltiplos Formatos de Exportação**: Excel, PDF e CSV
- **Integração WordPress**: Incorporação via shortcode em sites WordPress

## Estrutura da Planilha Excel

A planilha deve conter:
- **Coluna 1**: ID do candidato
- **Coluna 2**: Nome do candidato
- **Colunas 3-30**: 28 perguntas do questionário DISC (7 por perfil)

### Ordem das Perguntas:
- Perguntas 1-7: Dominância (D)
- Perguntas 8-14: Influência (I)
- Perguntas 15-21: Estabilidade (S)
- Perguntas 22-28: Conformidade (C)

## Instalação

### Requisitos
- Python 3.8+
- Flask
- Pandas
- OpenPyXL
- ReportLab
- NumPy

### Executar Localmente
```bash
pip install -r requirements.txt
python main.py
```

O servidor iniciará em `http://localhost:5000`

## Integração WordPress

### Opção 1: Código functions.php (Recomendado)

Adicione ao arquivo `functions.php` do seu tema:

```php
<?php
// Adicionar shortcode para o sistema DISC
add_shortcode('disc_system', 'disc_system_shortcode');

function disc_system_shortcode($atts) {
    $atts = shortcode_atts(array(
        'url' => 'https://SEU-APP.replit.app',
        'width' => '100%',
        'height' => '800px',
    ), $atts);
    
    $output = '<div class="disc-system-container" style="width: ' . esc_attr($atts['width']) . '; height: ' . esc_attr($atts['height']) . '; margin: 20px 0; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); border-radius: 8px;">';
    $output .= '<iframe src="' . esc_url($atts['url']) . '" width="100%" height="100%" frameborder="0" style="border: 1px solid #ddd; border-radius: 8px; display: block;" allowfullscreen></iframe>';
    $output .= '</div>';
    
    return $output;
}

add_action('wp_head', 'disc_system_styles');

function disc_system_styles() {
    echo '<style>
    .disc-system-container {
        position: relative;
        overflow: hidden;
    }
    @media (max-width: 768px) {
        .disc-system-container {
            height: 600px !important;
        }
    }
    @media (max-width: 480px) {
        .disc-system-container {
            height: 500px !important;
        }
    }
    </style>';
}
?>
```

### Uso do Shortcode

Básico:
```
[disc_system]
```

Com parâmetros personalizados:
```
[disc_system url="https://sua-url.replit.app" height="900px"]
```

### Opção 2: Incorporação Direta HTML

Cole em qualquer página (modo HTML):

```html
<div style="width: 100%; height: 800px; margin: 20px 0; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); border-radius: 8px;">
    <iframe 
        src="https://SEU-APP.replit.app" 
        width="100%" 
        height="100%" 
        frameborder="0"
        style="border: 1px solid #ddd; border-radius: 8px; display: block;"
        allowfullscreen>
    </iframe>
</div>
```

## Formatos de Exportação

### Excel (.xlsx)
- Planilha com resultados individuais
- Planilha de estatísticas gerais
- Planilha de análise por perfil

### PDF
- Relatório executivo
- Análise individual de cada candidato
- Estatísticas consolidadas

### CSV
- Dados tabulares para análise externa
- Compatível com outras ferramentas

## Perfis DISC

| Perfil | Características |
|--------|-----------------|
| **D - Dominância** | Direto, decisivo, orientado a resultados |
| **I - Influência** | Comunicativo, entusiasta, persuasivo |
| **S - Estabilidade** | Paciente, confiável, trabalho em equipe |
| **C - Conformidade** | Analítico, preciso, orientado a qualidade |

## Estrutura do Projeto

```
├── app.py                 # Configuração Flask
├── main.py                # Ponto de entrada
├── routes.py              # Rotas da aplicação
├── disc_calculator.py     # Lógica de cálculo DISC
├── disc_ai_analyzer.py    # Análise comportamental
├── templates/
│   └── index.html         # Interface principal
├── static/
│   ├── css/
│   └── js/
└── readme.md              # Este arquivo
```

## Licença

Este projeto é de uso privado.

## Suporte

Para dúvidas ou suporte, entre em contato com o desenvolvedor.
