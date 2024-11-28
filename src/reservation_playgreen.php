<?php

/**
 * Plugin Name: Reservation playgreen
 * Description: Module de reservation sur mesure by AtomikAgency
 * Version: 1.0.3
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