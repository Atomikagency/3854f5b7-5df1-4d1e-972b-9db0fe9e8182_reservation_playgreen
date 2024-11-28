<?php

// Créer le CPT pour les codes promo
function rp_register_promo_code_cpt() {
    $args = array(
        'label'                 => 'Codes Promo',
        'public'                => false,
        'exclude_from_search'   => true,
        'publicly_queryable'    => false,
        'show_ui'               => true,
        'show_in_rest'          => false,
        'supports'              => array('title','editor', 'thumbnail'),
        'capability_type'       => 'post',
        'menu_icon'             => 'dashicons-tickets',
        'rewrite'               => false,
    );

    register_post_type('promo_code', $args);
}
add_action('init', 'rp_register_promo_code_cpt');

function rp_add_promo_code_meta_box() {
    add_meta_box(
        'promo_code_meta',                // ID de la meta box
        'Détails du Code Promo',          // Titre
        'rp_render_promo_code_meta_box',  // Fonction de rendu
        'promo_code',                     // Post type
        'normal',                         // Contexte
        'high'                            // Priorité
    );
}
add_action('add_meta_boxes', 'rp_add_promo_code_meta_box');

function rp_render_promo_code_meta_box($post) {
    $percentage = get_post_meta($post->ID, '_promo_percentage', true);

    ?>
    <label for="promo_percentage">Pourcentage de réduction :</label>
    <input type="number" id="promo_percentage" name="promo_percentage" value="<?php echo esc_attr($percentage); ?>" step="1" min="1" max="100" style="width: 100px;"> %
    <?php
}

function rp_save_promo_code_meta($post_id) {
    if (isset($_POST['promo_percentage'])) {
        $percentage = sanitize_text_field($_POST['promo_percentage']);
        update_post_meta($post_id, '_promo_percentage', $percentage);
    }
}
add_action('save_post', 'rp_save_promo_code_meta');
