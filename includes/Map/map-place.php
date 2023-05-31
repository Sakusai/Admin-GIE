<?php

  // La fonction add_action va nous permettre d'ajouter un lien de menu dans l'administration, nous précisons que nous devrons exécuter la méthode BackOfficeMenu() sur $this (cet objet).
  add_action( 'admin_menu', 'BackOfficeMenuL' );

  // Nous demandons à wordpress de prendre en compte les méthodes suivantes :
  add_action( 'wp_loaded', 'actionLieu' );
  add_action( 'wp_loaded', 'actionCat' );

  // si pas dans l'administration / le backoffice
  if ( !is_admin() ) {
    // Création des ShortCodes ********************************************************************************************************************************************************************
    // Cela rend un shortCode disponible :  [Leaflet_GIE idcat="20"]   Affiche une carte :   [/Leaflet_GIE]
    add_shortcode( 'Leaflet_GIE', 'ShortCode_Leaflet_GIE');
    
      // Cela rend un shortCode disponible :  [Lien_Cat_GIE idcat="20"]   Affiche les catégories de lieu sous forme de lien :   [/Lien_Cat_GIE]
    add_shortcode( 'Lien_Cat_GIE', 'ShortCode_Lien_Cat_GIE' );
    // Permet de charger et d'enregistrer le paramétrages
    add_action( 'admin_init', 'registerSettings' );
  }
  install();

  function install() {
    // Cette ligne me permet d'importer une variable global dans un espace local - $wpdb->prefix nous permettra de récupérer les préfixes de tables s'il y en a.
    global $wpdb;

    // Cette ligne me permet de formuler une requête SQL pour créer une table dans la BDD.
    $wpdb->query( "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}annuaire_categorie (
      `annuaire_cat_id` int(10) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
      `annuaire_cat_nom` varchar(50) CHARACTER SET utf8 NOT NULL,
      `annuaire_parent` int(11) NOT NULL,
      `annuaire_cat_ordre` int(2) UNSIGNED DEFAULT NULL,
      `annuaire_cat_valid` tinyint(1) NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;" );
    
    $wpdb->query( "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}annuaire_lieu (
        `annuaire_lieu_id` int(10) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
        `annuaire_cat_id` int(10) UNSIGNED NOT NULL,
        `annuaire_souscat_id` int(10) UNSIGNED NOT NULL,
        `annuaire_ordre` int(2) UNSIGNED DEFAULT NULL,
        `annuaire_lieu_nom` varchar(100) CHARACTER SET utf8 NOT NULL,
        `annuaire_lat` float(10,8) NOT NULL,
        `annuaire_long` float(11,7) NOT NULL,
        `annuaire_adresse` varchar(100) CHARACTER SET utf8 NOT NULL,
        `annuaire_codepostal` char(5) CHARACTER SET utf8 NOT NULL,
        `annuaire_ville` varchar(100) COLLATE utf8mb4_unicode_520_ci NOT NULL,
        `annuaire_telephone` varchar(20) CHARACTER SET utf8 NOT NULL,
        `annuaire_mail` varchar(100) CHARACTER SET utf8 NOT NULL,
        `annuaire_site_adresse` varchar(255) CHARACTER SET utf8 NOT NULL,
        `annuaire_site_intitule` varchar(100) CHARACTER SET utf8 NOT NULL,
        `annuaire_horaire` varchar(255) CHARACTER SET utf8 NOT NULL,
        `annuaire_infos` varchar(255) CHARACTER SET utf8 NOT NULL,
        `annuaire_responsable` varchar(50) CHARACTER SET utf8 NOT NULL,
        `annuaire_photo` varchar(100) CHARACTER SET utf8 NOT NULL,
        `annuaire_icone` varchar(100) CHARACTER SET utf8 NOT NULL,
        `annuaire_date_debut` date DEFAULT NULL,
        `annuaire_date_fin` date DEFAULT NULL,
        `annuaire_lieu_valid` tinyint(1) NOT NULL
       ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;"
    );
         }

  function ShortCode_Lien_Cat_GIE( $atts, $content ) {
    // Cette ligne me permet d'importer une variable global dans un espace local
    global $wpdb;
	// $wpdb->get_results nous permet de formuler une requête SQL de selection.
    $row = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}annuaire_categorie WHERE annuaire_parent = '0' AND annuaire_cat_valid = '1' ORDER BY annuaire_cat_nom");

    // Chaque ligne de résultat est représentée par $row
	echo '<!-- début d\'affichage des catégories -->';
	if ( $wpdb->num_rows > 0 ) {
	  echo '<p>Cliquer sur une catégorie pour l\'afficher sur la carte <b>';
      foreach ( $row AS $valeur ) {
        echo '| <a href="?carteidcat=' . $valeur->annuaire_cat_id . '">' . $valeur->annuaire_cat_nom . '</a> ';
        //echo 'parent ' . $valeur->annuaire_parent . ' ';
        //echo 'num ordre ' . $valeur->annuaire_cat_ordre . ' ';
        //echo 'valid ' . $valeur->annuaire_cat_valid . ' ';
      }
	  echo '</b></p>';
	}
    wp_reset_postdata();
    return ob_get_clean();
  }
  function calculerCentreLieux($catId) {
    global $wpdb;

    // Récupérer les coordonnées géographiques des lieux de la catégorie spécifiée
    $query = $wpdb->prepare("SELECT annuaire_lat, annuaire_long FROM {$wpdb->prefix}annuaire_lieu WHERE annuaire_cat_id = %d", $catId);
    $coordonnees = $wpdb->get_results($query);

    // Initialiser les variables pour le calcul du centre
    $totalLat = 0;
    $totalLong = 0;
    $nombreLieux = count($coordonnees);

    // Calculer la somme des latitudes et des longitudes
    foreach ($coordonnees as $coordonnee) {
        $totalLat += $coordonnee->annuaire_lat;
        $totalLong += $coordonnee->annuaire_long;
    }

    // Calculer la latitude et la longitude moyenne
    $centreLat = $totalLat / $nombreLieux;
    $centreLong = $totalLong / $nombreLieux;

    // Retourner les coordonnées du centre
    return array('lat' => $centreLat, 'long' => $centreLong);
}
function ShortCode_Leaflet_GIE($atts, $content)
{
    global $wpdb;

    if (!empty($_GET['carteidcat'])) {
        $idcat = $_GET['carteidcat'];
    } else {
        extract(shortcode_atts(array('idcat' => '32'), $atts));
    }

    $centre = calculerCentreLieux($idcat);

    $codecarte = "<!-- début de l'affichage de la carte 12/040/2023 16:00:47 -->\n";
    $codecarte .= '<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css" integrity="sha256-kLaT2GOSpHechhsozzB+flnD+zUyjE2LlfWPgU04xyI=" crossorigin="" />' . "\n";
    $codecarte .= '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.markercluster/1.4.1/MarkerCluster.css" />' . "\n";
    $codecarte .= '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.markercluster/1.4.1/MarkerCluster.Default.css" />' . "\n";
    $codecarte .= '<script src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js" integrity="sha256-WBkoXOwTeyKclOHuWtc+i2uENFpDZ9YPdf5Hf+D7ewM=" crossorigin=""></script>' . "\n";
    $codecarte .= '<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.markercluster/1.4.1/leaflet.markercluster.js"></script>' . "\n";
    $codecarte .= '<div id="map" style="width: 100%; height: 650px; z-index: 1"></div>' . "\n";
    $codecarte .= '<script>';
    $codecarte .= 'const map = L.map(\'map\').setView([' . $centre['lat'] . ', ' . $centre['long'] . '], 9);

    const tiles = L.tileLayer(\'https://tile.openstreetmap.org/{z}/{x}/{y}.png\', {
        maxZoom: 19,
        attribution: \'&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>\'
    }).addTo(map);

    const markers = L.markerClusterGroup();

    map.addLayer(markers);';

    $rowcat = $wpdb->get_results("SELECT annuaire_cat_id FROM {$wpdb->prefix}annuaire_categorie WHERE annuaire_parent = $idcat");
    if ($wpdb->num_rows > 0) {
        foreach ($rowcat as $valcat) {
            $row = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}annuaire_lieu WHERE annuaire_cat_id = $valcat->annuaire_cat_id ");
            foreach ($row as $val) {
                $codecarte .= "
                var myIcon = L.icon({
                    iconUrl: '$val->annuaire_icone',
                    iconSize: [50, 50],
                    iconAnchor: [25, 50],
                    popupAnchor: [-3, -76],
                });

                var popupAff = \"<!-- g1 --><a href='http://maps.google.com/maps?daddr=$val->annuaire_lat, $val->annuaire_long&ll=' target='_blank'>$val->annuaire_lieu_nom</a><br>\";
                var marker = L.marker([$val->annuaire_lat, $val->annuaire_long], { icon: myIcon }).bindPopup(popupAff);
                markers.addLayer(marker);";
            }
        }
    } else {
        $row = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}annuaire_lieu WHERE annuaire_cat_id = $idcat");
        foreach ($row as $val) {
            $codecarte .= "
            var myIcon = L.icon({
                iconUrl: '$val->annuaire_icone',
                iconSize: [50, 50],
                iconAnchor: [25, 50],
                popupAnchor: [-3, -76],
            });

            var popupAff = \"<!-- g2 --><a href='http://maps.google.com/maps?daddr=$val->annuaire_lat, $val->annuaire_long&ll=' target='_blank'>$val->annuaire_lieu_nom</a><br>\";
            var marker = L.marker([$val->annuaire_lat, $val->annuaire_long], { icon: myIcon }).bindPopup(popupAff);
            markers.addLayer(marker);";
        }
    }

    $codecarte .= "</script>\n<!-- fin de l'affichage de la carte -->";

    return $codecarte;
}


  function uninstall() {
    global $wpdb;
    // $wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}contact;" );
  }

  function BackOfficeMenuL() {
    // add_menu_page() ajoute une rubrique dans la colonne de gauche et précise quelle sera la méthode à exécutée pour définir l'affichage : BackOfficeGestion()
    // Titre de la page, Titre du menu, droits, slug, fonction, icon, position
    // Icons : dashicons-excerpt-view, dashicons-universal-access-alt
    // add_menu_page( 'Administration', 'Admin GIE', 'manage_options', 'administration-gie', array( $this, 'BackOfficeGestion' ), 'dashicons-welcome-widgets-menus', 24 );
    // add_submenu_page() ajoute une sous-rubrique dans la colonne et précise quelle sera la méthode à exécutée pour définir l'affichage : BackOfficeAffichage()
    // ajouter null pour ne pas afficher le sous menu 
     add_submenu_page( 'administration-gie', 'Affichage des lieux', 'Les lieux', 'manage_options', 'affichageLieux', 'BackOfficeLieux' );
    add_submenu_page( Null, 'Ajouter un lieu', 'Ajouter un lieu', 'manage_options', 'ajouterLieu',  'BackOfficeLieuAjout'  );
    add_submenu_page( Null, 'Modification du lieu', 'Modification du lieu', 'manage_options', 'modificationLieu',  'BackOfficeLieuMod'  );
    add_submenu_page( Null, 'Suppression du lieu', 'Suppression du lieu', 'manage_options', 'suppressionLieu',  'BackOfficeLieuSuppr'  );
    add_submenu_page( 'administration-gie', 'Affichage des catégories', 'Catégories de lieux', 'manage_options', 'affichageCategories', 'BackOfficeCategories');
    add_submenu_page( Null, 'Ajouter une catégorie de lieu', 'Ajouter une catégorie de lieu', 'manage_options', 'ajouterCat', 'BackOfficeCatAjout' );
    add_submenu_page( Null, 'Modification de la catégorie de lieu', 'Modification de la catégorie de lieu', 'manage_options', 'modificationCat','BackOfficeCatMod' );
    add_submenu_page( Null, 'Suppression de la catégorie de lieu', 'Suppression de la catégorie de lieu', 'manage_options', 'suppressionCat', 'BackOfficeCatSuppr' );
  }

  function BackOfficeLieux() {
    global $wpdb;
    echo '<div class="wrap">';
    echo '  <h1 class="wp-heading-inline">' . get_admin_page_title() . '</h1>';
    echo '  <a href="admin.php?page=ajouterLieu" class="page-title-action">Ajouter</a>';
    echo '  <hr class="wp-header-end">';

    // Champ de recherche
    echo '<form method="GET" action="admin.php">';
    echo '<input type="hidden" name="page" value="affichageLieux">';
    echo '<input type="text" name="search" placeholder="Rechercher">';
    echo '<input type="submit" value="Rechercher">';
    echo '</form>';

    // Pagination
    $per_page = 20; // Nombre de lieux par page
    $current_page = isset($_GET['paged']) ? max(1, absint($_GET['paged'])) : 1;
    $offset = ($current_page - 1) * $per_page;

    // Requête de recherche
    $search_query = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';

    $query = $wpdb->prepare(
        "SELECT {$wpdb->prefix}annuaire_lieu.annuaire_cat_id, {$wpdb->prefix}annuaire_categorie.annuaire_cat_nom, annuaire_ordre, annuaire_lieu_id, annuaire_lieu_nom, annuaire_lat, annuaire_long, annuaire_adresse, annuaire_codepostal, annuaire_ville 
        FROM {$wpdb->prefix}annuaire_lieu 
        LEFT JOIN {$wpdb->prefix}annuaire_categorie 
        ON {$wpdb->prefix}annuaire_categorie.annuaire_cat_id = {$wpdb->prefix}annuaire_lieu.annuaire_cat_id 
        WHERE annuaire_lieu_nom LIKE %s
        LIMIT %d, %d",
        '%' . $wpdb->esc_like($search_query) . '%',
        $offset,
        $per_page
    );

    $row_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}annuaire_lieu WHERE annuaire_lieu_nom LIKE '%{$search_query}%'");

    $total_pages = ceil($row_count / $per_page);

    $row = $wpdb->get_results($query);

    // Affichage du tableau
    echo '  <table class="wp-list-table widefat fixed striped table-view-excerpt posts">
  			<thead>';
    $tableheadfoot = '
  		  <tr>
			  <th id="ID" class="manage-column column-title column-primary sortable desc"><a><span>ID</span></a></th>
			  <th id="Categorie" class="manage-column column-title column-primary sortable desc" width="25%"><a><span>Catégorie</span></a></th>
			  <th id="Numeros_ordre" class="manage-column column-title column-primary sortable desc"><a><span>Numéros d\'ordre</span></a></th>
			  <th id="Nom" class="manage-column column-title column-primary sortable desc"><a><span>Nom</span></a></th>
			  <th id="Coordonnees" class="manage-column column-title column-primary sortable desc"><a><span>Coordonnées</span></a></th>
			  <th id="Adresse" class="manage-column column-title column-primary sortable desc"><a><span>Adresse</span></a></th>
			  <th colspan="3">&nbsp;</th>
		  </tr>';
    echo $tableheadfoot;
    echo '    </thead>';
    echo '    <tbody id="the-list">';
    foreach ($row as $valeur) {
        echo '<tr>';
        echo '<td>' . $valeur->annuaire_lieu_id . '</td>';
        echo '<td>' . $valeur->annuaire_cat_id . ' ' . $valeur->annuaire_cat_nom . '</td>';
        echo '<td>' . $valeur->annuaire_ordre . '</td>';
        echo '<td>' . $valeur->annuaire_lieu_nom . '</td>';
        echo '<td>' . $valeur->annuaire_lat . ' , ' . $valeur->annuaire_long . '</td>';
        echo '<td>' . $valeur->annuaire_adresse . ' ' . $valeur->annuaire_codepostal . ' ' . $valeur->annuaire_ville . '</td>';
        echo '<td><a href="admin.php?page=modificationLieu&nuid=' . $valeur->annuaire_lieu_id . '">Modifier</a></td>';
        echo '<td><a href="admin.php?page=suppressionLieu&nuid=' . $valeur->annuaire_lieu_id . '">Supprimer</a></td>';
        echo '<td><!--Dupliquer--></td>';
        echo '</tr>';
    }
    echo '    </tbody>';
    echo '    <tfoot>';
    echo $tableheadfoot;
    echo '    </tfoot>';
    echo '  </table>';

    // Affichage de la pagination
    if ($total_pages > 1) {
        echo '<div class="tablenav">';
        echo '  <div class="tablenav-pages">';
        echo '    <span class="displaying-num">' . sprintf(
            _n(
                '1 élément',
                '%s éléments',
                $row_count,
                'text-domain'
            ),
            number_format_i18n($row_count)
        ) . '</span>';

        echo paginate_links(array(
            'base' => add_query_arg('paged', '%#%'),
            'format' => '',
            'prev_text' => __('&laquo; Précédent', 'text-domain'),
            'next_text' => __('Suivant &raquo;', 'text-domain'),
            'total' => $total_pages,
            'current' => $current_page,
        ));

        echo '  </div>';
        echo '</div>';
    }

    echo '</div>';
}
  function BackOfficeLieuAjout() {

    //annuaire_lieu_id	Primaire	int(10)			UNSIGNED				Non	Aucun(e)	AUTO_INCREMENT	
    //annuaire_cat_id					int(10)			UNSIGNED				Non	Aucun(e)	
    //annuaire_souscat_id				int(10)			UNSIGNED				Non	Aucun(e)	
    //annuaire_ordre					int(2)			UNSIGNED				Oui	NULL	
    //annuaire_lieu_nom				varchar(100)	utf8_general_ci			Non	Aucun(e)	
    //annuaire_lat					float(10,8)								Non	Aucun(e)	
    //annuaire_long					float(11,7)								Non	Aucun(e)	
    //annuaire_adresse				varchar(100)	utf8_general_ci			Non	Aucun(e)	
    //annuaire_codepostal				char(5)			utf8_general_ci			Non	Aucun(e)	
    //annuaire_ville					varchar(100)	utf8mb4_unicode_520_ci	Non	Aucun(e)	
    //annuaire_telephone				varchar(20)		utf8_generalactionLI_ci			Non	Aucun(e)	
    //annuaire_mail					varchar(100)	utf8_general_ci			Non	Aucun(e)	
    //annuaire_site_adresse			varchar(255)	utf8_general_ci			Non	Aucun(e)	
    //annuaire_site_intitule			varchar(100)	utf8_general_ci			Non	Aucun(e)	
    //annuaire_horaire				varchar(255)	utf8_general_ci			Non	Aucun(e)	
    //annuaire_infos					varchar(255)	utf8_general_ci			Non	Aucun(e)	
    //annuaire_responsable			varchar(50)		utf8_general_ci			Non	Aucun(e)	
    //annuaire_photo					varchar(100)	utf8_general_ci			Non	Aucun(e)	
    //annuaire_icone					varchar(100)	utf8_general_ci			Non	Aucun(e)	
    //annuaire_date_debut				date									Oui	NULL	
    //annuaire_date_fin				date									Oui	NULL	
    //annuaire_lieu_valid				bool
    
        global $wpdb;
        // get_admin_page_title() est une fonction qui permet de récupérer le titre définit dans le 1er argument de la fonction add_menu_page().
        echo '<h1>' . get_admin_page_title() . '</h1>';
        // Sur cette page nous décidons d'afficher un formulaire pour ajouter un lieu.
        echo '<form method="post" action="admin.php?page=affichageLieux">
                <input type="hidden" name="actionLI" value="AjoutLI">
              <table class="wp-list-table striped table-view-excerpt posts borderspacingz">
                <tr>
                    <td><label for="cat">Cat ID</label></td>
                    <td><select id="cat" name="cat_id">
                        <option value="">--- Choisir une catégorie ---</option>';
                        $rowcatid = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}annuaire_categorie ORDER BY annuaire_cat_nom" ); // var_dump($row);
                        foreach ( $rowcatid AS $valcatid ) {
                            echo '<option value="' . $valcatid->annuaire_cat_id . '">' . $valcatid->annuaire_cat_nom . '</option>';
                        }
                    echo '</select>
                    </td> 
                </tr><tr>
                    <td><label for="num_ordre">Numéro d\'ordre</label></td> 
                    <td colspan="2"><input type="text" id="num_ordre" name="ordre" value=""></td> 
                </tr><tr>
                    <td><label for="lieu_nom">Nom du lieu</label></td> 
                    <td colspan="2"><input type="text" size="100" id="lieu_nom" name="nom" value=""></td> 
                </tr><tr>
                    <td><label for="coordonnees">Coordonnées</label></td> 
                    <td><label for="lieu_lat">Lattitude</label> <input type="text" id="lieu_lat" name="lat" value=""></td> 
                    <td><label for="lieu_long">Longitude</label> <input type="text" id="lieu_long" name="long" value=""></td> 
                </tr><tr>
                    <td><label for="lieu_adresse">Adresse</label></td> 
                    <td colspan="2"><input type="text" size="100" id="lieu_adresse" name="adresse" value=""></td> 
                </tr><tr>
                    <td><label for="laville">Ville</label></td>
                    <td><label for="lieu_cp">Code postale</label> <input type="text" id="lieu_cp" name="codepostal" value=""></td>
                    <td><label for="lieu_ville">Ville</label> <input type="text" id="lieu_ville" name="ville" size="60" value=""></td>
                </tr><tr>
                    <td><label for="lieu_tel">Téléphone</label></td>
                    <td colspan="2"><input type="text" id="lieu_tel" name="telephone" value=""></td>
                </tr><tr>
                    <td><label for="lieu_mail">Mail</label></td> 
                    <td colspan="2"><input type="email" id="lieu_mail" name="mail" size="100" value=""></td>
                </tr><tr>
                    <td><label for="site_internet">Site internet</label></td>
                    <td colspan="2"><label for="adresse_web">Adresse du site</label> <input type="text" id="adresse_web" name="site_adresse" size="100" value=""></td>
                </tr><tr>
                    <td>&nbsp;</td>
                    <td colspan="2"><label for="intitule_web">Intitulé du site</label> <input type="text" id="intitule_web" name="site_intitule" size="100" value=""></td>
                </tr><tr>
                    <td><label for="lieu_horaire">Horaires</label></td> 
                    <td colspan="2"><textarea id="lieu_horaire" name="horaire" cols="100" rows="5"> </textarea></td>
                </tr><tr>
                    <td><label for="lieu_infos">Informations complémentaire</label></td>
                    <td colspan="2"><textarea id="lieu_infos" name="infos" cols="100" rows="5"> </textarea></td>
                </tr><tr>
                    <td><label for="lieu_responsable">Responsable</label></td> 
                    <td colspan="2"><input type="text" id="lieu_responsable" name="responsable" size="100" value=""></td>
                </tr><tr>
                <td><label for="lieu_photo">Photo</label></td> 
                <td colspan="2">
                    <div>
                        <button type="button" class="button select-image-button">Sélectionner une image</button>
                    </div>
                    <div class="selected-image-container"></div>
                    <input type="hidden" id="lieu_photo" name="photo" value="">
                </td>
            </tr>
            <tr>
                <td><label for="lieu_icone">Icone</label></td>
                <td colspan="2">
                    <div>
                        <button type="button" class="button select-image-button">Sélectionner une icône</button>
                    </div>
                    <div class="selected-image-container"></div>
                    <input type="hidden" id="lieu_icone" name="icone" value="">
                </td>
            </tr><tr>
                    <td><label for="lieu_date">Date (format : AAAA-MM-JJ)</label></td>
                    <td><label for="lieu_date_deb">Date de début</label> <input type="text" id="lieu_date_deb" name="date_debut" value=""></td>
                    <td><label for="lieu_date_fin">Date de fin</label> <input type="text" id="lieu_date_fin" name="date_fin" value=""></td>
                </tr><tr>
                    <td><label for="lieu_valid">Validation</label></td>
                    <td colspan="2"><input type="checkbox" id="lieu_valid" name="valid" checked></td>
                </tr>
              </table>
              ';
        submit_button( "Ajouter le lieu" ); // bouton submit.
        echo '</form>';
        ?>
        <script>
        jQuery(document).ready(function($) {

        // Gérer le clic sur le bouton de sélection d'image
        $('.select-image-button').click(function() {
            var button = $(this);
            var container = button.closest('td').find('.selected-image-container');
            var input = button.closest('td').find('input[type="hidden"]');

            // Ouvrir la bibliothèque multimédia WordPress
            var file_frame = wp.media.frames.file_frame = wp.media({
                multiple: false
            });

            // Gérer la sélection de l'image
            file_frame.on('select', function() {
                var attachment = file_frame.state().get('selection').first().toJSON();
                var img = $('<img>').attr('src', attachment.url);
                container.html(img);
                input.val(attachment.url);
            });

            // Ouvrir la bibliothèque multimédia
            file_frame.open();
        });
        });
        </script>
 <?php
        echo '<button onclick="history.go(-1);">Retour</button>';
    
      }
      function load_wp_media_files() {
        wp_enqueue_media();
    }
    add_action( 'admin_enqueue_scripts', 'load_wp_media_files' );
      function BackOfficeLieuMod() {
        global $wpdb;
        echo '<h1>' . get_admin_page_title() . '</h1>';
        $liid = $_GET[ 'nuid' ];
        echo "<p>ID Lieu : " . $liid . "</p>";
        $table_matable = "{$wpdb->prefix}annuaire_lieu";
        $enreg = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_matable WHERE annuaire_lieu_id = $liid " ) );
        if ( !( $enreg === FALSE ) ) {
          $checked = "checked";
          if ($enreg->annuaire_lieu_valid == 1) {
            $checked = " checked";
          } else {
            $checked = "";
          }
          // Sur cette page nous décidons d'afficher un formulaire pour modifier un lieu.
          echo '<form method="post" action="admin.php?page=affichageLieux">
            <input type="hidden" name="id" value="' . $liid . '">
            <input type="hidden" name="actionLI" value="ModifieLI">
            <label>Modification du lieu : </label><br />
            <table class="wp-list-table striped table-view-excerpt posts borderspacingz">
              <tr>
                <td><label for="cat">Cat ID</label></td>
                <td><select id="cat" name="cat_id">
                    <option value="">--- Choisir une catégorie ---</option>';
                    
                    $rowcatid = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}annuaire_categorie ORDER BY annuaire_cat_nom" ); // var_dump($row);
                    foreach ( $rowcatid AS $valcatid ) {
                        $selected = "";
                        if ( $enreg->annuaire_cat_id === $valcatid->annuaire_cat_id ) {$selected = " selected";}
                        echo '<option value="' . $valcatid->annuaire_cat_id . '"' . $selected . '>' . $valcatid->annuaire_cat_nom . '</option>';
                    }
                echo '</select>
                </td> 
                <td colspan="2"> <!--<input type="text" size="10" id="cat" name="cat_id" value="' . $enreg->annuaire_cat_id . '">--> </td> 
            </tr><tr>
                <td><label for="num_ordre">Numéro d\'ordre</label></td> 
                <td colspan="2"><input type="text" id="num_ordre" name="ordre" value="' . $enreg->annuaire_ordre . '"></td> 
            </tr><tr>
                <td><label for="lieu_nom">Nom du lieu</label></td> 
                <td colspan="2"><input type="text" size="100" id="lieu_nom" name="nom" value="' . $enreg->annuaire_lieu_nom . '"></td> 
            </tr><tr>
                <td><label for="coordonnees">Coordonnées</label></td> 
                <td><label for="lieu_lat">Lattitude</label> <input type="text" id="lieu_lat" name="lat" value="' . $enreg->annuaire_lat . '"></td> 
                <td><label for="lieu_long">Longitude</label> <input type="text" id="lieu_long" name="long" value="' . $enreg->annuaire_long . '"></td>
            </tr><tr>
                <td><label for="lieu_adresse">Adresse</label></td> 
                <td colspan="2"><input type="text" size="100" id="lieu_adresse" name="adresse" value="' . $enreg->annuaire_adresse . '"></td> 
            </tr><tr>
                <td><label for="laville">Ville</label></td>
                <td><label for="lieu_cp">Code postale</label> <input type="text" id="lieu_cp" name="codepostal" value="' . $enreg->annuaire_codepostal . '"></td>
                <td><label for="lieu_ville">Ville</label> <input type="text" id="lieu_ville" name="ville" size="60" value="' . $enreg->annuaire_ville . '"></td>
            </tr><tr>
                <td><label for="lieu_tel">Téléphone</label></td>
                <td colspan="2"><input type="text" id="lieu_tel" name="telephone" value="' . $enreg->annuaire_telephone . '"></td>
            </tr><tr>
                <td><label for="lieu_mail">Mail</label></td> 
                <td colspan="2"><input type="email" id="lieu_mail" name="mail" size="100" value="' . $enreg->annuaire_mail . '"></td>
            </tr><tr>
                <td><label for="site_internet">Site internet</label></td>
                <td colspan="2"><label for="adresse_web">Adresse du site</label> <input type="text" id="adresse_web" name="site_adresse" size="100" value="' . $enreg->annuaire_site_adresse . '"></td>
            </tr><tr>
                <td>&nbsp;</td>
                <td colspan="2"><label for="intitule_web">Intitulé du site</label> <input type="text" id="intitule_web" name="site_intitule" size="100" value="' . $enreg->annuaire_site_intitule . '"></td>
            </tr><tr>
                <td><label for="lieu_horaire">Horaires</label></td> 
                <td colspan="2"><textarea id="lieu_horaire" name="horaire" cols="100" rows="5">' . $enreg->annuaire_horaire . '</textarea></td>
            </tr><tr>
                <td><label for="lieu_infos">Informations complémentaire</label></td>
                <td colspan="2"><textarea id="lieu_infos" name="infos" cols="100" rows="5">' . $enreg->annuaire_infos . '</textarea></td>
            </tr><tr>
                <td><label for="lieu_responsable">Responsable</label></td> 
                <td colspan="2"><input type="text" id="lieu_responsable" name="responsable" size="100" value="' . $enreg->annuaire_responsable . '"></td>
            </tr>';
            ?>
              <tr>
                <td><label for="lieu_photo">Photo</label></td> 
                <td colspan="2">
                    <div>
                        <button type="button" class="button select-image-button">Sélectionner une image</button>
                    </div>
                    <div class="selected-image-container">
                        <?php
                        $photo = $enreg->annuaire_photo; // Récupérer le chemin d'accès de l'image de la base de données
                        if ($photo) { // Si une image a été sélectionnée précédemment
                            echo '<img src="' . $photo . '" style="max-width: 200px; max-height: 200px;">'; // Afficher l'image
                        }
                        ?>
                    </div>
                    <input type="hidden" id="lieu_photo" name="photo" value="<?php echo $enreg->annuaire_photo; ?>">
                </td>
            </tr>
            <tr>
                <td><label for="lieu_icone">Icone</label></td>
                <td colspan="2">
                    <div>
                        <button type="button" class="button select-image-button" data-field="lieu_icone">Sélectionner une icône</button>
                    </div>
                    <div class="selected-image-container">
                    <?php
                        $icon = $enreg->annuaire_icone; // Récupérer le chemin d'accès de l'image de la base de données
                        if ($icon) { // Si une image a été sélectionnée précédemment
                            echo '<img src="' . $icon . '" style="max-width: 200px; max-height: 200px;">'; // Afficher l'image
                        }
                        ?>
                    </div>
                    <input type="hidden" id="lieu_icone" name="icone" value="<?php echo $enreg->annuaire_icone; ?>">
                </td>
            </tr>

        <?php
        echo '<tr>
                <td><label for="lieu_date">Date (format : AAAA-MM-JJ)</label></td>
                <td><label for="lieu_date_deb">Date de début</label> <input type="text" id="lieu_date_deb" name="date_debut" value="' . $enreg->annuaire_date_debut . '"></td>
                <td><label for="lieu_date_fin">Date de fin</label> <input type="text" id="lieu_date_fin" name="date_fin" value="' . $enreg->annuaire_date_fin . '"></td>
            </tr><tr>
                <td><label for="lieu_valid">Validation</label></td>
                <td colspan="2"><input type="checkbox" id="lieu_valid" name="valid" ' . $checked . '></td>
            </tr>
          </table>';
          ?>
     <script>
        jQuery(document).ready(function($) {

        // Gérer le clic sur le bouton de sélection d'image
        $('.select-image-button').click(function() {
            var button = $(this);
            var container = button.closest('td').find('.selected-image-container');
            var input = button.closest('td').find('input[type="hidden"]');

            // Ouvrir la bibliothèque multimédia WordPress
            var file_frame = wp.media.frames.file_frame = wp.media({
                multiple: false
            });

            // Gérer la sélection de l'image
            file_frame.on('select', function() {
                var attachment = file_frame.state().get('selection').first().toJSON();
                var img = $('<img>').attr('src', attachment.url);
                container.html(img);
                input.val(attachment.url);
            });

            // Ouvrir la bibliothèque multimédia
            file_frame.open();
        });
        });
        </script>

         <?php
          submit_button(); // bouton submit.
          echo '</form>';
          echo '<button onclick="history.go(-1);">Retour</button>';
        } else {
          echo __( "Oups ! Un problème a été rencontré." );
        }
        
      }

      function BackOfficeCategories() {
        // Cette ligne me permet d'importer une variable global dans un espace local et plus généralement $wpdb me permettra de formuler des requêtes SQL.
        global $wpdb;
        echo '<div class="wrap">';
        echo '  <h1 class="wp-heading-inline">' . get_admin_page_title() . '</h1>';
        echo '  <a href="admin.php?page=ajouterCat" class="page-title-action">Ajouter</a>';
        echo '  <hr class="wp-header-end">';
        // get_admin_page_title() est une fonction qui permet de récupérer le 1er argument de la fonction add_submenu_page
        echo get_admin_page_title();
    
        // $wpdb->get_results nous permet de formuler une requête SQL de selection.
        $row = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}annuaire_categorie" ); // var_dump($row);
    
        // Les lignes suivantes nous permettent de définir un affichage sous forme de tableau
        echo '<table class="wp-list-table widefat fixed striped table-view-excerpt posts">';
        echo '<tr><th>ID</th><th>Nom</th><th>Parent</th><th>Numéro d\'ordre</th><th>Validation</th><th> </th></tr>';
    
        // La boucle FOREACH et son contenu permettent de parcourrir et d'afficher toutes les informations.
        // Chaque ligne de résultat est représentée par $row
        foreach ( $row AS $valeur ) {
          echo '<tr>';
          echo '<td>' . $valeur->annuaire_cat_id . '</td>';
          echo '<td>' . $valeur->annuaire_cat_nom . '</td>';
          echo '<td>' . $valeur->annuaire_parent . '</td>';
          echo '<td>' . $valeur->annuaire_cat_ordre . '</td>';
          echo '<td>' . $valeur->annuaire_cat_valid . '</td>';
          echo '<td><a href="admin.php?page=modificationCat&nuid=' . $valeur->annuaire_cat_id . '">Modifier</a></td>';
          echo '<td><a href="admin.php?page=suppressionCat&nuid=' . $valeur->annuaire_cat_id . '&actionCat=SupprCat">Supprimer</a></td>';
          echo '</tr>';
        }
        echo '</table>';
        echo '</div>';
      }
