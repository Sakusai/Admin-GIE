<?php

// Inclure la bibliothèque Font Awesome
wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css');

add_action('init', 'alerte_bd');

function alerte_bd()
{
  // Cette ligne me permet d'importer une variable global dans un espace local - $wpdb->prefix nous permettra de récupérer les préfixes de tables s'il y en a.
  global $wpdb;

  // Cette ligne me permet de formuler une requête SQL pour créer une table dans la BDD.
  $wpdb->query("CREATE TABLE IF NOT EXISTS {$wpdb->prefix}gie_alertes (
    `alert_id` int(10) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `alert_text` varchar(50) CHARACTER SET utf8 NOT NULL,
    `alert_text_size` int(11) NOT NULL,
    `alert_background_color` varchar(7) NOT NULL,
    `alert_text_color` varchar(7) NOT NULL,
    `alert_icon` varchar(11) NOT NULL,
    `alert_date_start` date NOT NULL,
    `alert_date_end` date NOT NULL,
    `alert_link_type` varchar(50) NOT NULL,
    `alert_link` varchar(200) NOT NULL,
    `alert_link_blank` boolean NOT NULL
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;");
}

/**
 * Effectue la fonction alert_menu_page quand le menu admin est utilisé
 */
add_action('admin_menu', 'alert_menu_page');

/**
 * Créer un sous menu Régagles pour les événements
 */
function alert_menu_page()
{
  add_submenu_page(
    'administration-gie',
    "Menu des alertes",
    'Ajoute alerte',
    'manage_options',
    'alert-menu',
    'alert_page'
  );

}
/**
 * Affiche la page d'alerte avec le bouton pour afficher le message d'alerte
 */
function alert_page()
{
  global $wpdb;

  if (isset($_POST['submit'])) {
    $alert_text = $_POST['alert_text'];
    $alert_text_size = $_POST['alert_text_size'];
    $alert_background_color = $_POST['alert_background_color'];
    $alert_text_color = $_POST['alert_text_color'];
    $alert_icon = $_POST['alert_icon'];
    $alert_date_start = $_POST['alert_date_start'];
    $alert_date_end = $_POST['alert_date_end'];
    $alert_link_type = $_POST['alert_link_type'];
    $alert_link = $_POST['alert_link'];
    $alert_link_blank = isset($_POST['alert_link_blank']);

    // Préparer la requête SQL d'insertion
    $insert_data = array(
      'alert_text' => $alert_text,
      'alert_text_size' => $alert_text_size,
      'alert_background_color' => $alert_background_color,
      'alert_text_color' => $alert_text_color,
      'alert_icon' => $alert_icon,
      'alert_date_start' => $alert_date_start,
      'alert_date_end' => $alert_date_end,
      'alert_link_type' => $alert_link_type,
      'alert_link' => $alert_link,
      'alert_link_blank' => $alert_link_blank,
    );
    $insert_format = array(
      '%s',
      '%d',
      '%s',
      '%s',
      '%s',
      '%s',
      '%s',
      '%s',
      '%s',
      '%d',
    );
    $wpdb->insert("{$wpdb->prefix}gie_alertes", $insert_data, $insert_format);
    if ($wpdb->last_error) {
      echo "Erreur lors de l'insertion de l'alerte : " . $wpdb->last_error;
    } else {
      echo  $alert_link;
      echo "Alerte ajoutée avec succès !";
    }
  }
  ?>
  <div class="wrap">
    <h1> Menu des alertes </h1>
    <h2> Personnaliser l'alerte </h2>
    <form method="post">
      <label for="alert_text">Texte de l'alerte :</label>
      <input type="text" name="alert_text" id="alert_text" required>
      <br>

      <label for="alert_text_size">Taille du texte :</label>
      <input type="number" name="alert_text_size" id="alert_text_size" required>
      <br>

      <label for="alert_background_color">Couleur de fond :</label>
      <input type="color" name="alert_background_color" id="alert_background_color" required>
      <br>

      <label for="alert_text_color">Couleur du texte :</label>
      <input type="color" name="alert_text_color" id="alert_text_color" value="#FFFFFF" required>
      <br>

      <label for="alert_icon">Icône :</label>
      <input type="text" name="alert_icon" id="alert_icon" required>
      <br>

      <label for="alert_date_start">Date de début :</label>
      <input type="date" name="alert_date_start" id="alert_date_start" required>
      <br>

      <label for="alert_date_end">Date de fin :</label>
      <input type="date" name="alert_date_end" id="alert_date_end" required>
      <br>

      <label for="alert_link_type">Type de lien :</label><br>
      <input type="radio" name="alert_link_type" value="custom" checked> Custom<br>
      <input type="radio" name="alert_link_type" value="articles"> Articles<br>
      <input type="radio" name="alert_link_type" value="pages"> Pages<br>
      <input type="radio" name="alert_link_type" value="evenements"> Événements<br>

      <label for="alert_link">Lien :</label><br>
      <div id="custom_link_container" style="display: none;">
        <input type="text" name="custom_link" id="custom_link">
      </div>

      <select name="alert_link" id="alert_link">
        <?php
        // Récupérer tous les articles, pages et événements
        $articles = get_posts(array('post_type' => 'post'));
        $pages = get_pages();
        $evenements = get_posts(array('post_type' => 'event'));

        // Par défaut, afficher la liste des articles
        foreach ($articles as $article) {
          echo '<option value="' . get_permalink($article) . '">' . $article->post_title . '</option>';
        }

        // Ajouter un écouteur d'événements sur les boutons radio
        echo '<script>';
        echo 'document.querySelectorAll(\'input[type="radio"][name="alert_link_type"]\').forEach(function(element) {';
        echo '  element.addEventListener(\'change\', function() {';
        echo '    var linkSelect = document.querySelector(\'#alert_link\');';
        echo '    var customLinkContainer = document.querySelector(\'#custom_link_container\');';
        echo '    linkSelect.innerHTML = \'\';'; // Vider la liste déroulante
        echo '    if (element.value === "custom") {';
        echo '      linkSelect.style.display = "none";'; // Masquer la liste déroulante
        echo '      customLinkContainer.style.display = "block";'; // Afficher le champ de texte
        echo '    } else {';
        echo '      linkSelect.style.display = "block";'; // Afficher la liste déroulante
        echo '      customLinkContainer.style.display = "none";'; // Masquer le champ de texte
        echo '      if (element.value === "pages") {';
        foreach ($pages as $page) {
          echo '        linkSelect.innerHTML += \'<option value="' . get_permalink($page) . '">' . $page->post_title . '</option>\';';
        }
        echo '      } else if (element.value === "evenements") {';
        foreach ($evenements as $evenement) {
          echo '        linkSelect.innerHTML += \'<option value="' . get_permalink($evenement) . '">' . $evenement->post_title . '</option>\';';
        }
        echo '      } else {'; // Par défaut, afficher la liste des articles
        foreach ($articles as $article) {
          echo '        linkSelect.innerHTML += \'<option value="' . get_permalink($article) . '">' . $article->post_title . '</option>\';';
        }
        echo '      }';
        echo '    }';
        echo '  });';
        echo '});';
        echo '</script>';
        ?>
      </select>
      <br>

      <label for="alert_link_blank">Ouvrir le lien dans un nouvel onglet :</label>
      <input type="checkbox" name="alert_link_blank" id="alert_link_blank">
      <br>

      <input type="submit" name="submit" value="Ajouter">
    </form>
  </div>
  <?php
}

add_action('init', 'display_alert_banner');
function display_alert_banner()
{
  // Vérifie si le cookie n'a pas été défini
  if (!isset($_COOKIE['alert_displayed'])) {
    // Définit un cookie pour indiquer que l'alerte a été affichée
    setcookie('alert_displayed', 'true', time() + 3600, COOKIEPATH, COOKIE_DOMAIN);
  } else if (!is_admin()) {
    // Affiche l'alerte sur la page
    echo '<div class="alert-banner" style="background-color:' . esc_attr(get_option('alert_background_color', '#ff0000')) . ';font-size:' . esc_attr(get_option('alert_text_size', '14')) . "px" . '; color:' . esc_attr(get_option('alert_text_color', '#ffffff')) . '"><span class="alert-icon">' . esc_attr(get_option('alert_icon', '🚨')) . '</span><span class="alert-message"><a href="' . esc_attr(get_option('alert_link')) . '" style="color:' . esc_attr(get_option('alert_text_color', '#ffffff')) . '">' . esc_html(get_option('alert_text', 'Alerte!')) . '</a></span><span class="close-button">×</span></div>';
    echo '<style>.alert-banner .close-button {float: right;font-size: 1.5em;}</style>';
    // Ajoute le script pour fermer le bandeau d'alerte
    echo '<script>
          // Sélectionne la croix de fermeture de l\'alerte
          var closeButton = document.querySelector(\'.close-button\');
    
          // Ajoute un gestionnaire d\'événements pour le clic sur la croix
          closeButton.addEventListener(\'click\', function() {
            // Sélectionne le bandeau d\'alerte
            var alertBanner = document.querySelector(\'.alert-banner\');
            // Masque le bandeau d\'alerte
            alertBanner.style.display = \'none\';
          });
        </script>';
  }
}

// Inclure la bibliothèque Font Awesome
add_action('wp_enqueue_scripts', 'load_font_awesome');
function load_font_awesome()
{
  wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css');
}