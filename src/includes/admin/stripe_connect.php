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

    // Générer un lien Stripe Connect
    if (isset($_POST['generate_link'])) {
        try {

            $params = [
                'refresh_url' => admin_url('admin.php?page=rp-stripe-connect'),
                'return_url' => admin_url('admin.php?page=rp-stripe-connect'),
                'type' => 'account_onboarding',
            ];

            if(!empty($_POST['stripe_account_id'])){
                $params['account'] = $_POST['stripe_account_id'];
            }else{
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

    ?>

    <div class="wrap">
        <h1>Stripe Connect - Générer un Lien</h1>
        <form method="post">
            <?php wp_nonce_field('rp_generate_stripe_link', 'rp_generate_stripe_link_nonce'); ?>
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="stripe_account_id">ID du compte Stripe (facultatif)</label>
                    </th>
                    <td>
                        <input type="text" name="stripe_account_id" id="stripe_account_id" placeholder="ex: acct_12345" style="width: 100%;">
                        <p class="description">Laissez vide pour créer un nouveau compte Stripe Connect.</p>
                    </td>
                </tr>
            </table>
            <p class="submit">
                <input type="submit" name="generate_link" class="button-primary" value="Générer le Lien">
            </p>
        </form>
    </div>

    <?php
}
