<?php

/**
 * Plugin Name: Reservation playgreen
 * Description: Module de reservation sur mesure by AtomikAgency
 * Version: 1.0.4
 * Author: AtomikAgency
 * Author URI: https://atomikagency.fr/
 */

define('RESERVATION_PLAYGREEN_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('RESERVATION_PLAYGREEN_PLUGIN_URL', plugin_dir_url(__FILE__));

if (!defined('ABSPATH')) {
    exit; // Prevent direct access
}

require_once RESERVATION_PLAYGREEN_PLUGIN_DIR . 'update-checker.php';
require_once RESERVATION_PLAYGREEN_PLUGIN_DIR. 'includes/category.shortcode.php';
require_once RESERVATION_PLAYGREEN_PLUGIN_DIR. 'includes/latest_article.shortcode.php';
require_once RESERVATION_PLAYGREEN_PLUGIN_DIR. 'includes/gift_card.shortcode.php';
require_once RESERVATION_PLAYGREEN_PLUGIN_DIR. 'includes/code_promo.cpt.php';
require_once RESERVATION_PLAYGREEN_PLUGIN_DIR. 'includes/admin/settings.php';
require_once RESERVATION_PLAYGREEN_PLUGIN_DIR. 'includes/activity.cpt.php';



// add asset style
function rp_enqueue_styles()
{
    wp_enqueue_style('rp-style', RESERVATION_PLAYGREEN_PLUGIN_URL . 'assets/css/style.css', [],'1.0.0');
}

// add asset script
function rp_enqueue_scripts()
{
    wp_enqueue_script('rp-script', RESERVATION_PLAYGREEN_PLUGIN_URL . 'assets/js/script.js', [],'1.0.0', true);
}

add_action('wp_enqueue_scripts', 'rp_enqueue_styles');
add_action('wp_enqueue_scripts', 'rp_enqueue_scripts');

// Remove divi project cpt :
add_filter('et_project_posttype_args', 'rp_et_project_posttype_args', 10, 1);
function rp_et_project_posttype_args($args)
{
    return array_merge($args, array(
        'public' => false,
        'exclude_from_search' => false,
        'publicly_queryable' => false,
        'show_in_nav_menus' => false,
        'show_ui' => false
    ));
}

// Charger les scripts nécessaires pour le Media Uploader
function rp_enqueue_admin_scripts($hook_suffix) {
    // Vérifier que nous sommes bien dans l'édition d'un "activite"
    global $post_type;
    if ($post_type === 'activite') {
        wp_enqueue_media();
        wp_enqueue_script('rp-media-upload', plugin_dir_url(__FILE__) . 'assets/js/media-upload.js', array('jquery'), '1.0', true);
    }
}
add_action('admin_enqueue_scripts', 'rp_enqueue_admin_scripts');
