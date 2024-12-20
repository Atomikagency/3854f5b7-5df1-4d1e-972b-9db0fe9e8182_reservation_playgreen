<?php

function rp_add_stripe_connect_page() {
    add_menu_page(
        'Stripe Connect',
        'Stripe Connect',
        'manage_options',
        'rp-stripe-connect',
        'rp_render_stripe_connect_page',
        'dashicons-admin-network',
        90
    );
}
add_action('admin_menu', 'rp_add_stripe_connect_page');


function rp_render_stripe_connect_page() {
    // Vérifier les permissions
    if (!current_user_can('manage_options')) {
        return;
    }

    // Charger Stripe PHP
    // Récupérer la clé API Stripe depuis les réglages
    $stripe_private = get_option('rp_stripe_private');
    if (!$stripe_private) {
        echo '<p style="color: red;">Veuillez configurer votre clé API Stripe dans les réglages.</p>';
        return;
    }

    \Stripe\Stripe::setApiKey($stripe_private);
    $stripe = new \Stripe\StripeClient($stripe_private);


    if (isset($_POST['generate_link'])) {
        try {

            $account = $stripe->accounts->create([
                'country' => 'FR',
                'type' => 'custom',
                'business_profile' => [
                    'mcc' => 7991,
                    'url' => $_POST['company_website']
                ],
                'external_account' => $_POST['token-bank_account'],
                'capabilities' => [
                    'card_payments' => ['requested' => true],
                    'transfers' => ['requested' => true],
                ],
                'account_token' => $_POST['token-account']
            ]);

            $person = $stripe->accounts->createPerson(
                $account->id,[
                    'person_token' => $_POST['token-person'],
                ]
            );


            echo '<p style="color: green;">Compte crée</p>';
        } catch (\Stripe\Exception\ApiErrorException $e) {
            echo '<p style="color: red;">Erreur Stripe : ' . esc_html($e->getMessage()) . '</p>';
        }
    }

    ob_start();
    $view_file = RESERVATION_PLAYGREEN_PLUGIN_DIR . 'views/stripe_connect.admin.php';
    if (file_exists($view_file)) {
        include $view_file;
    } else {
        echo '';
    }
    echo ob_get_clean();
}
