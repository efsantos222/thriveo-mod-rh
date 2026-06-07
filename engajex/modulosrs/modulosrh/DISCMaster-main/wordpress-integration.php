<?php
/**
 * Integração DISC para WordPress
 * Adicione este código ao functions.php do seu tema
 */

// Adicionar shortcode para exibir o sistema DISC
add_shortcode('disc_analyzer', 'disc_analyzer_shortcode');

function disc_analyzer_shortcode($atts) {
    // Atributos do shortcode
    $atts = shortcode_atts(array(
        'width' => '100%',
        'height' => '800px',
        'server_url' => 'https://seu-replit-app.replit.app', // Substitua pela URL do seu Replit
    ), $atts);
    
    // Gerar ID único para evitar conflitos
    $unique_id = 'disc_' . uniqid();
    
    ob_start();
    ?>
    <div id="<?php echo $unique_id; ?>" class="disc-analyzer-container" style="width: <?php echo $atts['width']; ?>; height: <?php echo $atts['height']; ?>;">
        <iframe 
            src="<?php echo esc_url($atts['server_url']); ?>" 
            width="100%" 
            height="100%" 
            frameborder="0"
            style="border: 1px solid #ddd; border-radius: 8px;"
            allowfullscreen>
        </iframe>
    </div>
    
    <style>
    .disc-analyzer-container {
        margin: 20px 0;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        border-radius: 8px;
    }
    
    .disc-analyzer-container iframe {
        display: block;
    }
    
    @media (max-width: 768px) {
        .disc-analyzer-container {
            height: 600px !important;
        }
    }
    </style>
    <?php
    return ob_get_clean();
}

// Função alternativa para incorporar via PHP (para uso em templates)
function embed_disc_analyzer($server_url = '', $width = '100%', $height = '800px') {
    if (empty($server_url)) {
        $server_url = 'https://seu-replit-app.replit.app'; // URL padrão
    }
    
    echo disc_analyzer_shortcode(array(
        'server_url' => $server_url,
        'width' => $width,
        'height' => $height
    ));
}

// Adicionar página administrativa para configurar a URL
add_action('admin_menu', 'disc_analyzer_admin_menu');

function disc_analyzer_admin_menu() {
    add_options_page(
        'Configurações DISC Analyzer',
        'DISC Analyzer',
        'manage_options',
        'disc-analyzer-settings',
        'disc_analyzer_settings_page'
    );
}

