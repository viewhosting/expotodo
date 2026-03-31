jQuery(document).ready(function ($) {
    'use strict';

    const $form = $('#expotodo-contact-form');
    const $submitBtn = $form.find('button[type="submit"]');

    $form.on('submit', function (e) {
        e.preventDefault();

        // Validar formulario (Bootstrap)
        if (!this.checkValidity()) {
            e.stopPropagation();
            $(this).addClass('was-validated');
            return;
        }

        const formData = new FormData(this);
        formData.append('action', 'expotodo_send_contact');
        formData.append('nonce', expotodo_contact_params.nonce);

        // UI Loading state
        $submitBtn.prop('disabled', true);
        $submitBtn.find('.btn-text').addClass('d-none');
        $submitBtn.find('.btn-loading').removeClass('d-none');

        $.ajax({
            url: expotodo_contact_params.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (res) {
                if (res.success) {
                    if (window.showToast) {
                        window.showToast('¡Éxito!', res.data, 'success');
                    } else {
                        alert(res.data);
                    }
                    $form[0].reset();
                    $form.removeClass('was-validated');
                } else {
                    if (window.showToast) {
                        window.showToast('Error', res.data, 'error');
                    } else {
                        alert('Error: ' + res.data);
                    }
                }
            },
            error: function () {
                if (window.showToast) {
                    window.showToast('Error', 'Ocurrió un error en el servidor. Inténtalo de nuevo más tarde.', 'error');
                } else {
                    alert('Ocurrió un error en el servidor.');
                }
            },
            complete: function () {
                $submitBtn.prop('disabled', false);
                $submitBtn.find('.btn-text').removeClass('d-none');
                $submitBtn.find('.btn-loading').addClass('d-none');
            }
        });
    });
});
