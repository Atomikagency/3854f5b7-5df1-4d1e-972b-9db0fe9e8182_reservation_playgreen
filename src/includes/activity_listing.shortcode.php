<?php

add_shortcode('rp_activity_listing', 'rp_activity_listing_shortcode');

function rp_activity_listing_shortcode($atts) {
    $atts = shortcode_atts(array(
        'nb_item' => '',
    ), $atts, 'rp_activity_listing');

    $args = array(
        'post_type'      => 'activite',
        'posts_per_page' => $atts['nb_item'] ? intval($atts['nb_item']) : -1,
        'orderby'        => 'title',
        'order'          => 'ASC',
    );

    $query = new WP_Query($args);

    if (!$query->have_posts()) {
        return '<p>Aucune activité trouvée.</p>';
    }

    // Début de l'affichage HTML
    ob_start();
    echo '<div class="rp-activity-listing">';

    while ($query->have_posts()) {
        $query->the_post();

        // Récupération des métadonnées
        $prix_adulte = get_post_meta(get_the_ID(), '_rp_prix_adulte', true);
        $prix_enfant = get_post_meta(get_the_ID(), '_rp_prix_enfant', true);
        $prix_min = min(array_filter([$prix_adulte, $prix_enfant]));

        $lieu = get_post_meta(get_the_ID(), '_rp_lieu', true);
        $duree = get_post_meta(get_the_ID(), '_rp_duree', true);
        $nb_personne = get_post_meta(get_the_ID(), '_rp_nb_personne', true);
        $langue_fr = get_post_meta(get_the_ID(), '_rp_langue_fr', true);
        $langue_en = get_post_meta(get_the_ID(), '_rp_langue_en', true);

        $permalink = get_permalink();

        echo '<div class="rp-activity-item">';
        echo '<div class="rp-activity-image">' . get_the_post_thumbnail(get_the_ID(), 'full') . '</div>';
        echo '<h3 class="rp-activity-title">' . get_the_title() . '</h3>';
        echo '<p class="rp-activity-price">À partir de ' . esc_html($prix_min) . '€</p>';
        echo '<p class="rp-activity-lieu">Lieu : ' . esc_html($lieu) . '</p>';
        echo '<p class="rp-activity-duree">Durée : ' . esc_html($duree) . '</p>';
        echo '<p class="rp-activity-nb-personne">Nombre de personnes : ' . esc_html($nb_personne) . '</p>';
        echo '<p class="rp-activity-langues">Langues : ' . ($langue_fr ? 'Français ' : '') . ($langue_en ? 'Anglais' : '') . '</p>';
        echo '</div>';
    }

    echo '</div>';

    wp_reset_postdata();
    return ob_get_clean();
}
