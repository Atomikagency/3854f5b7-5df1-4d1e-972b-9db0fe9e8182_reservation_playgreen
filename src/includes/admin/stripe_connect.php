<?php

function rp_add_stripe_connect_page()
{
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


function rp_render_stripe_connect_page()
{
    if (!current_user_can('manage_options')) {
        return;
    }
    $stripe_private = get_option('rp_stripe_private');
    if (!$stripe_private) {
        echo '<p style="color: red;">Veuillez configurer votre clé API Stripe dans les réglages.</p>';
        return;
    }

    \Stripe\Stripe::setApiKey($stripe_private);
    $stripe = new \Stripe\StripeClient($stripe_private);

    if (isset($_POST['generate_link'])) {

        // account : ct_1QV7RcJIbhuXUPDIZfMrSh8d
        // person : cpt_1QV7RcJIbhuXUPDIZELS4fQP
        // bank : btok_1QV7RdJIbhuXUPDIyItj8ZeI
        try {

            $account = $stripe->accounts->create([
                'country' => 'FR',
                'type' => 'custom',
                'business_profile' => [
                    'mcc' => 7991,
                    'url' => 'www.kevinjaniky.fr'
                ],
                'external_account' => $_POST['token-bank_account'],
                'capabilities' => [
                    'card_payments' => ['requested' => true],
                    'transfers' => ['requested' => true],
                ],
                'account_token' => $_POST['token-account']
            ]);

//            $bank = $stripe->accounts->createExternalAccount(
//                $account->id,
//                ['external_account' => $_POST['token-bank_account']]
//            );
//
//
//            $stripe->accounts->update(
//                $account->id,
//                ['external_account' => $_POST['token-bank_account']]
//            );
//
//            id : acct_1QV6sEQs2Xmtm1Gt

            $person = $stripe->accounts->createPerson(
                $account->id,
                [
//                    'first_name' => 'Kevin',
//                    'last_name' => 'jNAIK',
                    'person_token' => $_POST['token-person']
                ]
            );

            echo '<pre>';
            print_r($person);
            echo '</pre>';
            die();


            $params = [
                'refresh_url' => admin_url('admin.php?page=rp-stripe-connect'),
                'return_url' => admin_url('admin.php?page=rp-stripe-connect'),
                'type' => 'account_update',
                'account' => $account
            ];

            if (!empty($_POST['stripe_account_id'])) {
                $params['account'] = $_POST['stripe_account_id'];
            } else {
                $account = \Stripe\Account::create([]);
                $params['account'] = $account['id'];
            }


            $accountLink = \Stripe\AccountLink::create($params);

            echo '<p style="color: green;">Lien généré avec succès :</p>';
            echo '<a href="' . esc_url($accountLink->url) . '" target="_blank">' . esc_html($accountLink->url) . '</a>';


        } catch (\Stripe\Exception\ApiErrorException $e) {
            echo '<p style="color: red;">Erreur Stripe : ' . esc_html($e->getMessage()) . '</p>';
        }
    }
    $template_path = RESERVATION_PLAYGREEN_PLUGIN_DIR . 'views/stripe_connect.php';

    if (!$template_path) {
        return '';
    }

    ob_start();
    include $template_path;
    echo ob_get_clean();
    ?>

    <!--    <div class="wrap">-->
    <!--        <h1>Stripe Connect - Générer un Lien</h1>-->
    <!--        <form method="post">-->
    <!--            --><?php //wp_nonce_field('rp_generate_stripe_link', 'rp_generate_stripe_link_nonce');
    ?>
    <!--            <table class="form-table">-->
    <!--                <tr>-->
    <!--                    <th scope="row">-->
    <!--                        <label for="stripe_account_id">ID du compte Stripe (facultatif)</label>-->
    <!--                    </th>-->
    <!--                    <td>-->
    <!--                        <input type="text" name="stripe_account_id" id="stripe_account_id" placeholder="ex: acct_12345" style="width: 100%;">-->
    <!--                        <p class="description">Laissez vide pour créer un nouveau compte Stripe Connect.</p>-->
    <!--                    </td>-->
    <!--                </tr>-->
    <!--            </table>-->
    <!--            <p class="submit">-->
    <!--                <input type="submit" name="generate_link" class="button-primary" value="Générer le Lien">-->
    <!--            </p>-->
    <!--        </form>-->
    <!--    </div>-->

    <?php
}
