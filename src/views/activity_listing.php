<?php

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

        echo '<a href="'. $permalink .'" class="rp-activity-item">';
        echo '<div class="rp-activity-image">' . get_the_post_thumbnail(get_the_ID(), 'full') . '</div>';
        echo '<h3 class="rp-activity-title">' . get_the_title() . '</h3>';
        echo '<p class="rp-activity-price">à partir de <span>' . esc_html($prix_min) . ' €</span> TTC</p>';
        echo '<p class="rp-activty-meta"><span class="rp-activity-lieu">' . esc_html($lieu) . ' - </span>';
        echo '<span class="rp-activity-duree">' . esc_html($duree) . ' - </span>';
        echo '<span class="rp-activity-langues">'. ($langue_fr ? '<img src="/wp-content/uploads/2024/12/flag-fr.png" alt="Icône drapeau français"/>' : '') . ($langue_en && $langue_fr ? ' / ': '') . ($langue_en ? '<img src="/wp-content/uploads/2024/12/flag-en.png" alt="Icône drapeau anglais"/>' : '') .'</span></p>';
        echo '</a>';
    }

    echo '</div>';

wp_reset_postdata();
