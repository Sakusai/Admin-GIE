<?php

/**
 * Effectue la fonction afficher_sous_menu_alertes quand le menu admin est utilisé
 */
add_action('admin_menu', 'afficher_sous_menu_alertes');
// Fonction pour afficher le sous-menu des alertes dans votre plugin
function afficher_sous_menu_alertes()
{
    add_submenu_page(
        'administration-gie',
        'Toutes les alertes',
        'Toutes les alertes',
        'edit_posts',
        'gie_alertes_list',
        'gie_alertes_list_callback'
    );

    add_submenu_page(
        null,
        'Supprimer une alerte',
        'Supprimer une alerte',
        'edit_posts',
        'gie_alertes_delete_callback',
        'gie_alertes_delete_callback'
    );

    add_submenu_page(
        null,
        'Modifier une alerte',
        'Modifier une alerte',
        'edit_posts',
        'gie_alertes_edit_callback',
        'gie_alertes_edit_callback'
    );

    add_submenu_page(
        null,
        'Activer une alerte',
        'Activer une alerte',
        'edit_posts',
        'activate_alert',
        'activate_alert'
    );

    add_submenu_page(
        null,
        'Désactiver une alerte',
        'Désactiver une alerte',
        'edit_posts',
        'deactivate_alert',
        'deactivate_alert'
    );
}


function gie_alertes_list_callback()
{
    global $wpdb;

    // Récupération des paramètres de recherche et de pagination
    $search_query = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
    $per_page = 20;
    $current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
    $offset = ($current_page - 1) * $per_page;

    // Construction de la requête de recherche avec pagination
    $query = "SELECT * FROM {$wpdb->prefix}gie_alertes";

    if (!empty($search_query)) {
        $query .= $wpdb->prepare(
            " WHERE alert_text LIKE %s",
            '%' . $wpdb->esc_like($search_query) . '%'
        );
    }

    $query .= " LIMIT $per_page OFFSET $offset";

    $alerts = $wpdb->get_results($query);

    // Construction de la requête de comptage pour la pagination
    $row_count_query = "SELECT COUNT(*) FROM {$wpdb->prefix}gie_alertes";

    if (!empty($search_query)) {
        $row_count_query .= $wpdb->prepare(
            " WHERE alert_text LIKE %s",
            '%' . $wpdb->esc_like($search_query) . '%'
        );
    }

    $row_count = $wpdb->get_var($row_count_query);
    $total_pages = ceil($row_count / $per_page);

    ?>
    <div class="wrap">
        <h1 class="wp-heading-inline">Toutes les alertes</h1>
        <a href="admin.php?page=ajouterAlerte" class="page-title-action">Ajouter</a>
        <hr class="wp-header-end">

        <!-- Champ de recherche -->
        <form method="get" action="<?php echo admin_url('admin.php'); ?>">
            <input type="hidden" name="page" value="gie_alertes_list">
            <p class="search-box">
                <label class="screen-reader-text" for="alert-search-input">Rechercher :</label>
                <input type="search" id="alert-search-input" name="search" value="<?php echo esc_attr($search_query); ?>">
                <input type="submit" id="alert-search-submit" class="button" value="Rechercher">
            </p>
        </form>

        <table class="wp-list-table widefat fixed striped table-view-excerpt posts">
            <thead>
                <tr>
                    <th>Texte</th>
                    <th>Taille du texte</th>
                    <th>Couleur de fond</th>
                    <th>Couleur du texte</th>
                    <?php //<th>Icône</th> ?>
                    <th>Date de début</th>
                    <th>Date de fin</th>
                    <th>Type de lien</th>
                    <th>Lien</th>
                    <th>Ouvrir dans une nouvelle fenêtre</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($alerts as $alert): ?>
                    <tr>
                        <td>
                            <?php echo $alert->alert_text; ?>
                        </td>
                        <td>
                            <?php echo $alert->alert_text_size; ?>
                        </td>
                        <td>
                            <?php echo $alert->alert_background_color; ?>
                        </td>
                        <td>
                            <?php echo $alert->alert_text_color; ?>
                        </td>
                        <?php
                        /*
                        <td>
                            <?php echo $alert->alert_icon; ?>
                        </td>
                        */
                        ?>
                        <td>
                            <?php echo $alert->alert_date_start; ?>
                        </td>
                        <td>
                            <?php echo $alert->alert_date_end; ?>
                        </td>
                        <td>
                            <?php echo $alert->alert_link_type; ?>
                        </td>
                        <td>
                            <?php echo $alert->alert_link; ?>
                        </td>
                        <td>
                            <?php echo $alert->alert_link_blank ? 'Oui' : 'Non'; ?>
                        </td>
                        <td>
                            <a href="<?php echo admin_url('admin.php?page=gie_alertes_edit_callback&id=' . $alert->alert_id); ?>">Modifier</a>
                            <br>
                            <a href="<?php echo admin_url('admin.php?page=gie_alertes_delete_callback&id=' . $alert->alert_id); ?>">Supprimer</a>
                            <br>
                            <?php if($alert->alert_display){
                                ?>
                                <a href="<?php echo admin_url('admin.php?page=deactivate_alert&id='. $alert->alert_id ); ?>">Désactiver </a>
                                <?php
                            } else {
                                ?>
                                <a href="<?php echo admin_url('admin.php?page=activate_alert&id='. $alert->alert_id ); ?>">Activer </a>
                                <?php
                            }
                            ?>
                            
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <?php if ($total_pages > 1) : ?>
            <div class="tablenav">
                <div class="tablenav-pages">
                    <span class="displaying-num"><?php echo sprintf(
                        _n(
                            '1 élément',
                            '%s éléments',
                            $row_count,
                            'text-domain'
                        ),
                        number_format_i18n($row_count)
                    ); ?></span>

                    <?php echo paginate_links(array(
                        'base' => add_query_arg('paged', '%#%'),
                        'format' => '',
                        'prev_text' => __('&laquo; Précédent', 'text-domain'),
                        'next_text' => __('Suivant &raquo;', 'text-domain'),
                        'total' => $total_pages,
                        'current' => $current_page,
                    )); ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <?php
}


