<?php

function rp_reservation_button_shortcode()
{
    if (!is_singular('activite')) {
        return '<p style="color: red;">Ce shortcode doit être utilisé sur une page d\'activité.</p>';
    }

    $activite_id = get_the_ID();

    $form_page_id = get_option('rp_reservation_form_page');
    if (!$form_page_id) {
        return '<p style="color: red;">Page du formulaire de réservation non configurée.</p>';
    }

    $form_page_url = add_query_arg(
        array('activite_id' => $activite_id),
        get_permalink($form_page_id)
    );

    return sprintf(
        '<a href="%s" class="button reservation-button" style="background-color: #0073aa; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Réserver cette activité</a>',
        esc_url($form_page_url)
    );
}

add_shortcode('reservation_button', 'rp_reservation_button_shortcode');


function rp_reservation_form_shortcode()
{
    // Récupérer l'ID de l'activité depuis l'URL
    $activite_id = isset($_GET['activite_id']) ? intval($_GET['activite_id']) : null;
    // Vérifier que l'ID de l'activité est valide
    if (!$activite_id || get_post_type($activite_id) !== 'activite') {
        return '<p style="color: red;">Activité non valide ou manquante.</p>';
    }

    // Gérer la soumission du formulaire
    if (isset($_POST['reservation_adultes'])) {
        // Vérification nonce
        check_admin_referer('reservation_form_nonce', 'reservation_form_nonce_field');

        // Récupérer les données du formulaire
        $reservation_data = [
            'date' => sanitize_text_field($_POST['reservation_date']),
            'heure' => sanitize_text_field($_POST['reservation_time']),
            'nom' => sanitize_text_field($_POST['reservation_nom']),
            'prenom' => sanitize_text_field($_POST['reservation_prenom']),
            'email' => sanitize_email($_POST['reservation_email']),
            'langue_fr' => sanitize_text_field($_POST['reservation_francais']),
            'langue_en' => sanitize_text_field($_POST['reservation_anglais']),
            'adultes' => intval($_POST['reservation_adultes']),
            'enfants' => intval($_POST['reservation_enfants']),
            'activite' => $activite_id,
            'code_promo' => sanitize_text_field($_POST['reservation_code_promo']),
            'carte_cadeau' => sanitize_text_field($_POST['reservation_carte_cadeau']),
            'enterprise_name' => sanitize_text_field($_POST['enterprise_name']),
            'message' => sanitize_text_field($_POST['message']),
        ];


        // Créer un nouveau post pour la réservation
        $reservation_id = wp_insert_post([
            'post_type' => 'reservation',
            'post_status' => 'publish', // Statut "publish" pour le rendre visible dans l'admin
            'post_title' => 'Réservation - ' . $reservation_data['nom'] . ' ' . $reservation_data['prenom'],
        ]);

        // Si la création est réussie, ajouter les métadonnées
        if ($reservation_id) {
            $lang = (!empty($reservation_data['langue_fr']) ? 'Français' : '').' '.(!empty($reservation_data['langue_en']) ? 'Anglais' : '');
            update_post_meta($reservation_id, '_rp_date', $reservation_data['date']);
            update_post_meta($reservation_id, '_rp_heure', $reservation_data['heure']);
            update_post_meta($reservation_id, '_rp_nom', $reservation_data['nom']);
            update_post_meta($reservation_id, '_rp_prenom', $reservation_data['prenom']);
            update_post_meta($reservation_id, '_rp_email', $reservation_data['email']);
            update_post_meta($reservation_id, '_rp_langue', $lang);
            update_post_meta($reservation_id, '_rp_nb_adultes', $reservation_data['adultes']);
            update_post_meta($reservation_id, '_rp_nb_enfants', $reservation_data['enfants']);
            update_post_meta($reservation_id, '_rp_activite_id', $reservation_data['activite']);
            update_post_meta($reservation_id, '_rp_state', 'in_progress'); // Statut par défaut "in progress"
            update_post_meta($reservation_id, '_rp_code_promo', $reservation_data['code_promo']);
            update_post_meta($reservation_id, '_rp_carte_cadeau', $reservation_data['carte_cadeau']);
            update_post_meta($reservation_id, '_rp_enterprise_name', $reservation_data['enterprise_name']);
            update_post_meta($reservation_id, '_rp_message', $reservation_data['message']);

            $recap_page_id = get_option('rp_recap_page');
            if ($recap_page_id) {
                $recap_page_url = add_query_arg('reservation_id', $reservation_id, get_permalink($recap_page_id));
                echo "<script>
        window.location.href = '" . esc_url($recap_page_url) . "';
    </script>";
                exit;
            }


            return '<p style="color: green;">Votre réservation a été enregistrée avec succès.</p>';
        }

        return '<p style="color: red;">Une erreur est survenue lors de la création de votre réservation. Veuillez réessayer.</p>';
    }

    // Récupérer les métadonnées de l'activité
    $activity_meta = [
        'langue_fr' => get_post_meta($activite_id, '_rp_langue_fr', true),
        'langue_en' => get_post_meta($activite_id, '_rp_langue_en', true),
        'prix_adulte' => get_post_meta($activite_id, '_rp_prix_adulte', true),
        'prix_enfant' => get_post_meta($activite_id, '_rp_prix_enfant', true),
        'horaire' => get_post_meta($activite_id, '_rp_hours', true),
        'thumbnail' => get_the_post_thumbnail_url($activite_id, 'full'),
        'duree' => get_post_meta($activite_id, '_rp_duree', true),
        'note' => get_post_meta($activite_id, '_rp_note', true),
        'resumé' => get_the_excerpt($activite_id),
        'activite' => $activite_id,
    ];

    ob_start();
    $view_file = RESERVATION_PLAYGREEN_PLUGIN_DIR . 'views/reservation_form.php';
    if (file_exists($view_file)) {
        include $view_file;
    } else {
        echo '';
    }
    return ob_get_clean();
}

add_shortcode('reservation_form', 'rp_reservation_form_shortcode');

