/**
 * Manejo de edición de direcciones mediante Modal AJAX para Expotodo
 */
jQuery(document).ready(function ($) {
    const modalElement = $('#addressModal');
    const modalContainer = $('#address-modal-container');
    const modalTitle = $('#addressModalLabel');

    // Al hacer clic en un botón de edición
    $('.btn-edit-address').on('click', function (e) {
        e.preventDefault();
        const addressType = $(this).data('address-type');
        const title = addressType === 'billing' ? 'Editar Facturación' : 'Editar Envío';

        modalTitle.text(title);
        modalContainer.html(`
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="mt-2 text-muted">Cargando formulario boutique...</p>
            </div>
        `);

        const modal = new bootstrap.Modal(modalElement[0]);
        modal.show();

        // Cargar el formulario vía AJAX
        $.ajax({
            url: expotodo_account_params.ajax_url,
            type: 'POST',
            data: {
                action: 'expotodo_get_address_form',
                address_type: addressType,
                security: expotodo_account_params.nonce
            },
            success: function (response) {
                if (response.success) {
                    modalContainer.html(response.data.html);

                    // Limpiar el formulario de WooCommerce de elementos innecesarios para el modal
                    modalContainer.find('h3').remove();
                    modalContainer.find('button[type="submit"]').addClass('btn btn-primary w-100 py-3 fw-bold mt-4').text('Cerrar y Guardar Cambios');

                    // Manejar el submit del formulario cargado
                    modalContainer.find('form').on('submit', function (e) {
                        e.preventDefault();
                        saveAddress($(this), addressType);
                    });
                } else {
                    modalContainer.html('<div class="alert alert-danger">Error al cargar el formulario.</div>');
                }
            }
        });
    });

    /**
     * Función para guardar la dirección vía AJAX
     */
    function saveAddress(form, addressType) {
        const btn = form.find('button[type="submit"]');
        const originalText = btn.text();

        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Guardando...');

        const formData = form.serializeArray();
        formData.push({ name: 'action', value: 'expotodo_save_address_ajax' });
        formData.push({ name: 'address_type', value: addressType });
        formData.push({ name: 'security', value: expotodo_account_params.nonce });

        $.ajax({
            url: expotodo_account_params.ajax_url,
            type: 'POST',
            data: formData,
            success: function (response) {
                if (response.success) {
                    btn.removeClass('btn-primary').addClass('btn-success').text('¡Guardado!');

                    // Guardar notificación para después de la recarga
                    localStorage.setItem('expotodo_pending_toast', JSON.stringify({
                        message: 'Dirección actualizada con éxito.',
                        type: 'success'
                    }));

                    setTimeout(() => {
                        bootstrap.Modal.getInstance(modalElement[0]).hide();
                        location.reload();
                    }, 1000);
                } else {
                    alert(response.data || 'Ocurrió un error al guardar.');
                    btn.prop('disabled', false).text(originalText);
                }
            },
            error: function () {
                alert('Error de conexión.');
                btn.prop('disabled', false).text(originalText);
            }
        });
    }
});
