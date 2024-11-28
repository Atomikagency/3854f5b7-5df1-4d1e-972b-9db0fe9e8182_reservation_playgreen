<?php

add_action('admin_menu', 'dummy_plugin_add_admin_page');

function dummy_plugin_add_admin_page()
{
    // Ajoute la page "Dummy" dans le menu
    add_menu_page(
        'Dummy Page',          // Titre de la page
        'Dummy ',               // Nom du menu
        'manage_options',      // Capacité requise
        'dummy-plugin',        // Slug de la page
        'dummy_plugin_display', // Fonction callback pour afficher le contenu
        'dashicons-smiley',    // Icône du menu (facultatif)
        20                     // Position dans le menu (facultatif)
    );
}


function dummy_plugin_display()
{
    echo '<div class="wrap">';
    echo '<h1>Hello World </h1>';
    echo '<p>This is a dummy admin page created for demonstration purposes. BLABLA</p>';
    echo '</div>';
}