<?php

// Inclure la bibliothèque Font Awesome
wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css');

/**
 * Effectue la fonction alert_menu_page quand le menu admin est utilisé
 */
add_action( 'admin_menu', 'alert_menu_page' );

/**
 * Créer un sous menu Régagles pour les événements
 */
function alert_menu_page() {
    add_submenu_page(
        'administration-gie',
        "Menu des alertes",
        'Alerte',
        'manage_options',
        'alert-menu',
        'alert_page'
    );
    
}
/**
 * Affiche la page d'alerte avec le bouton pour afficher le message d'alerte
 */
function alert_page() {
    ob_start(); // démarre la mise en tampon de sortie

    // Vérifie si le bouton d'alerte a été cliqué
    if (isset($_POST['alert_button'])) {
        setcookie('alert_cookie', '1', time() + 3600); // définit un cookie pour activer l'alerte pendant 1 heure
        wp_redirect(get_permalink()); // recharge la page
        exit();
    }

    // Vérifie si le bouton de désactivation de l'alerte a été cliqué
    if (isset($_POST['disable_alert_button'])) {
        setcookie('alert_cookie', '', time() - 3600); // supprime le cookie pour désactiver l'alerte
        wp_redirect(get_permalink()); // recharge la page
        exit();
    }

    // Vérifie si le formulaire de personnalisation de l'alerte a été soumis
    if (isset($_POST['alert_text']) && isset($_POST['alert_background_color']) && isset($_POST['alert_text_color']) && isset($_POST['alert_icon'])) {
        // Met à jour les options de personnalisation de l'alerte
        update_option('alert_text', sanitize_text_field($_POST['alert_text']));
        update_option('alert_background_color', sanitize_hex_color($_POST['alert_background_color']));
        update_option('alert_text_color', sanitize_hex_color($_POST['alert_text_color']));
        update_option('alert_icon', sanitize_text_field($_POST['alert_icon']));
    }

    // Vérifie si le cookie est défini pour afficher l'alerte
    $alert_enabled = isset($_COOKIE['alert_cookie']) && $_COOKIE['alert_cookie'] === '1';

    if ($alert_enabled) {
        echo '<div class="alert-banner" style="background-color:' . esc_attr(get_option('alert_background_color', '#ff0000')) . '; color:' . esc_attr(get_option('alert_text_color', '#ffffff')) . '"><span class="alert-icon">' . esc_attr(get_option('alert_icon', '🚨')) . '</span><span class="alert-message">' . esc_html(get_option('alert_text', 'Alerte!')) . '</span><span class="close-button">&times;</span></div>';
    }
    // Inclure la bibliothèque Font Awesome
    wp_enqueue_style( 'font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css' );

    // Affiche le menu d'alerte
    echo '<div class="wrap">';
    echo '<h1>Menu des alertes</h1>';
    echo '<form method="post">';
    if ($alert_enabled) {
        echo '<p>L\'alerte est actuellement activée. <input type="submit" name="disable_alert_button" value="Désactiver l\'alerte" class="button-secondary"></p>';
    } else {
        echo '<p>Cliquez sur le bouton ci-dessous pour afficher une alerte: <input type="submit" name="alert_button" value="Afficher l\'alerte" class="button-primary"></p>';
    }
    echo '<h2>Personnaliser l\'alerte</h2>';
    echo '<p><label for="alert_text">Texte :</label> <input type="text" name="alert_text" value="' . esc_attr(get_option('alert_text', 'Alerte!')) . '" size="40"></p>';
    echo '<p><label for="alert_background_color">Couleur de fond :</label> <input type="color" name="alert_background_color" value="' . esc_attr(get_option('alert_background_color', '#ff0000')) . '"></p>';
    echo '<p><label for="alert_text_color">Couleur du texte :</label> <input type="color" name="alert_text_color" value="' . esc_attr(get_option('alert_text_color', '#ffffff')) . '"></p>';
    echo '<p><label for="alert_icon">Icône :</label> <select name="alert_icon" class="fa-icons">';
    $icons = array(
        'fa-bell' => 'Bell',
        'fa-exclamation-triangle' => 'Exclamation Triangle',
        'fa-fire' => 'Fire',
        'fa-flag' => 'Flag',
        'fa-shield-alt' => 'Shield',
        // Ajoutez ici d'autres icônes de Font Awesome
    );
    $current_icon = get_option('alert_icon', 'fa-bell');
    foreach ($icons as $icon => $label) {
        $selected = ($icon === $current_icon) ? ' selected' : '';
        echo '<option value="' . esc_attr($icon) . '"' . $selected . '><i class="fa ' . esc_attr($icon) . '"></i> ' . esc_html($label) . '</option>';
    }
    echo '</select></p>';
    
    echo '<p><input type="submit" value="Enregistrer les options de personnalisation" class="button-primary"></p>';
    echo '</form>';
    echo '</div>';
    }
