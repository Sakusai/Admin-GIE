<?php

/**
 * Effectue la fonction shortcode_settings_page quand le menu admin est utilisé
 */
add_action('admin_menu', 'shortcode_settings_page');

/**
 * Créer un sous menu Régagles pour les événements
 */
function shortcode_settings_page()
{
    add_submenu_page(
        'administration-gie',
        "Générateur de shortcode",
        'Slider d\'article',
        'manage_options',
        'generator-shortcode',
        'shortcode_generate_page'
    );

}

function shortcode_generate_page()
{
    // Récupération de toutes les catégories
    $categories = get_categories();
    echo '<h1>Créer un shortcode pour afficher un slider d\'article</h1>';
    echo '<h4>Vous pouvez générer un shortcode qui vous permettra d\'afficher un carrousel des articles de la catégorie choisie</h4>';
    
    // Création du formulaire
    $form = '<form method="post">';
    $form .= '<label for="categorie">Catégorie :</label>';
    
    // Générer le menu déroulant des catégories
    $form .= wp_dropdown_categories(
        array(
            'name' => 'categorie',
            'id' => 'categorie',
            'show_option_none' => 'Choisir une catégorie',
            'option_none_value' => '',
            'orderby' => 'name',
            'hide_empty' => false,
            'echo' => false,
            'selected' => isset($_POST['categorie']) ? $_POST['categorie'] : false, // Conserve la catégorie sélectionnée lors de la soumission du formulaire
        )
    );
    
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
            $shortcode = '[slider_article idCat=' . $categorie_id . ']' . $categorie_name . '[/slider_article]';
            
            echo '<textarea id="generatedText" rows="5" readonly>' . $shortcode . '</textarea>';
            echo '<button id="copyButton" onclick="copyText()">Copier</button>';
            echo '<script>
                function copyText() {
                    var generatedText = document.getElementById("generatedText");
                    generatedText.select();
                    document.execCommand("copy");
                    alert("Le shortcode a été copié !");
                }
            </script>';
        }
    }
}
