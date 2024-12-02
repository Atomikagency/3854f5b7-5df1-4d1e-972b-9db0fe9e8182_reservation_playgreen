<?php
// Ajouter un endpoint pour la redirection Stripe Checkout
function rp_register_payment_endpoint()
{
    add_rewrite_rule(
        '^reservation-payment/?$', // URL personnalisée
        'index.php?reservation_payment=1', // Redirection vers une requête personnalisée
        'top'
    );
}

add_action('init', 'rp_register_payment_endpoint');

// Ajouter une variable de requête pour détecter l'endpoint
function rp_add_query_vars($vars)
{
    $vars[] = 'reservation_payment';
    return $vars;
}

add_filter('query_vars', 'rp_add_query_vars');

// Charger Stripe PHP

// Gérer la requête Stripe Checkout
function rp_handle_payment_request()
{
    if (get_query_var('reservation_payment') == 1) {
        // Vérifier que la requête est POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reservation'])) {
            $reservation_id = intval($_POST['reservation']);

            // Vérifier que l'ID de réservation est valide
            if (get_post_type($reservation_id) === 'reservation') {
                // Récupérer les clés API Stripe depuis les réglages
                $stripe_private = get_option('rp_stripe_private');
                $commission_percentage = get_option('rp_commission', 0); // Pourcentage de commission

                if (!$stripe_private) {
                    wp_die('Clé Stripe privée manquante.');
                }

                // Configurer Stripe
                \Stripe\Stripe::setApiKey($stripe_private);

                // Récupérer les détails de la réservation
                $reservation_data = [
                    'adultes' => get_post_meta($reservation_id, '_rp_nb_adultes', true),
                    'enfants' => get_post_meta($reservation_id, '_rp_nb_enfants', true),
                    'activite' => get_post_meta($reservation_id, '_rp_activite_id', true),
                ];

                // Vérifier l'ID de l'activité
                if (!$reservation_data['activite'] || get_post_type($reservation_data['activite']) !== 'activite') {
                    wp_die('Activité associée invalide.');
                }

                // Récupérer les prix et le Stripe Connect ID depuis l'activité
                $prix_adulte = get_post_meta($reservation_data['activite'], '_rp_prix_adulte', true);
                $prix_enfant = get_post_meta($reservation_data['activite'], '_rp_prix_enfant', true);
                $stripe_connect_id = get_post_meta($reservation_data['activite'], '_rp_stripe_connect', true);
                // Calculer le montant total
                $total_amount = ($reservation_data['adultes'] * $prix_adulte) +
                    ($reservation_data['enfants'] * $prix_enfant);

                $promo_code = get_post_meta($reservation_id, '_rp_code_promo', true); // Champ personnalisé dans la réservation
                if (!empty($promo_code)) {
                    // Rechercher le code promo dans le CPT
                    $promo_post = get_page_by_title($promo_code, OBJECT, 'promo_code');


                    if ($promo_post) {
                        // Récupérer le pourcentage de réduction
                        $discount_percentage = intval(get_post_meta($promo_post->ID, '_promo_percentage', true));

                        if ($discount_percentage > 0) {
                            // Appliquer la réduction
                            $discount = ($total_amount * $discount_percentage) / 100;
                            $total_amount -= $discount;
                        }
                    }
                }
                $total_amount = max($total_amount, 0);

                $application_fee_amount = 0; // Frais pour la plateforme en centimes
                $transfer_data = []; // Données pour le compte Stripe Connect
                $params = [
                    'payment_method_types' => ['card'],
                    'line_items' => [[
                        'price_data' => [
                            'currency' => 'eur',
                            'product_data' => [
                                'name' => 'Réservation Activité #' . $reservation_data['activite'],
                            ],
                            'unit_amount' => $total_amount * 100, // Montant en centimes
                        ],
                        'quantity' => 1,
                    ]],
                    'mode' => 'payment',
                    'success_url' => site_url('/process-payment') . '?session_id={CHECKOUT_SESSION_ID}&reservation_id=' . $reservation_id, // Redirection intermédiaire
                    'cancel_url' => site_url('/recapitulatif') . '?reservation_id=' . $reservation_id,
                    'customer_email' => get_post_meta($reservation_id, '_rp_email', true),
                    'metadata' => [
                        'reservation_id' => $reservation_id, // Inclure l'ID de réservation
                    ],
                    'invoice_creation' => [
                        'enabled' => true,
                    ],

                ];
                if (!empty($stripe_connect_id) && $commission_percentage > 0) {
                    $application_fee_amount = (($total_amount*100) * $commission_percentage) / 100;

                    $transfer_data = [
                        'destination' => $stripe_connect_id, // Compte Stripe Connect
                    ];

                    $params['payment_intent_data'] = [
                        'application_fee_amount' => $application_fee_amount, // Frais de la plateforme
                        'transfer_data' => $transfer_data, // Données pour le compte Stripe Connect
                    ];
                }


                // Créer une session Stripe Checkout
                try {
                    $session = \Stripe\Checkout\Session::create($params);
                    wp_redirect($session->url);
                    exit;
                } catch (\Stripe\Exception\ApiErrorException $e) {
                    wp_die('Erreur Stripe : ' . $e->getMessage());
                }
            } else {
                wp_die('Réservation invalide.');
            }
        } else {
            wp_die('Méthode non autorisée ou données manquantes.');
        }
    }
}

add_action('template_redirect', 'rp_handle_payment_request');


