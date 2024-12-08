<?php

// Créer le CPT "Réservation"
function rp_register_reservation_cpt() {
    $args = array(
        'label'               => 'Réservations',
        'public'              => false, // Non accessible depuis le front-end
        'show_ui'             => true,  // Accessible depuis l'admin
        'supports'            => array('title'), // Utilise le titre pour identifier une réservation
        'menu_icon'           => 'dashicons-clipboard',
        'show_in_rest'        => false, // Non accessible via REST API
        'has_archive'         => false, // Pas d'archive
    );

    register_post_type('reservation', $args);
}
add_action('init', 'rp_register_reservation_cpt');

// Ajouter une meta box pour les détails de la réservation
function rp_add_reservation_meta_boxes() {
    add_meta_box(
        'rp_reservation_details',
        'Détails de la Réservation',
        'rp_render_reservation_meta_box',
        'reservation',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'rp_add_reservation_meta_boxes');

// Rendu de la meta box
function rp_render_reservation_meta_box($post) {
    $fields = [
        'activite_id'     => get_post_meta($post->ID, '_rp_activite_id', true),
        'email'           => get_post_meta($post->ID, '_rp_email', true),
        'nom'             => get_post_meta($post->ID, '_rp_nom', true),
        'prenom'          => get_post_meta($post->ID, '_rp_prenom', true),
        'langue'          => get_post_meta($post->ID, '_rp_langue', true),
        'nb_adultes'      => get_post_meta($post->ID, '_rp_nb_adultes', true),
        'nb_enfants'      => get_post_meta($post->ID, '_rp_nb_enfants', true),
        'date'            => get_post_meta($post->ID, '_rp_date', true),
        'heure'           => get_post_meta($post->ID, '_rp_heure', true),
        'is_paid'         => get_post_meta($post->ID, '_rp_is_paid', true),
        'stripe_charge_id'=> get_post_meta($post->ID, '_rp_stripe_charge_id', true),
        'state'           => get_post_meta($post->ID, '_rp_state', true),
        'code_promo' => get_post_meta($post->ID, '_rp_code_promo', true),
        'carte_cadeau' => get_post_meta($post->ID, '_rp_carte_cadeau', true),
        'entreprise_name' => get_post_meta($post->ID, '_rp_enterprise_name', true),
        'message' => get_post_meta($post->ID, '_rp_message', true),
    ];

    $activites = get_posts([
        'post_type'      => 'activite',
        'posts_per_page' => -1,
    ]);

    ?>
    <table class="form-table">
        <tr>
            <th><label for="rp_activite_id">Activité</label></th>
            <td>
                <select name="rp_activite_id" id="rp_activite_id" style="width: 100%;">
                    <option value="">Choisir une activité</option>
                    <?php foreach ($activites as $activite) : ?>
                        <option value="<?php echo $activite->ID; ?>" <?php selected($fields['activite_id'], $activite->ID); ?>>
                            <?php echo esc_html($activite->post_title); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>
        <tr>
            <th><label for="rp_state">État</label></th>
            <td>
                <select name="rp_state" id="rp_state" style="width: 100%;">
                    <option value="in_progress" <?php selected($fields['state'], 'in_progress'); ?>>En cours</option>
                    <option value="done" <?php selected($fields['state'], 'done'); ?>>Done</option>
                </select>
            </td>
        </tr>
        <tr>
            <th><label for="rp_email">Email</label></th>
            <td><input type="email" name="rp_email" id="rp_email" value="<?php echo esc_attr($fields['email']); ?>" style="width: 100%;"></td>
        </tr>
        <tr>
            <th><label for="rp_nom">Nom</label></th>
            <td><input type="text" name="rp_nom" id="rp_nom" value="<?php echo esc_attr($fields['nom']); ?>" style="width: 100%;"></td>
        </tr>
        <tr>
            <th><label for="rp_prenom">Prénom</label></th>
            <td><input type="text" name="rp_prenom" id="rp_prenom" value="<?php echo esc_attr($fields['prenom']); ?>" style="width: 100%;"></td>
        </tr>
        <tr>
            <th><label for="rp_langue">Langue</label></th>
            <td><input type="text" name="rp_langue" id="rp_langue" value="<?php echo esc_attr($fields['langue']); ?>" style="width: 100%;"></td>
        </tr>
        <tr>
            <th><label for="rp_nb_adultes">Nombre d'adultes</label></th>
            <td><input type="number" name="rp_nb_adultes" id="rp_nb_adultes" value="<?php echo esc_attr($fields['nb_adultes']); ?>" min="0" style="width: 100%;"></td>
        </tr>
        <tr>
            <th><label for="rp_nb_enfants">Nombre d'enfants</label></th>
            <td><input type="number" name="rp_nb_enfants" id="rp_nb_enfants" value="<?php echo esc_attr($fields['nb_enfants']); ?>" min="0" style="width: 100%;"></td>
        </tr>
        <tr>
            <th><label for="rp_date">Date</label></th>
            <td><input type="date" name="rp_date" id="rp_date" value="<?php echo esc_attr($fields['date']); ?>" style="width: 100%;"></td>
        </tr>
        <tr>
            <th><label for="rp_heure">Heure</label></th>
            <td><input type="text" name="rp_heure" id="rp_heure" value="<?php echo esc_attr($fields['heure']); ?>" style="width: 100%;"></td>
        </tr>
        <tr>
            <th><label for="rp_code_promo">Code promo</label></th>
            <td><input type="text" name="rp_code_promo" id="rp_code_promo" value="<?php echo esc_attr($fields['code_promo']); ?>" style="width: 100%;"></td>
        </tr>
        <tr>
            <th><label for="rp_carte_cadeau">Carte Cadeau</label></th>
            <td><input type="text" name="rp_carte_cadeau" id="rp_carte_cadeau" value="<?php echo esc_attr($fields['carte_cadeau']); ?>" style="width: 100%;"></td>
        </tr>
        <tr>
            <th><label for="rp_enterprise_name">Entreprise</label></th>
            <td><input type="text" name="rp_enterprise_name" id="rp_enterprise_name" value="<?php echo esc_attr($fields['entreprise_name']); ?>" style="width: 100%;"></td>
        </tr>
        <tr>
            <th><label for="rp_message">Message</label></th>
            <td><input type="text" name="rp_message" id="rp_message" value="<?php echo esc_attr($fields['message']); ?>" style="width: 100%;"></td>
        </tr>
        <tr>
            <th><label for="rp_is_paid">Paiement reçu</label></th>
            <td>
                <select name="rp_is_paid" id="rp_is_paid" style="width: 100%;">
                    <option value="0" <?php selected($fields['is_paid'], '0'); ?>>Non</option>
                    <option value="1" <?php selected($fields['is_paid'], '1'); ?>>Oui</option>
                </select>
            </td>
        </tr>
<!--        <tr>-->
<!--            <th><label for="rp_stripe_charge_id">Stripe Charge ID</label></th>-->
<!--            <td><input type="text" name="rp_stripe_charge_id" id="rp_stripe_charge_id" value="--><?php //echo esc_attr($fields['stripe_charge_id']); ?><!--" style="width: 100%;"></td>-->
<!--        </tr>-->
    </table>
    <?php
}

// Sauvegarder les champs personnalisés
function rp_save_reservation_meta($post_id) {
    $fields = [
        'activite_id', 'email', 'nom', 'prenom', 'langue',
        'nb_adultes', 'nb_enfants', 'date', 'heure', 'is_paid', 'stripe_charge_id', 'state', 'carte_cadeau',
        'entreprise_name','message'
    ];

    foreach ($fields as $field) {
        if (isset($_POST["rp_$field"])) {
            update_post_meta($post_id, "_rp_$field", sanitize_text_field($_POST["rp_$field"]));
        }
    }
}
add_action('save_post', 'rp_save_reservation_meta');

add_action('save_post', function($post_id) {
    if (get_post_type($post_id) === 'reservation' && empty(get_post_meta($post_id, '_rp_state', true))) {
        update_post_meta($post_id, '_rp_state', 'in_progress');
    }
});

// Ajouter des colonnes personnalisées
function rp_reservation_columns($columns) {
    $new_columns = array(
        'cb'     => $columns['cb'], // Colonne checkbox
        'title'  => $columns['title'], // Titre
        'state'  => 'État', // Colonne État
        'date'   => 'Date', // Colonne Date
        'heure'  => 'Heure', // Colonne Heure
    );

    return $new_columns;
}
add_filter('manage_reservation_posts_columns', 'rp_reservation_columns');

// Afficher les données des colonnes personnalisées
function rp_reservation_custom_column($column, $post_id) {
    if ($column === 'state') {
        $state = get_post_meta($post_id, '_rp_state', true);
        $state_label = $state === 'done' ? 'Done' : 'En cours';
        $state_color = $state === 'done' ? 'background-color: #d4edda; color: #155724; padding: 5px; border-radius: 3px;' : 'background-color: #d1ecf1; color: #0c5460; padding: 5px; border-radius: 3px;';
        echo "<span style='$state_color'>$state_label</span>";
    }

    if ($column === 'date') {
        $date = get_post_meta($post_id, '_rp_date', true);
        echo esc_html($date ?: '—');
    }

    if ($column === 'heure') {
        $heure = get_post_meta($post_id, '_rp_heure', true);
        echo esc_html($heure ?: '—');
    }
}
add_action('manage_reservation_posts_custom_column', 'rp_reservation_custom_column', 10, 2);


// Rendre les colonnes triables
function rp_reservation_sortable_columns($columns) {
    $columns['date'] = 'date';
    return $columns;
}
add_filter('manage_edit-reservation_sortable_columns', 'rp_reservation_sortable_columns');
// Ajouter des styles pour les labels dans l'admin
function rp_admin_custom_css() {
    echo '<style>
        .column-state span {
            display: inline-block;
            font-size: 12px;
            font-weight: bold;
            text-align: center;
        }
    </style>';
}
add_action('admin_head', 'rp_admin_custom_css');
