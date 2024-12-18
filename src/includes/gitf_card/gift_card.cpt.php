<?php

function register_gift_card_cpt() {
    $labels = array(
        'name'                  => _x('Cartes Cadeaux', 'Post Type General Name', 'reservation_playgreen'),
        'singular_name'         => _x('Carte Cadeau', 'Post Type Singular Name', 'reservation_playgreen'),
        'menu_name'             => __('Cartes Cadeaux', 'reservation_playgreen'),
        'name_admin_bar'        => __('Carte Cadeau', 'reservation_playgreen'),
        'add_new_item'          => __('Ajouter une nouvelle Carte Cadeau', 'reservation_playgreen'),
        'edit_item'             => __('Modifier la Carte Cadeau', 'reservation_playgreen'),
        'view_item'             => __('Voir la Carte Cadeau', 'reservation_playgreen'),
        'all_items'             => __('Toutes les Cartes Cadeaux', 'reservation_playgreen'),
        'search_items'          => __('Rechercher des Cartes Cadeaux', 'reservation_playgreen'),
    );

    $args = array(
        'label'                 => __('Carte Cadeau', 'reservation_playgreen'),
        'labels'                => $labels,
        'supports'              => array('title', 'custom-fields'),
        'public'                => false,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 25,
        'menu_icon'             => 'dashicons-tickets-alt',
        'capability_type'       => 'post',
        'hierarchical'          => false,
        'has_archive'           => false,
        'exclude_from_search'   => true,
        'publicly_queryable'    => false,
        'show_in_rest'          => false,
    );
    register_post_type('carte_cadeau', $args);
}
add_action('init', 'register_gift_card_cpt');

function gift_card_add_meta_box() {
    add_meta_box(
        'gift_card_meta_box',
        __('Détails de la Carte Cadeau', 'reservation_playgreen'),
        'gift_card_meta_box_callback',
        'carte_cadeau'
    );
}
add_action('add_meta_boxes', 'gift_card_add_meta_box');

function gift_card_meta_box_callback($post) {
    wp_nonce_field('save_gift_card_meta', 'gift_card_meta_nonce');

    $fields = [
        'email' => __('Email', 'reservation_playgreen'),
        'emailSend' => __('Email de Réception', 'reservation_playgreen'),
        'theme' => __('Thème', 'reservation_playgreen'),
        'montant' => __('Montant', 'reservation_playgreen'),
        'from' => __('De', 'reservation_playgreen'),
        'to' => __('Pour', 'reservation_playgreen'),
        'message' => __('Message', 'reservation_playgreen'),
        'state' => __('Statut', 'reservation_playgreen'),
        'stripe_payment_id' => __('ID de Paiement Stripe', 'reservation_playgreen'),
        'consumed' => __('Montant consummé', 'reservation_playgreen'),
        'entreprise' => __('Entreprise', 'reservation_playgreen')
    ];

    foreach ($fields as $field => $label) {
        $value = get_post_meta($post->ID, $field, true);
        echo '<p><label for="' . esc_attr($field) . '">' . esc_html($label) . ':</label> ';
        if ($field === 'message') {
            echo '<textarea id="' . esc_attr($field) . '" name="' . esc_attr($field) . '" rows="4" style="width:100%;">' . esc_textarea($value) . '</textarea></p>';
        } else {
            echo '<input type="text" id="' . esc_attr($field) . '" name="' . esc_attr($field) . '" value="' . esc_attr($value) . '" style="width:100%;" /></p>';
        }
    }
}

function save_gift_card_meta($post_id) {
    if (!isset($_POST['gift_card_meta_nonce']) || !wp_verify_nonce($_POST['gift_card_meta_nonce'], 'save_gift_card_meta')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    if (get_post_type($post_id) !== 'carte_cadeau') {
        return;
    }

    $fields = [
        'email' => 'sanitize_email',
        'emailSend' => 'sanitize_email',
        'theme' => 'sanitize_text_field',
        'montant' => 'sanitize_text_field',
        'from' => 'sanitize_text_field',
        'to' => 'sanitize_text_field',
        'message' => 'sanitize_textarea_field',
        'state' => 'sanitize_text_field',
        'stripe_payment_id' => 'sanitize_text_field',
        'consumed' => 'sanitize_text_field',
        'entreprise' => 'sanitize_text_field'
    ];

    foreach ($fields as $field => $sanitizer) {
        if (isset($_POST[$field])) {
            update_post_meta($post_id, $field, call_user_func($sanitizer, $_POST[$field]));
        }
    }
}
add_action('save_post', 'save_gift_card_meta');

// Add Custom Columns for Admin List View
function gift_card_custom_columns($columns) {
    unset($columns['date']);
    $columns['montant'] = __('Montant', 'reservation_playgreen');
    $columns['montant_consumed'] = __('Montant Consommé', 'reservation_playgreen');
    $columns['state'] = __('Statut', 'reservation_playgreen');
    return $columns;
}
add_filter('manage_carte_cadeau_posts_columns', 'gift_card_custom_columns');

function gift_card_custom_column_content($column, $post_id) {
    switch ($column) {
        case 'montant':
            echo esc_html(get_post_meta($post_id, 'montant', true));
            break;
        case 'state':
            echo esc_html(get_post_meta($post_id, 'state', true));
            break;
        case 'montant_consumed':
            $consumed = get_post_meta($post_id, 'consumed', true);
            if(empty($consumed)){
                echo '0';
            }
            echo esc_html($consumed);
            break;
    }
}
add_action('manage_carte_cadeau_posts_custom_column', 'gift_card_custom_column_content', 10, 2);

