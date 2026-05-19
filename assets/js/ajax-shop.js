jQuery(document).ready(function ($) {
    var currentPage = 1;
    var isLoading = false;
    var maxPages = $('#page-end').data('max-pages') || 999;
    var productContainer = $('#product-grid-container');
    var spinner = $('#loading-spinner');

    function fetchProducts(page, append) {
        append = typeof append !== 'undefined' ? append : false;

        if (isLoading) return;
        if (page > maxPages && maxPages !== 999) return;

        isLoading = true;
        spinner.removeClass('d-none');

        // Collect Filters
        var categories = [];

        // Mode 1: Checkboxes (archive-product.php)
        if ($('.filter-category').length > 0) {
            $('.filter-category:checked').each(function () {
                categories.push($(this).val());
            });
            if ($('#cat-all').is(':checked') || categories.length === 0) {
                categories = ['all'];
            }
        }
        // Mode 2: Radios (page-lista-productos.php)
        else if ($('.filter-category-radio').length > 0) {
            categories = [$('.filter-category-radio:checked').val()];
        }

        // Price Logic
        var minPrice = 0;
        var maxPrice = 9999999;
        var priceRange = '';

        if ($('#price-filter').length > 0) {
            priceRange = $('#price-filter').val();
        } else if ($('#priceMin').length > 0) {
            minPrice = $('#priceMin').val() || 0;
            maxPrice = $('#priceMax').val() || 9999999;
        }

        // Flags Logic
        var flags = [];
        $('.filter-flag:checked').each(function () {
            flags.push($(this).val());
        });

        $.ajax({
            url: expotodo_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'expotodo_filter_products',
                nonce: expotodo_ajax.nonce,
                page: page,
                categories: categories,
                price_range: priceRange,
                min_price: minPrice,
                max_price: maxPrice,
                flags: flags
            },
            success: function (response) {
                if (response.success) {
                    if (!append) {
                        productContainer.html(response.data.html);
                    } else {
                        productContainer.append(response.data.html);
                    }

                    maxPages = response.data.max_pages;

                    if (response.data.html.trim() === '' && !append) {
                        productContainer.html('<div class="col-12 text-center py-5">No se encontraron productos.</div>');
                    }
                }
            },
            complete: function () {
                isLoading = false;
                spinner.addClass('d-none');
            }
        });
    }

    // Event Listeners: Categories
    $(document).on('change', '.filter-category, .filter-category-radio', function () {
        // UI Sync for checkboxes
        if ($(this).hasClass('filter-category')) {
            var val = $(this).val();
            if (val === 'all') {
                if ($(this).is(':checked')) $('.filter-category').not(this).prop('checked', false);
            } else {
                if ($(this).is(':checked')) $('#cat-all').prop('checked', false);
            }
            if ($('.filter-category:checked').length === 0) $('#cat-all').prop('checked', true);
        }

        currentPage = 1;
        maxPages = 999;
        fetchProducts(currentPage, false);
    });

    // Event Listeners: Flags
    $(document).on('change', '.filter-flag', function () {
        currentPage = 1;
        maxPages = 999;
        fetchProducts(currentPage, false);
    });

    // Event Listeners: Price
    $(document).on('change', '#price-filter', function () {
        currentPage = 1;
        maxPages = 999;
        fetchProducts(currentPage, false);
    });

    $(document).on('click', '#btnFilterPrice', function (e) {
        e.preventDefault();
        currentPage = 1;
        maxPages = 999;
        fetchProducts(currentPage, false);
    });

    // Infinite Scroll (Intersection Observer)
    if (typeof IntersectionObserver !== 'undefined') {
        var observer = new IntersectionObserver(function (entries) {
            if (entries[0].isIntersecting && !isLoading) {
                if (currentPage < maxPages) {
                    currentPage++;
                    fetchProducts(currentPage, true);
                }
            }
        }, {
            rootMargin: '400px'
        });

        var pageEnd = document.getElementById('page-end');
        if (pageEnd) {
            observer.observe(pageEnd);
        }
    } else {
        // Fallback for very old browsers: window scroll
        $(window).on('scroll', function () {
            if ($(window).scrollTop() + $(window).height() > $(document).height() - 400) {
                if (!isLoading && currentPage < maxPages) {
                    currentPage++;
                    fetchProducts(currentPage, true);
                }
            }
        });
    }
});
