<?php

/**
 * Effectue la fonction shortcode_settings_page quand le menu admin est utilisé
 */
add_action( 'admin_menu', 'shortcode_settings_page' );

/**
 * Créer un sous menu Régagles pour les événements
 */
function shortcode_settings_page() {
    add_submenu_page(
        'administration-gie',
        "Générateur de shortcode",
        'Shortcode Slider de catégorie',
        'manage_options',
        'generator-shortcode',
        'shortcode_generate_page'
    );
    
}

function shortcode_generate_page() {
    // Récupération de toutes les catégories
    $categories = get_categories();

    // Création du formulaire
    $form = '<form method="post">';
    $form .= '<label for="categorie">Catégorie :</label>';
    $form .= '<select name="categorie" id="categorie">';
    foreach ($categories as $categorie) {
        $form .= '<option value="' . $categorie->term_id . '">' . $categorie->name . '</option>';
    }
    $form .= '</select>';
    $form .= '<input type="submit" value="Valider">';
    $form .= '</form>';

    // Affichage du formulaire
    echo $form;

    // Traitement du formulaire
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_POST['categorie'])) {
            $categorie_id = $_POST['categorie'];
            $categorie_name = '';
            foreach ($categories as $categorie) {
                if ($categorie->term_id == $categorie_id) {
                    $categorie_name = $categorie->name;
                    break;
                }
            }
            echo 'Shortcode à copier-coller : [slider_article idCat=' . $categorie_id . ']' . $categorie_name . '[/slider_article]';
        }
    }
}
