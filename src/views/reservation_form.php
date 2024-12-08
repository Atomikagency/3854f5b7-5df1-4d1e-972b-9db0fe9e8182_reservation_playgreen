<div class="reservation-form-container" style="display: flex; gap: 20px; align-items: flex-start;">
    <!-- Colonne gauche -->
    <div class="reservation-form-left" style="flex: 1;">
        <form id="reservation-form" method="post" data-activity="<?php echo $activity_meta['activite'] ?>">
            <?php wp_nonce_field('reservation_form_nonce', 'reservation_form_nonce_field'); ?>
            <!-- Sélection de la date -->
            <label for="reservation-date">Date *</label>
            <input type="text" id="reservation-date" name="reservation_date" class="flatpickr" placeholder="Choisissez une date" required>

            <!-- Sélection du créneau horaire -->
            <div id="time-slot-container">
                <label for="reservation-time">Créneau horaire *</label>
                <select id="reservation-time" name="reservation_time" required disabled>
                    <option value="">-- Sélectionnez un créneau horaire --</option>
                </select>
            </div>

            <!-- Informations personnelles -->
            <div style="display: flex; gap: 10px;">
                <div style="flex: 1;">
                    <label for="reservation-nom">Nom *</label>
                    <input type="text" id="reservation-nom" name="reservation_nom" required>
                </div>
                <div style="flex: 1;">
                    <label for="reservation-prenom">Prénom *</label>
                    <input type="text" id="reservation-prenom" name="reservation_prenom" required>
                </div>
            </div>

            <label for="reservation-email">Email *</label>
            <input type="email" id="reservation-email" name="reservation_email" required>

            <!-- Langue -->
            <p style="font-weight: 600; font-size: 16px; margin-top: 20px; margin-bottom: 5px;">Langue *</p>
            <div class="reservation-langue">
                <?php if ($activity_meta['langue_fr']) : ?>
                    <div style="margin-bottom: 10px;">
                        <input type="checkbox" id="reservation_francais" name="reservation_francais" <?php if (!$activity_meta['langue_en']) : ?> checked <?php endif; ?>>
                        <label for="reservation_francais">Français</label>
                    </div>
                <?php endif; ?>
                <?php if ($activity_meta['langue_en']) : ?>
                    <div>
                        <input type="checkbox" id="reservation_anglais" name="reservation_anglais" <?php if (!$activity_meta['langue_fr']) : ?> checked <?php endif; ?>>
                        <label for="reservation_anglais">Anglais</label>
                    </div>
                <?php endif; ?>
                <?php if ($activity_meta['langue_fr'] && $activity_meta['langue_en']) : ?>
                    <small>Vous pouvez choisir plusieurs langues</small>
                <?php endif; ?>
            </div>

            <!-- Nombre de personnes -->
            <label for="reservation-adultes">Nombre d'adultes (<?php echo esc_html($activity_meta['prix_adulte']); ?>€ TTC par adulte)</label>
            <input type="number" id="reservation-adultes" name="reservation_adultes" min="0" value="0" required>

            <label for="reservation-enfants">Nombre de jeunes (<?php echo esc_html($activity_meta['prix_enfant']); ?>€ TTC par jeune) <sup style="font-size: 12px;">1</sup></label>
            <input type="number" id="reservation-enfants" name="reservation_enfants" min="0" value="0" required>

            <label for="reservation-cp">Code promo</label>
            <input type="text" id="reservation-cp" name="reservation_code_promo" >

            <label for="reservation-cc">Carte cadeau</label>
            <input type="text" id="reservation-cc" name="reservation_carte_cadeau" >

            <div id="ask_entreprise">
                <label for="enterprise-name">Nom de l'entreprise (optionnel)</label>
                <input type="text" id="enterprise-name" name="enterprise_name" >
            </div>
            <div id="reservation-message">
                <label for="message">Message (optionnel)</label>
                <textarea name="message" id="message" ></textarea>
            </div>

            <div style="display: grid; grid-template-columns: auto 1fr; align-items: center; gap: 10px;">
                <input type="checkbox" id="reservation-cgv" name="reservation_cgv" required>
                <label for="reservation-cgv">
                    J'accepte que mes données personnelles saisies dans ce formulaire soient utilisées dans le cadre d'une prise de contact. *
                </label>
            </div>

            <div id="reservation-error" style="color: red; margin-top: 10px;"></div>


            <!-- Soumettre -->
            <button type="submit" class="button reservation-button" name="form_reservation_submit" id="reservation-submit" style="padding: 12px 35px; width: 100%;">Réserver</button>
            <p style="margin-top: 20px; color: #000; font-weight: 500;">Vous êtes une entreprise ? Vous pourrez saisir vos informations au moment du paiement.</p>
            <p style="color: #000; font-weight: 500;">1: Jeunes de 3 à 17 ans - Gratuit pour les moins de 3 ans</p>
        </form>
    </div>

    <!-- Colonne droite -->
    <div class="reservation-form-right" style="flex: 1; background-color: #f9f9f9; padding: 20px; border-radius: 25px;">
        <img src="<?php echo esc_url($activity_meta['thumbnail']); ?>" alt="<?php echo esc_html(get_the_title($activite_id)); ?>" style="width: 100%; border-radius: 20px;">
        <h3><?php echo esc_html(get_the_title($activite_id)); ?></h3>
        <p><strong>Durée :</strong> <?php echo esc_html($activity_meta['duree']); ?></p>
        <!-- <p><strong>Note :</strong> <?php echo esc_html($activity_meta['note']); ?>/5</p> -->
        <p><?php echo esc_html($activity_meta['resumé']); ?></p>
        <div class="reservation-form-price">
            <p class="reservation-form-price-total">Total: <span id="price-with-discount">0.00</span> € TTC</p>
            <p class="reservation-form-price-without-discount"><span id="price-without-discount">0.00</span> € TTC</p>
        </div>
    </div>
</div>
