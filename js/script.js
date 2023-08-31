jQuery(document).ready(function ($) {

    // Handle filters
    function handleFilters() {
		var postType = $('#embark-post-feed').data('post-type');
        var catFilter = $('.category-button.active').data('category-id');
        var orderFilter = $('#order-filter').val();
        var urlParams = new URLSearchParams(window.location.search);
		var linkedServicesFilter = $('#linked-services-filter').val();
    	var linkedProductsFilter = $('#linked-products-filter').val();
    	var industryFilter = $('#industry-filter').val();

        // Update URL parameters
        if (catFilter) {
            urlParams.set('cat', catFilter);
        } else {
            urlParams.delete('cat');
        }

        if (orderFilter) {
            urlParams.set('order', orderFilter);
        } else {
            urlParams.delete('order');
        }

        // Update the URL without reloading the page
        history.pushState(null, null, '?' + urlParams.toString());

        // Perform AJAX request to reload post feed
        $.ajax({
            url: ajax_object.ajax_url,
            data: {
                action: 'my_custom_post_feed',
                cat: catFilter,
                order: orderFilter,
				linked_services: linkedServicesFilter,
            	linked_products: linkedProductsFilter,
            	industry: industryFilter,
                paged: urlParams.get('paged') || 1,
				post_type: postType,
                count: 12,  // add your count here
                filters: 'true',  // add your filters value here
                pagination: 'true',  // add your pagination value here
            },
            success: function (response) {
                //$('#post-feed-container').html(response);
                $('#embark-post-feed').html(response);
                animatePosts();
            },
        });
    }

    // Handle category button click
    $(document).on('click', '.category-button', function () {
        // Remove the active class from all category buttons
        $('.category-button').removeClass('active');

        // Add the active class to the clicked button
        $(this).addClass('active');

        handleFilters();
    });

    // Handle order filter change
    $(document).on('change', '#order-filter', function () {
        handleFilters();
    });
	
	// Handle linked services filter change
	$(document).on('change', '#linked-services-filter', function () {
		handleFilters();
	});

	// Handle linked products filter change
	$(document).on('change', '#linked-products-filter', function () {
		handleFilters();
	});

	// Handle industry filter change
	$(document).on('change', '#industry-filter', function () {
		handleFilters();
	});
	
	// Handle the clear button click
	$(document).on('click', '#clear-filters', function () {
		$('#linked-services-filter').val('');
		$('#linked-products-filter').val('');
		$('#industry-filter').val('');
		// Here we trigger the change event to run the handleFilters function
		$('#linked-services-filter, #linked-products-filter, #industry-filter').trigger('change');
	});

    // Handle pagination
    $('body').on('click', '.pagination a', function (e) {
        e.preventDefault();
        var urlParams = new URLSearchParams(window.location.search);
        var page = $(this).data('page');
        urlParams.set('paged', page);
        history.pushState(null, null, '?' + urlParams.toString());
        handleFilters();
    });

    // Animate posts
    function animatePosts() {
        // Get all post-items
        var $postItems = $('.post-item');

        // Animate each post-item with a 100ms delay between them
        $postItems.each(function (index, element) {
            var $element = $(element);
            setTimeout(function () {
                $element.animate({
                    'opacity': 1,
                    'top': 0,
                }, 400); // Animation duration
            }, index * 100); // Delay between animations
        });
    }
    
    animatePosts();
});
