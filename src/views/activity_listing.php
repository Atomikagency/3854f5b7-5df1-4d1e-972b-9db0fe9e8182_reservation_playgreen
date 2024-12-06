<?php
echo '<div class="rp-activity-listing-container">';

//if (empty($atts['nb_item'])) {
//    echo '<div id="filters">
//        <input type="text" id="titleFilter" placeholder="Rechercher par titre">
//       <!-- <select id="locationFilter">
//            <option value="">Sélectionnez un lieu</option>
//            <option value="paris">Paris</option>
//            <option value="lyon">Lyon</option>
//        </select>-->
//        <button id="applyFilters">Appliquer les filtres</button>
//    </div>';
//}
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

    // Get post thumbnail alt text
    $thumbnail_id = get_post_thumbnail_id(get_the_ID());
    $alt_text = get_post_meta($thumbnail_id, '_wp_attachment_image_alt', true);

    echo '<a href="' . esc_url($permalink) . '" class="rp-activity-item" data-title="' . esc_attr(get_the_title()) . '" data-lieu="' . esc_attr($lieu) . '" >';
    echo '<div class="rp-activity-image">' . get_the_post_thumbnail(get_the_ID(), 'full', array('alt' => esc_attr($alt_text))) . '</div>';
    echo '<h3 class="rp-activity-title">' . esc_html(get_the_title()) . '</h3>';
    echo '<p class="rp-activity-price">' . __('à partir de', 'your-text-domain') . ' <span>' . esc_html($prix_min) . ' €</span> ' . __('TTC', 'your-text-domain') . '</p>';
    echo '<p class="rp-activity-meta"><span class="rp-activity-lieu">' . esc_html($lieu) . ' - </span>';
    echo '<span class="rp-activity-duree">' . esc_html($duree) . ' - </span>';
    echo '<span class="rp-activity-langues">' . ($langue_fr ? '<img src="/wp-content/uploads/2024/12/flag-fr.png" alt="Icône drapeau français"/>' : '') . ($langue_en && $langue_fr ? ' / ' : '') . ($langue_en ? '<img src="/wp-content/uploads/2024/12/flag-en.png" alt="Icône drapeau anglais"/>' : '') . '</span></p>';
    echo '</a>';
}

echo '</div>';
echo '</div>';

wp_reset_postdata();
?>
<!---->
<!--<script>-->
<!--    document.getElementById('applyFilters').addEventListener('click', function() {-->
<!--        const titleFilter = document.getElementById('titleFilter').value.toLowerCase();-->
<!--        // const locationFilter = document.getElementById('locationFilter').value;-->
<!---->
<!--        document.querySelectorAll('.rp-activity-item').forEach(function(item) {-->
<!--            const title = item.getAttribute('data-title').toLowerCase();-->
<!--            // const lieu = item.getAttribute('data-lieu');-->
<!---->
<!--            let isVisible = true;-->
<!---->
<!--            if (titleFilter && !title.includes(titleFilter)) {-->
<!--                isVisible = false;-->
<!--            }-->
<!---->
<!--            // if (locationFilter && lieu !== locationFilter) {-->
<!--            //     isVisible = false;-->
<!--            // }-->
<!---->
<!--            item.style.display = isVisible ? 'block' : 'none';-->
<!--        });-->
<!--    });-->
<!--</script>-->