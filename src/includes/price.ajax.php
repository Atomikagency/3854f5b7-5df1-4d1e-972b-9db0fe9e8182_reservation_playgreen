<?php
// Ajouter une action AJAX pour le calcul
add_action('wp_ajax_calculer_total_paiement', 'rp_calculer_total_paiement');
add_action('wp_ajax_nopriv_calculer_total_paiement', 'rp_calculer_total_paiement');

function rp_calculer_total_paiement() {
    // Vérifier les données envoyées
    if (!isset($_POST['activite_id'], $_POST['nb_adulte'], $_POST['nb_enfant'])) {
        wp_send_json_error(['message' => 'Paramètres manquants.'], 400);
    }

    $activite_id = max(0, intval($_POST['activite_id']));
    $nb_adulte = max(0, intval($_POST['nb_adulte']));
    $nb_enfant = max(0, intval($_POST['nb_enfant']));
    $code_promo = isset($_POST['code_promo']) ? sanitize_text_field($_POST['code_promo']) : null;

    // Récupérer les prix depuis l'activité
    $prix_adulte = floatval(get_post_meta($activite_id, '_rp_prix_adulte', true));
    $prix_enfant = floatval(get_post_meta($activite_id, '_rp_prix_enfant', true));

    if (!$prix_adulte || !$prix_enfant) {
        wp_send_json_error(['message' => 'Activité ou prix introuvable.'], 404);
    }

    // Calculer le total sans réduction
    $total_without_discount = max(0, ($nb_adulte * $prix_adulte) + ($nb_enfant * $prix_enfant));

    // Initialiser la réduction
    $discount = 0;
    $discount_is_valid = false;

    // Vérifier le code promo
    if ($code_promo) {
        $promo_query = new WP_Query([
            'post_type' => 'promo_code',
            'title' => $code_promo,
            'post_status' => 'publish',
            'posts_per_page' => 1,
        ]);

        if ($promo_query->have_posts()) {
            $promo_post = $promo_query->posts[0];
            $discount = floatval(get_post_meta($promo_post->ID, '_promo_percentage', true)) / 100;
            $discount_is_valid = true;
        }
    }

    // Calculer le total avec réduction
    $total = $discount_is_valid ? $total_without_discount * (1 - $discount) : $total_without_discount;

    // Formatter les totaux avec number_format
    $total = number_format($total, 2, '.', '');
    $total_without_discount = number_format($total_without_discount, 2, '.', '');

    // Envoyer les résultats en JSON
    wp_send_json_success([
        'total' => $total,
        'total_without_discount' => $total_without_discount,
        'discount_is_valid' => $discount_is_valid,
    ]);
}
