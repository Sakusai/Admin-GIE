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
    `alert_link_type` varchar(50) NULL,
    `alert_link` varchar(200) NULL,
    `alert_link_blank` boolean NULL,
    `alert_display` boolean NOT NULL DEFAULT FALSE
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
    null,
    "Menu des alertes",
    'Ajoute alerte',
    'manage_options',
    'ajouterAlerte',
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
    $alert_text = stripslashes($_POST['alert_text']);
    $alert_text_size = $_POST['alert_text_size'];
    $alert_background_color = $_POST['alert_background_color'];
    $alert_text_color = $_POST['alert_text_color'];
    //$alert_icon = $_POST['alert_icon'];
    $alert_date_start = $_POST['alert_date_start'];
    $alert_date_end = $_POST['alert_date_end'];
    $alert_link_type = $_POST['alert_link_type'];
    $alert_link = isset($_POST['alert_link']) ? $_POST['alert_link'] : null;
    $alert_link_blank = isset($_POST['alert_link_blank']);
    $alert_display = isset($_POST['alert_display']);

    // Préparer la requête SQL d'insertion
    $insert_data = array(
      'alert_text' => $alert_text,
      'alert_text_size' => $alert_text_size,
      'alert_background_color' => $alert_background_color,
      'alert_text_color' => $alert_text_color,
      //'alert_icon' => $alert_icon,
      'alert_date_start' => $alert_date_start,
      'alert_date_end' => $alert_date_end,
      'alert_link_type' => $alert_link_type,
      'alert_link' => $alert_link,
      'alert_link_blank' => $alert_link_blank,
      'alert_display' => $alert_display,
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
      '%d',
    );
    $wpdb->insert("{$wpdb->prefix}gie_alertes", $insert_data, $insert_format);
    if ($wpdb->last_error) {
      echo "Erreur lors de l'insertion de l'alerte : " . $wpdb->last_error;
      ?>
      <script>
        setTimeout(function () {
          window.location.href = '<?php echo admin_url('admin.php?page=gie_alertes_list'); ?>';
        }, 2000); // Rediriger après 2 secondes (vous pouvez ajuster le délai selon vos besoins)
      </script>
      <?php
    } else {
      ?>
      <script>
        setTimeout(function () {
          window.location.href = '<?php echo admin_url('admin.php?page=gie_alertes_list'); ?>';
        }, 2000); // Rediriger après 2 secondes (vous pouvez ajuster le délai selon vos besoins)
      </script>
      <?php
      echo $alert_link;
      echo "Alerte ajoutée avec succès !";
    }
  }
  ?>
  <div class="wrap">
    <h1> Menu des alertes </h1>
    <h2> Personnaliser l'alerte </h2>
    <form method="post">
      <label for="alert_text">Texte de l'alerte :</label>
      <input type="text" name="alert_text" id="alert_text" required style="width: 700px; height: 10px;">
      <br>

      <label for="alert_text_size">Taille du texte (en pt) :</label>
      <input type="number" name="alert_text_size" id="alert_text_size" required>
      <br>

      <label for="alert_background_color">Couleur de fond :</label>
      <input type="color" name="alert_background_color" id="alert_background_color" required>
      <br>

      <label for="alert_text_color">Couleur du texte :</label>
      <input type="color" name="alert_text_color" id="alert_text_color" value="#FFFFFF" required>
      <br>
      <?php /*
<label for="alert_icon">Icône :</label>
<input type="text" name="alert_icon" id="alert_icon" required>
<br>
*/?>

      <label for="alert_date_start">Date de début :</label>
      <input type="date" name="alert_date_start" id="alert_date_start" required>
      <br>

      <label for="alert_date_end">Date de fin :</label>
      <input type="date" name="alert_date_end" id="alert_date_end" required>
      <br>
      <label for="alert_link_type">Type de lien :</label><br>
      <input type="radio" name="alert_link_type" value="null" checked> Aucun<br>
      <input type="radio" name="alert_link_type" value="custom"> Custom<br>
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

        // Ajouter les options des pages
        foreach ($pages as $page) {
          echo '<option value="' . get_permalink($page) . '">' . $page->post_title . '</option>';
        }

        // Ajouter les options des événements
        foreach ($evenements as $evenement) {
          echo '<option value="' . get_permalink($evenement) . '">' . $evenement->post_title . '</option>';
        }
        ?>
      </select>

      <script>
        document.addEventListener("DOMContentLoaded", function () {
          var linkTypeRadios = document.querySelectorAll('input[type="radio"][name="alert_link_type"]');
          var linkSelect = document.querySelector('#alert_link');
          var customLinkContainer = document.querySelector('#custom_link_container');

          linkTypeRadios.forEach(function (radio) {
            radio.addEventListener('change', function () {
              if (radio.value === "null") {
                linkSelect.value = ""; // La valeur de alert_link est nulle
                customLinkContainer.style.display = "none";
              } else if (radio.value === "custom") {
                linkSelect.style.display = "none";
                customLinkContainer.style.display = "block";
              } else {
                linkSelect.style.display = "block";
                customLinkContainer.style.display = "none";
                linkSelect.innerHTML = ""; // Vider la liste déroulante

                <?php
                // Ajouter les options en fonction de la valeur sélectionnée
                foreach ($pages as $page) {
                  echo 'if (radio.value === "pages") {';
                  echo '  linkSelect.innerHTML += \'<option value="' . get_permalink($page) . '">' . $page->post_title . '</option>\';';
                  echo '}';
                }

                foreach ($evenements as $evenement) {
                  echo 'if (radio.value === "evenements") {';
                  echo '  linkSelect.innerHTML += \'<option value="' . get_permalink($evenement) . '">' . $evenement->post_title . '</option>\';';
                  echo '}';
                }

                foreach ($articles as $article) {
                  echo 'if (radio.value === "articles" || radio.value === "") {';
                  echo '  linkSelect.innerHTML += \'<option value="' . get_permalink($article) . '">' . $article->post_title . '</option>\';';
                  echo '}';
                }
                ?>
              }
            });
          });
        });
      </script>

      </select>
      <br>

      <label for="alert_link_blank">Ouvrir le lien dans une nouvelle fenêtre </label>
      <input type="checkbox" name="alert_link_blank" id="alert_link_blank">
      <br>
      <label for="alert_display">Activer l'alerte</label>
      <input type="checkbox" name="alert_display" id="alert_display">
      <br>
      <input type="submit" name="submit" value="Ajouter">
    </form>
  </div>
  <?php
}


// Inclure la bibliothèque Font Awesome
add_action('wp_enqueue_scripts', 'load_font_awesome');
function load_font_awesome()
{
  wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css');
}