function disc_analyzer_settings_page() {
    if (isset($_POST['submit'])) {
        update_option('disc_analyzer_url', sanitize_url($_POST['disc_analyzer_url']));
        echo '<div class="notice notice-success"><p>Configurações salvas!</p></div>';
    }
    
    $current_url = get_option('disc_analyzer_url', '');
    ?>
    <div class="wrap">
        <h1>Configurações do DISC Analyzer</h1>
        <form method="post" action="">
            <table class="form-table">
                <tr>
                    <th scope="row">URL do Servidor DISC</th>
                    <td>
                        <input type="url" name="disc_analyzer_url" value="<?php echo esc_attr($current_url); ?>" class="regular-text" />
                        <p class="description">Digite a URL completa do seu aplicativo DISC no Replit (ex: https://seu-app.replit.app)</p>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
        
        <h2>Como usar:</h2>
        <h3>1. Usando Shortcode (Recomendado)</h3>
        <p>Cole este código em qualquer post ou página:</p>
        <code>[disc_analyzer]</code>
        
        <p>Ou com parâmetros personalizados:</p>
        <code>[disc_analyzer width="100%" height="900px" server_url="<?php echo esc_attr($current_url ?: 'https://seu-app.replit.app'); ?>"]</code>
        
        <h3>2. Usando código PHP (para desenvolvedores)</h3>
        <p>No seu template PHP:</p>
        <code>&lt;?php embed_disc_analyzer('<?php echo esc_attr($current_url ?: 'https://seu-app.replit.app'); ?>'); ?&gt;</code>
        
        <h3>3. Parâmetros disponíveis:</h3>
        <ul>
            <li><strong>server_url:</strong> URL do seu aplicativo DISC</li>
            <li><strong>width:</strong> Largura do iframe (padrão: 100%)</li>
            <li><strong>height:</strong> Altura do iframe (padrão: 800px)</li>
        </ul>
    </div>
    <?php
}

// Versão melhorada do shortcode que usa a URL configurada
add_shortcode('disc_analyzer_v2', 'disc_analyzer_v2_shortcode');

function disc_analyzer_v2_shortcode($atts) {
    $saved_url = get_option('disc_analyzer_url', '');
    
    $atts = shortcode_atts(array(
        'width' => '100%',
        'height' => '800px',
        'server_url' => $saved_url,
    ), $atts);
    
    if (empty($atts['server_url'])) {
        return '<div class="notice notice-error"><p>Configure a URL do DISC Analyzer nas configurações do WordPress.</p></div>';
    }
    
    return disc_analyzer_shortcode($atts);
}

// Adicionar script para comunicação entre iframe e WordPress (opcional)
add_action('wp_footer', 'disc_analyzer_footer_script');

function disc_analyzer_footer_script() {
    // Só adiciona o script se houver um shortcode DISC na página
    global $post;
    if (is_a($post, 'WP_Post') && (has_shortcode($post->post_content, 'disc_analyzer') || has_shortcode($post->post_content, 'disc_analyzer_v2'))) {
        ?>
        <script>
        // Script para ajustar altura do iframe automaticamente (opcional)
        window.addEventListener('message', function(event) {
            // Verificar origem por segurança
            if (event.origin !== '<?php echo esc_js(get_option('disc_analyzer_url', '')); ?>') {
                return;
            }
            
            // Ajustar altura se o app enviar essa informação
            if (event.data.type === 'resize' && event.data.height) {
                const iframes = document.querySelectorAll('.disc-analyzer-container iframe');
                iframes.forEach(iframe => {
                    iframe.style.height = event.data.height + 'px';
                });
            }
        });
        </script>
        <?php
    }
}

// Widget para usar no sidebar (opcional)
class DISC_Analyzer_Widget extends WP_Widget {
    
    public function __construct() {
        parent::__construct(
            'disc_analyzer_widget',
            'DISC Analyzer',
            array('description' => 'Adiciona o sistema DISC Analyzer ao sidebar')
        );
    }
    
    public function widget($args, $instance) {
        echo $args['before_widget'];
        
        if (!empty($instance['title'])) {
            echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
        }
        
        $server_url = !empty($instance['server_url']) ? $instance['server_url'] : get_option('disc_analyzer_url', '');
        $height = !empty($instance['height']) ? $instance['height'] : '600px';
        
        echo disc_analyzer_shortcode(array(
            'server_url' => $server_url,
            'width' => '100%',
            'height' => $height
        ));
        
        echo $args['after_widget'];
    }
    
    public function form($instance) {
        $title = !empty($instance['title']) ? $instance['title'] : 'Análise DISC';
        $server_url = !empty($instance['server_url']) ? $instance['server_url'] : get_option('disc_analyzer_url', '');
        $height = !empty($instance['height']) ? $instance['height'] : '600px';
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>">Título:</label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('server_url'); ?>">URL do Servidor:</label>
            <input class="widefat" id="<?php echo $this->get_field_id('server_url'); ?>" name="<?php echo $this->get_field_name('server_url'); ?>" type="url" value="<?php echo esc_attr($server_url); ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('height'); ?>">Altura:</label>
            <input class="widefat" id="<?php echo $this->get_field_id('height'); ?>" name="<?php echo $this->get_field_name('height'); ?>" type="text" value="<?php echo esc_attr($height); ?>">
        </p>
        <?php
    }
    
    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? sanitize_text_field($new_instance['title']) : '';
        $instance['server_url'] = (!empty($new_instance['server_url'])) ? sanitize_url($new_instance['server_url']) : '';
        $instance['height'] = (!empty($new_instance['height'])) ? sanitize_text_field($new_instance['height']) : '600px';
        return $instance;
    }
}

// Registrar o widget
add_action('widgets_init', function() {
    register_widget('DISC_Analyzer_Widget');
});

?>