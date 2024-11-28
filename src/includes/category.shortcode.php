<?php
add_shortcode('rp_categories', 'rp_category_shortcode');
function rp_category_shortcode($atts){
    // Fetch all categories
    $categories = get_categories(array(
        'hide_empty' => true, // Only include categories with at least one post
    ));

    if (empty($categories)) {
        return '';
    }

    // Build the output
    $output = '<ul class="rp-categories">';
    foreach ($categories as $category) {
        $output .= sprintf(
            '<li>%s <span>%d</span></li>',
            esc_html($category->name), // Category name
            esc_html($category->count) // Number of posts in the category
        );
    }
    $output .= '</ul>';
    return $output;
}

