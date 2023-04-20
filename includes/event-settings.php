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
    register_setting( 'events_options_group', 'events_slides_format' );
    register_setting( 'events_options_group', 'events_page_auto', array(
        'type'         => 'boolean',
        'default'      => false,
        'sanitize_callback' => 'absint'
    ));
}

/**
 * Créé les options, ici le choix du nombre d'événements dans le slider
 */
function events_render_settings_page() {
    $slides_to_show = get_option( 'events_slides_to_show', 4 );
    $slides_format = get_option( 'events_slides_format', 1 );
    $events_page = get_option( 'events_page_auto',false);
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
                        De 3 à 6 slides affiché en même temps.
                    </td>
                </tr>
            </table>
            <?php do_settings_sections( 'events_slides_format' ); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Format des sliders</th>
                    <td>
                        <input type="number" name="events_slides_format" min="1" max="2" value="<?php echo esc_attr( $slides_format ); ?>">
                        Format 1 ou 2.
                    </td>
                </tr>
            </table>
            <?php do_settings_sections( 'events_page_auto' ); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Créer la page d'événements par mois</th>
                    <td>
                        <input type="hidden" name="events_page_auto" value="0">
                        <input type="checkbox" name="events_page_auto" value="1" <?php checked( $events_page, true ); ?>>
                    </td>
                </tr>
            </table>
            <?php submit_button( 'Enregistrer les réglages', 'primary', 'events_submit_button' ); ?>
        </form>
    </div>
<?php
}
