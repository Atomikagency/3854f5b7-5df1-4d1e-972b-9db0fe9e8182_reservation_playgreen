<?php

add_shortcode('rp_activity_metadata', 'rp_activity_metadata_shortcode');
/**
 * Create shortcode to display :
 * - _rp_note
 * - _rp_lieu
 * - _rp_nb_personne
 * - _rp_duree
 * - _rp_langue_fr
 * - _rp_langue_en
 *
 * // return template views/activity_metadata.php using ob_start()
 */

function rp_activity_metadata_shortcode($atts) {
    // Fetch metadata
    global $post;
    $metadata = [
        'note' => get_post_meta($post->ID, '_rp_note', true),
        'lieu' => get_post_meta($post->ID, '_rp_lieu', true),
        'nb_personne' => get_post_meta($post->ID, '_rp_nb_personne', true),
        'duree' => get_post_meta($post->ID, '_rp_duree', true),
        'langue_fr' => get_post_meta($post->ID, '_rp_langue_fr', true),
        'langue_en' => get_post_meta($post->ID, '_rp_langue_en', true),
    ];

    // Path to the template
    $template_path = locate_template('views/activity_metadata.php');

    if (!$template_path) {
        return '<p>Template file not found: views/activity_metadata.php</p>';
    }

    // Start output buffering
    ob_start();

    // Include template file and pass metadata
    include $template_path;

    // Get the content and clean the buffer
    return ob_get_clean();
}