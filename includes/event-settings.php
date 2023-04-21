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
    register_setting( 'events_options_group', 'events_slides_speed' );
    register_setting( 'events_options_group', 'events_slides_auto', array(
        'type'         => 'boolean',
        'default'      => true,
        'sanitize_callback' => 'absint'
    ));
    register_setting( 'events_options_group', 'events_background_color', array(
        'type'         => 'string',
        'default'      => '#ffffff',
        'sanitize_callback' => 'sanitize_hex_color'
    ));
    register_setting( 'events_options_group', 'events_font_family', array(
        'type'         => 'string',
        'default'      => 'Arial, sans-serif',
        'sanitize_callback' => 'sanitize_text_field'
    ));
}

/**
 * Créé les options, ici le choix du nombre d'événements dans le slider
 */
function events_render_settings_page() {
    $slides_to_show = get_option( 'events_slides_to_show', 4 );
    $slides_format = get_option( 'events_slides_format', 1 );
    $events_page_auto = get_option( 'events_page_auto', false );
    $slides_speed = get_option('events_slides_speed', 3000);
    $slides_auto = get_option('events_slides_auto', true);
    $background_color = get_option( 'events_background_color', '#ffffff' );
    $font_family = get_option('events_font_family', 'Arial');
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
                <th scope="row">Format des slides</th>
                    <td>
                        <label>
                            <input type="radio" name="events_slides_format" value="1" <?php checked( $slides_format, 1 ); ?>>
                            Format 1
                        </label><br>
                        <label>
                            <input type="radio" name="events_slides_format" value="2" <?php checked( $slides_format, 2 ); ?>>
                            Format 2
                        </label>
                    </td>
                </tr>
            </table>
            <?php do_settings_sections( 'events_page_auto' ); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Créer la page d'événements par mois</th>
                    <td>
                        <input type="hidden" name="events_page_auto" value="0">
                        <input type="checkbox" name="events_page_auto" value="1" <?php checked( $events_page_auto, true ); ?>>
                    </td>
                </tr>
            </table>
            <?php do_settings_sections( 'events_slides_auto' ); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Défilement automatique des slides</th>
                    <td>
                        <input type="hidden" name="events_slides_auto" value="0">
                        <input type="checkbox" name="events_slides_auto" value="1" <?php checked( $slides_auto, true ); ?>>
                    </td>
                </tr>
            </table>
            <?php do_settings_sections( 'events_slides_speed' ); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Vitesse de défilement des slides</th>
                    <td>
                        <input type="number" name="events_slides_speed" value="<?php echo esc_attr( $slides_speed ); ?>">
                        En millisecondes.
                    </td>
                </tr>
            </table>
            <?php do_settings_sections( 'events_background_color' ); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Couleur de fond</th>
                    <td>
                        <input type="color" name="events_background_color" value="<?php echo esc_attr( $background_color ); ?>">
                    </td>
                </tr>
            </table>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Police du titre</th>
                    <td>
                        <select name="events_font_family">
                            <option value="Arial" <?php selected( $font_family, 'Arial' ); ?>>Arial</option>
                            <option value="Helvetica" <?php selected( $font_family, 'Helvetica' ); ?>>Helvetica</option>
                            <option value="Times New Roman" <?php selected( $font_family, 'Times New Roman' ); ?>>Times New Roman</option>
                            <option value="Georgia" <?php selected( $font_family, 'Georgia' ); ?>>Georgia</option>
                            <option value="Verdana" <?php selected( $font_family, 'Verdana' ); ?>>Verdana</option>
                            <option value="Garamond" <?php selected( $font_family, 'Garamond' ); ?>>Garamond</option>
                            <option value="Trebuchet MS" <?php selected( $font_family, 'Trebuchet MS' ); ?>>Trebuchet MS</option>
                        </select>
                    </td>
                </tr>
            </table>
            <?php submit_button( 'Enregistrer les réglages', 'primary', 'events_submit_button' ); ?>
        </form>
    </div>
<?php
}
