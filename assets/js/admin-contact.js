/**
 * Manejo de Mensajería y Plantillas Pro para Expotodo (Admin)
 */
jQuery(document).ready(function ($) {

    // 1. ELIMINAR MENSAJE
    $('.btn-delete').on('click', function (e) {
        e.preventDefault();
        if (!confirm('¿Estás seguro de eliminar este mensaje boutique? Esta acción no se puede deshacer.')) return;

        const id = $(this).data('id');
        const row = $('#message-row-' + id);

        $.post(expotodo_admin_params.ajax_url, {
            action: 'expotodo_delete_message',
            id: id,
            security: expotodo_admin_params.nonce
        }, function (response) {
            if (response.success) {
                row.fadeOut(400, function () { $(this).remove(); });
            } else {
                alert('Error al eliminar el mensaje.');
            }
        });
    });

    // 2. ABRIR MODAL DE RESPUESTA
    $('.btn-reply').on('click', function (e) {
        e.preventDefault();
        const id = $(this).data('id');
        const email = $(this).data('email');
        const name = $(this).data('name');

        $('#replyEmail').val(email);
        $('#replyName').val(name);
        $('#replyTitle').text('Respondiendo a: ' + name + ' (' + email + ')');
        $('#replyContent').val('');
        $('#replyModal').fadeIn(300);
    });

    // 3. ENVIAR RESPUESTA VÍA AJAX
    $('#btn-send-reply').on('click', function (e) {
        const btn = $(this);
        const replyContent = $('#replyContent').val();

        if (!replyContent) {
            alert('Por favor, escribe una respuesta boutique.');
            return;
        }

        btn.prop('disabled', true).text('Enviando correo profesional...');

        $.post(expotodo_admin_params.ajax_url, {
            action: 'expotodo_reply_message',
            email: $('#replyEmail').val(),
            name: $('#replyName').val(),
            reply_content: replyContent,
            security: expotodo_admin_params.nonce
        }, function (response) {
            if (response.success) {
                alert('¡Respuesta enviada con éxito!');
                $('#replyModal').hide();
            } else {
                alert(response.data || 'Error al enviar la respuesta.');
            }
            btn.prop('disabled', false).text('Enviar Respuesta Profesional');
        });
    });

    // Cerrar modal al hacer clic fuera
    $(window).on('click', function (event) {
        if ($(event.target).is('#replyModal')) {
            $('#replyModal').hide();
        }
    });
});
