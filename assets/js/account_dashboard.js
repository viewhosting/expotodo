/**
 * Account Dashboard Boutique Scripts
 * Manejo de notificaciones (Toasts) y transiciones dinámicas en Mi Cuenta
 */

(function ($) {
    'use strict';

    $(document).ready(function () {

        // Crear contenedor de Toasts si no existe
        if ($('#expotodo-toast-container').length === 0) {
            $('body').append('<div id="expotodo-toast-container"></div>');
        }

        /**
         * Función Global para Mostrar Notificaciones Premium
         * @param {string} message - El mensaje a mostrar
         * @param {string} type - success, error, info
         */
        window.expotodo_show_toast = function (message, type = 'success') {
            const icon = type === 'success' ? 'fa-check-circle' : (type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle');
            const toast = $(`
                <div class="boutique-toast ${type}">
                    <div class="toast-icon"><i class="fas ${icon}"></i></div>
                    <div class="toast-content">${message}</div>
                </div>
            `);

            $('#expotodo-toast-container').append(toast);

            // Eliminar automáticamente después de la animación
            setTimeout(() => {
                toast.fadeOut(600, function () { $(this).remove(); });
            }, 3800);
        };

        // Verificar si hay Toasts pendientes después de una recarga de página
        const pendingToast = localStorage.getItem('expotodo_pending_toast');
        if (pendingToast) {
            const data = JSON.parse(pendingToast);
            window.expotodo_show_toast(data.message, data.type);
            localStorage.removeItem('expotodo_pending_toast');
        }

        // Escuchar clics en tarjetas del Dashboard para cambiar de pestaña
        $('.stat-card').on('click', function (e) {
            const target = $(this).attr('href');
            if (target && target.startsWith('#')) {
                e.preventDefault();
                const tabLink = $(`[href="${target}"]`);
                if (tabLink.length) {
                    bootstrap.Tab.getOrCreateInstance(tabLink[0]).show();
                    // Scroll suave al inicio del contenido
                    $('html, body').animate({
                        scrollTop: $('.tab-content').offset().top - 100
                    }, 500);
                }
            }
        });

        // Función para guardar perfil con Toast
        window.expotodo_save_profile_boutique = function (btn, event) {
            event.preventDefault();
            const $btn = $(btn);
            const form = $('#profile-form');
            const originalText = $btn.html();

            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i> Guardando...');

            $.ajax({
                url: expotodo_account_params.ajax_url,
                type: 'POST',
                data: form.serialize() + '&action=expotodo_update_profile&security=' + expotodo_account_params.nonce,
                success: function (response) {
                    if (response.success) {
                        window.expotodo_show_toast(response.data.message || 'Perfil actualizado con éxito.', 'success');
                    } else {
                        window.expotodo_show_toast(response.data.message || 'Error al actualizar.', 'error');
                    }
                    $btn.prop('disabled', false).html(originalText);
                },
                error: function () {
                    window.expotodo_show_toast('Error de conexión con el servidor.', 'error');
                    $btn.prop('disabled', false).html(originalText);
                }
            });
        };

        // Función para cambiar contraseña con Toast
        window.expotodo_change_password_boutique = function (btn, event) {
            event.preventDefault();
            const $btn = $(btn);
            const form = $('#password-form');
            const originalText = $btn.html();

            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i> Procesando...');

            $.ajax({
                url: expotodo_account_params.ajax_url,
                type: 'POST',
                data: form.serialize() + '&action=expotodo_change_password&security=' + expotodo_account_params.nonce,
                success: function (response) {
                    if (response.success) {
                        window.expotodo_show_toast(response.data || 'Contraseña actualizada.', 'success');
                        form[0].reset();
                    } else {
                        window.expotodo_show_toast(response.data || 'Error al cambiar contraseña.', 'error');
                    }
                    $btn.prop('disabled', false).html(originalText);
                },
                error: function () {
                    window.expotodo_show_toast('Error de conexión.', 'error');
                    $btn.prop('disabled', false).html(originalText);
                }
            });
        };

    });

})(jQuery);
