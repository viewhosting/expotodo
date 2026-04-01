<?php
if (!defined('ABSPATH')) exit;

/**
 * Controlador del Menú Administrativo Expotodo
 * Orquesta la jerarquía del menú y enlaza las páginas de otros controladores.
 */
class AdminController {

    public function __construct() {
        add_action('admin_menu', array($this, 'register_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_styles'));
        
        // Registrar opciones generales para asegurar consistencia
        add_action('admin_init', array($this, 'register_settings'));
    }

    public function register_settings() {
        register_setting('expotodo_settings_group', 'expotodo_whatsapp');
        register_setting('expotodo_settings_group', 'expotodo_boutique_mode');
        register_setting('expotodo_settings_group', 'expotodo_footer_bg');
    }

    /**
     * Carga modular de activos premium bajo demanda
     */
    public function enqueue_admin_styles($hook) {
        // Solo cargamos si estamos en una de nuestras páginas de Expotodo
        if (strpos($hook, 'expotodo') === false) return;
        
        $base_url = get_template_directory_uri() . '/assets/admin';
        $ver = time(); // 🧪 cache-busting Boutique

        // 1. Assets Globales de la Suite (Core)
        wp_enqueue_media(); // 🖼️ Habilitar Biblioteca de Medios
        wp_enqueue_style('expotodo-admin-core', $base_url . '/css/core.css', array(), $ver);

        // 2. Assets Condicionales por Página
        switch ($hook) {
            case 'toplevel_page_expotodo-main': // Wishlist Insights
                wp_enqueue_style('expotodo-admin-wishlist', $base_url . '/css/wishlist.css', array(), $ver);
                break;

            case 'expotodo_page_expotodo-messages': // Centro de Mensajes
                wp_enqueue_style('expotodo-admin-messages', $base_url . '/css/messages.css', array(), $ver);
                wp_enqueue_script('expotodo-admin-messages-js', $base_url . '/js/messages.js', array('jquery'), $ver, true);
                wp_localize_script('expotodo-admin-messages-js', 'expotodo_admin_params', array(
                    'ajax_url' => admin_url('admin-ajax.php'),
                    'nonce'    => wp_create_nonce('expotodo_admin_nonce')
                ));
                break;

            case 'expotodo_page_expotodo-settings': // Configuración
                wp_enqueue_style('expotodo-admin-settings', $base_url . '/css/settings.css', array(), $ver);
                wp_enqueue_script('expotodo-admin-settings-js', $base_url . '/js/settings.js', array('jquery'), $ver, true);
                wp_localize_script('expotodo-admin-settings-js', 'expotodo_admin_params', array(
                    'test_smtp_nonce' => wp_create_nonce('expotodo_test_smtp')
                ));
                break;

            case 'expotodo_page_expotodo-email-templates': // Gestor de Plantillas
                wp_enqueue_style('expotodo-admin-templates', $base_url . '/css/templates.css', array(), $ver);
                wp_enqueue_script('expotodo-admin-templates-js', $base_url . '/js/templates.js', array('jquery'), $ver, true);
                break;

            case 'expotodo_page_expotodo-email-queue': // Cola de Envío
                wp_enqueue_style('expotodo-admin-queue', $base_url . '/css/queue.css', array(), $ver);
                wp_enqueue_script('expotodo-admin-queue-js', $base_url . '/js/queue.js', array('jquery'), $ver, true);
                break;
        }
    }

    public function register_menu() {
        // Menú Principal
        add_menu_page(
            'Expotodo', 
            'Expotodo', 
            'manage_options', 
            'expotodo-main', 
            array( 'WishlistController', 'render_admin_page' ), 
            'dashicons-store', 
            25
        );

        // Submenús vinculados a sus respectivos controladores
        add_submenu_page(
            'expotodo-main', 
            'Wishlist', 
            'Wishlist', 
            'manage_options', 
            'expotodo-main', 
            array( 'WishlistController', 'render_admin_page' )
        );

        add_submenu_page(
            'expotodo-main', 
            'Mensajes de la web', 
            'Mensajes de la web', 
            'manage_options', 
            'expotodo-messages', 
            array( 'ContactController', 'render_messages_page' )
        );

        add_submenu_page(
            'expotodo-main', 
            'Configuración', 
            'Configuración', 
            'manage_options', 
            'expotodo-settings', 
            array( $this, 'render_settings_page' )
        );

        add_submenu_page(
            'expotodo-main', 
            'Plantillas de Email', 
            'Plantillas', 
            'manage_options', 
            'expotodo-email-templates', 
            array($this, 'render_templates_page')
        );

        add_submenu_page(
            'expotodo-main', 
            'Cola de Envío', 
            'Cola de Envío', 
            'manage_options', 
            'expotodo-email-queue', 
            array('EmailController', 'render_queue_page')
        );
    }

