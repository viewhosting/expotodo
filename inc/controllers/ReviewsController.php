<?php
if (!defined('ABSPATH')) exit;

/**
 * Controlador de Reseñas y Testimonios
 * Gestiona el almacenamiento, administración y exposición pública de las opiniones.
 */
class ReviewsController {

    public function __construct() {
        add_action( 'after_setup_theme', array( $this, 'setup_table' ) );
        add_action( 'admin_init', array( $this, 'handle_admin_actions' ) );
        add_action( 'wp_ajax_expotodo_submit_review', array( $this, 'ajax_handle_public_review' ) );
        add_action( 'wp_ajax_nopriv_expotodo_submit_review', array( $this, 'ajax_handle_public_review' ) );
        add_action( 'wp_ajax_expotodo_add_manual_review_ajax', array( $this, 'ajax_handle_admin_manual_review' ) );
        add_action( 'wp_ajax_expotodo_edit_review_ajax', array( $this, 'ajax_handle_admin_edit_review' ) );
    }

    /**
     * Crear la tabla de reseñas al inicializar el tema
     */
    public function setup_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'expotodo_reviews';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            email varchar(255) NOT NULL,
            review text NOT NULL,
            photo varchar(255) DEFAULT '',
            status varchar(50) DEFAULT 'inactive' NOT NULL,
            created_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Obtener todas las reseñas activas para mostrarlas en el slider del frontend
     */
    public static function get_active_reviews() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'expotodo_reviews';
        return $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM $table_name WHERE status = %s ORDER BY created_at DESC", 'active')
        );
    }

    /**
     * Manejar las acciones de administración tradicionales (activar, desactivar, borrar, subir foto)
     */
    public function handle_admin_actions() {
        if (!current_user_can('manage_options')) {
            return;
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'expotodo_reviews';

        // 1. Activar / Desactivar reseña
        if (isset($_GET['action']) && $_GET['action'] === 'toggle_review_status' && isset($_GET['id']) && isset($_GET['_wpnonce'])) {
            $id = intval($_GET['id']);
            if (wp_verify_nonce($_GET['_wpnonce'], 'expotodo_toggle_review_' . $id)) {
                $current_status = $wpdb->get_var($wpdb->prepare("SELECT status FROM $table_name WHERE id = %d", $id));
                $new_status = ($current_status === 'active') ? 'inactive' : 'active';
                
                $wpdb->update($table_name, array('status' => $new_status), array('id' => $id));
                wp_redirect(admin_url('admin.php?page=expotodo-reviews&msg=status_updated'));
                exit;
            }
        }

        // 2. Eliminar reseña
        if (isset($_GET['action']) && $_GET['action'] === 'delete_review' && isset($_GET['id']) && isset($_GET['_wpnonce'])) {
            $id = intval($_GET['id']);
            if (wp_verify_nonce($_GET['_wpnonce'], 'expotodo_delete_review_' . $id)) {
                $wpdb->delete($table_name, array('id' => $id));
                wp_redirect(admin_url('admin.php?page=expotodo-reviews&msg=review_deleted'));
                exit;
            }
        }

        // 3. Subir/Cambiar Foto desde el listado Admin
        if (isset($_POST['expotodo_upload_admin_photo']) && isset($_POST['review_id']) && isset($_POST['_wpnonce'])) {
            $id = intval($_POST['review_id']);
            if (wp_verify_nonce($_POST['_wpnonce'], 'expotodo_upload_photo_' . $id)) {
                if (!empty($_FILES['review_photo']['name'])) {
                    require_once(ABSPATH . 'wp-admin/includes/image.php');
                    require_once(ABSPATH . 'wp-admin/includes/file.php');
                    require_once(ABSPATH . 'wp-admin/includes/media.php');

                    $attachment_id = media_handle_upload('review_photo', 0);

                    if (!is_wp_error($attachment_id)) {
                        $photo_url = wp_get_attachment_url($attachment_id);
                        $wpdb->update($table_name, array('photo' => $photo_url), array('id' => $id));
                        wp_redirect(admin_url('admin.php?page=expotodo-reviews&msg=photo_updated'));
                        exit;
                    } else {
                        wp_redirect(admin_url('admin.php?page=expotodo-reviews&msg=photo_error'));
                        exit;
                    }
                }
            }
        }
    }

    /**
     * Procesar la creación de una reseña manualmente mediante AJAX desde el panel administrativo
     */
    public function ajax_handle_admin_manual_review() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'No tienes permisos suficientes para realizar esta acción.'));
        }

        if (!isset($_POST['_ajax_nonce']) || !wp_verify_nonce($_POST['_ajax_nonce'], 'expotodo_add_manual')) {
            wp_send_json_error(array('message' => 'Validación de seguridad fallida. Por favor, recarga e intenta de nuevo.'));
        }

        $name = isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '';
        $email_input = isset($_POST['email']) ? trim($_POST['email']) : '';
        if (strpos($email_input, '*') !== false) {
            $email = preg_replace('/[^a-zA-Z0-9@\.\-_*]/', '', $email_input);
        } else {
            $email = sanitize_email($email_input);
        }
        $review = isset($_POST['review']) ? wp_kses($_POST['review'], array('br' => array(), 'p' => array())) : '';
        $status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : 'inactive';
        $photo_url = '';

        if (empty($name) || empty($email) || empty($review)) {
            wp_send_json_error(array('message' => 'Por favor, rellene todos los campos obligatorios.'));
        }

        // Cargar foto si se seleccionó una
        if (!empty($_FILES['review_photo']['name'])) {
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            require_once(ABSPATH . 'wp-admin/includes/file.php');
            require_once(ABSPATH . 'wp-admin/includes/media.php');

            $attachment_id = media_handle_upload('review_photo', 0);
            if (!is_wp_error($attachment_id)) {
                $photo_url = wp_get_attachment_url($attachment_id);
            }
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'expotodo_reviews';

        $inserted = $wpdb->insert($table_name, array(
            'name'       => $name,
            'email'      => $email,
            'review'     => $review,
            'photo'      => $photo_url,
            'status'     => $status,
            'created_at' => current_time('mysql')
        ));

        if ($inserted) {
            wp_send_json_success(array('message' => 'Reseña guardada exitosamente.'));
        } else {
            wp_send_json_error(array('message' => 'Error al guardar la reseña en la base de datos.'));
        }
    }

    /**
     * Procesar la edición de una reseña mediante AJAX desde el panel administrativo
     */
    public function ajax_handle_admin_edit_review() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'No tienes permisos suficientes para realizar esta acción.'));
        }

        if (!isset($_POST['_ajax_nonce']) || !wp_verify_nonce($_POST['_ajax_nonce'], 'expotodo_edit_manual')) {
            wp_send_json_error(array('message' => 'Validación de seguridad fallida. Por favor, recarga e intenta de nuevo.'));
        }

        $id = isset($_POST['review_id']) ? intval($_POST['review_id']) : 0;
        if (empty($id)) {
            wp_send_json_error(array('message' => 'ID de reseña no válido.'));
        }

        $name = isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '';
        $email_input = isset($_POST['email']) ? trim($_POST['email']) : '';
        if (strpos($email_input, '*') !== false) {
            $email = preg_replace('/[^a-zA-Z0-9@\.\-_*]/', '', $email_input);
        } else {
            $email = sanitize_email($email_input);
        }
        $review = isset($_POST['review']) ? wp_kses($_POST['review'], array('br' => array(), 'p' => array())) : '';
        $status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : 'inactive';

        if (empty($name) || empty($email) || empty($review)) {
            wp_send_json_error(array('message' => 'Por favor, rellene todos los campos obligatorios.'));
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'expotodo_reviews';

        $data_to_update = array(
            'name'   => $name,
            'email'  => $email,
            'review' => $review,
            'status' => $status,
        );

        // Si se subió una nueva foto en la edición
        if (!empty($_FILES['review_photo']['name'])) {
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            require_once(ABSPATH . 'wp-admin/includes/file.php');
            require_once(ABSPATH . 'wp-admin/includes/media.php');

            $attachment_id = media_handle_upload('review_photo', 0);
            if (!is_wp_error($attachment_id)) {
                $photo_url = wp_get_attachment_url($attachment_id);
                $data_to_update['photo'] = $photo_url;
            }
        }

        $updated = $wpdb->update($table_name, $data_to_update, array('id' => $id));

        if ($updated !== false) {
            wp_send_json_success(array('message' => 'Reseña actualizada exitosamente.'));
        } else {
            wp_send_json_error(array('message' => 'Error al actualizar la reseña en la base de datos.'));
        }
    }

    /**
     * Procesar el formulario público de reseñas enviado por los clientes (Soporte AJAX y archivos)
     */
    public function ajax_handle_public_review() {
        if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'expotodo_public_review_nonce')) {
            wp_send_json_error(array('message' => 'Validación de seguridad fallida. Por favor, recarga la página e intenta de nuevo.'));
        }

        $name = isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '';
        $email_input = isset($_POST['email']) ? trim($_POST['email']) : '';
        if (strpos($email_input, '*') !== false) {
            $email = preg_replace('/[^a-zA-Z0-9@\.\-_*]/', '', $email_input);
        } else {
            $email = sanitize_email($email_input);
        }
        $review = isset($_POST['review']) ? wp_kses($_POST['review'], array('br' => array(), 'p' => array())) : '';
        $photo_url = '';

        if (empty($name) || empty($email) || empty($review)) {
            wp_send_json_error(array('message' => 'Por favor, rellene todos los campos obligatorios.'));
        }

        // Cargar foto del cliente si fue seleccionada
        if (!empty($_FILES['photo']['name'])) {
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            require_once(ABSPATH . 'wp-admin/includes/file.php');
            require_once(ABSPATH . 'wp-admin/includes/media.php');

            $attachment_id = media_handle_upload('photo', 0);
            if (!is_wp_error($attachment_id)) {
                $photo_url = wp_get_attachment_url($attachment_id);
            }
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'expotodo_reviews';

        $inserted = $wpdb->insert($table_name, array(
            'name'       => $name,
            'email'      => $email,
            'review'     => $review,
            'photo'      => $photo_url,
            'status'     => 'inactive',
            'created_at' => current_time('mysql')
        ));

        if ($inserted) {
            wp_send_json_success(array('message' => '¡Muchas gracias por tu reseña! Tu opinión es sumamente valiosa para nosotros. Será revisada y publicada a la brevedad.'));
        } else {
            wp_send_json_error(array('message' => 'Ocurrió un error al guardar la reseña. Por favor, intenta de nuevo.'));
        }
    }

    /**
     * Renderizar la página del listado de reseñas en el panel administrativo
     */
    public static function render_reviews_page() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'expotodo_reviews';
        $reviews = $wpdb->get_results("SELECT * FROM $table_name ORDER BY created_at DESC");

        // Mensajes del sistema
        $msg = isset($_GET['msg']) ? sanitize_text_field($_GET['msg']) : '';
        if ($msg === 'status_updated') {
            echo '<div class="notice notice-success is-dismissible"><p>El estado de la reseña ha sido actualizado.</p></div>';
        } elseif ($msg === 'review_deleted') {
            echo '<div class="notice notice-success is-dismissible"><p>La reseña ha sido eliminada con éxito.</p></div>';
        } elseif ($msg === 'photo_updated') {
            echo '<div class="notice notice-success is-dismissible"><p>La foto de la reseña ha sido actualizada con éxito.</p></div>';
        } elseif ($msg === 'photo_error') {
            echo '<div class="notice notice-error is-dismissible"><p>Error al cargar o procesar la imagen seleccionada.</p></div>';
        }
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline">Gestión de Reseñas de Clientes</h1>
            <button id="btn-open-review-modal" class="page-title-action button button-primary" style="margin-left: 10px;">Añadir Nueva Reseña</button>
            <hr class="wp-header-end">

            <!-- Modal Administrativo de WordPress para Añadir Reseñas -->
            <div id="expotodo-admin-review-modal" style="display:none; position:fixed; z-index:99999; left:0; top:0; width:100%; height:100%; overflow:auto; background-color:rgba(0,0,0,0.5); align-items:center; justify-content:center;">
                <div style="background-color:#fff; margin:auto; padding:25px; border:1px solid #ccd0d4; width:500px; border-radius:4px; box-shadow:0 4px 20px rgba(0,0,0,0.15); position:relative; box-sizing:border-box;">
                    <span class="expotodo-admin-modal-close" style="color:#aaa; float:right; font-size:28px; font-weight:bold; cursor:pointer; line-height:1; position:absolute; right:15px; top:10px;">&times;</span>
                    <h2 style="margin-top:0; border-bottom:1px solid #eee; padding-bottom:10px;">Añadir Nueva Reseña Manualmente</h2>
                    <form id="expotodo-admin-manual-review-form" enctype="multipart/form-data">
                        <?php wp_nonce_field('expotodo_add_manual', '_ajax_nonce'); ?>
                        
                        <div style="margin-bottom:15px;">
                            <label style="display:block; font-weight:bold; margin-bottom:5px;">Nombre <span style="color:red;">*</span></label>
                            <input type="text" name="name" required style="width:100%; height:32px; padding: 4px 8px;">
                        </div>
                        <div style="margin-bottom:15px;">
                            <label style="display:block; font-weight:bold; margin-bottom:5px;">Correo <span style="color:red;">*</span></label>
                            <input type="text" name="email" required style="width:100%; height:32px; padding: 4px 8px;">
                        </div>
                        <div style="margin-bottom:15px;">
                            <label style="display:block; font-weight:bold; margin-bottom:5px;">Reseña <span style="color:red;">*</span></label>
                            <textarea name="review" required rows="4" style="width:100%; padding: 6px 8px; resize:vertical;"></textarea>
                        </div>
                        <div style="margin-bottom:15px;">
                            <label style="display:block; font-weight:bold; margin-bottom:5px;">Foto (Opcional)</label>
                            <input type="file" name="review_photo" accept="image/*" style="width:100%;">
                        </div>
                        <div style="margin-bottom:15px;">
                            <label style="display:block; font-weight:bold; margin-bottom:5px;">Estado Inicial</label>
                            <select name="status" style="width:100%; height:32px;">
                                <option value="active">Activo (Publicado)</option>
                                <option value="inactive">Inactivo (Pendiente)</option>
                            </select>
                        </div>
                        
                        <div id="admin-modal-response" style="margin-bottom:15px; display:none; padding:10px; border-radius:3px;"></div>
                        
                        <div style="text-align:right; border-top:1px solid #eee; padding-top:15px;">
                            <button type="button" class="button expotodo-admin-modal-cancel" style="margin-right:10px; height:32px;">Cancelar</button>
                            <button type="submit" class="button button-primary btn-save-admin-review" style="height:32px;">Guardar Reseña</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Modal Administrativo de WordPress para Editar Reseñas -->
            <div id="expotodo-admin-edit-review-modal" style="display:none; position:fixed; z-index:99999; left:0; top:0; width:100%; height:100%; overflow:auto; background-color:rgba(0,0,0,0.5); align-items:center; justify-content:center;">
                <div style="background-color:#fff; margin:auto; padding:25px; border:1px solid #ccd0d4; width:500px; border-radius:4px; box-shadow:0 4px 20px rgba(0,0,0,0.15); position:relative; box-sizing:border-box;">
                    <span class="expotodo-admin-edit-modal-close" style="color:#aaa; float:right; font-size:28px; font-weight:bold; cursor:pointer; line-height:1; position:absolute; right:15px; top:10px;">&times;</span>
                    <h2 style="margin-top:0; border-bottom:1px solid #eee; padding-bottom:10px;">Editar Reseña</h2>
                    <form id="expotodo-admin-edit-review-form" enctype="multipart/form-data">
                        <?php wp_nonce_field('expotodo_edit_manual', '_ajax_nonce'); ?>
                        <input type="hidden" name="review_id" id="edit-review-id">
                        
                        <div style="margin-bottom:15px;">
                            <label style="display:block; font-weight:bold; margin-bottom:5px;">Nombre <span style="color:red;">*</span></label>
                            <input type="text" name="name" id="edit-review-name" required style="width:100%; height:32px; padding: 4px 8px;">
                        </div>
                        <div style="margin-bottom:15px;">
                            <label style="display:block; font-weight:bold; margin-bottom:5px;">Correo <span style="color:red;">*</span></label>
                            <input type="text" name="email" id="edit-review-email" required style="width:100%; height:32px; padding: 4px 8px;">
                        </div>
                        <div style="margin-bottom:15px;">
                            <label style="display:block; font-weight:bold; margin-bottom:5px;">Reseña <span style="color:red;">*</span></label>
                            <textarea name="review" id="edit-review-text" required rows="4" style="width:100%; padding: 6px 8px; resize:vertical;"></textarea>
                        </div>
                        <div style="margin-bottom:15px;">
                            <label style="display:block; font-weight:bold; margin-bottom:5px;">Foto (Opcional - Selecciona una nueva para reemplazar la actual)</label>
                            <input type="file" name="review_photo" accept="image/*" style="width:100%;">
                        </div>
                        <div style="margin-bottom:15px;">
                            <label style="display:block; font-weight:bold; margin-bottom:5px;">Estado</label>
                            <select name="status" id="edit-review-status" style="width:100%; height:32px;">
                                <option value="active">Activo (Publicado)</option>
                                <option value="inactive">Inactivo (Pendiente)</option>
                            </select>
                        </div>
                        
                        <div id="admin-edit-modal-response" style="margin-bottom:15px; display:none; padding:10px; border-radius:3px;"></div>
                        
                        <div style="text-align:right; border-top:1px solid #eee; padding-top:15px;">
                            <button type="button" class="button expotodo-admin-edit-modal-cancel" style="margin-right:10px; height:32px;">Cancelar</button>
                            <button type="submit" class="button button-primary btn-save-admin-edit-review" style="height:32px;">Guardar Cambios</button>
                        </div>
                    </form>
                </div>
            </div>

            <table class="wp-list-table widefat fixed striped" style="margin-top: 20px;">
                <thead>
                    <tr>
                        <th width="80">Foto</th>
                        <th width="180">Cliente</th>
                        <th>Reseña</th>
                        <th width="150">Fecha de Creación</th>
                        <th width="120">Estado</th>
                        <th width="180">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($reviews) : foreach ($reviews as $rev) : ?>
                        <tr>
                            <td style="vertical-align: middle; text-align: center;">
                                <?php if ($rev->photo) : ?>
                                    <img src="<?php echo esc_url($rev->photo); ?>" alt="<?php echo esc_attr($rev->name); ?>" style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover; border: 1px solid #ddd;">
                                <?php else : ?>
                                    <div style="width: 50px; height: 50px; border-radius: 50%; background: #4e73df; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 16px; margin: 0 auto;">
                                        <?php 
                                        $initials = '';
                                        $words = explode(' ', $rev->name);
                                        foreach ($words as $w) {
                                            $initials .= strtoupper(substr($w, 0, 1));
                                            if (strlen($initials) >= 2) break;
                                        }
                                        echo esc_html($initials ? $initials : '?');
                                        ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td style="vertical-align: middle;">
                                <strong><?php echo esc_html($rev->name); ?></strong><br>
                                <small class="text-muted"><?php echo esc_html($rev->email); ?></small>
                            </td>
                            <td style="vertical-align: middle;">
                                <?php echo nl2br(wp_kses($rev->review, array('br' => array(), 'p' => array()))); ?>
                            </td>
                            <td style="vertical-align: middle;">
                                <?php echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($rev->created_at)); ?>
                            </td>
                            <td style="vertical-align: middle;">
                                <?php if ($rev->status === 'active') : ?>
                                    <span class="badge" style="background: #1cc88a; color: #fff; padding: 4px 8px; border-radius: 4px; font-weight: bold; font-size: 11px;">Activo</span>
                                <?php else : ?>
                                    <span class="badge" style="background: #e74a3b; color: #fff; padding: 4px 8px; border-radius: 4px; font-weight: bold; font-size: 11px;">Inactivo</span>
                                <?php endif; ?>
                            </td>
                            <td style="vertical-align: middle;">
                                <div style="display: flex; flex-direction: column; gap: 5px;">
                                    <div style="display: flex; gap: 5px;">
                                        <!-- Botón de Activar / Desactivar -->
                                        <?php 
                                        $toggle_nonce = wp_create_nonce('expotodo_toggle_review_' . $rev->id);
                                        $toggle_url = admin_url('admin.php?page=expotodo-reviews&action=toggle_review_status&id=' . $rev->id . '&_wpnonce=' . $toggle_nonce);
                                        ?>
                                        <a href="<?php echo $toggle_url; ?>" class="button button-small <?php echo ($rev->status === 'active') ? 'button-secondary' : 'button-primary'; ?>" style="flex: 1; text-align: center;">
                                            <?php echo ($rev->status === 'active') ? 'Desactivar' : 'Activar'; ?>
                                        </a>

                                        <!-- Botón de Editar -->
                                        <button class="button button-small btn-edit-review" 
                                                data-id="<?php echo esc_attr($rev->id); ?>"
                                                data-name="<?php echo esc_attr($rev->name); ?>"
                                                data-email="<?php echo esc_attr($rev->email); ?>"
                                                data-review="<?php echo esc_attr($rev->review); ?>"
                                                data-status="<?php echo esc_attr($rev->status); ?>"
                                                style="background: #f1f5f9; color: #1e293b; border: 1px solid #cbd5e1; padding: 3px 8px; border-radius: 3px; cursor: pointer; line-height: 20px;">
                                            Editar
                                        </button>

                                        <!-- Botón de Eliminar -->
                                        <?php 
                                        $delete_nonce = wp_create_nonce('expotodo_delete_review_' . $rev->id);
                                        $delete_url = admin_url('admin.php?page=expotodo-reviews&action=delete_review&id=' . $rev->id . '&_wpnonce=' . $delete_nonce);
                                        ?>
                                        <a href="<?php echo $delete_url; ?>" class="button button-link-delete button-small" onclick="return confirm('¿Estás seguro de que deseas eliminar permanentemente esta reseña?');" style="color: #a00; border: 1px solid #ccd0d4; padding: 3px 8px; border-radius: 3px; text-decoration: none; text-align: center; line-height: 20px;">
                                            Eliminar
                                        </a>
                                    </div>
                                    
                                    <!-- Formulario para subir/cambiar foto -->
                                    <form method="post" enctype="multipart/form-data" style="margin-top: 5px; border-top: 1px solid #eee; padding-top: 5px; display: flex; gap: 5px; align-items: center;">
                                        <?php wp_nonce_field('expotodo_upload_photo_' . $rev->id); ?>
                                        <input type="hidden" name="review_id" value="<?php echo esc_attr($rev->id); ?>">
                                        <input type="file" name="review_photo" accept="image/*" style="font-size: 10px; width: 100px;" required>
                                        <input type="submit" name="expotodo_upload_admin_photo" class="button button-small" value="Subir" style="font-size: 10px; height: 22px; line-height: 20px; padding: 0 6px;">
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; else : ?>
                        <tr><td colspan="6" style="text-align: center;">No se encontraron reseñas registradas en el sistema.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <script>
        jQuery(document).ready(function($) {
            var modal = $('#expotodo-admin-review-modal');
            var form = $('#expotodo-admin-manual-review-form');
            var responseDiv = $('#admin-modal-response');
            var submitBtn = $('.btn-save-admin-review');

            // Abrir Modal
            $('#btn-open-review-modal').on('click', function(e) {
                e.preventDefault();
                modal.css('display', 'flex');
                form[0].reset();
                responseDiv.hide().empty();
            });

            // Cerrar Modal
            $('.expotodo-admin-modal-close, .expotodo-admin-modal-cancel').on('click', function(e) {
                e.preventDefault();
                modal.hide();
            });

            // Cerrar al hacer clic fuera del contenido
            $(window).on('click', function(e) {
                if ($(e.target).is(modal)) {
                    modal.hide();
                }
            });

            // Guardar por AJAX
            form.on('submit', function(e) {
                e.preventDefault();
                var formData = new FormData(this);
                formData.append('action', 'expotodo_add_manual_review_ajax');

                responseDiv.hide().empty();
                submitBtn.prop('disabled', true).text('Guardando...');

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            responseDiv.css({'background-color': '#e2f0d9', 'color': '#385723', 'border': '1px solid #c5e0b4'})
                                       .html('<strong>' + response.data.message + '</strong>').fadeIn();
                            setTimeout(function() {
                                modal.hide();
                                window.location.reload();
                            }, 1000);
                        } else {
                            responseDiv.css({'background-color': '#fce4d6', 'color': '#c00000', 'border': '1px solid #f8cbad'})
                                       .html('<strong>' + (response.data.message || 'Error al guardar.') + '</strong>').fadeIn();
                            submitBtn.prop('disabled', false).text('Guardar Reseña');
                        }
                    },
                    error: function() {
                        responseDiv.css({'background-color': '#fce4d6', 'color': '#c00000', 'border': '1px solid #f8cbad'})
                                   .html('<strong>Error de conexión con el servidor.</strong>').fadeIn();
                        submitBtn.prop('disabled', false).text('Guardar Reseña');
                    }
                });
            });

            // Lógica del Modal de Edición
            var editModal = $('#expotodo-admin-edit-review-modal');
            var editForm = $('#expotodo-admin-edit-review-form');
            var editResponseDiv = $('#admin-edit-modal-response');
            var editSubmitBtn = $('.btn-save-admin-edit-review');

            // Abrir Edit Modal al hacer clic en Editar
            $('.btn-edit-review').on('click', function(e) {
                e.preventDefault();
                var id = $(this).data('id');
                var name = $(this).data('name');
                var email = $(this).data('email');
                var review = $(this).data('review');
                var status = $(this).data('status');

                $('#edit-review-id').val(id);
                $('#edit-review-name').val(name);
                $('#edit-review-email').val(email);
                $('#edit-review-text').val(review);
                $('#edit-review-status').val(status);

                editResponseDiv.hide().empty();
                editModal.css('display', 'flex');
            });

            // Cerrar Edit Modal
            $('.expotodo-admin-edit-modal-close, .expotodo-admin-edit-modal-cancel').on('click', function(e) {
                e.preventDefault();
                editModal.hide();
            });

            // Cerrar Edit Modal al hacer clic fuera del contenido
            $(window).on('click', function(e) {
                if ($(e.target).is(editModal)) {
                    editModal.hide();
                }
            });

            // Guardar cambios de edición por AJAX
            editForm.on('submit', function(e) {
                e.preventDefault();
                var formData = new FormData(this);
                formData.append('action', 'expotodo_edit_review_ajax');

                editResponseDiv.hide().empty();
                editSubmitBtn.prop('disabled', true).text('Guardando...');

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            editResponseDiv.css({'background-color': '#e2f0d9', 'color': '#385723', 'border': '1px solid #c5e0b4'})
                                           .html('<strong>' + response.data.message + '</strong>').fadeIn();
                            setTimeout(function() {
                                editModal.hide();
                                window.location.reload();
                            }, 1000);
                        } else {
                            editResponseDiv.css({'background-color': '#fce4d6', 'color': '#c00000', 'border': '1px solid #f8cbad'})
                                           .html('<strong>' + (response.data.message || 'Error al actualizar.') + '</strong>').fadeIn();
                            editSubmitBtn.prop('disabled', false).text('Guardar Cambios');
                        }
                    },
                    error: function() {
                        editResponseDiv.css({'background-color': '#fce4d6', 'color': '#c00000', 'border': '1px solid #f8cbad'})
                                       .html('<strong>Error de conexión con el servidor.</strong>').fadeIn();
                        editSubmitBtn.prop('disabled', false).text('Guardar Cambios');
                    }
                });
            });
        });
        </script>
        <?php
    }
}