function BackOfficeCatAjout() {
    //annuaire_cat_id					int			UNSIGNED				Non	Aucun(e)	 AUTO_INCREMENT	
    //annuaire_cat_nom					varchar(50)     utf8mb3_general_ci						Non	Aucun(e)	
    //annuaire_parent 					int						Non	Aucun(e)	
    //annuaire_cat_ordre					int			UNSIGNED				Non	Aucun(e)	
    //annuaire_cat_valid					tinyint(1) 							Non	Aucun(e)	
    
        global $wpdb;
        // get_admin_page_title() est une fonction qui permet de récupérer le titre définit dans le 1er argument de la fonction add_menu_page().
        echo '<h1>' . get_admin_page_title() . '</h1>';
        // Sur cette page nous décidons d'afficher un formulaire pour ajouter un lieu.
        echo '<form method="post" action="admin.php?page=affichageCategories">
                <input type="hidden" name="actionCAT" value="AjoutCAT">
              <table class="wp-list-table striped table-view-excerpt posts borderspacingz">
                <tr>
                    <td><label for="cat_nom">Nom de la catégorie</label></td> 
                    <td colspan="2"><input type="text" size="100" id="cat_nom" name="nom" value=""></td> 
                </tr><tr>
                    <td><label for="cat_parent">Catégorie parent</label></td> 
                    <td colspan="2"><input type="text" id="cat_parent" name="parent" value=""></td> 
                </tr><tr>
                    <td><label for="cat_ordre">Ordre</label></td>
                    <td colspan="2"><input type="text" id="cat_ordre" name="ordre" value=""></td>
                </tr><tr>
                <td><label for="lieu_valid">Validation</label></td>
                <td colspan="2"><input type="checkbox" id="lieu_valid" name="valid" checked></td>
              </tr>
              </table>';
        submit_button( "Ajouter la catégorie" ); // bouton submit.
        echo '</form>';
        echo '<button onclick="history.go(-1);">Retour</button>';
    
      }
      function BackOfficeCatMod() {
        global $wpdb;
        echo '<h1>' . get_admin_page_title() . '</h1>';
        $catid = $_GET[ 'nuid' ];
        echo "<p>ID Catégorie : " . $catid . "</p>";
        $table_matable = "{$wpdb->prefix}annuaire_categorie";
        $enreg = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_matable WHERE annuaire_cat_id = $catid " ) );
        if ( !( $enreg === FALSE ) ) {
          $checked = "checked";
          if ($enreg->annuaire_cat_valid == 1) {
            $checked = " checked";
          } else {
            $checked = "";
          }
          // Sur cette page nous décidons d'afficher un formulaire pour modifier un lieu.
          echo '<form method="post" action="admin.php?page=affichageCategories">
            <input type="hidden" name="id" value="' . $catid . '">
            <input type="hidden" name="actionCAT" value="ModifieCAT">
            <label>Modification de la catégorie : </label><br />
            <table class="wp-list-table striped table-view-excerpt posts borderspacingz">
            <tr>
                <td><label for="cat_nom">Nom de la catégorie</label></td> 
                <td colspan="2"><input type="text" size="100" id="cat_nom" name="nom" value="'. $enreg->annuaire_cat_nom .'"></td> 
            </tr><tr>
                <td><label for="cat_parent">Catégorie parent</label></td> 
                <td colspan="2"><input type="text" id="cat_parent" name="parent" value="'. $enreg->annuaire_parent .'"></td> 
            </tr><tr>
                <td><label for="cat_ordre">Ordre</label></td>
                <td colspan="2"><input type="text" id="cat_ordre" name="ordre" value="'. $enreg->annuaire_cat_ordre .'"></td>
            </tr><tr>
            <td><label for="lieu_valid">Validation</label></td>
            <td colspan="2"><input type="checkbox" id="lieu_valid" name="valid" ' . $checked . '></td>
          </tr>
          </table>';
          submit_button(); // bouton submit.
          echo '</form>';
          echo '<button onclick="history.go(-1);">Retour</button>';
        } else {
          echo __( "Oups ! Un problème a été rencontré." );
        }
      
      }
