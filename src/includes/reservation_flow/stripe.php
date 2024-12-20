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
                            $total_amount = max($total_amount - $remainingToSub, 0);
                        }
                    }
                }

                $total_amount = max($total_amount, 0);

                $taxRate = get_option('_rp_stripe_tax_rate_id');
                if (empty($taxRate)) {
                    $tax_rate = \Stripe\TaxRate::create([
                        'display_name' => 'TVA',
                        'percentage' => 10.0,
                        'inclusive' => true,
                        'country' => 'FR', // Pays de la TVA
                        'jurisdiction' => 'FR', // Juridiction
                        'description' => 'TVA incluse',
                    ]);

                    // Sauvegarder l'ID du taux de taxe dans les options WordPress
                    update_option('_rp_stripe_tax_rate_id', $tax_rate->id);
                    $taxRate = $tax_rate->id;
                }


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
                            'tax_behavior' => 'inclusive'
                        ],
                        'quantity' => 1,
                        'tax_rates' => [$taxRate]
                    ]],
                    'mode' => 'payment',
                    'success_url' => site_url('/process-payment') . '?session_id={CHECKOUT_SESSION_ID}&reservation_id=' . $reservation_id, // Redirection intermédiaire
                    'cancel_url' => site_url('/recapitulatif') . '?reservation_id=' . $reservation_id,
                    'metadata' => [
                        'reservation_id' => $reservation_id, // Inclure l'ID de réservation
                    ],
                    'invoice_creation' => [
                        'enabled' => true,
                    ],
                    'billing_address_collection' => 'required'
                ];


                $enterprise = get_post_meta($reservation_id, '_rp_enterprise_name', true);
                if (!empty($enterprise)) {
                    $customer_data = [
                        'email' => get_post_meta($reservation_id, '_rp_email', true),
                        'name' => $enterprise,
                    ];
                    $customer = \Stripe\Customer::create($customer_data);
                    $params['customer'] = $customer->id;
                } else {
                    $params['customer_email'] = get_post_meta($reservation_id, '_rp_email', true);
                }

                if (!empty($stripe_connect_id) && $commission_percentage > 0) {
                    $application_fee_amount = (($total_amount * 100) * $commission_percentage) / 100;

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
                        'date' => get_post_meta($reservation_id, '_rp_date', true),
                        'heure' => get_post_meta($reservation_id, '_rp_heure', true),
                        'nom' => get_post_meta($reservation_id, '_rp_nom', true),
                        'prenom' => get_post_meta($reservation_id, '_rp_prenom', true),
                        'email' => get_post_meta($reservation_id, '_rp_email', true),
                        'tel' => get_post_meta($reservation_id, '_rp_tel', true),
                        'langue' => get_post_meta($reservation_id, '_rp_langue', true) == 'fr' ? 'Français' : 'Anglais',
                        'adultes' => get_post_meta($reservation_id, '_rp_nb_adultes', true),
                        'enfants' => get_post_meta($reservation_id, '_rp_nb_enfants', true),
                        'activite' => intval(get_post_meta($reservation_id, '_rp_activite_id', true)),
                        'message' => get_post_meta($reservation_id,'_rp_message',true)
                    ];

                    $prix_adulte = floatval(get_post_meta($reservation_data['activite'], '_rp_prix_adulte', true));
                    $prix_enfant = floatval(get_post_meta($reservation_data['activite'], '_rp_prix_enfant', true));
                    $activite_name = get_the_title($reservation_data['activite']);

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
                                $total = min($total, $remainingToSub);
                                $consumedCarteCadeau = update_post_meta($gift->ID, 'consumed', ($total + $consumedCarteCadeau));
                                $total = max($total - $remainingToSub, 0);
                            }
                        }
                    }

                    $total = max($total, 0);
                    $is_prix_fixe = get_post_meta($reservation_data['activite'], '_rp_is_prix_fixe', true) === 'on' ? 'on' : 'off';
                    $info_joueurs = "";
                    if($is_prix_fixe === 'off'){
                        $info_joueurs = "
    - Nombre d'adultes : {$reservation_data['adultes']}
    - Nombre d'enfants : {$reservation_data['enfants']}";
                    }

                    $adresse = get_post_meta($reservation_data['activite'], '_rp_adresse_rdv', true);
                    $pdf_enigme = get_post_meta($reservation_data['activite'], '_rp_pdf_enigme', true);
                    $subject_client = "Info relatives à votre réservation (à lire en entier, svp !)";
                    $message_client = "
    Bonjour {$reservation_data['prenom']} {$reservation_data['nom']},

    Merci pour votre réservation. Voici les détails de votre réservation :

    - Date : {$reservation_data['date']}
    - Heure : {$reservation_data['heure']}{$info_joueurs}
    - Langue : {$reservation_data['langue']}
    - Total : {$total}
    - Activité : {$activite_name}
    - Lieux de rendez-vous : {$adresse}
    
    La partie se jouera à l’aide d’un téléphone portable, pensez à avoir de la batterie. Vous recevrez un code permettant de débloquer l’escape game deux heures avant le début de l'activité. 

    Cordialement,
    L’équipe de Playgreen
";
                    $attachments = [];
//                    if (!empty($pdf_enigme)) {
//                        $upload_dir = wp_upload_dir();
//                        $relative_path = str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $pdf_enigme);
//                        if (file_exists($relative_path)) {
//                            $attachments[] = $relative_path;
//                        }
//                    }

                    wp_mail($reservation_data['email'], $subject_client, $message_client, '', $attachments);


                    $admin_email = 'contact@playgreen-paris.com';
                    $subject_admin = "Nouvelle réservation effectuée";
                    $message_admin = "
    Une nouvelle réservation a été effectuée. Voici les détails :

    - Activité : {$activite_name}
    - Nom : {$reservation_data['prenom']} {$reservation_data['nom']}
    - Email : {$reservation_data['email']}
    - Numéro de téléphone : {$reservation_data['tel']}
    - Date : {$reservation_data['date']}
    - Heure : {$reservation_data['heure']}
    - Nombre d'adultes : {$reservation_data['adultes']}
    - Nombre d'enfants : {$reservation_data['enfants']}
    - Langue : {$reservation_data['langue']}
    - Total : {$total}
    - Message : {$reservation_data['message']}

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
