<?php
function rp_gift_register_payment_endpoint()
{
    add_rewrite_rule(
        '^reservation-payment-gift/?$',
        'index.php?reservation_payment_gift=1',
        'top'
    );
}

add_action('init', 'rp_gift_register_payment_endpoint');

function rp_gift_add_query_vars($vars)
{
    $vars[] = 'reservation_payment_gift';
    return $vars;
}

add_filter('query_vars', 'rp_gift_add_query_vars');

// Charger Stripe PHP

// Gérer la requête Stripe Checkout
function rp_gift_handle_payment_request()
{
    if (get_query_var('reservation_payment_gift') == 1) {
        if (!empty($_POST['gift_card_form_submit'])) {
            $stripe_private = get_option('rp_stripe_private');
            if (!$stripe_private) {
                wp_die('Clé Stripe privée manquante.');
            }
            $email = sanitize_email($_POST['email'] ?? '');
            $emailSend = sanitize_email($_POST['emailSend'] ?? '');
            $theme = sanitize_text_field($_POST['theme'] ?? '');
            $montant = sanitize_text_field($_POST['montant'] ?? '');
            $from = sanitize_text_field($_POST['from'] ?? '');
            $to = sanitize_text_field($_POST['to'] ?? '');
            $message = sanitize_textarea_field($_POST['message'] ?? '');

            if (empty($theme) || empty($montant) || empty($email) || empty($from) || empty($to) || empty($emailSend)) {
                echo '<p>Veuillez remplir tous les champs obligatoires et accepter les conditions RGPD.</p>';
                return;
            }

            \Stripe\Stripe::setApiKey($stripe_private);

            $code_carte_cadeau = generate_random_string();

            $gift_id = wp_insert_post([
                'post_type' => 'carte_cadeau',
                'post_status' => 'publish',
                'post_title' => $code_carte_cadeau
            ]);

            if ($gift_id) {
                update_post_meta($gift_id, 'email', $email);
                update_post_meta($gift_id, 'emailSend', $emailSend);
                update_post_meta($gift_id, 'theme', $theme);
                update_post_meta($gift_id, 'montant', $montant);
                update_post_meta($gift_id, 'from', $from);
                update_post_meta($gift_id, 'to', $to);
                update_post_meta($gift_id, 'message', $message);
                update_post_meta($gift_id, 'state', 'En cours');
            } else {
                wp_die('Erreur lors de la reservation de carte cadeau');
            }


            $params = [
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'eur',
                        'product_data' => [
                            'name' => 'Carte cadeau #' . $gift_id,
                        ],
                        'unit_amount' => $montant * 100,
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => site_url('/process-payment-gift-post-stripe') . '?session_id={CHECKOUT_SESSION_ID}&gift_id=' . $gift_id, // Redirection intermédiaire
                'cancel_url' => site_url('/carte-cadeau'),
                'customer_email' => $email,
                'metadata' => [
                    'gift_id' => $gift_id,
                ],
                'invoice_creation' => [
                    'enabled' => true,
                ],

            ];
            try {
                $session = \Stripe\Checkout\Session::create($params);
                wp_redirect($session->url);
                exit;
            } catch (\Stripe\Exception\ApiErrorException $e) {
                wp_die('Erreur Stripe : ' . $e->getMessage());
            }

        } else {
            wp_die('Méthode non autorisée ou données manquantes.');
        }
    }
}

add_action('template_redirect', 'rp_gift_handle_payment_request');

function rp_gift_register_payment_processing_endpoint()
{
    add_rewrite_rule(
        '^process-payment-gift-post-stripe/?$',
        'index.php?process_payment_gift_post_stripe=1',
        'top'
    );
}

add_action('init', 'rp_gift_register_payment_processing_endpoint');

function rp_gift_add_process_payment_query_vars($vars)
{
    $vars[] = 'process_payment_gift_post_stripe';
    return $vars;
}

add_filter('query_vars', 'rp_gift_add_process_payment_query_vars');

