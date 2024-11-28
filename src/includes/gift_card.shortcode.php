<?php
add_shortcode('rp_gift_card', 'rp_gift_card_shortcode');
function rp_gift_card_shortcode($atts) {
    // Traiter le formulaire si soumis
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['gift_card_form_submit'])) {
        rp_process_gift_card_form($_POST);
    }

    // Charger la vue
    ob_start();
    $view_file = RESERVATION_PLAYGREEN_PLUGIN_DIR . 'views/gift_card.php';
    if (file_exists($view_file)) {
        include $view_file;
    } else {
        echo '<p>Le fichier de vue est introuvable.</p>';
    }
    return ob_get_clean();
}


function rp_process_gift_card_form($data) {
    // Valider et nettoyer les données
    $theme = sanitize_text_field($data['theme'] ?? '');
    $montant = sanitize_text_field($data['montant'] ?? '');
    $message = isset($data['add_message']) ? sanitize_textarea_field($data['message'] ?? '') : 'Aucun message';
    $email = sanitize_email($data['email'] ?? '');
    $send_direct = isset($data['send_direct']) ? 'Oui' : 'Non';
    $emailSend = sanitize_email($data['emailSend'] ?? '');
    $rgpd = isset($data['rgpd']) ? 'Oui' : 'Non';

    // Vérification des champs obligatoires
    if (empty($theme) || empty($montant) || empty($email) || $rgpd !== 'Oui') {
        echo '<p>Veuillez remplir tous les champs obligatoires et accepter les conditions RGPD.</p>';
        return;
    }

    // Construire le contenu de l'email
    $email_content = "
        Nouvelle demande de carte cadeau :
        - Thème : $theme
        - Montant : $montant €
        - Message : $message
        - Email de l'acheteur : $email
        - Envoi direct : $send_direct
        " . ($send_direct === 'Oui' ? "- Email du destinataire : $emailSend\n" : '') . "
        - RGPD accepté : $rgpd
    ";

    // Envoyer l'email
    $to = 'contact@playgreen-paris.fr';
    $subject = 'Demande de carte cadeau';
    $headers = [
        'Content-Type: text/plain; charset=UTF-8',
        'From: Playgreen <no-reply@playgreen-paris.fr>',
    ];

    if (wp_mail($to, $subject, $email_content, $headers)) {
        echo '<p>Merci ! Votre demande de carte cadeau a bien été envoyée.</p>';
    } else {
        echo '<p>Une erreur s\'est produite lors de l\'envoi de votre demande. Veuillez réessayer.</p>';
    }
}
