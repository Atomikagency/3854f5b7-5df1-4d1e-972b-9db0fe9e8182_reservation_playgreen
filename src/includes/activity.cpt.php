<?php

// Créer le CPT "Activité"
function rp_register_activity_cpt() {
    $args = array(
        'label'               => 'Activités',
        'public'              => true, // Public pour le front-end
        'publicly_queryable' => true,
        'show_ui' => true,
        'query_var' => true,
        'show_in_menu' => true,
        'menu_icon'           => 'dashicons-calendar-alt',
        'supports'            => array('title','editor','thumbnail'),
        'has_archive'         => true,
        'show_in_rest'        => false, // Accessible via REST API
        'rewrite' => array('slug' => 'activite'),
    );

    register_post_type('activite', $args);
}
add_action('init', 'rp_register_activity_cpt');

function rp_register_ville_taxonomy() {
    $args = array(
        'labels' => array(
            'name'              => 'Villes',
            'singular_name'     => 'Ville',
            'search_items'      => 'Rechercher des villes',
            'all_items'         => 'Toutes les villes',
            'parent_item'       => 'Ville parente',
            'parent_item_colon' => 'Ville parente :',
            'edit_item'         => 'Éditer la ville',
            'update_item'       => 'Mettre à jour la ville',
            'add_new_item'      => 'Ajouter une nouvelle ville',
            'new_item_name'     => 'Nom de la nouvelle ville',
            'menu_name'         => 'Villes',
        ),
        'hierarchical'      => true,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array('slug' => 'ville'),
    );

    register_taxonomy('ville', 'activite', $args);
}
add_action('init', 'rp_register_ville_taxonomy');

function rp_register_category_taxonomy() {
    $args = array(
        'labels' => array(
            'name'              => 'Categories',
            'singular_name'     => 'Categorie',
            'search_items'      => 'Rechercher des Categories',
            'all_items'         => 'Toutes les Categories',
            'parent_item'       => 'Categorie parente',
            'parent_item_colon' => 'Categorie parente :',
            'edit_item'         => 'Éditer la Categorie',
            'update_item'       => 'Mettre à jour la Categorie',
            'add_new_item'      => 'Ajouter une nouvelle Categorie',
            'new_item_name'     => 'Nom de la nouvelle Categorie',
            'menu_name'         => 'Categories',
        ),
        'hierarchical'      => true,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array('slug' => 'category_activity'),
    );

    register_taxonomy('category_activity', 'activite', $args);
}
add_action('init', 'rp_register_category_taxonomy');

