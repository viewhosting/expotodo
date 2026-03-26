jQuery(document).ready(function($) {
    let currentPage = 1;
    let isLoading = false;
    let maxPages = 999; // Assume more pages initially, updated by first AJAX response
    const productContainer = $('#product-grid-container');
    const spinner = $('#loading-spinner');
    
    // Initial max pages from PHP if available (could be added to localize script)
    // For now, we rely on the first pagination request to correct this if needed.

    function fetchProducts(page, append = false) {
        if (isLoading) return;
        // If we know we reached the end, stop
        if (page > maxPages && maxPages !== 999) return;

        isLoading = true;
        spinner.removeClass('d-none');

        // Collect Filters
        let categories = [];
        $('.filter-category:checked').each(function() {
            categories.push($(this).val());
        });
        
        // Handle "All" logic
        if ($('#cat-all').is(':checked')) {
            categories = ['all'];
        } else if (categories.length === 0) {
            // Fallback to all if nothing selected manually (though UI logic handles this)
            categories = ['all'];
        }

        const priceRange = $('#price-filter').val();

        $.ajax({
            url: expotodo_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'expotodo_filter_products',
                nonce: expotodo_ajax.nonce,
                page: page,
                categories: categories,
                price_range: priceRange
            },
            success: function(response) {
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
            complete: function() {
                isLoading = false;
                spinner.addClass('d-none');
            }
        });
    }

    // Filter Change Events
    $('.filter-category').on('change', function() {
        const val = $(this).val();
        
        if (val === 'all') {
            if ($(this).is(':checked')) {
                $('.filter-category').not(this).prop('checked', false);
            }
        } else {
            if ($(this).is(':checked')) {
                $('#cat-all').prop('checked', false);
            }
        }
        
        // If nothing is checked, check "All"
        if ($('.filter-category:checked').length === 0) {
            $('#cat-all').prop('checked', true);
        }

        currentPage = 1;
        maxPages = 999; // Reset max pages
        fetchProducts(currentPage, false);
    });

    $('#price-filter').on('change', function() {
        currentPage = 1;
        maxPages = 999; // Reset max pages
        fetchProducts(currentPage, false);
    });

    // Infinite Scroll (Intersection Observer)
    const observer = new IntersectionObserver((entries) => {
        if (entries[0].isIntersecting && !isLoading) {
            // Only load next page if we haven't reached the limit
            if (currentPage < maxPages) {
                currentPage++;
                fetchProducts(currentPage, true);
            }
        }
    }, {
        rootMargin: '200px' // Load before reaching bottom
    });

    const pageEnd = document.getElementById('page-end');
    if (pageEnd) {
        observer.observe(pageEnd);
    }
});