    /**
     * Renderiza la página de gestión de plantillas con Sidebar Pro
     */
    public function render_templates_page() {
        if (!current_user_can('manage_options')) return;
        
        $active_template = isset($_GET['template']) ? sanitize_text_field($_GET['template']) : 'general';
        
        // Mapa Maestro de Plantillas Boutique
        $templates_map = array(
            'general' => array(
                'label' => 'DISEÑO MAESTRO',
                'cat'   => 'General',
                'desc'  => 'El marco visual (Layout) que envuelve a todos los correos.',
                'icon'  => 'dashicons-layout',
                'tags'  => array('{content}')
            ),
            'contact' => array(
                'label' => 'CONTACTO WEB',
                'cat'   => 'General',
                'desc'  => 'Respuesta manual a clientes desde el centro de mensajes.',
                'icon'  => 'dashicons-email-alt',
                'tags'  => array('{name}', '{message}', '{email}', '{subject}')
            ),
            'new_order' => array(
                'label' => 'NUEVO PEDIDO',
                'cat'   => 'Tienda',
                'desc'  => 'Aviso al administrador cuando entra una compra.',
                'icon'  => 'dashicons-cart',
                'tags'  => array('{order_number}', '{order_total}', '{customer_name}', '{order_items}')
            ),
            'customer_processing_order' => array(
                'label' => 'PROCESANDO',
                'cat'   => 'Tienda',
                'desc'  => 'Enviado al cliente tras el pago exitoso.',
                'icon'  => 'dashicons-update',
                'tags'  => array('{order_number}', '{customer_name}', '{order_items}', '{order_total}')
            ),
            'customer_completed_order' => array(
                'label' => 'COMPLETADO',
                'cat'   => 'Tienda',
                'desc'  => 'Aviso de que el pedido ha sido enviado o finalizado.',
                'icon'  => 'dashicons-yes-alt',
                'tags'  => array('{order_number}', '{customer_name}', '{order_items}', '{order_total}')
            ),
            'cancelled_order' => array(
                'label' => 'CANCELADO',
                'cat'   => 'Tienda',
                'desc'  => 'Notificación de pedido anulado.',
                'icon'  => 'dashicons-dismiss',
                'tags'  => array('{order_number}', '{customer_name}')
            ),
            'failed_order' => array(
                'label' => 'FALLIDO',
                'cat'   => 'Tienda',
                'desc'  => 'Aviso al cliente si el pago no se pudo procesar.',
                'icon'  => 'dashicons-warning',
                'tags'  => array('{order_number}', '{customer_name}', '{payment_link}')
            ),
            'customer_on_hold_order' => array(
                'label' => 'EN ESPERA',
                'cat'   => 'Tienda',
                'desc'  => 'Pedido recibido pero esperando pago (ej: transferencia).',
                'icon'  => 'dashicons-clock',
                'tags'  => array('{order_number}', '{customer_name}', '{payment_instructions}')
            ),
            'customer_refunded_order' => array(
                'label' => 'REEMBOLSO',
                'cat'   => 'Tienda',
                'desc'  => 'Aviso de devolución de dinero.',
                'icon'  => 'dashicons-undo',
                'tags'  => array('{order_number}', '{refund_amount}')
            ),
            'customer_invoice' => array(
                'label' => 'DETALLES/FACTURA',
                'cat'   => 'Tienda',
                'desc'  => 'Envío manual de detalles de pedido o factura.',
                'icon'  => 'dashicons-media-document',
                'tags'  => array('{order_number}', '{invoice_link}')
            ),
            'customer_note' => array(
                'label' => 'NOTA CLIENTE',
                'cat'   => 'Tienda',
                'desc'  => 'Se envía cuando añades una nota al pedido.',
                'icon'  => 'dashicons-sticky',
                'tags'  => array('{order_number}', '{note_content}')
            ),
            'customer_new_account' => array(
                'label' => 'NUEVA CUENTA',
                'cat'   => 'Cuentas',
                'desc'  => 'Bienvenida al crear una cuenta en la tienda.',
                'icon'  => 'dashicons-admin-users',
                'tags'  => array('{user_login}', '{set_pass_link}')
            ),
            'customer_reset_password' => array(
                'label' => 'RESET PASSWORD',
                'cat'   => 'Cuentas',
                'desc'  => 'Correo para recuperar la contraseña.',
                'icon'  => 'dashicons-lock',
                'tags'  => array('{user_login}', '{reset_link}')
            )
        );

        // Guardar si es necesario
        if (isset($_POST['save_boutique_template'])) {
            check_admin_referer('expotodo_save_temp');
            $content = wp_kses_post($_POST['template_body']);
            update_option('expotodo_template_' . $active_template, $content);
            echo '<div class="updated"><p>Plantilla <strong>' . $templates_map[$active_template]['label'] . '</strong> guardada correctamente.</p></div>';
        }

        require_once get_template_directory() . '/inc/defaults/TemplateDefaults.php';
        $current_content = get_option('expotodo_template_' . $active_template, '');
        
        if (empty($current_content)) {
            // Diseño Boutique Pro por defecto para cada caso
            $current_content = TemplateDefaults::get_default($active_template);
            
            // Caso especial: Layout Maestro
            if ($active_template === 'general') {
                $current_content = '<div style="background:#f8fafc; padding:50px 20px; font-family:sans-serif;">
                    <div style="max-width:600px; margin:0 auto; background:#fff; border-radius:16px; overflow:hidden; box-shadow:0 10px 40px rgba(0,0,0,0.05);">
                        <div style="padding:40px;">{content}</div>
                    </div>
                </div>';
            }
        }

        ?>
        <div class="wrap expotodo-admin-wrap">
            <h1 class="wp-heading-inline">Suite de Comunicación Boutique</h1>
            <p class="description">Control total sobre la experiencia visual de Expotodo.</p>

            <div class="expotodo-template-manager">
                <!-- Sidebar Lateral -->
                <div class="exp-template-sidebar">
                    <?php 
                    $current_cat = '';
                    foreach ($templates_map as $id => $info) : 
                        if ($current_cat !== $info['cat']) : 
                            $current_cat = $info['cat'];
                            echo '<div class="sidebar-category">' . $current_cat . '</div>';
                        endif;
                    ?>
                        <a href="?page=expotodo-email-templates&template=<?php echo $id; ?>" 
                           class="sidebar-nav-item <?php echo $active_template === $id ? 'active' : ''; ?>">
                            <span class="dashicons <?php echo $info['icon']; ?>"></span>
                            <?php echo $info['label']; ?>
                        </a>
                    <?php endforeach; ?>
                </div>

                <!-- Área del Editor -->
                <div class="exp-template-main">
                    <form method="post">
                        <?php wp_nonce_field('expotodo_save_temp'); ?>
                        
                        <div class="exp-template-header">
                            <div>
                                <h2><?php echo $templates_map[$active_template]['label']; ?></h2>
                                <p><?php echo $templates_map[$active_template]['desc']; ?></p>
                            </div>
                            <input type="submit" name="save_boutique_template" class="button button-primary button-large" value="Guardar Plantilla Pro">
                        </div>

                        <div class="editor-container">
                            <?php wp_editor($current_content, 'template_body', array('textarea_rows' => 22, 'media_buttons' => true)); ?>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 350px; gap: 30px; margin-top: 30px;">
                            <div class="exp-test-box">
                                <span class="dashicons dashicons-testimonial" style="color:var(--exp-primary);"></span>
                                <input type="email" id="target_test_email" placeholder="Enviar prueba a: tu@correo.com">
                                <button type="button" onclick="sendTest('<?php echo $active_template; ?>')" class="button button-secondary">🧪 Enviar Prueba</button>
                            </div>
                            
                            <div class="card" style="padding: 20px; margin: 0; background: #f8fafc; border: 1px solid #e2e8f0;">
                                <h4 style="margin: 0 0 10px 0;">💎 Tags Disponibles</h4>
                                <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                                    <?php foreach ($templates_map[$active_template]['tags'] as $tag) : ?>
                                        <code style="cursor: pointer;" onclick="insertTag('<?php echo $tag; ?>')"><?php echo $tag; ?></code>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
<?php
    }

