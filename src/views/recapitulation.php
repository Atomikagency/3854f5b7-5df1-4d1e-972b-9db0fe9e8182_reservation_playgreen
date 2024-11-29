<?php
echo '<pre>';
print_r($reservation_data);
echo '</pre>';
?>
<div class="reservation-recap">
    <h2>Récapitulatif de votre réservation</h2>
    <p><strong>Nom :</strong> <?php echo esc_html($reservation_data['nom']); ?></p>
    <p><strong>Prénom :</strong> <?php echo esc_html($reservation_data['prenom']); ?></p>
    <p><strong>Email :</strong> <?php echo esc_html($reservation_data['email']); ?></p>
    <p><strong>Date :</strong> <?php echo esc_html($reservation_data['date']); ?></p>
    <p><strong>Heure :</strong> <?php echo esc_html($reservation_data['heure']); ?></p>
    <p><strong>Nombre d'adultes :</strong> <?php echo intval($reservation_data['adultes']); ?></p>
    <p><strong>Nombre d'enfants :</strong> <?php echo intval($reservation_data['enfants']); ?></p>
    <p><strong>Total :</strong> <?php echo ($reservation_data['total']); ?></p>
    <p><strong>Total :</strong> <?php echo ($reservation_data['totalBeforeDiscount']); ?></p>
    <form method="post" action="<?php echo site_url('/reservation-payment'); ?>">
        <input type="hidden" name="reservation" value="<?php echo esc_attr($reservation_data['id']); ?>">
        <input type="submit" name="submit_recapitulatif" value="Payer">
    </form>
</div>