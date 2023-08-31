<?php
$shortcode_atts = get_query_var('shortcode_atts');

// Get query parameters
$post_type = isset($_GET['post_type']) ? $_GET['post_type'] : $shortcode_atts['post_type'];
$cat = isset($_GET['cat']) ? $_GET['cat'] : '';
$order = isset($_GET['order']) ? $_GET['order'] : '';
$paged = isset($_GET['paged']) ? $_GET['paged'] : 1;


$linked_services = $_GET['linked_services']; // from AJAX request
$linked_products = $_GET['linked_products']; // from AJAX request
$industry = $_GET['industry']; // from AJAX request

$meta_query = array('relation' => 'AND');
if ($shortcode_atts['post_type'] == 'case-study') {
    if ($linked_services) {
        $meta_query[] = array(
            'key' => 'linked_services',
            'value' => $linked_services,
            'compare' => 'IN',
        );
    }
    if ($linked_products) {
        $meta_query[] = array(
            'key' => 'linked_products',
            'value' => $linked_products,
            'compare' => 'IN',
        );
    }
    if ($industry) {
        $meta_query[] = array(
            'key' => 'industry',
            'value' => $industry,
            'compare' => 'IN',
        );
    }
}

// Build query arguments
$args = array(
    'post_type' => $post_type,
    'posts_per_page' => $shortcode_atts['count'],
    'paged' => $paged,
);

if (!empty($meta_query)) {
    $args['meta_query'] = $meta_query;
}

// Add category filter
if ($cat) {
    $args['cat'] = $cat;
}

// Add order filter
if ($order) {
    switch ($order) {
        case 'date_asc':
            $args['orderby'] = 'date';
            $args['order'] = 'ASC';
            break;
        case 'date_desc':
            $args['orderby'] = 'date';
            $args['order'] = 'DESC';
            break;
    }
}

// Get posts
$query = new WP_Query($args);

$output = '';

// Display post feed
if ($query->have_posts()) {

    if ($shortcode_atts['filters'] == 'true') {  // use the shortcode attribute here
        // Display filters
        $output .= '<div class="post-feed-filters">';
		
		if ($shortcode_atts['post_type'] == 'case-study') {
			// Build dropdown for linked_services
			$linked_services = get_posts(array(
				'post_type' => 'service',
				'posts_per_page' => -1
			));
			$output .= '<select id="linked-services-filter">';
			$output .= '<option value="">All Services</option>'; // Default option
			foreach ($linked_services as $service) {
				$output .= '<option value="' . $service->ID . '">' . $service->post_title . '</option>';
			}
			$output .= '</select>';
			
			// Build dropdown for linked_products
			$linked_products = get_posts(array(
				'post_type' => 'product',
				'posts_per_page' => -1
			));
			$output .= '<select id="linked-products-filter">';
			$output .= '<option value="">All Products</option>'; // Default option
			foreach ($linked_products as $product) {
				$output .= '<option value="' . $product->ID . '">' . $product->post_title . '</option>';
			}
			$output .= '</select>';
			
			// Build dropdown for industries
			$linked_industries = get_posts(array(
				'post_type' => 'industry',
				'posts_per_page' => -1
			));
			$output .= '<select id="industry-filter">';
			$output .= '<option value="">All Industries</option>'; // Default option
			foreach ($linked_industries as $industry) {
				$output .= '<option value="' . $industry->ID . '">' . $industry->post_title . '</option>';
			}
			$output .= '</select>';
			
			// Clear filters button
			$output .= '<button id="clear-filters">Clear Filters</button>';
			
		} else { // Filters for blog post feed
			// Category filter
            $categories = get_categories(array('hide_empty' => 1));
            $output .= '<div id="cat-filter">';
                $output .= '<span class="filter-label">Filter</span>';
            $output .= '<button class="category-button' . (empty($cat) ? ' active' : '') . '" data-category-id="">All Categories</button>';
            foreach ($categories as $category) {
                $active = ($cat == $category->term_id) ? ' active' : '';
                $output .= '<button class="category-button' . $active . '" data-category-id="' . $category->term_id . '">' . $category->name . '</button>';
            }
            $output .= '</div>';

            // Order filter
            $output .= '<div class="order-filter-wrapper">';
                $output .= '<span class="filter-label">Sort by</span>';
                $output .= '<select id="order-filter">';
                $output .= '<option value="">Default Order</option>';
                $output .= '<option value="date_asc"' . ($order == 'date_asc' ? ' selected' : '') . '>Date Ascending</option>';
                $output .= '<option value="date_desc"' . ($order == 'date_desc' ? ' selected' : '') . '>Date Descending</option>';
                $output .= '</select>';
            $output .= '</div>';
		}

        $output .= '</div>';
    }

   

    // Display posts
    $output .= '<div id="post-feed-container">';
    while ($query->have_posts()) {
        $query->the_post();

        // Display post info
        $output .= '<div class="post-item">';
        $output .= '<a href="' . get_permalink() . '">';
        $output .= '<div class="post-thumbnail">' . get_the_post_thumbnail(null, 'medium') . '</div>';
        $output .= '</a>';
        $output .= '<div class="post-content-wrapper">';
		
			$output .= '<div>';
				// Estimated read time
				$output .= '<span class="estimated-read-time">' . get_post_read_time(get_the_ID()) . '</span>';

				$output .= '<a href="' . get_permalink() . '"><h3 class="post-title">' . get_the_title() . '</h3></a>';

				// Display excerpt
				$excerpt = wp_trim_words(get_the_excerpt(), 20, '...');
				$output .= '<p class="post-excerpt">' . $excerpt . '</p>';
			$output .= '</div>';

			$output .= '<div>';
				$output .= '<div class="post-categories">';
				$categories = get_the_category();
				$separator = ' ';
				$cat_output = '';
				if ($categories) {
					foreach ($categories as $category) {
						$cat_output .= '<a href="' . get_category_link($category->term_id) . '">' . $category->name . '</a>' . $separator;
					}
					$output .= trim($cat_output, $separator);
				}
				$output .= '</div>';
			$output .= '</div>';
		
        $output .= '</div>';
        $output .= '</div>';
    }
    $output .= '</div>';

    // Display pagination
    $total_pages = $query->max_num_pages;
    if ($shortcode_atts['pagination'] == 'true' && $total_pages > 1) {  // use the shortcode attribute here
        $output .= '<div class="pagination">';

            // Determine the range of pagination links to display
            $start = max(1, $paged - 2);
            $end = min($total_pages, $paged + 2);
            if ($start > 2) {
                $output .= '<a href="#" data-page="1">1</a>';
                $output .= '<span>...</span>';
            }

            for ($i = $start; $i <= $end; $i++) {
                $class = ($paged == $i) ? ' class="active"' : '';
                $output .= '<a href="#" data-page="' . $i . '"' . $class . '>' . $i . '</a>';
            }

            if ($end < $total_pages - 1) {
                $output .= '<span>...</span>';
                $output .= '<a href="#" data-page="' . $total_pages . '">' . $total_pages . '</a>';
            }

            $output .= '</div>';
    }

} else {
    // Display "no posts found" message
    $output = '<p>No posts found.</p>';
}

wp_reset_postdata();

echo '<div id="embark-post-feed" data-post-type="'.$shortcode_atts['post_type'].'">' . $output . '</div>';



// Helper functions
function get_post_read_time($post_id) {
    $content = get_post_field('post_content', $post_id);
    $word_count = str_word_count(strip_tags($content));
    $read_time = ceil($word_count / 225); // Average reading speed
    
    return $read_time . ' min read';
}
