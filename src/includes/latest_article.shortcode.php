<?php
add_shortcode('rp_latest_article', 'rp_latest_article_shortcode');

function rp_latest_article_shortcode($atts){
    $atts = shortcode_atts(
        array(
            'posts_per_page' => 3,
        ),
        $atts,
        'rp_latest_article'
    );

    $latest_posts = new WP_Query(array(
        'post_type'      => 'post',
        'posts_per_page' => intval($atts['posts_per_page']),
        'post_status'    => 'publish',
    ));

    ob_start();

    $view_file = RESERVATION_PLAYGREEN_PLUGIN_DIR . 'views/latest_article.php';
    if (file_exists($view_file)) {
        include $view_file;
    } else {
        echo '';
    }
    return ob_get_clean();
}
