<?php


// La fonction add_action va nous permettre d'ajouter un lien de menu dans l'administration, nous précisons que nous devrons exécuter la méthode BackOfficeMenu() sur $this (cet objet).
add_action( 'admin_menu', 'BackOfficeMenu' );

// Nous demandons à wordpress de prendre en compte les méthodes suivantes :.
add_action( 'wp_loaded', 'sauvegardeContact' );

// si pas dans l'administration / le backoffice
if ( !is_admin() ) {
  // Création des ShortCodes ********************************************************************************************************************************************************************
  // Cela rend un shortCode disponible :  [listingContact_GIE]   Voici les derniers contacts :   [/listingContact_GIE]
  add_shortcode( 'listingContact_GIE', 'affichageShortCode_GIE' );
  // Permet de charger et d'enregistrer le paramétrages
  add_action( 'admin_init', 'registerSettings' );
}
  // affichageShortCode_GIE() représente notre méthode qui s'exécutera lorsque l'affichage du ShortCode sera demandé. *****************************************************************************
function affichageShortCode_GIE() {
    // Cette ligne me permet d'importer une variable global dans un espace local
    global $wpdb;
    ob_start(); // Met en mémoire tampon la sortie du code
    // Requête SQL permettant de selectionner les categories.
    $row = $wpdb->get_results( "SELECT annuaire_cat_nom FROM {$wpdb->prefix}annuaire_categorie" ); // var_dump($row);
    echo '<table class="wp-list-table widefat fixed striped table-view-excerpt posts">';
    echo '<tr><th>Les catégories</th></tr>';

    // La boucle FOREACH et son contenu permettent de parcourrir et d'afficher toutes les informations.
    // Chaque ligne de résultat est représentée par $row
    foreach ( $row AS $valeur ) {
      echo '<tr>';
      echo '<td>' . $valeur->annuaire_cat_nom . '</td>';
      echo '</tr>';
    }
    echo '</table>';
    wp_reset_postdata();
    return ob_get_clean();
  }

	
  // Cette méthode permet d'ajouter des liens de menu dans le Back Office.**************************************************************************************************************************
function BackOfficeMenu() {
    // add_menu_page() ajoute une rubrique dans la colonne de gauche et précise quelle sera la méthode à exécutée pour définir l'affichage : BackOfficeGestion()
    // Titre de la page, Titre du menu, droits, slug, fonction, icon, position
    // Icons : dashicons-excerpt-view, dashicons-universal-access-alt
    add_menu_page( 'Administration', 'Admin GIE', 'manage_options', 'administration-gie', 'BackOfficeGestion' , 'dashicons-welcome-widgets-menus', 24 );
    // add_submenu_page() ajoute une sous-rubrique dans la colonne et précise quelle sera la méthode à exécutée pour définir l'affichage : BackOfficeAffichage()
    // ajouter null pour ne pas afficher le sous menu 
/*    add_submenu_page( 'administration-gie', 'Affichage des catégories', 'Les catégories', 'manage_options', 'affichageCategories', array( $this, 'BackOfficeCategories' ) );
    add_submenu_page( Null, 'Ajouter une catégorie de lieu', 'Ajouter une catégorie de lieu', 'manage_options', 'ajouterCat', array( $this, 'BackOfficeCatAjout' ) );*/
  }
	
  // Cette méthode est exécutée pour afficher notre page de backOffice : Gestion
function BackOfficeGestion() {
    // get_admin_page_title() est une fonction qui permet de récupérer le titre définit dans le 1er argument de la fonction add_menu_page().
    echo '<h1>' . get_admin_page_title() . '</h1>';
    echo '<h2> Les codes courts ou ShortCodes</h2>
		 <hr>
		 <h3>[numerosUtiles_GIE]   Les numéros utiles :   [/numerosUtiles_GIE]</h3>
		 <p>Affiche la liste des numéros utiles</p>
		 <hr>
		 <h3>[listingContact_GIE]   Voici les derniers contacts :   [/listingContact_GIE]</h3>
		 <p>Affiche la liste des contacts</p>
		 <hr>
		 <h3>[Leaflet_GIE]   Affiche une carte :   [/Leaflet_GIE]</h3>
		 <p>Affiche une carte centré sur Chauny</p>
		 <hr>
		 <h3>[Leaflet_GIE idcat="20"]   Affiche une carte :   [/Leaflet_GIE]</h3>
		 <p>Affiche une carte centré sur Chauny avec les différents lieux en fonction de la catégorie.</p>
		 <hr>
		 <h3>[Lien_Cat_GIE]   Affiche les catégories de lieu :   [/Lien_Cat_GIE]</h3>
		 <p> Affiche les catégories de lieu sous forme de lien. /!\ Doit être associé au code court "Affichage d\'une carte" /!\ </p>
		 <hr>
		 [carteSeule_GIE]   affiche une carte seule :   [/carteSeule_GIE]';
  }
	
  // La méthode sauvegardeContact() s'exécute pour enregistrer un contact.****************************************************************************************************************************
function sauvegardeContact() {
    // si le champ emailContact n'est pas vide (et donc rempli ;-)), nous procédons à l'insertion en base de données.
    if ( !empty( $_POST[ 'emailContact' ] ) ) {
      global $wpdb;
      $wpdb->insert( "{$wpdb->prefix}contact", array( 'email' => $_POST[ 'emailContact' ] ) );
    }
  }

  // Fonction permettrant d'enregistrer le paramétrage
function registerSettings() {
    register_setting( 'parametres', 'emailContact' );
  }