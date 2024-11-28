<div class="reservation-form-container" style="display: flex; gap: 20px;">
    <!-- Colonne gauche -->
    <div class="reservation-form-left" style="flex: 1;">
        <form id="reservation-form" method="post">
            <?php wp_nonce_field('reservation_form_nonce', 'reservation_form_nonce_field'); ?>
            <!-- Sélection de la date -->
            <label for="reservation-date">Date</label>
            <input type="text" id="reservation-date" name="reservation_date" class="flatpickr" placeholder="Choisissez une date" required>

            <!-- Sélection du créneau horaire -->
            <div id="time-slot-container" style="display: none; margin-top: 10px;">
                <label for="reservation-time">Créneau horaire</label>
                <select id="reservation-time" name="reservation_time" required>
                    <option value="">-- Sélectionnez un créneau horaire --</option>
                </select>
            </div>

            <!-- Informations personnelles -->
            <label for="reservation-nom">Nom</label>
            <input type="text" id="reservation-nom" name="reservation_nom" required>

            <label for="reservation-prenom">Prénom</label>
            <input type="text" id="reservation-prenom" name="reservation_prenom" required>

            <label for="reservation-email">Email</label>
            <input type="email" id="reservation-email" name="reservation_email" required>

            <!-- Langue -->
            <label for="reservation-langue">Langue</label>
            <select id="reservation-langue" name="reservation_langue" required>
                <?php if ($activity_meta['langue_fr']) : ?>
                    <option value="fr">Français</option>
                <?php endif; ?>
                <?php if ($activity_meta['langue_en']) : ?>
                    <option value="en">Anglais</option>
                <?php endif; ?>
            </select>

            <!-- Nombre de personnes -->
            <label for="reservation-adultes">Nombre d'adultes (<?php echo esc_html($activity_meta['prix_adulte']); ?>€ par adulte)</label>
            <input type="number" id="reservation-adultes" name="reservation_adultes" min="0" value="0" required>

            <label for="reservation-enfants">Nombre d'enfants (<?php echo esc_html($activity_meta['prix_enfant']); ?>€ par enfant)</label>
            <input type="number" id="reservation-enfants" name="reservation_enfants" min="0" value="0" required>

            <label for="reservation-cp">Code promo</label>
            <input type="text" id="reservation-cp" name="reservation_code_promo" >


            <!-- Soumettre -->
            <button type="submit" class="button" name="form_reservation_submit">Réserver</button>
        </form>
    </div>

    <!-- Colonne droite -->
    <div class="reservation-form-right" style="flex: 1; background-color: #f9f9f9; padding: 20px; border-radius: 5px;">
        <img src="<?php echo esc_url($activity_meta['thumbnail']); ?>" alt="<?php echo esc_html(get_the_title($activite_id)); ?>" style="width: 100%; border-radius: 5px;">
        <h3><?php echo esc_html(get_the_title($activite_id)); ?></h3>
        <p><strong>Durée :</strong> <?php echo esc_html($activity_meta['duree']); ?></p>
        <p><strong>Note :</strong> <?php echo esc_html($activity_meta['note']); ?></p>
        <p><?php echo esc_html($activity_meta['resumé']); ?></p>
    </div>
</div>
