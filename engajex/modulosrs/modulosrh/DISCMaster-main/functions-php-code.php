<?php
/**
 * Código para adicionar no functions.php do WordPress
 * Sistema DISC Analyzer - Incorporação via Shortcode
 */

// Adicionar shortcode para o sistema DISC
add_shortcode('disc_system', 'disc_system_shortcode');

function disc_system_shortcode($atts) {
    // Configurações padrão do shortcode
    $atts = shortcode_atts(array(
        'url' => 'https://SEU-APP.replit.app', // Substitua pela sua URL
        'width' => '100%',
        'height' => '800px',
    ), $atts);
    
    // Gerar HTML do iframe
    $output = '<div class="disc-system-container" style="width: ' . esc_attr($atts['width']) . '; height: ' . esc_attr($atts['height']) . '; margin: 20px 0; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); border-radius: 8px;">';
    $output .= '<iframe src="' . esc_url($atts['url']) . '" width="100%" height="100%" frameborder="0" style="border: 1px solid #ddd; border-radius: 8px; display: block;" allowfullscreen></iframe>';
    $output .= '</div>';
    
    return $output;
}

// Adicionar CSS responsivo
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
            margin: 15px 0 !important;
        }
    }
    
    @media (max-width: 480px) {
        .disc-system-container {
            height: 500px !important;
            margin: 10px 0 !important;
        }
    }
    </style>';
}

?>