// Page de suppression d'une alerte
function gie_alertes_delete_callback()
{
    if (isset($_GET['id'])) {
        $alert_id = $_GET['id'];

        // Supprimer l'alerte correspondante de la base de données
        global $wpdb;
        $wpdb->delete($wpdb->prefix . 'gie_alertes', array('alert_id' => $alert_id));

        // Afficher un message de succès (facultatif)
        echo "L'alerte a été supprimée avec succès.";

        // Ajouter un script JavaScript pour rediriger après un court délai
        ?>
        <script>
            setTimeout(function () {
                window.location.href = '<?php echo admin_url('admin.php?page=gie_alertes_list'); ?>';
            }, 2000); // Rediriger après 2 secondes (vous pouvez ajuster le délai selon vos besoins)
        </script>
        <?php
        exit();
    }
}




// Page de modification d'une alerte
function gie_alertes_edit_callback()
{
    global $wpdb;

    if (isset($_GET['id'])) {
        $alert_id = $_GET['id'];
        $alert = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}gie_alertes WHERE alert_id = %d", $alert_id));

        // Vérifier si le formulaire de modification a été soumis
        if (isset($_POST['update_alert'])) {
            $alert_text = stripslashes($_POST['alert_text']);
            $alert_text_size = $_POST['alert_text_size'];
            $alert_background_color = $_POST['alert_background_color'];
            $alert_text_color = $_POST['alert_text_color'];
            //$alert_icon = $_POST['alert_icon'];
            $alert_date_start = $_POST['alert_date_start'];
            $alert_date_end = $_POST['alert_date_end'];
            $alert_link_type = $_POST['alert_link_type'];
            $alert_link = $_POST['alert_link'];
            $alert_link_blank = isset($_POST['alert_link_blank']) ? 1 : 0;
            $alert_defil = isset($_POST['alert_defil']) ? 1 : 0;

            // Mettre à jour les valeurs de l'alerte dans la base de données
            $wpdb->update(
                "{$wpdb->prefix}gie_alertes",
                array(
                    'alert_text' => $alert_text,
                    'alert_text_size' => $alert_text_size,
                    'alert_background_color' => $alert_background_color,
                    'alert_text_color' => $alert_text_color,
                    // 'alert_icon' => $alert_icon,
                    'alert_date_start' => $alert_date_start,
                    'alert_date_end' => $alert_date_end,
                    'alert_link_type' => $alert_link_type,
                    'alert_link' => $alert_link,
                    'alert_link_blank' => $alert_link_blank,
                    'alert_defil' => $alert_defil
                ),
                array('alert_id' => $alert_id)
            );
            echo "Les modifications ont été enregistrées avec succès.";

            // Ajouter un script JavaScript pour rediriger après un court délai
            ?>
            <script>
                setTimeout(function () {
                    window.location.href = '<?php echo admin_url('admin.php?page=gie_alertes_list'); ?>';
                }, 2000); // Rediriger après 2 secondes (vous pouvez ajuster le délai selon vos besoins)
            </script>
            <?php

            exit();
        }
        // Afficher le formulaire de modification de l'alerte
        ?>
        <style>
            .hidden-submenu {
                display: none !important;
            }
        </style>
        <div class="wrap">
            <h1>Modifier l'alerte</h1>
            <form method="post" action="">
                <label for="alert_text">Texte de l'alerte :</label>
                <input type="text" name="alert_text" id="alert_text" value="<?php echo $alert->alert_text; ?>" required style="width: 700px; height: 10px;"><br>

                <label for="alert_text_size">Taille du texte (en pt) :</label>
                <input type="number" name="alert_text_size" id="alert_text_size" value="<?php echo $alert->alert_text_size; ?>" required><br>

                <label for="alert_background_color">Couleur de fond :</label>
                <input type="color" name="alert_background_color" id="alert_background_color" value="<?php echo $alert->alert_background_color; ?>" required><br>

                <label for="alert_text_color">Couleur du texte :</label>
                <input type="color" name="alert_text_color" id="alert_text_color" value="<?php echo $alert->alert_text_color; ?>" required><br>
                <?php
                /*
                <label for="alert_icon">Icône :</label>
                <input type="text" name="alert_icon" id="alert_icon" value="<?php echo $alert->alert_icon; ?>" required><br>
                */
                ?>
                <label for="alert_date_start">Date de début :</label>
                <input type="date" name="alert_date_start" id="alert_date_start" value="<?php echo $alert->alert_date_start; ?>" required><br>

                <label for="alert_date_end">Date de fin :</label>
                <input type="date" name="alert_date_end" id="alert_date_end" value="<?php echo $alert->alert_date_end; ?>" required><br>

                <label for="alert_link_type">Type de lien :</label><br>
                <input type="radio" name="alert_link_type" value="null" checked> Aucun<br>
                <input type="radio" name="alert_link_type" value="custom"> Custom<br>
                <input type="radio" name="alert_link_type" value="articles"> Articles<br>
                <input type="radio" name="alert_link_type" value="pages"> Pages<br>
                <input type="radio" name="alert_link_type" value="evenements"> Événements<br>

                <div id="custom_link_container" style="display: none;">
                    <input type="text" name="custom_link" id="custom_link">
                </div>
                <select name="alert_link" id="alert_link">
                    <option value=""></option>
                </select>
                <script>
                    document.addEventListener("DOMContentLoaded", function () {
                        var linkTypeRadios = document.querySelectorAll('input[type="radio"][name="alert_link_type"]');
                        var linkSelect = document.querySelector('#alert_link');
                        var customLinkContainer = document.querySelector('#custom_link_container');
                        linkSelect.style.display = "none";
                        linkTypeRadios.forEach(function (radio) {
                            radio.addEventListener('change', function () {
                                if (radio.value === "null") {
                                    linkSelect.style.display = "none"; // La valeur de alert_link est nulle
                                    customLinkContainer.style.display = "none";
                                } else if (radio.value === "custom") {
                                    linkSelect.style.display = "none";
                                    customLinkContainer.style.display = "block";
                                } else {
                                    linkSelect.style.display = "block";
                                    customLinkContainer.style.display = "none";
                                    linkSelect.innerHTML = ""; // Vider la liste déroulante

                                    <?php
                                    // Récupérer tous les articles, pages et événements
                                    $articles = get_posts(array('post_type' => 'post'));
                                    $pages = get_pages();
                                    $evenements = get_posts(array('post_type' => 'event'));

                                    // Ajouter les options en fonction de la valeur sélectionnée
                                    foreach ($pages as $page) {
                                        echo 'if (radio.value === "pages") {';
                                        echo '  linkSelect.innerHTML += \'<option value="' . get_permalink($page) . '">' . str_replace("'", "\'", $page->post_title) . '</option>\';';
                                        echo '}';
                                    }

                                    foreach ($evenements as $evenement) {
                                        echo 'if (radio.value === "evenements") {';
                                        echo '  linkSelect.innerHTML += \'<option value="' . get_permalink($evenement) . '">' . str_replace("'", "\'", $evenement->post_title) . '</option>\';';
                                        echo '}';
                                    }

                                    foreach ($articles as $article) {
                                        echo 'if (radio.value === "articles" || radio.value === "") {';
                                        echo '  linkSelect.innerHTML += \'<option value="' . get_permalink($article) . '">' . str_replace("'", "\'", $article->post_title) . '</option>\';';
                                        echo '}';
                                    }
                                    ?>

                                }
                            });
                        });
                    });
                </script>
                <br>
                <label for="alert_link_blank">Ouvrir le lien dans une nouvelle fenêtre</label>
                <input type="checkbox" name="alert_link_blank" id="alert_link_blank" <?php echo $alert->alert_link_blank ? 'checked' : ''; ?>><br>
                <br>
                <label for="alert_scroll">Défilement :</label>
                <input type="checkbox" name="alert_defil" id="alert_defil" <?php echo $alert->alert_defil ? 'checked' : ''; ?>>
                <br>
                <input type="submit" name="update_alert" value="Enregistrer les modifications">
            </form>
        </div>
        <?php
    }
}

