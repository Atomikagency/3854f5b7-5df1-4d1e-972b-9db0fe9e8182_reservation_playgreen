<?php

add_shortcode('rp_activity_listing', 'rp_activity_listing_shortcode');

function rp_activity_listing_shortcode($atts) {
    $atts = shortcode_atts(array(
        'nb_item' => '',
        'filter' => false,
    ), $atts, 'rp_activity_listing');

    $args = array(
        'post_type'      => 'activite',
        'posts_per_page' => $atts['nb_item'] ? intval($atts['nb_item']) : -1,
        'orderby'          => 'rand',
    );

    $query = new WP_Query($args);

    if (!$query->have_posts()) {
        return '<p>Aucune activité trouvée.</p>';
    }

    $template_path = RESERVATION_PLAYGREEN_PLUGIN_DIR . 'views/activity_listing.php'; 

    if (!$template_path) {
        return '';
    }

    // Start output buffering
    ob_start();

    // Include template file and pass metadata
    include $template_path;

    // Get the content and clean the buffer
    return ob_get_clean();
}
