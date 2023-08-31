<?php
/*
Plugin Name: Embark Blog Feed
Description: A custom post feed plugin with filters and AJAX functionality.
Version: 1.0
Author: Hayden Stapleton
 */

// Enqueue scripts and styles
function embark_blog_feed_scripts()
{
    wp_enqueue_style('embark-blog-feed-css', plugins_url('css/style.css', __FILE__));
    wp_enqueue_script('embark-blog-feed-js', plugins_url('js/script.js', __FILE__), array('jquery'), '1.0', true);
    wp_localize_script('embark-blog-feed-js', 'ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));
}
add_action('wp_enqueue_scripts', 'embark_blog_feed_scripts');

// Register the shortcode
function embark_blog_feed($atts)
{
    // Parse the shortcode attributes
    $args = shortcode_atts(array(
		'count' => 12,
		'filters' => 'true',
		'pagination' => 'true',
		'post_type' => 'post', // default value is 'post'
	), $atts);

    ob_start();

    // pass the attributes to the template
    set_query_var('shortcode_atts', $args);

    require plugin_dir_path(__FILE__) . 'templates/post-feed.php';
    return ob_get_clean();
}
add_shortcode('embark-blog-feed', 'embark_blog_feed');


// AJAX functions
function my_custom_post_feed_ajax()
{
    // Parse the shortcode attributes
    $args = shortcode_atts(array(
		'count' => 12,
		'filters' => 'true',
		'pagination' => 'true',
		'post_type' => $_POST['post_type'] // Get it from AJAX POST data
	), $_POST);

    // pass the attributes to the template
    set_query_var('shortcode_atts', $args);

    require plugin_dir_path(__FILE__) . 'templates/post-feed.php';
    wp_die();
}
add_action('wp_ajax_my_custom_post_feed', 'my_custom_post_feed_ajax');
add_action('wp_ajax_nopriv_my_custom_post_feed', 'my_custom_post_feed_ajax');

