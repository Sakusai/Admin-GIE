<?php

/**
 * Effectue la fonction post_settings_page quand le menu admin est utilisé
 */
add_action( 'admin_menu', 'post_settings_page' );

/**
 * Créer un sous menu Régagles pour les événements
 */
function post_settings_page() {
    add_submenu_page(
        'edit.php',
        "Réglages du slider de catégorie d'article",
        'Réglages',
        'manage_options',
        'post-settings',
        'post_render_settings_page'
    );
    
}


/**
 * Effectue la fonction events_register_settings quand le panel admin est initialisé
 */
add_action( 'admin_init', 'post_register_settings' );

/**
 * Créer les options modifiables
 */
function post_register_settings() {
    register_setting( 'post_options_group', 'post_slides_to_show' );
    register_setting( 'post_options_group', 'post_slides_format' );
    register_setting( 'post_options_group', 'post_slides_speed' );
    register_setting( 'post_options_group', 'post_slides_speed_pass' );
}


/**
 * Créé les options, ici le choix du nombre d'événements dans le slider
 */
function post_render_settings_page() {
    $slides_to_show = get_option( 'post_slides_to_show', 4 );
    $slides_format = get_option( 'post_slides_format', 1 );
    $slides_speed = get_option('post_slides_speed', 3000);
    $slides_speed_pass = get_option('post_slides_speed_pass', 300);

    ?>
     <link rel="stylesheet" type="text/css" href="<?php echo plugin_dir_url(__FILE__) . '../CSS/style.css'; ?>"> <!-- Lien vers notre fichier css -->
    <div class="wrap">
        <h1><strong>Réglages des événements</strong></h1>
        <h2>Slider</h2>
        <!-- Formulaire des réglages possible -->
        <form method="post" action="options.php">
            <?php settings_fields( 'post_options_group' ); ?> <!-- Sélection de l'emplacement des réglages -->
            <!-- Réglage qui permet de choisir combien de slides on affiche en même temps -->
            <?php do_settings_sections( 'post_slides_to_show' ); ?> 
            <table class="settings">
                <tr valign="top">
                    <th scope="row">Nombre de slides à afficher</th>
                    <td>
                        <input type="number" name="post_slides_to_show" min="3" max="6" value="<?php echo esc_attr( $slides_to_show ); ?>">
                        De 3 à 6 slides affiché en même temps.
                    </td>
                </tr>
            <!-- Réglage qui permet de chosir le format des slides -->
            <?php do_settings_sections( 'post_slides_format' ); ?> 
 
                <tr valign="top">
                <th scope="row">Format des slides</th>
                    <td>
                        <label>
                            <input type="radio" name="post_slides_format" value="1" <?php checked( $slides_format, 1 ); ?>>
                            Format 1
                        </label><br>
                        <label>
                            <input type="radio" name="post_slides_format" value="2" <?php checked( $slides_format, 2 ); ?>>
                            Format 2
                        </label>
                    </td>
                </tr>
            <!-- Réglage qui permet de choisir la vitesse de défilement des slides-->
            <?php do_settings_sections( 'post_slides_speed' ); ?>
                <tr valign="top">
                    <th scope="row">Durée entre chaque défilement des slides</th>
                    <td>
                        <input type="number" name="post_slides_speed" value="<?php echo esc_attr( $slides_speed ); ?>">
                        En millisecondes.
                    </td>
                </tr>
            <!-- Réglage qui permet de choisir la vitesse de défilement entre deux slides-->
            <?php do_settings_sections( 'post_slides_speed_pass' ); ?>
                <tr valign="top">
                    <th scope="row">Vitesse de défilement d'une slide à une autre</th>
                    <td>
                        <input type="number" name="post_slides_speed_pass" value="<?php echo esc_attr( $slides_speed_pass ); ?>">
                        En millisecondes.
                    </td>
                </tr>
            </table>
            <!-- Bouton de validation des régagles -->
            <?php submit_button( 'Enregistrer les réglages', 'primary', 'events_submit_button' ); ?>
        </form>
    </div>
<?php
}