<?php


function reservation_playgreen_check_for_update( $transient ) {
    if ( empty( $transient->checked ) ) {
        return $transient;
    }

    // URL de votre endpoint de mise à jour
    $update_url = 'https://plugin-manager.atomikagency.fr/api/pull/3854f5b7-5df1-4d1e-972b-9db0fe9e8182';

    // Récupère les données de mise à jour
    $response = wp_remote_get( $update_url );
    if ( is_wp_error( $response ) ) {
        return $transient;
    }

    $update_data = json_decode( wp_remote_retrieve_body( $response) );

    // Compare les versions
    $plugin_slug = plugin_basename( RESERVATION_PLAYGREEN_PLUGIN_DIR.'/reservation_playgreen.php' );
    $current_version = get_plugin_data( RESERVATION_PLAYGREEN_PLUGIN_DIR.'/reservation_playgreen.php' )['Version'];

    if ( version_compare( $current_version, $update_data->version, '<' ) ) {
        $transient->response[ $plugin_slug ] = (object) [
            'slug'        => $plugin_slug,
            'new_version' => $update_data->version,
            'package'     => $update_data->package,
            'url'         => 'https://atomikagency.fr/', // Page de détails du plugin
        ];
    }

    return $transient;
}
add_filter( 'site_transient_update_plugins', 'reservation_playgreen_check_for_update' );
