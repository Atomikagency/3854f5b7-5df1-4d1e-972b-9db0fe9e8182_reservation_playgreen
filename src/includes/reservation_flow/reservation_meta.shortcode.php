<?php
function rp_reservation_metadata_shortcode($attrs)
{
    if (empty($_GET['reservation_id'])) {
        return '';
    }
    $reservation_id = intval($_GET['reservation_id']);

    $attrs = shortcode_atts([
            'field' => ''
        ],
        $attrs);

    if (empty($attrs['field'])) {
        return '';
    }
    $meta_value = get_post_meta($reservation_id, $attrs['field'], true);
    if (!empty($meta_value)) {
        return esc_html($meta_value);
    } else {
        return '';
    }
}
add_shortcode('rp_reservation_metadata', 'rp_reservation_metadata_shortcode');