function rp_register_payment_processing_endpoint()
{
    add_rewrite_rule(
        '^process-payment/?$',
        'index.php?process_payment=1',
        'top'
    );
}

add_action('init', 'rp_register_payment_processing_endpoint');

function rp_add_process_payment_query_vars($vars)
{
    $vars[] = 'process_payment';
    return $vars;
}

add_filter('query_vars', 'rp_add_process_payment_query_vars');


function rp_handle_payment_processing()
{
    if (get_query_var('process_payment') == 1) {
        // Vérifier si session_id est présent
        if (!isset($_GET['session_id'])) {
            wp_die('Session ID manquant.');
        }

        if (!isset($_GET['reservation_id'])) {
            wp_die('Reservation non trouvé');
        }

        $session_id = sanitize_text_field($_GET['session_id']);

        // Charger Stripe PHP

        // Configurer Stripe avec la clé API privée
        $stripe_private = get_option('rp_stripe_private');
        if (!$stripe_private) {
            wp_die('Clé Stripe privée manquante.');
        }
        \Stripe\Stripe::setApiKey($stripe_private);

        try {
            // Récupérer la session de paiement Stripe
            $session = \Stripe\Checkout\Session::retrieve($session_id);
            $payment_intent_id = $session->payment_intent;

            // Vérifier le statut du paiement
            if ($session->payment_status === 'paid') {

                // Trouver la réservation associée via metadata ou URL précédente
                $reservation_id = intval($_GET['reservation_id']);

                if ($reservation_id && get_post_type($reservation_id) === 'reservation') {
                    update_post_meta($reservation_id, '_rp_state', 'done'); // Passer en "terminé"
                    update_post_meta($reservation_id, '_rp_stripe_charge_id', $payment_intent_id);
                    update_post_meta($reservation_id, '_rp_is_paid', 1);

                    $reservation_data = [
                        'id' => $reservation_id,
                        'date'      => get_post_meta($reservation_id, '_rp_date', true),
                        'heure'     => get_post_meta($reservation_id, '_rp_heure', true),
                        'nom'       => get_post_meta($reservation_id, '_rp_nom', true),
                        'prenom'    => get_post_meta($reservation_id, '_rp_prenom', true),
                        'email'     => get_post_meta($reservation_id, '_rp_email', true),
                        'langue'    => get_post_meta($reservation_id, '_rp_langue', true)  == 'fr' ? 'Français' : 'Anglais',
                        'adultes'   => get_post_meta($reservation_id, '_rp_nb_adultes', true),
                        'enfants'   => get_post_meta($reservation_id, '_rp_nb_enfants', true),
                        'activite' => intval(get_post_meta($reservation_id, '_rp_activite_id', true)),
                    ];

                    $prix_adulte = floatval(get_post_meta($reservation_data['activite'], '_rp_prix_adulte', true));
                    $prix_enfant = floatval(get_post_meta($reservation_data['activite'], '_rp_prix_enfant', true));

                    // Calculer le total
                    // apply code promo if exist
                    $total = ($reservation_data['adultes'] * $prix_adulte) + ($reservation_data['enfants'] * $prix_enfant);
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
                    $total = max($total, 0);

                    $subject_client = "Confirmation de votre réservation";
                    $message_client = "
    Bonjour {$reservation_data['prenom']} {$reservation_data['nom']},

    Merci pour votre réservation. Voici les détails de votre réservation :

    - Date : {$reservation_data['date']}
    - Heure : {$reservation_data['heure']}
    - Nombre d'adultes : {$reservation_data['adultes']}
    - Nombre d'enfants : {$reservation_data['enfants']}
    - Langue : {$reservation_data['langue']}
    - Total : {$total}

    Nous avons hâte de vous accueillir !

    Cordialement,
    L’équipe de Playgreen
";

                    wp_mail($reservation_data['email'], $subject_client, $message_client);
                    $admin_email = get_option('admin_email'); // Email de l'administrateur WordPress
                    $subject_admin = "Nouvelle réservation effectuée";
                    $message_admin = "
    Une nouvelle réservation a été effectuée. Voici les détails :

    - Nom : {$reservation_data['prenom']} {$reservation_data['nom']}
    - Email : {$reservation_data['email']}
    - Date : {$reservation_data['date']}
    - Heure : {$reservation_data['heure']}
    - Nombre d'adultes : {$reservation_data['adultes']}
    - Nombre d'enfants : {$reservation_data['enfants']}
    - Langue : {$reservation_data['langue']}
    - Total : {$total}

    Vous pouvez consulter la réservation dans l'administration WordPress.
";

                    wp_mail($admin_email, $subject_admin, $message_admin);



                    $thank_you_page_id = get_option('rp_thank_you_page', 0);
                    if ($thank_you_page_id) {
                        $thank_you_page_url = get_permalink($thank_you_page_id);
                        wp_redirect(add_query_arg('reservation_id', $reservation_id, $thank_you_page_url));
                        exit;
                    } else {
                        wp_die('La page de félicitation n\'est pas configurée.');
                    }
                    exit;
                } else {
                    wp_die('Réservation invalide.');
                }
            } else {
                // Rediriger vers la page d'annulation
                wp_redirect(site_url('/recapitulatif'));
                exit;
            }
        } catch (\Stripe\Exception\ApiErrorException $e) {
            wp_die('Erreur Stripe : ' . $e->getMessage());
        }
    }
}

add_action('template_redirect', 'rp_handle_payment_processing');