function BackOfficeCatSuppr() {
  global $wpdb;
  echo '<h1>' . get_admin_page_title() . '</h1>';
  $catid = $_GET[ 'nuid' ];
  // Vérifier si l'utilisateur a soumis le formulaire de confirmatio
  // Afficher le formulaire de confirmation
  echo '<form method="post" action="admin.php?page=affichageCategories">
    <input type="hidden" name="id" value="' . $catid . '">
    <input type="hidden" name="actionCAT" value="SupprCAT">
    <label>Êtes-vous sûr de vouloir supprimer cette catégorie?</label>';
    submit_button('Supprimer'); // bouton submit.
    echo '</form> <button onclick="history.go(-1);">Retour</button>';
             
 }
function BackOfficeLieuSuppr() {
  global $wpdb;
  echo '<h1>' . get_admin_page_title() . '</h1>';
  $liid = $_GET[ 'nuid' ];
  // Vérifier si l'utilisateur a soumis le formulaire de confirmatio
  // Afficher le formulaire de confirmation
  echo '<form method="post" action="admin.php?page=affichageLieux">
    <input type="hidden" name="id" value="' . $liid . '">
    <input type="hidden" name="actionLI" value="SupprLI">
    <label>Êtes-vous sûr de vouloir supprimer ce lieu?</label>';
    submit_button('Supprimer'); // bouton submit.
    echo '</form> <button onclick="history.go(-1);">Retour</button>';
  }
  
  function actionCat() { // name="actionLI" value="AjoutLI"
    global $wpdb;
    $resultat = FALSE;
    $message = '';
    if (isset($_POST['actionCAT'])) {
      $actionLI = $_POST['actionCAT'];
      //echo '******************************************************** function actionNumUtile ligne 495 $actionNU = '.$actionNU.' ********************************************************<br>';
      switch ( $actionLI ) {
        case "AjoutCAT":
          if ( !empty( $_POST[ 'nom' ] ) ) {
        if (empty( $_POST[ 'valid' ] ) ) {$valid=0;} else {$valid=1;}
            $resultat = $wpdb->insert( "{$wpdb->prefix}annuaire_categorie", array( 'annuaire_cat_nom' => stripslashes( $_POST[ 'nom' ] ),  'annuaire_parent' => stripslashes( $_POST[ 'parent' ] ),  'annuaire_cat_ordre' => stripslashes( $_POST[ 'ordre' ] ), 'annuaire_cat_valid' => $valid ) );
            $message = "d'ajout d'une catégorie";
          }
          break;
        case "ModifieCAT":
          if ( !empty( $_POST[ 'id' ] ) ) {
        if (empty( $_POST[ 'valid' ] ) ) {$valid=0;} else {$valid=1;}
            $resultat = $wpdb->update( "{$wpdb->prefix}annuaire_categorie", array( 'annuaire_cat_nom' => stripslashes( $_POST[ 'nom' ] ),  'annuaire_parent' => stripslashes( $_POST[ 'parent' ] ),  'annuaire_cat_ordre' => stripslashes( $_POST[ 'ordre' ] ), 'annuaire_cat_valid' => $valid ), array( 'annuaire_cat_id' => $_POST[ 'id' ] ) );
            $message = "de modification du lieu";
          }
          break;
        case "SupprCAT":
          //echo "*************************************************** function actionNumUtile ligne 512 $ _POST[ 'id' ] = ".$_POST[ 'id' ]." ***************************************************<br>";
          if ( !empty( $_POST[ 'id' ] ) ) {
            $resultat = $wpdb->delete( "{$wpdb->prefix}annuaire_categorie", array( 'annuaire_cat_id' => $_POST[ 'id' ] ) );
            $message = "de suppression du lieu";
          }
          break;
      }
  }
    if ( $message != '' ) {
      echo '<div id="setting-error-settings_updated" class="updated settings-error">';
      if ( !( $resultat === FALSE ) ) {
        echo( "<p><strong>La requète " . $message . " a fonctionné.</strong></p>" );
      } else {
        echo __( "<p><strong>Oups ! Un problème " . $message . " a été rencontré.</strong></p>" );
      }
      echo '</div>';
    }
  }
 function actionLieu() { // name="actionLI" value="AjoutLI"
    global $wpdb;
    $resultat = FALSE;
    $message = '';
    if (isset($_POST['actionLI'])) {
      $actionLI = $_POST['actionLI'];
    //echo '******************************************************** function actionNumUtile ligne 495 $actionNU = '.$actionNU.' ********************************************************<br>';
    switch ( $actionLI ) {
      case "AjoutLI":
        if ( !empty( $_POST[ 'nom' ] ) ) {
		  if (empty( $_POST[ 'valid' ] ) ) {$valid=0;} else {$valid=1;}
          $resultat = $wpdb->insert( "{$wpdb->prefix}annuaire_lieu", array( 'annuaire_lieu_nom' => stripslashes( $_POST[ 'nom' ] ), 'annuaire_cat_id' => $_POST[ 'cat_id' ], 'annuaire_ordre' => $_POST[ 'ordre' ], 'annuaire_lat' => $_POST[ 'lat' ], 'annuaire_long' => $_POST[ 'long' ], 'annuaire_adresse' => stripslashes( $_POST[ 'adresse' ]), 'annuaire_codepostal' => $_POST[ 'codepostal' ], 'annuaire_ville' => stripslashes( $_POST[ 'ville' ]), 'annuaire_telephone' => $_POST[ 'telephone' ], 'annuaire_mail' => $_POST[ 'mail' ], 'annuaire_site_adresse' => $_POST[ 'site_adresse' ], 'annuaire_site_intitule' =>  stripslashes( $_POST[ 'site_intitule' ]), 'annuaire_horaire' => stripslashes( $_POST[ 'horaire' ]), 'annuaire_infos' => stripslashes( $_POST[ 'infos' ]), 'annuaire_responsable' => stripslashes( $_POST[ 'responsable' ]), 'annuaire_photo' => $_POST[ 'photo' ], 'annuaire_icone' => $_POST[ 'icone' ], 'annuaire_date_debut' => $_POST[ 'date_debut' ], 'annuaire_date_fin' => $_POST[ 'date_fin' ], 'annuaire_lieu_valid' => $valid ) );
          $message = "d'ajout du lieu";
        }
        break;
      case "ModifieLI":
        if ( !empty( $_POST[ 'id' ] ) ) {
		  if (empty( $_POST[ 'valid' ] ) ) {$valid=0;} else {$valid=1;}
          $resultat = $wpdb->update( "{$wpdb->prefix}annuaire_lieu", array( 'annuaire_lieu_nom' => stripslashes( $_POST[ 'nom' ] ), 'annuaire_cat_id' => $_POST[ 'cat_id' ], 'annuaire_ordre' => $_POST[ 'ordre' ], 'annuaire_lat' => $_POST[ 'lat' ], 'annuaire_long' => $_POST[ 'long' ], 'annuaire_adresse' => stripslashes( $_POST[ 'adresse' ]), 'annuaire_codepostal' => $_POST[ 'codepostal' ], 'annuaire_ville' => stripslashes( $_POST[ 'ville' ]), 'annuaire_telephone' => $_POST[ 'telephone' ], 'annuaire_mail' => $_POST[ 'mail' ], 'annuaire_site_adresse' => $_POST[ 'site_adresse' ], 'annuaire_site_intitule' =>  stripslashes( $_POST[ 'site_intitule' ]), 'annuaire_horaire' => stripslashes( $_POST[ 'horaire' ]), 'annuaire_infos' => stripslashes( $_POST[ 'infos' ]), 'annuaire_responsable' => stripslashes( $_POST[ 'responsable' ]), 'annuaire_photo' => $_POST[ 'photo' ], 'annuaire_icone' => $_POST[ 'icone' ], 'annuaire_date_debut' => $_POST[ 'date_debut' ], 'annuaire_date_fin' => $_POST[ 'date_fin' ], 'annuaire_lieu_valid' => $valid ), array( 'annuaire_lieu_id' => $_POST[ 'id' ] ) );
          $message = "de modification du lieu";
        }
        break;
      case "SupprLI":
        //echo "*************************************************** function actionNumUtile ligne 512 $ _POST[ 'id' ] = ".$_POST[ 'id' ]." ***************************************************<br>";
        if ( !empty( $_POST[ 'id' ] ) ) {
          $resultat = $wpdb->delete( "{$wpdb->prefix}annuaire_lieu", array( 'annuaire_lieu_id' => $_POST[ 'id' ] ) );
          $message = "de suppression du lieu";
        }
        break;
    }
  }
    if ( $message != '' ) {
      echo '<div id="setting-error-settings_updated" class="updated settings-error">';
      if ( !( $resultat === FALSE ) ) {
        echo( "<p><strong>La requète " . $message . " a fonctionné.</strong></p>" );
      } else {
        echo __( "<p><strong>Oups ! Un problème " . $message . " a été rencontré.</strong></p>" );
      }
      echo '</div>';
    }
  }