// ==========================================
// GLOBAL LOGIN HANDLER
// ==========================================
window.expotodo_handle_login = function (btn, e) {
    if (e) e.preventDefault();

    var $ = jQuery;
    var $btn = $(btn);
    var $form = $btn.closest('form');

    // Support both ID based (sidebar) and class based (main page) message containers
    var $msg = $form.find('#login-message');
    if ($msg.length === 0) {
        $msg = $form.find('.login-message');
    }

    var $spinner = $btn.find('.spinner-border');

    console.log('Login attempt started for form:', $form.attr('id'));

    if (typeof expotodo_globals === 'undefined') {
        console.error('expotodo_globals is not defined');
        $msg.text('Error de configuración del sitio.').addClass('text-danger');
        return;
    }

    $msg.text('').removeClass('text-success text-danger');
    $btn.prop('disabled', true);

    // Show spinner
    if ($spinner.length) {
        $spinner.removeClass('d-none');
    } else {
        // Add spinner if it doesn't exist
        var spinnerHtml = '<span class="spinner-border spinner-border-sm d-none me-2" role="status" aria-hidden="true"></span>';
        $btn.prepend(spinnerHtml);
        $btn.find('.spinner-border').removeClass('d-none');
    }

    var formData = {
        action: 'expotodo_login',
        username: $form.find('input[name="username"]').val(),
        password: $form.find('input[name="password"]').val(),
        security: expotodo_globals.login_nonce
    };

    $.ajax({
        url: expotodo_globals.ajax_url,
        type: 'POST',
        data: formData,
        success: function (response) {
            console.log('Login response:', response);
            if (response.success) {
                $msg.html(response.data.message).addClass('text-success');
                // Reload page if on my-account or if redirect url matches current
                if (window.location.href.indexOf('cuenta') > -1 || window.location.href === expotodo_globals.redirect_url) {
                    window.location.reload();
                } else {
                    window.location.href = expotodo_globals.redirect_url;
                }
            } else {
                $msg.html(response.data.message).addClass('text-danger');
                $btn.prop('disabled', false);
                $btn.find('.spinner-border').addClass('d-none');
            }
        },
        error: function (xhr, status, error) {
            console.error('Login error:', error);
            console.error('Response:', xhr.responseText);
            $msg.html('Error de conexión. Inténtalo de nuevo.').addClass('text-danger');
            $btn.prop('disabled', false);
            $btn.find('.spinner-border').addClass('d-none');
        }
    });
};

// ==========================================
// PROFILE SAVE HANDLER
// ==========================================
window.expotodo_save_profile = function (btn, e) {
    if (e) e.preventDefault();

    var $ = jQuery;
    var $btn = $(btn);
    var $form = $btn.closest('form');
    var $msg = $form.find('.profile-message');

    // Reset message
    $msg.text('').removeClass('text-success text-danger');
    $btn.prop('disabled', true);

    // Show spinner
    var originalText = $btn.html(); // Save HTML to preserve icon if exists
    // Simple loading state
    $btn.html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Guardando...');

    var formData = {
        action: 'expotodo_update_profile',
        first_name: $form.find('input[name="first_name"]').val(),
        last_name: $form.find('input[name="last_name"]').val(),
        security: expotodo_globals.profile_nonce
    };

    $.ajax({
        url: expotodo_globals.ajax_url,
        type: 'POST',
        data: formData,
        success: function (response) {
            console.log('Profile update response:', response);
            if (response.success) {
                $msg.html(response.data.message).addClass('text-success');
            } else {
                $msg.html(response.data.message).addClass('text-danger');
            }
        },
        error: function (xhr, status, error) {
            console.error('Profile update error:', error);
            $msg.html('Error de conexión. Inténtalo de nuevo.').addClass('text-danger');
        },
        complete: function () {
            $btn.prop('disabled', false);
            $btn.html(originalText);
        }
    });
};