// Ajouter les champs personnalisés via des meta boxes
function rp_add_activity_meta_boxes() {
    add_meta_box(
        'rp_activity_details',
        'Détails de l\'Activité',
        'rp_render_activity_details_meta_box',
        'activite',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'rp_add_activity_meta_boxes');

// Rendu de la meta box
function rp_render_activity_details_meta_box($post) {
    $fields = [
        'note'            => get_post_meta($post->ID, '_rp_note', true),
        'lieu'            => get_post_meta($post->ID, '_rp_lieu', true),
        'nb_personne'     => get_post_meta($post->ID, '_rp_nb_personne', true),
        'duree'           => get_post_meta($post->ID, '_rp_duree', true),
        'langue_fr'       => get_post_meta($post->ID, '_rp_langue_fr', true),
        'langue_en'       => get_post_meta($post->ID, '_rp_langue_en', true),
        'photo_1'         => get_post_meta($post->ID, '_rp_photo_1', true),
        'photo_2'         => get_post_meta($post->ID, '_rp_photo_2', true),
        'photo_3'         => get_post_meta($post->ID, '_rp_photo_3', true),
        'photo_4'         => get_post_meta($post->ID, '_rp_photo_4', true),
        'prix_adulte'     => get_post_meta($post->ID, '_rp_prix_adulte', true),
        'prix_enfant'     => get_post_meta($post->ID, '_rp_prix_enfant', true),
        'stripe_connect'  => get_post_meta($post->ID, '_rp_stripe_connect', true),
        'adresse_rdv'  => get_post_meta($post->ID, '_rp_adresse_rdv', true),
        'pdf_enigme'    => get_post_meta($post->ID, '_rp_pdf_enigme', true),
        'age' => get_post_meta($post->ID, '_rp_age', true),
        ];

    $hours = get_post_meta($post->ID, '_rp_hours', true) ?: [];
    $days = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];

    $unavailability_dates = get_post_meta($post->ID, '_rp_unavailability_dates', true) ?: [];

    ?>
    <table class="form-table">
        <tr>
            <th><label for="rp_note">Note</label></th>
            <td><input type="text" name="rp_note" id="rp_note" value="<?php echo esc_attr($fields['note']); ?>" style="width: 100%;"></td>
        </tr>
        <tr>
            <th><label for="rp_lieu">Lieu</label></th>
            <td><input type="text" name="rp_lieu" id="rp_lieu" value="<?php echo esc_attr($fields['lieu']); ?>" style="width: 100%;"></td>
        </tr>
        <tr>
            <th><label for="rp_nb_personne">Nombre de personnes</label></th>
            <td><input type="text" name="rp_nb_personne" id="rp_nb_personne" value="<?php echo esc_attr($fields['nb_personne']); ?>" style="width: 100%;"></td>
        </tr>
        <tr>
            <th><label for="rp_duree">Durée</label></th>
            <td><input type="text" name="rp_duree" id="rp_duree" value="<?php echo esc_attr($fields['duree']); ?>" style="width: 100%;"></td>
        </tr>
        <tr>
            <th>Langues disponibles</th>
            <td>
                <label><input type="checkbox" name="rp_langue_fr" <?php checked($fields['langue_fr'], 'on'); ?>> Français</label><br>
                <label><input type="checkbox" name="rp_langue_en" <?php checked($fields['langue_en'], 'on'); ?>> Anglais</label>
            </td>
        </tr>
        <tr>
            <th>Photos</th>
            <td>
                <?php for ($i = 1; $i <= 4; $i++) :
                    $photo_field = "rp_photo_$i";
                    $photo_value = get_post_meta($post->ID, "_$photo_field", true);
                    ?>
                    <div class="photo-field" style="margin-bottom: 10px;">
                        <label>Photo <?php echo $i; ?> :</label>
                        <input type="hidden" name="<?php echo $photo_field; ?>" id="<?php echo $photo_field; ?>" value="<?php echo esc_attr($photo_value); ?>">
                        <img id="preview-<?php echo $photo_field; ?>" src="<?php echo $photo_value ?: ''; ?>" style="max-width: 150px; display: block; margin-bottom: 5px;">
                        <button type="button" class="button rp-upload-button" data-target="#<?php echo $photo_field; ?>">Télécharger</button>
                        <button type="button" class="button rp-remove-button" data-target="#<?php echo $photo_field; ?>">Supprimer</button>
                    </div>
                <?php endfor; ?>
            </td>
        </tr>
        <tr>
            <th><label for="rp_prix_adulte">Prix Adulte (€)</label></th>
            <td><input type="number" name="rp_prix_adulte" id="rp_prix_adulte" value="<?php echo esc_attr($fields['prix_adulte']); ?>" style="width: 100%;"></td>
        </tr>
        <tr>
            <th><label for="rp_prix_enfant">Prix Enfant (€)</label></th>
            <td><input type="number" name="rp_prix_enfant" id="rp_prix_enfant" value="<?php echo esc_attr($fields['prix_enfant']); ?>" style="width: 100%;"></td>
        </tr>
        <tr>
            <th><label for="rp_adresse_rdv">Adresse de RDV</label></th>
            <td>
                <input type="text" name="rp_adresse_rdv" id="rp_adresse_rdv" value="<?php echo esc_attr($fields['adresse_rdv']); ?>" style="width: 100%;">
                <small>Cette adresse apparaitra dans le mail après réservation</small>
            </td>
        </tr>
        <tr>
            <th><label for="rp_age">Age</label></th>
            <td>
                <select name="rp_age" id="rp_age">
                    <option>Choisissez un age</option>
                    <option value="adulte" <?php echo !empty($fields['age']) && $fields['age'] == 'adulte'  ? 'selected' :'' ?>>Adulte</option>
                    <option value="enfant" <?php echo !empty($fields['age']) && $fields['age'] == 'enfant'  ? 'selected' :'' ?>>Enfant</option>
                </select>
            </td>
        </tr>
        <tr>
            <th><label for="rp_pdf_enigme">PDF Enigme 0</label></th>
            <td>
                <?php
                $pdf_field = 'rp_pdf_enigme';
                $pdf_value = get_post_meta($post->ID, "_rp_pdf_enigme", true);
                $filename = basename($pdf_value);
                ?>
                <input type="hidden" name="rp_pdf_enigme" id="rp_pdf_enigme" value="<?php echo esc_attr($fields['pdf_enigme']); ?>">
                <p id="title-<?php echo $pdf_field ?>"><?php echo $filename ?></p>
                <button type="button" class="button rp-upload-button" data-target="#rp_pdf_enigme">Télécharger</button>
                <button type="button" class="button rp-remove-button" data-target="#<?php echo $pdf_field; ?>">Supprimer</button>
                <small>Charger un fichier PDF pour l'énigme 0 - Sera transmis dans le mail après réservation</small>
            </td>
        </tr>
        <tr>
            <th><label for="rp_stripe_connect">Stripe Connect ID (optionnel)</label></th>
            <td><input type="text" name="rp_stripe_connect" id="rp_stripe_connect" value="<?php echo esc_attr($fields['stripe_connect']); ?>" style="width: 100%;"></td>
        </tr>
    </table>

    <h3>Gestion des horaires</h3>
    <table class="wp-list-table widefat fixed striped">
        <thead>
        <tr>
            <th>Heures</th>
            <?php foreach ($days as $day) : ?>
                <th><?php echo $day; ?></th>
            <?php endforeach; ?>
        </tr>
        </thead>
        <tbody>
        <?php for ($hour = 11; $hour <= 22.5; $hour += 0.5) :
            $time_label = ($hour == floor($hour)) ? $hour . 'h00' : floor($hour) . 'h30';
            ?>
            <tr>
                <td><?php echo $time_label; ?></td>
                <?php foreach ($days as $day) :
                    $checked = isset($hours[$day][$time_label]) ? 'checked' : '';
                    ?>
                    <td><input type="checkbox" name="rp_hours[<?php echo $day; ?>][<?php echo $time_label; ?>]" <?php echo $checked; ?>></td>
                <?php endforeach; ?>
            </tr>
        <?php endfor; ?>
        </tbody>
    </table>

    <h3>Dates d'indisponibilité</h3>
    <div id="rp-unavailability-dates-container">
        <?php foreach ($unavailability_dates as $index => $dates) : ?>
            <div class="rp-unavailability-date">
                <label>Date de début : <input type="date" name="rp_unavailability_dates[<?php echo $index; ?>][start]" value="<?php echo esc_attr($dates['start']); ?>" required></label>
                <label>Date de fin : <input type="date" name="rp_unavailability_dates[<?php echo $index; ?>][end]" value="<?php echo esc_attr($dates['end']); ?>"></label>
                <button type="button" class="button rp-remove-unavailability">Supprimer</button>
            </div>
        <?php endforeach; ?>
    </div>
    <button type="button" class="button" id="rp-add-unavailability-date">Ajouter une date d'indisponibilité</button>

    <script>
        jQuery(document).ready(function($) {
            $('#rp-add-unavailability-date').on('click', function() {
                var index = $('#rp-unavailability-dates-container .rp-unavailability-date').length;
                var newField = `
                    <div class="rp-unavailability-date">
                        <label>Date de début : <input type="date" name="rp_unavailability_dates[${index}][start]" required></label>
                        <label>Date de fin : <input type="date" name="rp_unavailability_dates[${index}][end]"></label>
                        <button type="button" class="button rp-remove-unavailability">Supprimer</button>
                    </div>
                `;
                $('#rp-unavailability-dates-container').append(newField);
            });

            $(document).on('click', '.rp-remove-unavailability', function() {
                $(this).closest('.rp-unavailability-date').remove();
            });
        });
    </script>
    <?php
}

// Sauvegarder les champs personnalisés
function rp_save_activity_meta($post_id) {
    $fields = [
        'note', 'lieu', 'nb_personne', 'duree', 'langue_fr', 'langue_en',
        'photo_1', 'photo_2', 'photo_3', 'photo_4', 'prix_adulte', 'prix_enfant', 'stripe_connect','adresse_rdv','pdf_enigme','age'
    ];

    foreach ($fields as $field) {
        if (isset($_POST["rp_$field"])) {
            update_post_meta($post_id, "_rp_$field", sanitize_text_field($_POST["rp_$field"]));
        }
    }

    if (isset($_POST['rp_hours'])) {
        update_post_meta($post_id, '_rp_hours', $_POST['rp_hours']);
    }

    if (isset($_POST['rp_unavailability_dates'])) {
        update_post_meta($post_id, '_rp_unavailability_dates', $_POST['rp_unavailability_dates']);
    }
}
add_action('save_post', 'rp_save_activity_meta');
