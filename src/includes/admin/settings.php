<?php

// Ajouter une page de settings au menu admin
function rp_add_settings_page() {
    add_menu_page(
        'Réglages', // Titre de la page
        'Réservations',                   // Titre du menu
        'manage_options',                // Capacité requise
        'rp-settings',                   // Slug du menu
        'rp_render_settings_page',       // Fonction de rendu
        'dashicons-admin-generic',       // Icône
        80                               // Position dans le menu
    );
}
add_action('admin_menu', 'rp_add_settings_page');

// Rendu de la page de settings
function rp_render_settings_page() {
    // Vérifier les permissions
    if (!current_user_can('manage_options')) {
        return;
    }

    // Sauvegarder les données si soumises
    if (isset($_POST['rp_save_settings'])) {
        check_admin_referer('rp_save_settings_nonce', 'rp_settings_nonce'); // Vérification du nonce

        // Récupérer et valider les données
        $commission = intval($_POST['rp_commission']);
        $commission = ($commission >= 0 && $commission <= 100) ? $commission : 0;

        $stripe_public = sanitize_text_field($_POST['rp_stripe_public']);
        $stripe_private = sanitize_text_field($_POST['rp_stripe_private']);

        // Sauvegarder les options
        update_option('rp_commission', $commission);
        update_option('rp_stripe_public', $stripe_public);
        update_option('rp_stripe_private', $stripe_private);

        // Afficher un message de succès
        echo '<div class="updated"><p>Réglages sauvegardés avec succès.</p></div>';
    }

    // Récupérer les valeurs existantes
    $commission = get_option('rp_commission', 0);
    $stripe_public = get_option('rp_stripe_public', '');
    $stripe_private = get_option('rp_stripe_private', '');

    ?>

    <div class="wrap">
        <h1>Réglages </h1>
        <form method="post">
            <?php wp_nonce_field('rp_save_settings_nonce', 'rp_settings_nonce'); ?>

            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="rp_commission">Pourcentage de commission (%)</label>
                    </th>
                    <td>
                        <input type="number" id="rp_commission" name="rp_commission" value="<?php echo esc_attr($commission); ?>" min="0" max="100" step="1" required>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="rp_stripe_public">Stripe Public API Key</label>
                    </th>
                    <td>
                        <input type="password" id="rp_stripe_public" name="rp_stripe_public" value="<?php echo esc_attr($stripe_public); ?>" required>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="rp_stripe_private">Stripe Private API Key</label>
                    </th>
                    <td>
                        <input type="password" id="rp_stripe_private" name="rp_stripe_private" value="<?php echo esc_attr($stripe_private); ?>" required>
                    </td>
                </tr>
            </table>

            <p class="submit">
                <input type="submit" name="rp_save_settings" class="button-primary" value="Enregistrer les réglages">
            </p>
        </form>
    </div>

    <?php
}
