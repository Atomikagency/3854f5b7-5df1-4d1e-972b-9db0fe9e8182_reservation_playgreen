<?php
add_shortcode('rp_gift_card', 'rp_gift_card_shortcode');
function rp_gift_card_shortcode($atts)
{
    ob_start();
    $view_file = RESERVATION_PLAYGREEN_PLUGIN_DIR . 'views/gift_card.php';
    if (file_exists($view_file)) {
        include $view_file;
    } else {
        echo '<p>Le fichier de vue est introuvable.</p>';
    }
    return ob_get_clean();
}

