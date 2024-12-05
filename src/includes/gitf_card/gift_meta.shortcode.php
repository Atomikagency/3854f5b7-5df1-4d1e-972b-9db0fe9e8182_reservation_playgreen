<?php
function rp_gift_metadata_shortcode($attrs)
{
    if (empty($_GET['gift_id'])) {
        return '';
    }
    $gift_id = intval($_GET['gift_id']);

    $attrs = shortcode_atts([
            'field' => ''
        ],
        $attrs);

    if (empty($attrs['field'])) {
        return esc_html($_GET['gift_id']);
    }
    $meta_value = get_post_meta($gift_id, $attrs['field'], true);
    if (!empty($meta_value)) {
        return esc_html($meta_value);
    } else {
        return '';
    }
}
add_shortcode('rp_gift_metadata', 'rp_gift_metadata_shortcode');
