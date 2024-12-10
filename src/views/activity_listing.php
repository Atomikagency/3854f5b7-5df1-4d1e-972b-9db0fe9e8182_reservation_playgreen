<?php
if(!empty($atts['filter']) && $atts['filter'] == true ){
    echo '<div class="rp-activity-filter">';
    echo '<form id="rp-activity-filter-form">';
    echo '<select id="rp-filter-ville" name="ville">';
    echo '<option value="">Paris/Lyon</option>';

    // Récupérer les termes de la taxonomie "ville"
    $villes = get_terms(array(
        'taxonomy' => 'ville',
        'hide_empty' => true,
    ));
    foreach ($villes as $ville) {
        echo '<option value="' . esc_attr($ville->slug) . '">' . esc_html($ville->name) . '</option>';
    }

    echo '</select>';

    echo '<select id="rp-filter-age" name="age">';
    echo '<option value="">Adulte/Enfant</option>';
    echo '<option value="adulte">Adulte</option>';
    echo '<option value="enfant">Enfant</option>';
    echo '</select>';

    // Filtre par catégorie
    echo '<select id="rp-filter-category" name="category_activity">';
    echo '<option value="">Categories</option>';
    $categories = get_terms(array(
        'taxonomy' => 'category_activity',
        'hide_empty' => true,
    ));
    foreach ($categories as $category) {
        echo '<option value="' . esc_attr($category->slug) . '">' . esc_html($category->name) . '</option>';
    }
    echo '</select>';

    echo '<button type="button" id="rp-filter-button">Filtrer</button>';
    echo '</form>';
    echo '</div>';
}
echo '<div class="rp-activity-listing" id="rp-activity-listing">';

while ($query->have_posts()) {
    $query->the_post();

    $prix_adulte = get_post_meta(get_the_ID(), '_rp_prix_adulte', true);
    $prix_enfant = get_post_meta(get_the_ID(), '_rp_prix_enfant', true);
    $prix_min = min(array_filter([$prix_adulte, $prix_enfant]));

    $lieu = get_post_meta(get_the_ID(), '_rp_lieu', true);
    $age = get_post_meta(get_the_ID(), '_rp_age', true);
    $duree = get_post_meta(get_the_ID(), '_rp_duree', true);
    $nb_personne = get_post_meta(get_the_ID(), '_rp_nb_personne', true);
    $langue_fr = get_post_meta(get_the_ID(), '_rp_langue_fr', true);
    $langue_en = get_post_meta(get_the_ID(), '_rp_langue_en', true);
    $ville = wp_get_post_terms(get_the_ID(), 'ville');
    $ville = !empty($ville) && $ville[0] ? $ville[0]->slug : '';

    $category = wp_get_post_terms(get_the_ID(), 'category_activity');
    $category_slugs = !empty($categories) ? implode(',', wp_list_pluck($categories, 'slug')) : '';
    $permalink = get_permalink();

    echo '<a href="'. $permalink .'" class="rp-activity-item" data-ville="'. esc_attr($ville) .'" data-category="'.esc_attr($category_slugs).'" data-age="'. esc_attr($age) .'">';
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
?>

<script>
    document.getElementById('rp-filter-button').addEventListener('click', function() {
        const villeFilter = document.getElementById('rp-filter-ville').value;
        const ageFilter = document.getElementById('rp-filter-age').value;
        const categoryFilter = document.getElementById('rp-filter-category').value;

        const activities = document.querySelectorAll('.rp-activity-item');

        activities.forEach(activity => {
            const activityVille = activity.getAttribute('data-ville');
            const activityAge = activity.getAttribute('data-age');
            const activityCategories = activity.getAttribute('data-category').split(',');

            let villeMatch = !villeFilter || activityVille === villeFilter;
            let ageMatch = !ageFilter || activityAge === ageFilter;
            let categoryMatch = !categoryFilter || activityCategories.includes(categoryFilter);

            if (villeMatch && ageMatch && categoryMatch) {
                activity.style.display = '';
            } else {
                activity.style.display = 'none';
            }
        });
    });

</script>