function generate_random_string($length = 8)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $characters_length = strlen($characters);
    $random_string = '';

    for ($i = 0; $i < $length; $i++) {
        $random_string .= $characters[rand(0, $characters_length - 1)];
    }

    return strtoupper($random_string);
}

function rp_gift_handle_payment_processing()
{
    if (get_query_var('process_payment_gift_post_stripe') == 1) {
        // Vérifier si session_id est présent
        if (!isset($_GET['session_id'])) {
            wp_die('Session ID manquant.');
        }

        if (!isset($_GET['gift_id'])) {
            wp_die('Gift non trouvé');
        }

        $session_id = sanitize_text_field($_GET['session_id']);

        $stripe_private = get_option('rp_stripe_private');
        if (!$stripe_private) {
            wp_die('Clé Stripe privée manquante.');
        }
        \Stripe\Stripe::setApiKey($stripe_private);

        try {
            $session = \Stripe\Checkout\Session::retrieve($session_id);

            if (empty($session->metadata->gift_id)) {
                wp_die('Carte cadeau non trouvé');
            }
            $payment_intent_id = $session->payment_intent;

            if ($session->payment_status === 'paid') {

                $gift_id = intval($_GET['gift_id']);
                if ($gift_id && get_post_type($gift_id) === 'carte_cadeau') {
                    update_post_meta($gift_id, 'state', 'done');
                    update_post_meta($gift_id, 'stripe_payment_id', $payment_intent_id);
                    // PUT all data from gift_id ( load also metadata )
                    $data = [
                        'gift_id' => $gift_id,
                        'code' => get_the_title($gift_id),
                        'buyer_email' => get_post_meta($gift_id, 'email', true),
                        'recipient_email' => get_post_meta($gift_id, 'emailSend', true),
                        'message' => get_post_meta($gift_id, 'message', true),
                        'theme' => get_post_meta($gift_id, 'theme', true),
                        'from' => get_post_meta($gift_id, 'from', true),
                        'to' => get_post_meta($gift_id, 'to', true),
                        'montant' => get_post_meta($gift_id, 'montant', true),
                    ];

                    $pdf_content = generate_pdf_from_fpdi($data);
                    send_gift_purchase_notification_email($data);
                    send_gift_card_to_recipient($data, $pdf_content);
                    send_gift_card_to_buyer($data, $pdf_content);

                    wp_redirect(add_query_arg('gift_id', $gift_id, '/carte-cadeau-felicitation'));

                } else {
                    wp_die('Carte cadeau invalide.');
                }
            } else {
                wp_redirect(site_url('/carte-cadeau'));
                exit;
            }
        } catch (\Stripe\Exception\ApiErrorException $e) {
            wp_die('Erreur Stripe : ' . $e->getMessage());
        }
    }
}
add_action('template_redirect', 'rp_gift_handle_payment_processing');

function send_gift_purchase_notification_email($data){
        $to = 'contact@playgreen-paris.com';
        $subject = 'Nouvelle carte cadeau achetée';
        $body = 'Une nouvelle carte cadeau a été achetée. Détails : ' . print_r($data, true);
        $headers = ['Content-Type: text/html; charset=UTF-8'];

        wp_mail($to, $subject, nl2br($body), $headers);
}

function send_gift_card_to_buyer($data, $pdf_content)
{
    $to = $data['buyer_email'];
    $subject = 'Confirmation de votre achat de carte cadeau Playgreen';
    $body = 'Merci pour votre achat de carte cadeau. Voici une copie de la carte que vous avez achetée.\n\nMessage : ' . $data['message'];
    $headers = ['Content-Type: text/html; charset=UTF-8'];

    $attachments = wp_upload_bits('carte_cadeau.pdf', null, $pdf_content);

    if (!$attachments['error']) {
        wp_mail($to, $subject, nl2br($body), $headers, [$attachments['file']]);
    }else{
        echo '<pre>';
        var_dump($attachments);
        echo '</pre>';
        die();
    }
}

