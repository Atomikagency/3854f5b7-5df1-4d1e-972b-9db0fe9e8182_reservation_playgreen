<?php

/**
 * Plugin Name: Reservation playgreen
 * Description: Module de reservation sur mesure by AtomikAgency
 * Version: 1.0.27
 * Author: AtomikAgency
 * Author URI: https://atomikagency.fr/
 */

define('RESERVATION_PLAYGREEN_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('RESERVATION_PLAYGREEN_PLUGIN_URL', plugin_dir_url(__FILE__));

if (!defined('ABSPATH')) {
    exit; // Prevent direct access
}

require_once 'vendor/autoload.php';

require_once RESERVATION_PLAYGREEN_PLUGIN_DIR . 'update-checker.php';
require_once RESERVATION_PLAYGREEN_PLUGIN_DIR. 'includes/category.shortcode.php';
require_once RESERVATION_PLAYGREEN_PLUGIN_DIR. 'includes/latest_article.shortcode.php';
require_once RESERVATION_PLAYGREEN_PLUGIN_DIR . 'includes/gitf_card/gift_card.shortcode.php';
require_once RESERVATION_PLAYGREEN_PLUGIN_DIR. 'includes/code_promo.cpt.php';
require_once RESERVATION_PLAYGREEN_PLUGIN_DIR. 'includes/admin/settings.php';
require_once RESERVATION_PLAYGREEN_PLUGIN_DIR. 'includes/activity.cpt.php';
require_once RESERVATION_PLAYGREEN_PLUGIN_DIR. 'includes/reservation.cpt.php';
require_once RESERVATION_PLAYGREEN_PLUGIN_DIR. 'includes/reservation_flow/form.shortcode.php';
require_once RESERVATION_PLAYGREEN_PLUGIN_DIR. 'includes/reservation_flow/recapitulation.shortcode.php';
require_once RESERVATION_PLAYGREEN_PLUGIN_DIR. 'includes/reservation_flow/stripe.php';
require_once RESERVATION_PLAYGREEN_PLUGIN_DIR. 'includes/reservation_flow/reservation_meta.shortcode.php';
require_once RESERVATION_PLAYGREEN_PLUGIN_DIR. 'includes/admin/stripe_connect.php';
require_once RESERVATION_PLAYGREEN_PLUGIN_DIR. 'includes/price.ajax.php';
require_once RESERVATION_PLAYGREEN_PLUGIN_DIR. 'includes/activity_metadata.shortcode.php';
require_once RESERVATION_PLAYGREEN_PLUGIN_DIR. 'includes/activity_listing.shortcode.php';
require_once RESERVATION_PLAYGREEN_PLUGIN_DIR. 'includes/gitf_card/gift_card.cpt.php';
require_once RESERVATION_PLAYGREEN_PLUGIN_DIR. 'includes/gitf_card/stripe.php';
require_once RESERVATION_PLAYGREEN_PLUGIN_DIR. 'includes/gitf_card/gift_meta.shortcode.php';

//

// add asset style
function rp_enqueue_styles()
{
    wp_enqueue_style('rp-style', RESERVATION_PLAYGREEN_PLUGIN_URL . 'assets/css/style.css', [],'1.0.0');
}

// add asset script
function rp_enqueue_scripts()
{
    wp_enqueue_script('rp-script', RESERVATION_PLAYGREEN_PLUGIN_URL . 'assets/js/script.js', [],'1.0.0', true);

    wp_localize_script('rp-script', 'ajax_object', [
        'ajax_url' => admin_url('admin-ajax.php'),
    ]);

    $reservation_form_page_id = get_option('rp_reservation_form_page');
    $gift_card_page_id = get_option('rp_gift_card_page');

    if(is_page($reservation_form_page_id) || is_page($gift_card_page_id)) {
        // Ajouter Fastest Validator
        wp_enqueue_script('fastest-validator', 'https://unpkg.com/fastest-validator', [], '1.0.0', true);
    }

    if (!empty($reservation_form_page_id) && is_page($reservation_form_page_id)) {
        // Ajouter Flatpickr CSS et JS
        wp_enqueue_style('flatpickr-css', 'https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css', [], '4.6.13');
        wp_enqueue_script('flatpickr-js', 'https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.js', [], '4.6.13', true);
        wp_enqueue_script('flatpickr-js-fr', 'https://npmcdn.com/flatpickr/dist/l10n/fr.js', [], '4.6.13', true);
        // Ajouter le script personnalisé pour le formulaire
        wp_enqueue_script(
            'reservation-form-js',
            plugin_dir_url(__FILE__) . 'assets/js/reservation-form.js',
            ['jquery', 'flatpickr-js'],
            '1.0.0',
            true
        );

        // Passer des données PHP vers le JS
        wp_localize_script('reservation-form-js', 'rpReservationData', [
            'prixAdulte'     => intval(get_post_meta($_GET['activite_id'], '_rp_prix_adulte', true)),
            'prixEnfant'     => intval(get_post_meta($_GET['activite_id'], '_rp_prix_enfant', true)),
            'availableDates' => get_post_meta($_GET['activite_id'], '_rp_hours', true),
            'unavailabilityDates' => get_post_meta($_GET['activite_id'], '_rp_unavailability_dates', true),
        ]);
    }
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
