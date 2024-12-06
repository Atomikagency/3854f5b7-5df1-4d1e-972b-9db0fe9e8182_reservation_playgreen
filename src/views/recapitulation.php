<div class="reservation-recap">
    <div class="reservation-recap-card">
        <div class="reservation-recap-card-game-container">
            <img src="<?php echo esc_url($reservation_data['activite_thumbnail']); ?>" alt="<?php echo esc_html($reservation_data['activite_data']->post_title); ?>" style="width: 100%; border-radius: 20px;">
            <div>
                <h3 style="margin-bottom: 20px; font-weight: 600;"><?php echo esc_html($reservation_data['activite_data']->post_title); ?></h3>
                <p><strong>Durée :</strong> <?php echo esc_html($$reservation_data['activite_duration']); ?></p>
                <p><strong>Nom :</strong> <?php echo esc_html($reservation_data['nom']); ?></p>
                <p><strong>Prénom :</strong> <?php echo esc_html($reservation_data['prenom']); ?></p>
                <p><strong>Email :</strong> <?php echo esc_html($reservation_data['email']); ?></p>
                <p><strong>Date :</strong> <?php echo esc_html($reservation_data['date']); ?></p>
                <p><strong>Heure :</strong> <?php echo esc_html($reservation_data['heure']); ?></p>
                <p><strong>Nombre d'adultes :</strong> <?php echo intval($reservation_data['adultes']); ?></p>
                <p><strong>Nombre d'enfants :</strong> <?php echo intval($reservation_data['enfants']); ?></p>
                <div class="reservation-recap-price" style="justify-self: flex-end;">
                    <p class="reservation-recap-price-total">Total: <span id="price-with-discount"><?php echo ($reservation_data['total']); ?></span> €</p>
                    <?php if ($reservation_data['totalBeforeDiscount'] !== $reservation_data['total']) { ?>
                        <p class="reservation-recap-price-without-discount"><span id="price-without-discount"><?php echo ($reservation_data['totalBeforeDiscount']); ?></span> €</p>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>

    <div style="display: flex; gap: 20px; align-items: center; justify-content: center; margin-top: 40px;">
        <form method="post" action="<?php echo site_url('/reservation-payment'); ?>">
            <button type="submit" class="button reservation-button" style="padding: 12px 40px; font-weight: 700;">Payer</button>
            <input type="hidden" name="reservation" value="<?php echo esc_attr($reservation_data['id']); ?>">
        </form>
    </div>
</div>