function send_gift_card_to_recipient($data, $pdf_content)
{
    $to = $data['recipient_email'];
    $subject = 'Votre carte cadeau Playgreen';
    $body = 'Vous avez reçu une carte cadeau de la part de ' . $data['buyer_email'] . ".\n\nMessage : " . $data['message'];
    $headers = ['Content-Type: text/html; charset=UTF-8'];

    $attachments = wp_upload_bits('carte_cadeau.pdf', null, $pdf_content);
    if (!$attachments['error']) {
        wp_mail($to, $subject, nl2br($body), $headers, [$attachments['file']]);
    }
}


function generate_pdf_from_fpdi($data)
{
    $coords = createCoordonnates($data);
    return add_content_to_pdf($coords, $data['theme']);
}

function createCoordonnates($data)
{
    $coords = [];
    switch ($data['theme']) {
        case '1':
            $coords[] = ['x' => 63, 'y' => 82, 'text' => $data['to']];
            $coords[] = ['x' => 56, 'y' => 94, 'text' => $data['from']];
            $coords[] = ['x' => 125, 'y' => 94, 'text' => $data['code']];
            break;

        case '2':
            $coords[] = ['x' => 55, 'y' => 76.5, 'text' => $data['to']];
            $coords[] = ['x' => 50, 'y' => 95, 'text' => $data['from']];
            $coords[] = ['x' => 55, 'y' => 114, 'text' => $data['code']];
            break;

        case '3':
            $coords[] = ['x' => 78, 'y' => 87, 'text' => $data['to']];
            $coords[] = ['x' => 73, 'y' => 99, 'text' => $data['from']];
            $coords[] = ['x' => 82, 'y' => 121, 'text' => $data['code']];
            $coords[] = ['x' => 86, 'y' => 110, 'text' => $data['montant'] . ' euros'];
            break;
        
        default:
            # code...
            break;
    }

    return $coords;
}

function add_content_to_pdf($coordinates = [], $theme = '1')
{
    $pdf = new \setasign\Fpdi\Fpdi();
    $default_pdf_path = RESERVATION_PLAYGREEN_PLUGIN_DIR . 'assets/gift/theme-' . $theme . '.pdf';
    if (!file_exists($default_pdf_path)) {
        die('Default PDF not found.');
    }

    $pageCount = $pdf->setSourceFile($default_pdf_path);
    $tplIdx = $pdf->importPage(1);
    $pdf->AddPage();
    $pdf->useTemplate($tplIdx);
    //$pdf = debugGrid($pdf);

    foreach ($coordinates as $coord) {
        add_text_to_coord($pdf, $coord['text'], $coord);
    }

    return $pdf->Output('S'); // 'I' outputs to the browser
}

function add_text_to_coord($pdf, $text, $coord)
{
    $x = $coord['x'];
    $y = $coord['y'];
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Text($x + 2, $y - 2, utf8_decode($text));
}

function debugGrid($pdf)
{

    $pdf->SetDrawColor(200, 200, 200); // Light gray for grid lines
    $pdf->SetTextColor(50, 50, 50);    // Darker color for labels
    $pdf->SetLineWidth(0.1);           // Thin lines for the grid
    $pdf->SetFont('Arial', '', 8);     // Small font for the labels
    $page_width = $pdf->GetPageWidth();
    $page_height = $pdf->GetPageHeight();

    $grid_size = 5;
    for ($x = 0; $x <= $page_width; $x += $grid_size) {
        $pdf->Line($x, 0, $x, $page_height);  // Draw vertical line

        // Add labels at every x coordinate
        $pdf->Text($x + 1, 5, $x);  // X-axis labels
    }

    // Draw horizontal grid lines
    for ($y = 0; $y <= $page_height; $y += $grid_size) {
        $pdf->Line(0, $y, $page_width, $y);   // Draw horizontal line

        // Add labels at every y coordinate
        $pdf->Text(1, $y + 5, utf8_decode($y));  // Y-axis labels
    }

    return $pdf;
}