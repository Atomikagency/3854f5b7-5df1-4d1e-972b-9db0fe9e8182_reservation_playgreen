<form id="gift_card" method="post" class="gift_card_form" action="<?php echo site_url('/reservation-payment-gift') ?>">

    <div class="col-2">
        <!-- Email -->
        <div class="email_container">
            <p class="gift_card_section_title">Votre adresse email</p>
            <label>
                <input type="email" name="email" placeholder="dupont.jean@mail.com" required>
            </label>
            <small>Vous recevrez la confirmation de votre commande à cette adresse</small>
        </div>

        <!-- Envoi direct -->
        <div>
            <p class="gift_card_section_title">A qui voulez-vous envoyer votre carte cadeau ?</p>
            <input type="email" name="emailSend" placeholder="Email du destinataire" required>
            <small>Adresse email qui recevera la carte cadeau</small>
        </div>
    </div>

    <!-- Thèmes -->
    <p class="gift_card_section_title">Choisissez votre thème</p>
    <div class="themes_container">
        <label>
            <input type="radio" name="theme" value="1">
            <img src="/wp-content/uploads/2024/12/gift-card-theme-1.png" alt="Thème 1">
        </label>
        <label>
            <input type="radio" name="theme" value="4">
            <img src="/wp-content/uploads/2024/12/gift-card-theme-4.png" alt="Thème 4">
        </label>
        <label>
            <input type="radio" name="theme" value="3">
            <img src="/wp-content/uploads/2024/12/gift-card-theme-3.png" alt="Thème 3">
        </label>
        <label>
            <input type="radio" name="theme" value="2">
            <img src="/wp-content/uploads/2024/12/gift-card-theme-2.png" alt="Thème 2">
        </label>
    </div>

    <!-- Montant -->
    <div class="montant_container" style="margin-bottom: 10px;">
        <p class="gift_card_section_title">Choisissez un montant</p>
        <label>
            <input type="radio" name="preset_amount" value="50"> 50 €
        </label>
        <label>
            <input type="radio" name="preset_amount" value="100"> 100 €
        </label>
        <label>
            <input type="radio" name="preset_amount" value="150"> 150 €
        </label>
        <label>
            <input type="radio" name="preset_amount" value="200"> 200 €
        </label>
        <label>
            <input type="radio" name="preset_amount" value="500"> 500 €
        </label>
        <label>
            <input type="number" name="custom_amount" min="1" step="1" placeholder="Montant">€ 
        </label>
        <input type="hidden" name="montant" value="">
    </div>
    <small>Tous les prix sont en TTC</small>
    <div style="margin-top: 20px;">
        <p class="gift_card_section_title">Entreprise (optionnel)</p>
        <input type="text" name="entreprise" placeholder="Entreprise" >
    </div>

    <h3 style="font-size: 23px; font-weight: 700; margin-bottom: 10px; margin-top: 60px;">Contenu de la carte cadeau</h3>


    <div class="col-2">
        <div>
            <p class="gift_card_section_title">De la part de</p>
            <input type="text" name="from" required>
        </div>

        <div>
            <p class="gift_card_section_title">Pour</p>
            <input type="text" name="to" required>
        </div>
    </div>

    <!-- Message -->
    <div class="message_container">
    <p class="gift_card_section_title">Votre message</p>
        <textarea name="message" id="message" placeholder="Votre message"></textarea>
    </div>

    <!-- RGPD -->
    <div class="rgpd_container">
        <label>
            <input type="checkbox" name="rgpd">
            J'accepte que mes données personnelles saisies dans ce formulaire soient utilisées dans le cadre d'une prise de contact.
        </label>
    </div>

    <input type="hidden" name="gift_card_form_submit" value="1">

    <div id="gift-card-error" style="color: red; margin-top: 10px;"></div>

    <button type="submit" class="reservation-button" style="padding: 12px 40px; font-weight: 700;">Terminer</button>
</form>

