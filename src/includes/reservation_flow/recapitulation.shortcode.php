<?php
// Ajouter un shortcode pour afficher le récapitulatif d'une réservation
function rp_reservation_recap_shortcode($atts)
{
    // Vérifier si un ID de réservation est fourni via GET
    if (isset($_GET['reservation_id'])) {
        $reservation_id = intval($_GET['reservation_id']);


        // Vérifier si l'ID est valide et correspond au CPT "reservation"
        if (get_post_type($reservation_id) === 'reservation') {
            $reservation_data = [
                'id' => $reservation_id,
                'date' => get_post_meta($reservation_id, '_rp_date', true),
                'heure' => get_post_meta($reservation_id, '_rp_heure', true),
                'nom' => get_post_meta($reservation_id, '_rp_nom', true),
                'prenom' => get_post_meta($reservation_id, '_rp_prenom', true),
                'email' => get_post_meta($reservation_id, '_rp_email', true),
                'langue' => get_post_meta($reservation_id, '_rp_langue', true),
                'adultes' => get_post_meta($reservation_id, '_rp_nb_adultes', true),
                'enfants' => get_post_meta($reservation_id, '_rp_nb_enfants', true),
                'activite' => intval(get_post_meta($reservation_id, '_rp_activite_id', true)),
                'activite_data' => get_post(intval(get_post_meta($reservation_id, '_rp_activite_id', true)), 'activite'),
                'activite_duration' => get_post_meta(get_post_meta($reservation_id, '_rp_activite_id', true), '_rp_duree', true),
                'activite_thumbnail' => get_the_post_thumbnail_url(intval(get_post_meta($reservation_id, '_rp_activite_id', true)))
            ];

            $prix_adulte = floatval(get_post_meta($reservation_data['activite'], '_rp_prix_adulte', true));
            $prix_enfant = floatval(get_post_meta($reservation_data['activite'], '_rp_prix_enfant', true));

            // Calculer le total
            // apply code promo if exist
            $total = ($reservation_data['adultes'] * $prix_adulte) + ($reservation_data['enfants'] * $prix_enfant);
            $totalBeforeDiscount = $total;
            $promo_code = get_post_meta($reservation_id, '_rp_code_promo', true); // Champ personnalisé dans la réservation
            if (!empty($promo_code)) {
                // Rechercher le code promo dans le CPT
                $promo_post = get_page_by_title($promo_code, OBJECT, 'promo_code');


                if ($promo_post) {
                    // Récupérer le pourcentage de réduction
                    $discount_percentage = intval(get_post_meta($promo_post->ID, '_promo_percentage', true));

                    if ($discount_percentage > 0) {
                        // Appliquer la réduction
                        $discount = ($total * $discount_percentage) / 100;
                        $total -= $discount;
                    }
                }
            }


            $carte_cadeau = get_post_meta($reservation_id, '_rp_carte_cadeau', true); // Champ personnalisé dans la réservation
            if (!empty($carte_cadeau)) {
                $carte_cadeau_is_valid = false;
                $promo_query = new WP_Query([
                    'post_type' => 'carte_cadeau',
                    'title' => $carte_cadeau,
                    'post_status' => 'publish',
                    'posts_per_page' => 1,
                ]);

                if ($promo_query->have_posts()) {
                    $gift = $promo_query->posts[0];
                    $consumedCarteCadeau = floatval(get_post_meta($gift->ID, 'consumed', true));
                    $totalCarteCadeau = floatval(get_post_meta($gift->ID, 'montant', true));
                    $remainingToSub = max($totalCarteCadeau - $consumedCarteCadeau, 0);

                    if ($remainingToSub > 0) {
                        $carte_cadeau_is_valid = true;
                        $total = max($total - $remainingToSub, 0);
                    }
                }
            }
            $total = max($total, 0);

            $reservation_data['total'] = $total;
            $reservation_data['totalBeforeDiscount'] = $totalBeforeDiscount;

            ob_start();
            $view_file = RESERVATION_PLAYGREEN_PLUGIN_DIR . 'views/recapitulation.php';
            if (file_exists($view_file)) {
                include $view_file;
            } else {
                echo '';
            }
            return ob_get_clean();
        } else {
            return '<p style="color: red;">ID de réservation invalide.</p>';
        }
    } else {
        return '<p style="color: red;">Aucune réservation spécifiée.</p>';
    }
}

add_shortcode('reservation_recap', 'rp_reservation_recap_shortcode');



