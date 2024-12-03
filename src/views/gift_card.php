<form id="gift_card" method="post" class="gift_card_form">
    <!-- Thèmes -->
    <p class="gift_card_section_title">Choisissez votre thème :</p>
    <div class="themes_container">
        <label>
            <input type="radio" name="theme" value="1" required>
            <img src="theme1.jpg" alt="Thème 1">
        </label>
        <label>
            <input type="radio" name="theme" value="2">
            <img src="theme2.jpg" alt="Thème 2">
        </label>
        <label>
            <input type="radio" name="theme" value="3">
            <img src="theme3.jpg" alt="Thème 3">
        </label>
        <label>
            <input type="radio" name="theme" value="4">
            <img src="theme4.jpg" alt="Thème 4">
        </label>
    </div>

    <!-- Montant -->
    <div class="montant_container">
        <p class="gift_card_section_title">Choisissez un montant :</p>
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

    <!-- Message -->
    <div class="message_container">
        <p class="gift_card_section_title">Souhaitez-vous écrire un message ?</p>
        <label>
            <input type="checkbox" name="add_message" checked> Oui
        </label>
        <textarea name="message" placeholder="Votre message"></textarea>
    </div>

    <!-- Email -->
    <div class="email_container">
        <label class="gift_card_section_title">
            Renseignez votre adresse email
            <input type="email" name="email" required>
        </label>
    </div>

    <!-- Envoi direct -->
    <div class="envoie_direct_container">
        <p class="gift_card_section_title">Souhaitez-vous directement envoyer la carte par mail ? (Vous recevrez aussi la carte par mail)</p>
        <label>
            <input type="checkbox" name="send_direct"> Oui
        </label>
        <input type="email" name="emailSend" placeholder="Email du destinataire" style="display: none;">
    </div>

    <!-- RGPD -->
    <div class="rgpd_container">
        <label>
            <input type="checkbox" name="rgpd" required>
            J'accepte que mes données personnelles saisies dans ce formulaire soient utilisées dans le cadre d'une prise de contact.
        </label>
    </div>

    <input type="hidden" name="gift_card_form_submit" value="1">

    <button type="submit" class="reservation-button" style="padding: 12px 40px; font-weight: 700;">Terminer</button>
</form>

