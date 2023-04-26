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
    register_setting( 'events_options_group', 'events_slides_speed_pass' );
    register_setting( 'events_options_group', 'events_slides_auto', array(
        'type'         => 'boolean',
        'default'      => true,
        'sanitize_callback' => 'absint'
    ));
    register_setting( 'events_options_group', 'events_slides_infinite', array(
        'type'         => 'boolen',
        'default'      => true,
        'sanitize_callback' => 'absint'
));
    register_setting( 'events_options_group', 'events_slides_dots', array(
        'type'         => 'boolen',
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
    register_setting( 'events_options_group', 'events_text_color', array(
        'type'         => 'string',
        'default'      => '#ffffff',
        'sanitize_callback' => 'sanitize_hex_color'
    ));
    register_setting( 'events_options_group', 'events_text_hover_color', array(
        'type'         => 'string',
        'default'      => '#ffffff',
        'sanitize_callback' => 'sanitize_hex_color'
    ));

}

/**
 * Créé les options, ici le choix du nombre d'événements dans le slider
 */
function events_render_settings_page() {
    $slides_to_show = get_option( 'events_slides_to_show', 4 );
    $slides_format = get_option( 'events_slides_format', 1 );
    $events_page_auto = get_option( 'events_page_auto', false );
    $slides_infinite = get_option('events_slides_infinite',true);
    $slides_dots = get_option('events_slides_dots',true);
    $slides_speed = get_option('events_slides_speed', 3000);
    $slides_speed_pass = get_option('events_slides_speed_pass', 300);
    $slides_auto = get_option('events_slides_auto', true);
    $background_color = get_option( 'events_background_color', '#ffffff' );
    $font_family = get_option('events_font_family', 'Arial');
    $text_color = get_option( 'events_text_color', '#ffffff' );
    $text_hover_color = get_option( 'events_text_hover_color', '#ffffff' );
    ?>
     <link rel="stylesheet" type="text/css" href="<?php echo plugin_dir_url(__FILE__) . '../CSS/style.css'; ?>"> <!-- Lien vers notre fichier css -->
    <div class="wrap">
        <h1><strong>Réglages des événements</strong></h1>
        <h2>Slider</h2>
        <!-- Formulaire des réglages possible -->
        <form method="post" action="options.php">
            <?php settings_fields( 'events_options_group' ); ?> <!-- Sélection de l'emplacement des réglages -->
            <!-- Réglage qui permet de choisir combien de slides on affiche en même temps -->
            <?php do_settings_sections( 'events_slides_to_show' ); ?> 
            <table class="settings">
                <tr valign="top">
                    <th scope="row">Nombre de slides à afficher</th>
                    <td>
                        <input type="number" name="events_slides_to_show" min="3" max="6" value="<?php echo esc_attr( $slides_to_show ); ?>">
                        De 3 à 6 slides affiché en même temps.
                    </td>
                </tr>
            <!-- Réglage qui permet de chosir le format des slides -->
            <?php do_settings_sections( 'events_slides_format' ); ?> 
 
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
            <!-- Réglage qui permet la création automatique d'une page qui affiche les événements par mois  -->
            <?php do_settings_sections( 'events_page_auto' ); ?> 
                <tr valign="top">
                    <th scope="row">Page d'événements par mois</th>
                    <td>
                        <input type="hidden" name="events_page_auto" value="0">
                        <input type="checkbox" name="events_page_auto" value="1" <?php checked( $events_page_auto, true ); ?>>
                        Créer une page qui affiche tous les événements du mois sélectionné.
                    </td>
                </tr>
            <!-- Réglage qui permet d'activer ou non le défilement automatique des slides -->
            <?php do_settings_sections( 'events_slides_auto' ); ?>
                <tr valign="top">
                    <th scope="row">Défilement automatique des slides</th>
                    <td>
                        <input type="hidden" name="events_slides_auto" value="0">
                        <input type="checkbox" name="events_slides_auto" value="1" <?php checked( $slides_auto, true ); ?>>
                        Change de slide automatiquement toutes les <strong><?php echo esc_attr( $slides_speed ); ?></strong> millisecondes.
                    </td>
                </tr>
            <!-- Réglage qui permet de choisir les slides boucles ou non -->
            <?php do_settings_sections( 'events_slides_infinite' ); ?>
                <tr valign="top">
                    <th scope="row">Slides en boucle</th>
                    <td>
                        <input type="hidden" name="events_slides_infinite" value="0">
                        <input type="checkbox" name="events_slides_infinite" value="1" <?php checked( $slides_infinite, true ); ?>>
                        Crée une boucle de slide.
                    </td>
                </tr>
            <!-- Réglage qui permet de choisir si les points sont affiché ou non -->
            <?php do_settings_sections( 'events_slides_dots' ); ?>
                <tr valign="top">
                    <th scope="row">Points de défilement</th>
                    <td>
                        <input type="hidden" name="events_slides_dots" value="0">
                        <input type="checkbox" name="events_slides_dots" value="1" <?php checked( $slides_dots, true ); ?>>
                        Affiche les pointillés indiquant à quel slide on est.
                    </td>
                </tr>
            <!-- Réglage qui permet de choisir la vitesse de défilement des slides-->
            <?php do_settings_sections( 'events_slides_speed' ); ?>
                <tr valign="top">
                    <th scope="row">Durée entre chaque défilement des slides</th>
                    <td>
                        <input type="number" name="events_slides_speed" value="<?php echo esc_attr( $slides_speed ); ?>">
                        En millisecondes.
                    </td>
                </tr>
            <!-- Réglage qui permet de choisir la vitesse de défilement entre deux slides-->
            <?php do_settings_sections( 'events_slides_speed_pass' ); ?>
                <tr valign="top">
                    <th scope="row">Vitesse de défilement d'une slide à une autre</th>
                    <td>
                        <input type="number" name="events_slides_speed_pass" value="<?php echo esc_attr( $slides_speed_pass ); ?>">
                        En millisecondes.
                    </td>
                </tr>
            <!-- Réglage qui permet de choisir la couleur de fond des slides-->
            <?php do_settings_sections( 'events_background_color' ); ?>
                <tr valign="top">
                    <th scope="row">Couleur de fond</th>
                    <td>
                        <input type="color" name="events_background_color" value="<?php echo esc_attr( $background_color ); ?>">
                        Veuillez vous assurer d'avoir toujours un ratio de contraste entre le texte et le fond d'au moins 7.
                    </td>
                </tr>
            <!-- Réglage qui permet de choisir la couleur du texte-->
            <?php do_settings_sections( 'events_text_color' ); ?>
                <tr valign="top">
                    <th scope="row">Couleur du texte</th>
                    <td>
                        <input type="color" name="events_text_color" value="<?php echo esc_attr( $text_color ); ?>">
                        Veuillez vous assurer d'avoir toujours un ratio de contraste entre le texte et le fond d'au moins 7.
                    </td>
                </tr>
            <!-- Réglage qui permet de choisir la couleur du survole de texte-->
            <?php do_settings_sections( 'events_text_hover_color' ); ?>
                <tr valign="top">
                    <th scope="row">Couleur du texte en survole</th>
                    <td>
                        <input type="color" name="events_text_hover_color" value="<?php echo esc_attr( $text_hover_color ); ?>">
                        Veuillez vous assurer d'avoir toujours un ratio de contraste entre le texte et le fond d'au moins 7.
                    </td>
                </tr>
            <!-- Réglage qui permet de choisir la police d'écriture du titre-->
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
            <!-- Bouton de validation des régagles -->
            <?php submit_button( 'Enregistrer les réglages', 'primary', 'events_submit_button' ); ?>
        </form>
    </div>
<?php
}
