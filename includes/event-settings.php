<?php

/**
 * Effectue la fonction events_settings_page quand le menu admin est utilisé
 */
add_action( 'admin_menu', 'events_settings_page' );

/**
 * Créer un sous menu Régagles pour les événements
 */
function events_settings_page() {
    add_submenu_page(
        'edit.php?post_type=event',
        'Réglages du plugin Events',
        'Réglages',
        'manage_options',
        'events-settings',
        'events_render_settings_page'
    );
}

/**
 * Effectue la fonction events_register_settings quand le panel admin est initialisé
 */
add_action( 'admin_init', 'events_register_settings' );

/**
 * Créer les options modifiables
 */
function events_register_settings() {
    register_setting( 'events_options_group', 'events_slides_to_show' );
}

/**
 * Créé les options, ici le choix du nombre d'événements dans le slider
 */
function events_render_settings_page() {
    $slides_to_show = get_option( 'events_slides_to_show', 4 );
    ?>
    <div class="wrap">
        <h1><strong>Réglages des événements</strong></h1>
        <h2>Carrousel</h2>
        <form method="post" action="options.php">
            <?php settings_fields( 'events_options_group' ); ?>
            <?php do_settings_sections( 'events_slides_to_show' ); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Nombre de slides à afficher</th>
                    <td>
                        <input type="number" name="events_slides_to_show" min="3" max="6" value="<?php echo esc_attr( $slides_to_show ); ?>">
                    </td>
                </tr>
            </table>
            <?php submit_button( 'Enregistrer les réglages', 'primary', 'events_submit_button' ); ?>
        </form>
    </div>
    <?php
}