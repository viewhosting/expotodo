jQuery(document).ready(function($) {
    // 1. Lógica para "Quiere factura?"
    function toggleInvoiceFields() {
        if ($('#billing_invoice_required').is(':checked')) {
            $('.invoice-field').slideDown();
        } else {
            $('.invoice-field').slideUp();
        }
    }

    // Inicializar estado
    $('.invoice-field').hide();
    toggleInvoiceFields();

    // Listener
    $('body').on('change', '#billing_invoice_required', function() {
        toggleInvoiceFields();
    });

    // 2. Lógica para Ciudad dependiente del Estado (AJAX)
    
    function loadCities(state, targetSelector) {
        var $citySelect = $(targetSelector);
        
        // Mostrar cargando
        $citySelect.empty();
        $citySelect.append('<option value="">Cargando ciudades...</option>');
        $citySelect.prop('disabled', true);

        $.ajax({
            url: expotodo_checkout_params.ajax_url,
            type: 'POST',
            data: {
                action: 'expotodo_get_cities',
                state: state,
                nonce: expotodo_checkout_params.nonce
            },
            success: function(response) {
                $citySelect.empty();
                $citySelect.append('<option value="">Seleccione una ciudad</option>');
                
                if (response.success && response.data.length > 0) {
                    $.each(response.data, function(index, value) {
                        $citySelect.append('<option value="' + value + '">' + value + '</option>');
                    });
                } else {
                    // Fallback si no hay ciudades o error
                    if(state) {
                        $citySelect.append('<option value="Otra">Otra</option>');
                    }
                }
                
                $citySelect.prop('disabled', false);
                
                // Actualizar Select2 si existe
                if ($.fn.select2) {
                    $citySelect.trigger('change');
                }
            },
            error: function() {
                $citySelect.empty();
                $citySelect.append('<option value="">Error al cargar</option>');
                $citySelect.prop('disabled', false);
            }
        });
    }

    // Listener Billing State
    $('body').on('change', '#billing_state', function() {
        var state = $(this).val();
        loadCities(state, '#billing_city');
    });
    
    // Listener Shipping State
    $('body').on('change', '#shipping_state', function() {
        var state = $(this).val();
        loadCities(state, '#shipping_city');
    });

    // Cargar ciudades iniciales si hay estado seleccionado (por ejemplo al recargar con error)
    var initialBillingState = $('#billing_state').val();
    if (initialBillingState) {
        // Solo cargar si el select de ciudad está vacío o tiene valor por defecto
        if ($('#billing_city option').length <= 1) {
             loadCities(initialBillingState, '#billing_city');
        }
    }

    var initialShippingState = $('#shipping_state').val();
    if (initialShippingState) {
         if ($('#shipping_city option').length <= 1) {
             loadCities(initialShippingState, '#shipping_city');
        }
    }

});
