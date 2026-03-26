jQuery(document).ready(function($) {
    const productGrid = $('#product-grid-container');
    
    // Selectors for page-lista-productos.php
    const categoryRadios = $('.filter-category-radio');
    const priceMin = $('#priceMin');
    const priceMax = $('#priceMax');
    const btnFilterPrice = $('#btnFilterPrice');
    
    // Selectors for archive-product.php
    const categoryCheckboxes = $('.filter-category');
    const priceSelect = $('#price-filter');

    // Common selectors
    const flagCheckboxes = $('.filter-flag');

    function fetchProducts() {
        let category = null;
        let min_price = 0;
        let max_price = 9999999;
        const flags = [];
        
        // --- Category Logic ---
        if (categoryRadios.length) {
            // Radio button mode (page-lista-productos.php)
            category = categoryRadios.filter(':checked').val();
        } else if (categoryCheckboxes.length) {
            // Checkbox mode (archive-product.php)
            const checked = categoryCheckboxes.filter(':checked');
            // If "all" is checked or nothing checked, send 'all' or empty
            if (checked.filter('[value="all"]').length > 0) {
                 category = 'all';
            } else {
                 category = checked.map(function() { return $(this).val(); }).get();
            }
        }

        // --- Price Logic ---
        if (priceMin.length && priceMax.length) {
            // Input mode
             const min = parseFloat(priceMin.val());
             const max = parseFloat(priceMax.val());
             if (!isNaN(min)) min_price = min;
             if (!isNaN(max)) max_price = max;
        } else if (priceSelect.length) {
            // Select mode (Ranges)
            const range = priceSelect.val();
            if (range === 'low') { max_price = 300; }
            else if (range === 'mid') { min_price = 300; max_price = 600; }
            else if (range === 'high') { min_price = 600; }
        }

        // --- Flags Logic ---
        flagCheckboxes.each(function() {
            if ($(this).is(':checked')) {
                flags.push($(this).val());
            }
        });

        // Loading state
        productGrid.css('opacity', '0.5');

        $.ajax({
            url: expotodo_params.ajax_url,
            type: 'POST',
            data: {
                action: 'expotodo_filter_products',
                category: category,
                min_price: min_price,
                max_price: max_price,
                flags: flags
            },
            success: function(response) {
                if (response.success) {
                    productGrid.html(response.data.html);
                } else {
                    productGrid.html('<div class="col-12"><p class="text-center py-5">Error al cargar productos.</p></div>');
                }
            },
            error: function() {
                productGrid.html('<div class="col-12"><p class="text-center py-5">Error de conexión.</p></div>');
            },
            complete: function() {
                productGrid.css('opacity', '1');
            }
        });
    }

    // Event Listeners
    if (categoryRadios.length) {
        categoryRadios.on('change', fetchProducts);
    }
    
    if (categoryCheckboxes.length) {
        categoryCheckboxes.on('change', function() {
            // Basic logic: if "all" is clicked, uncheck others. If other clicked, uncheck "all".
            if ($(this).val() === 'all' && $(this).is(':checked')) {
                categoryCheckboxes.not(this).prop('checked', false);
            } else if ($(this).val() !== 'all' && $(this).is(':checked')) {
                categoryCheckboxes.filter('[value="all"]').prop('checked', false);
            }
            // If nothing checked, check "all" (optional UX, but good for consistency)
            if (categoryCheckboxes.filter(':checked').length === 0) {
                categoryCheckboxes.filter('[value="all"]').prop('checked', true);
            }
            
            fetchProducts();
        });
    }

    if (flagCheckboxes.length) {
        flagCheckboxes.on('change', fetchProducts);
    }

    if (btnFilterPrice.length) {
        btnFilterPrice.on('click', function(e) {
            e.preventDefault();
            fetchProducts();
        });
    }
    
    if (priceSelect.length) {
        priceSelect.on('change', fetchProducts);
    }
});
