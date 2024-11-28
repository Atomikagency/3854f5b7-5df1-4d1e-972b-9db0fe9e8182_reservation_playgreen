<?php

/**
 * Plugin Name: Reservation playgreen
 * Description: Module de reservation sur mesure by AtomikAgency
 * Version: 1.0.1
 * Author: AtomikAgency
 * Author URI: https://atomikagency.fr/
 */

define('RESERVATION_PLAYGREEN_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('RESERVATION_PLAYGREEN_PLUGIN_URL', plugin_dir_url(__FILE__));

if (!defined('ABSPATH')) {
    exit; // Prevent direct access
}

require_once RESERVATION_PLAYGREEN_PLUGIN_DIR . 'update-checker.php';