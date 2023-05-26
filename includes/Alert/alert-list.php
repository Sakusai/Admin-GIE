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

    $alerts = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}gie_alertes");

    ?>
    <div class="wrap">
        <h1 class="wp-heading-inline">Toutes les alertes</h1>
        <a href="admin.php?page=ajouterAlerte" class="page-title-action">Ajouter</a>
        <hr class="wp-header-end">
        <table class="widefat fixed">
            <thead>
                <tr>
                    <th>Texte</th>
                    <th>Taille du texte</th>
                    <th>Couleur de fond</th>
                    <th>Couleur du texte</th>
                    <th>Icône</th>
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
                        <td>
                            <?php echo $alert->alert_icon; ?>
                        </td>
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
            $alert_text = $_POST['alert_text'];
            $alert_text_size = $_POST['alert_text_size'];
            $alert_background_color = $_POST['alert_background_color'];
            $alert_text_color = $_POST['alert_text_color'];
            $alert_icon = $_POST['alert_icon'];
            $alert_date_start = $_POST['alert_date_start'];
            $alert_date_end = $_POST['alert_date_end'];
            $alert_link_type = $_POST['alert_link_type'];
            $alert_link = $_POST['alert_link'];
            $alert_link_blank = isset($_POST['alert_link_blank']) ? 1 : 0;

            // Mettre à jour les valeurs de l'alerte dans la base de données
            $wpdb->update(
                "{$wpdb->prefix}gie_alertes",
                array(
                    'alert_text' => $alert_text,
                    'alert_text_size' => $alert_text_size,
                    'alert_background_color' => $alert_background_color,
                    'alert_text_color' => $alert_text_color,
                    'alert_icon' => $alert_icon,
                    'alert_date_start' => $alert_date_start,
                    'alert_date_end' => $alert_date_end,
                    'alert_link_type' => $alert_link_type,
                    'alert_link' => $alert_link,
                    'alert_link_blank' => $alert_link_blank
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
                <input type="text" name="alert_text" id="alert_text" value="<?php echo $alert->alert_text; ?>" required><br>

                <label for="alert_text_size">Taille du texte :</label>
                <input type="number" name="alert_text_size" id="alert_text_size" value="<?php echo $alert->alert_text_size; ?>"
                    required><br>

                <label for="alert_background_color">Couleur de fond :</label>
                <input type="text" name="alert_background_color" id="alert_background_color"
                    value="<?php echo $alert->alert_background_color; ?>" required><br>

                <label for="alert_text_color">Couleur du texte :</label>
                <input type="text" name="alert_text_color" id="alert_text_color" value="<?php echo $alert->alert_text_color; ?>"
                    required><br>

                <label for="alert_icon">Icône :</label>
                <input type="text" name="alert_icon" id="alert_icon" value="<?php echo $alert->alert_icon; ?>" required><br>

                <label for="alert_date_start">Date de début :</label>
                <input type="date" name="alert_date_start" id="alert_date_start" value="<?php echo $alert->alert_date_start; ?>"
                    required><br>

                <label for="alert_date_end">Date de fin :</label>
                <input type="date" name="alert_date_end" id="alert_date_end" value="<?php echo $alert->alert_date_end; ?>"
                    required><br>

                <label for="alert_link_type">Type de lien :</label>
                <input type="text" name="alert_link_type" id="alert_link_type" value="<?php echo $alert->alert_link_type; ?>"
                    required><br>

                <label for="alert_link">Lien :</label>
                <input type="text" name="alert_link" id="alert_link" value="<?php echo $alert->alert_link; ?>" required><br>

                <label for="alert_link_blank">Ouvrir dans une nouvelle fenêtre :</label>
                <input type="checkbox" name="alert_link_blank" id="alert_link_blank" <?php echo $alert->alert_link_blank ? 'checked' : ''; ?>><br>

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


function display_alert_banner()
{
    $alert_text = get_option('alert_text');

    // Vérifiez si un texte d'alerte est disponible
    if ($alert_text) {
        // Affichez le bandeau d'alerte
        echo '<div id="alert-banner" class="alert-banner">';
        echo '<span class="alert-text">' . esc_html($alert_text) . '</span>';
        echo '<span class="alert-close" onclick="closeAlert()">&#10006;</span>';
        echo '</div>';
    }
}



add_action('admin_post_activate_alert', 'activate_alert');
add_action('admin_post_deactivate_alert', 'deactivate_alert');
add_action('admin_post_nopriv_activate_alert', 'activate_alert');
add_action('admin_post_nopriv_deactivate_alert', 'deactivate_alert');

add_action('wp_enqueue_scripts', 'enqueue_alert_scripts');

function enqueue_alert_scripts()
{
    // Intégration directe du JavaScript
    echo '<script type="text/javascript">
        function closeAlert() {
            document.getElementById("alert-banner").style.display = "none";
        }
    </script>';

    // Intégration directe du CSS
    echo '<style type="text/css">
        .alert-banner {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            background-color: #ff0000;
            color: #ffffff;
            padding: 10px;
            text-align: center;
        }

        .alert-close {
            position: absolute;
            top: 5px;
            right: 5px;
            cursor: pointer;
        }
    </style>';
}