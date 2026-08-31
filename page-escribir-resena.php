<?php
/*
Template Name: Escribir Reseña
*/
get_header();
?>

<main class="flex-grow-1" style="background: linear-gradient(135deg, #f5f7fa 0%, #e4e8f0 100%); min-height: 80vh; display: flex; align-items: center; padding: 60px 0;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-7 col-md-10">
                <!-- Tarjeta del Formulario con efecto Premium Glassmorphism -->
                <div class="review-form-card p-5 rounded-4 shadow-lg bg-white border" style="border-color: rgba(255,255,255,0.8); backdrop-filter: blur(10px);">
                    <div class="text-center mb-5">
                        <div class="icon-wrap mb-3" style="display: inline-flex; align-items: center; justify-content: center; width: 80px; height: 80px; border-radius: 50%; background: rgba(78, 115, 223, 0.1); color: #4e73df; font-size: 35px;">
                            <i class="fas fa-comment-dots"></i>
                        </div>
                        <h1 class="h2 fw-bold text-dark mb-2">Cuéntanos tu Experiencia</h1>
                        <p class="text-muted">Tu opinión es de gran valor para ayudarnos a mejorar y seguir ofreciendo el mejor servicio.</p>
                    </div>

                    <form id="expotodo-public-review-form" class="needs-validation" novalidate enctype="multipart/form-data">
                        <?php wp_nonce_field('expotodo_public_review_nonce'); ?>
                        
                        <div class="row g-4">
                            <!-- Nombre completo -->
                            <div class="col-12">
                                <label for="client_name" class="form-label fw-semibold text-dark">Nombre Completo <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-user text-muted"></i></span>
                                    <input type="text" class="form-control bg-light border-start-0" name="name" id="client_name" placeholder="Ej. Juan Pérez" required style="padding: 12px;">
                                </div>
                            </div>

                            <!-- Correo electrónico -->
                            <div class="col-12">
                                <label for="client_email" class="form-label fw-semibold text-dark">Correo Electrónico <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-envelope text-muted"></i></span>
                                    <input type="text" class="form-control bg-light border-start-0" name="email" id="client_email" placeholder="juan.perez@ejemplo.com o ****@****.com" required style="padding: 12px;">
                                </div>
                                <div class="form-text text-muted small">Tu correo no será publicado; solo lo usaremos para verificar tu compra.</div>
                            </div>

                            <!-- Opinión -->
                            <div class="col-12">
                                <label for="client_review" class="form-label fw-semibold text-dark">Tu Opinión / Reseña <span class="text-danger">*</span></label>
                                <textarea class="form-control bg-light" name="review" id="client_review" rows="5" placeholder="Escribe aquí tu experiencia con nuestro servicio, tiempos de envío, empaque o atención..." required style="padding: 15px; border-radius: 8px;"></textarea>
                            </div>

                            <!-- Foto del cliente -->
                            <div class="col-12">
                                <label for="client_photo" class="form-label fw-semibold text-dark">Foto de Perfil (Opcional)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-camera text-muted"></i></span>
                                    <input type="file" class="form-control bg-light border-start-0" name="photo" id="client_photo" accept="image/*" style="padding: 12px;">
                                </div>
                                <div class="form-text text-muted small">Sube una foto tuya o de tu empresa para darle un rostro real a tu testimonio.</div>
                            </div>

                            <!-- Botón de Envío -->
                            <div class="col-12 mt-4">
                                <button type="submit" class="btn btn-primary btn-lg w-100 py-3 fw-bold shadow-sm" style="background: #4e73df; border: none; border-radius: 8px; transition: all 0.3s ease;">
                                    <span class="btn-text">Enviar mi Reseña</span>
                                    <span class="btn-loading d-none"><i class="fas fa-spinner fa-spin me-2"></i> Enviando...</span>
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- Mensajes de Respuesta -->
                    <div id="review-response" class="mt-4"></div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
jQuery(document).ready(function($) {
    $('#expotodo-public-review-form').on('submit', function(e) {
        e.preventDefault();
        
        var $form = $(this);
        var $btn = $form.find('button[type="submit"]');
        var $btnText = $btn.find('.btn-text');
        var $btnLoading = $btn.find('.btn-loading');
        var $response = $('#review-response');

        if (!$form[0].checkValidity()) {
            $form.addClass('was-validated');
            return;
        }

        // Preparar FormData para subir archivos vía AJAX
        var formData = new FormData($form[0]);
        formData.append('action', 'expotodo_submit_review');

        // Reset estado anterior
        $response.empty();
        $btn.prop('disabled', true);
        $btnText.addClass('d-none');
        $btnLoading.removeClass('d-none');

        $.ajax({
            url: '<?php echo admin_url("admin-ajax.php"); ?>',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    $response.html(
                        '<div class="alert alert-success d-flex align-items-center rounded-3 p-4 border-0 shadow-sm" style="background: rgba(28, 200, 138, 0.1); color: #1cc88a;">' +
                        '<i class="fas fa-check-circle me-3" style="font-size: 24px;"></i>' +
                        '<div>' + response.data.message + '</div>' +
                        '</div>'
                    );
                    $form.slideUp(400);
                } else {
                    $response.html(
                        '<div class="alert alert-danger d-flex align-items-center rounded-3 p-4 border-0 shadow-sm" style="background: rgba(231, 74, 59, 0.1); color: #e74a3b;">' +
                        '<i class="fas fa-exclamation-circle me-3" style="font-size: 24px;"></i>' +
                        '<div>' + (response.data.message || 'Ocurrió un error inesperado.') + '</div>' +
                        '</div>'
                    );
                    $btn.prop('disabled', false);
                    $btnText.removeClass('d-none');
                    $btnLoading.addClass('d-none');
                }
            },
            error: function() {
                $response.html(
                    '<div class="alert alert-danger d-flex align-items-center rounded-3 p-4 border-0 shadow-sm" style="background: rgba(231, 74, 59, 0.1); color: #e74a3b;">' +
                    '<i class="fas fa-wifi me-3" style="font-size: 24px;"></i>' +
                    '<div>Error de conexión. Por favor, comprueba tu internet e intenta de nuevo.</div>' +
                    '</div>'
                );
                $btn.prop('disabled', false);
                $btnText.removeClass('d-none');
                $btnLoading.addClass('d-none');
            }
        });
    });
});
</script>

<?php get_footer(); ?>