    /**
     * Renderiza la página de configuración con pestañas
     */
    public function render_settings_page() {
        if (!current_user_can('manage_options')) return;

        $active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'general';

        // Procesar guardado si se envió el formulario
        if (isset($_POST['expotodo_save_settings'])) {
            if (check_admin_referer('expotodo_settings_verify')) {
                
                if ($active_tab === 'general') {
                    update_option('expotodo_whatsapp', isset($_POST['whatsapp']) ? sanitize_text_field($_POST['whatsapp']) : '');
                    update_option('expotodo_boutique_mode', isset($_POST['boutique_mode']) ? 'yes' : 'no');
                    update_option('expotodo_footer_bg', isset($_POST['footer_bg']) ? sanitize_text_field($_POST['footer_bg']) : '');
                    update_option('expotodo_footer_bg_align', isset($_POST['footer_bg_align']) ? sanitize_text_field($_POST['footer_bg_align']) : 'center center');
                }

                if ($active_tab === 'smtp') {
                    if (isset($_POST['smtp_host'])) update_option('expotodo_smtp_host', sanitize_text_field($_POST['smtp_host']));
                    if (isset($_POST['smtp_port'])) update_option('expotodo_smtp_port', sanitize_text_field($_POST['smtp_port']));
                    if (isset($_POST['smtp_user'])) update_option('expotodo_smtp_user', sanitize_text_field($_POST['smtp_user']));
                    if (!empty($_POST['smtp_pass'])) {
                        update_option('expotodo_smtp_pass', sanitize_text_field($_POST['smtp_pass']));
                    }
                    if (isset($_POST['smtp_secure'])) update_option('expotodo_smtp_secure', sanitize_text_field($_POST['smtp_secure']));
                    if (isset($_POST['smtp_from_name'])) update_option('expotodo_smtp_from_name', sanitize_text_field($_POST['smtp_from_name']));
                }

                if ($active_tab === 'tracking') {
                    update_option('expotodo_google_analytics', isset($_POST['google_analytics']) ? sanitize_text_field($_POST['google_analytics']) : '');
                }

                echo '<div class="updated"><p>Ajustes de <strong>' . strtoupper($active_tab) . '</strong> actualizados.</p></div>';
            }
        }

        ?>
        <div class="wrap expotodo-admin-wrap">
            <h1>Configuración Boutique Expotodo</h1>
            <p class="description">Gestión centralizada del ecosistema digital.</p>
            <hr class="wp-header-end">

            <h2 class="nav-tab-wrapper">
                <a href="?page=expotodo-settings&tab=general" class="nav-tab <?php echo $active_tab == 'general' ? 'nav-tab-active' : ''; ?>">General</a>
                <a href="?page=expotodo-settings&tab=smtp" class="nav-tab <?php echo $active_tab == 'smtp' ? 'nav-tab-active' : ''; ?>">Correo (SMTP)</a>
                <a href="?page=expotodo-settings&tab=tracking" class="nav-tab <?php echo $active_tab == 'tracking' ? 'nav-tab-active' : ''; ?>">Seguimiento</a>
            </h2>

            <form method="post" action="">
                <?php wp_nonce_field('expotodo_settings_verify'); ?>
                
                <div class="card" style="max-width: 900px; margin-top: 20px; padding: 30px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); border:none; background:#fff;">
                    
                    <?php if ($active_tab === 'general') : 
                        $whatsapp = get_option('expotodo_whatsapp', '');
                        $boutique = get_option('expotodo_boutique_mode', 'yes');
                    ?>
                        <h2 class="title" style="margin-bottom:20px;">Identidad y Estilo</h2>
                        <table class="form-table" role="presentation">
                            <tr>
                                <th scope="row"><label for="whatsapp">WhatsApp Directo</label></th>
                                <td>
                                    <input name="whatsapp" type="text" id="whatsapp" value="<?php echo esc_attr($whatsapp); ?>" class="regular-text" placeholder="Ej: 5219991234567">
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Dashboard Boutique</th>
                                <td>
                                    <label class="switch">
                                        <input name="boutique_mode" type="checkbox" id="boutique_mode" value="yes" <?php checked('yes', $boutique); ?>>
                                        Activar visuales premium en el sitio.
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="footer_bg">Fondo de Footer</label></th>
                                <td>
                                    <?php 
                                        $footer_bg = get_option('expotodo_footer_bg', '');
                                        $footer_align = get_option('expotodo_footer_bg_align', 'center center');
                                    ?>
                                    
                                    <style>
                                        /* 💎 Boutique Settings UI */
                                        .boutique-upl-wrap { display: flex; gap: 10px; margin-bottom: 25px; align-items: center; }
                                        .boutique-upl-wrap input { flex-grow: 1; border-color: #cbd5e1; border-radius: 6px; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
                                        .boutique-upl-wrap button { background: #b0d443 !important; border-color: #8ca835 !important; color: #1a1a1a !important; font-weight: 600; border-radius: 6px; }
                                        
                                        .boutique-control-grid { display: grid; grid-template-columns: 1fr 2fr; gap: 30px; margin-bottom: 30px; }
                                        
                                        .boutique-pos-panel { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 15px; }
                                        .boutique-pos-panel h4 { margin: 0 0 10px 0; font-size: 13px; color: #475569; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
                                        
                                        .pos-grid-container { display: grid; grid-template-columns: repeat(3, 1fr); gap: 4px; width: 100px; height: 100px; background: #fff; border: 1px solid #cbd5e1; padding: 5px; border-radius: 8px; box-shadow: inset 0 2px 4px rgba(0,0,0,0.05); }
                                        .pos-cell { position: relative; cursor: pointer; }
                                        .pos-cell input[type="radio"] { opacity: 0; position: absolute; width: 0; height: 0; }
                                        .pos-cell span { display: block; width: 100%; height: 100%; background: #f1f5f9; border-radius: 4px; transition: all 0.2s; border: 2px solid transparent; }
                                        .pos-cell:hover span { background: #e2e8f0; }
                                        .pos-cell input[type="radio"]:checked + span { background: #b0d443; border-color: #8ca835; box-shadow: 0 2px 5px rgba(176, 212, 67, 0.4); }
                                        
                                        .boutique-preview-card { border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; background: #fff; box-shadow: 0 10px 25px rgba(0,0,0,0.05); }
                                        .boutique-preview-header { padding: 10px 15px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; font-weight: 600; color: #334155; font-size: 13px; }
                                        .boutique-preview-header .badge { background: #b0d443; color: #111; font-size: 10px; padding: 2px 8px; border-radius: 12px; text-transform: uppercase; letter-spacing: 1px; }
                                        
                                        .boutique-preview-window { height: 140px; position: relative; overflow: hidden; background: #111; display: flex; align-items: center; }
                                        .b-prev-img { position: absolute; top: -10px; left: -10px; width: calc(100% + 20px); height: calc(100% + 20px); background-size: cover; background-repeat: no-repeat; filter: blur(8px); -webkit-filter: blur(8px); z-index: 1; transition: background-position 0.3s; }
                                        .b-prev-overlay { position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(to right, rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.7)); z-index: 2; }
                                        .b-prev-content { position: relative; z-index: 10; width: 100%; display: flex; justify-content: space-between; padding: 0 25px; color: #fff; font-family: -apple-system, sans-serif; align-items: center; }
                                        .b-prev-logo { font-size: 16px; font-weight: 800; letter-spacing: 2px; }
                                        .b-prev-menu { display: flex; gap: 15px; font-size: 11px; text-transform: uppercase; font-weight: 500; opacity: 0.9; }
                                    </style>

                                    <!-- 🔗 Selector de Imagen -->
                                    <div class="boutique-upl-wrap">
                                        <input name="footer_bg" type="text" id="footer_bg" value="<?php echo esc_attr($footer_bg); ?>" placeholder="URL de la imagen (selecciona o pega aquí)">
                                        <button type="button" class="button button-secondary" id="btn_upload_footer_bg">
                                            <span class="dashicons dashicons-format-image" style="margin-top:2px;"></span> Galería
                                        </button>
                                    </div>

                                    <div class="boutique-control-grid">
                                        <!-- 🕹️ Panel de Posición -->
                                        <div class="boutique-pos-panel">
                                            <h4>Punto de Enfoque</h4>
                                            <div class="pos-grid-container" id="boutique-align-grid">
                                                <?php 
                                                    $positions = ['left top', 'center top', 'right top', 'left center', 'center center', 'right center', 'left bottom', 'center bottom', 'right bottom'];
                                                    foreach ($positions as $pos) {
                                                        $checked = checked($pos, $footer_align, false);
                                                        echo "<label class='pos-cell' title='{$pos}'>";
                                                        echo "<input type='radio' name='footer_bg_align' value='{$pos}' {$checked}>";
                                                        echo "<span></span>";
                                                        echo "</label>";
                                                    }
                                                ?>
                                            </div>
                                            <p style="font-size: 11px; color: #64748b; margin-top: 10px; line-height: 1.4;">Dirige la parte visible de la imagen (Grid de 9 puntos).</p>
                                        </div>

                                        <!-- 🖥️ Previsualización -->
                                        <div class="boutique-preview-card" style="display: <?php echo empty($footer_bg) ? 'none' : 'block'; ?>;" id="footer-preview-wrapper">
                                            <div class="boutique-preview-header">
                                                <span>Aesthetic Footer</span>
                                                <span class="badge">Live</span>
                                            </div>
                                            <div class="boutique-preview-window">
                                                <div class="b-prev-img" id="footer_bg_preview_img" style="background-image: url('<?php echo esc_url($footer_bg); ?>'); background-position: <?php echo esc_attr($footer_align); ?>;"></div>
                                                <div class="b-prev-overlay"></div>
                                                <div class="b-prev-content">
                                                    <div class="b-prev-logo">EXPOTODO</div>
                                                    <div class="b-prev-menu"><span>Catálogo</span><span>Contacto</span></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </td>
                            </tr>
                        </table>

                    <?php elseif ($active_tab === 'smtp') : 
                        $smtp_host = get_option('expotodo_smtp_host', '');
                        $smtp_port = get_option('expotodo_smtp_port', '587');
                        $smtp_user = get_option('expotodo_smtp_user', '');
                        $smtp_secure = get_option('expotodo_smtp_secure', 'tls');
                        $smtp_from_name = get_option('expotodo_smtp_from_name', 'Expotodo Boutique');
                    ?>
                        <h2 class="title" style="margin-bottom:20px;">Motor de Envío Profesional (SMTP)</h2>
                        <table class="form-table">
                            <tr>
                                <th scope="row">Nombre Remitente</th>
                                <td><input type="text" name="smtp_from_name" value="<?php echo esc_attr($smtp_from_name); ?>" class="regular-text" /></td>
                            </tr>
                            <tr>
                                <th scope="row">Servidor Host</th>
                                <td><input type="text" name="smtp_host" value="<?php echo esc_attr($smtp_host); ?>" class="regular-text" /></td>
                            </tr>
                            <tr>
                                <th scope="row">Puerto</th>
                                <td><input type="text" name="smtp_port" value="<?php echo esc_attr($smtp_port); ?>" class="small-text" /></td>
                            </tr>
                            <tr>
                                <th scope="row">Seguridad</th>
                                <td>
                                    <select name="smtp_secure">
                                        <option value="tls" <?php selected($smtp_secure, 'tls'); ?>>TLS (587)</option>
                                        <option value="ssl" <?php selected($smtp_secure, 'ssl'); ?>>SSL (465)</option>
                                        <option value="none" <?php selected($smtp_secure, 'none'); ?>>Ninguna</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Usuario</th>
                                <td><input type="text" name="smtp_user" value="<?php echo esc_attr($smtp_user); ?>" class="regular-text" /></td>
                            </tr>
                            <tr>
                                <th scope="row">Contraseña</th>
                                <td><input type="password" name="smtp_pass" value="" class="regular-text" placeholder="●●●●●●●●" /></td>
                            </tr>
                        </table>

                        </div>
<?php
                    elseif ($active_tab === 'tracking') : 
                        $ga = get_option('expotodo_google_analytics', '');
                    ?>
                        <h2 class="title" style="margin-bottom:20px;">Rastreo y Analítica</h2>
                        <table class="form-table" role="presentation">
                            <tr>
                                <th scope="row"><label for="google_analytics">Google Analytics ID</label></th>
                                <td>
                                    <input name="google_analytics" type="text" id="google_analytics" value="<?php echo esc_attr($ga); ?>" class="regular-text" placeholder="G-XXXXXXXXXX">
                                </td>
                            </tr>
                        </table>
                    <?php endif; ?>

                    <p class="submit" style="margin-top:30px; border-top:1px solid #eee; padding-top:20px;">
                        <input type="submit" name="expotodo_save_settings" id="submit" class="button button-primary button-large" value="Guardar Cambios de <?php echo strtoupper($active_tab); ?>">
                    </p>
                </div>
            </form>
        </div>
        <?php
    }
}