function activate_alert()
{
    // Récupère l'ID de l'alerte depuis l'URL
    $alert_id = isset($_GET['id']) ? intval($_GET['id']) : 0;


    global $wpdb;

    // Met à jour la valeur de alert_display en base de données
    $table_name = $wpdb->prefix . 'gie_alertes';
    $wpdb->update(
        $table_name,
        array('alert_display' => 1),
        array('alert_id' => $alert_id)
    );

    // Affiche un message de confirmation
    echo 'Alerte activée avec succès.';

    // Redirige l'utilisateur vers la page précédente après l'activation de l'alerte
    ?>
    <script>
        setTimeout(function () {
            window.location.href = '<?php echo admin_url('admin.php?page=gie_alertes_list'); ?>';
        }, 2000); // Redirige après 2 secondes (vous pouvez ajuster le délai selon vos besoins)
    </script>
    <?php
    exit;
}




function deactivate_alert()
{
        // Récupère l'ID de l'alerte depuis l'URL
        $alert_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    global $wpdb;

    // Met à jour la valeur de alert_display en base de données
    $table_name = $wpdb->prefix . 'gie_alertes';
    $wpdb->update(
        $table_name,
        array('alert_display' => 0),
        array('alert_id' => $alert_id)
    );

    // Affiche un message de confirmation
    echo 'Alerte désactivée avec succès.';

    // Redirige l'utilisateur vers la page précédente après l'activation de l'alerte
    ?>
    <script>
        setTimeout(function () {
            window.location.href = '<?php echo admin_url('admin.php?page=gie_alertes_list'); ?>';
        }, 2000); // Redirige après 2 secondes (vous pouvez ajuster le délai selon vos besoins)
    </script>
    <?php
    exit;
}
function display_alert_banner() {
    global $wpdb;

    // Récupérer la date actuelle
    $current_date = date('Y-m-d');

    // Récupérer les alertes actives dont la date de début est antérieure ou égale à la date actuelle et la date de fin est postérieure ou égale à la date actuelle
    $active_alerts = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}gie_alertes WHERE alert_display = 1 AND alert_date_start <= '$current_date' AND alert_date_end >= '$current_date' ORDER BY alert_id DESC");

    // Récupérer la valeur de $alert_defil depuis la base de données
    $alert_defil = $wpdb->get_var("SELECT alert_defil FROM {$wpdb->prefix}gie_alertes WHERE alert_display = 1 AND alert_date_start <= '$current_date' AND alert_date_end >= '$current_date' LIMIT 1");

    // Vérifier s'il y a des alertes actives
    if (count($active_alerts) > 0) {
        echo '<div class="alert-wrapper">';

        foreach ($active_alerts as $alert) {
            // Construire les styles CSS personnalisés
            $styles = 'background-color: ' . esc_attr($alert->alert_background_color) . ' ; font-size: ' . esc_attr($alert->alert_text_size) .' px;';

            // Construire le code HTML du bandeau d'alerte avec les styles personnalisés
            echo '<div class="alert-banner" style="' . $styles . '">';
            echo '<span class="alert-icon">' . esc_attr($alert->alert_icon) . '</span>';
            
            // Vérifier si le défilement doit être activé
            $alert_class = 'alert-message';
            if ($alert_defil) {
                $alert_class .= ' marquee';
            }
            
            echo '<span class="' . $alert_class . '"><a href="' . esc_attr($alert->alert_link) . '" style="color: ' . esc_attr($alert->alert_text_color) . ';">' . esc_html($alert->alert_text) . '</a></span>';
            echo '<span class="close-button" onclick="closeAlert(this)">&#10006;</span>';
            echo '</div>';
        }

        echo '</div>';
    }
}
add_action('wp_body_open', 'display_alert_banner');
add_action('admin_post_activate_alert', 'activate_alert');
add_action('admin_post_deactivate_alert', 'deactivate_alert');
add_action('admin_post_nopriv_activate_alert', 'activate_alert');
add_action('admin_post_nopriv_deactivate_alert', 'deactivate_alert');
add_action('wp_enqueue_scripts', 'enqueue_alert_scripts');

function enqueue_alert_scripts() {
    // Intégration directe du JavaScript
    echo '<script type="text/javascript">
        function closeAlert(element) {
            element.parentNode.style.display = "none";
        }
        
        document.addEventListener("DOMContentLoaded", function() {
            var alertBanners = document.querySelectorAll(".alert-banner");
            
            for (var i = 0; i < alertBanners.length; i++) {
                alertBanners[i].style.display = "block";
            }
        });
    </script>';

    // Intégration directe du CSS
    echo '<style type="text/css">
    .alert-wrapper {
        top: 0;
        left: 0;
        width: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        z-index: 99999; /* Valeur z-index plus élevée */
    }
    
    .alert-banner {
        display: none;
        width: 100%;
        height: 40px; /* Hauteur fixe pour le défilement */
        padding: 10px;
        text-align: center;
        position: relative;
    }

    .alert-icon {
        display: inline-block;
        margin-right: 5px;
    }

    .alert-message {
        display: inline-block;
        margin-right: 10px;
        overflow: hidden;
        white-space: nowrap;
        animation: defilement 20s linear infinite;
    }

    .alert-message.marquee:hover {
        animation-play-state: paused;
    }
    
    @keyframes defilement {
        0% { margin-left: 100%; }
        100% { margin-left: -100%; }
    }

    .close-button {
        position: absolute;
        top: 5px;
        right: 5px;
        cursor: pointer;
    }
    
    body {
        margin-top: 100px; /* Ajoutez une marge en haut pour laisser de la place aux alertes */
    }
</style>';

}
