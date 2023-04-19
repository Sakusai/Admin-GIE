<?php

require_once plugin_dir_path(__FILE__) . '../includes/event-type.php';

/**
 * Appel la fonction event_custom_posttype_glance_items dans la case "D'un coup d'oeil" du tableau de bord
 */
add_action('dashboard_glance_items', 'event_custom_posttype_glance_items');

/**
 * On va récupérer le nombre de chaque Custom Post Type
 */
function event_custom_posttype_glance_items()
{
    $glances = array();

    $args = array(
        'public' => true,
        // On ne montre que les CPT publics
        '_builtin' => false // On n'affiche pas les posts types de base de WordPress (page, post, ...)
    );
    // On récupère chaque CPT
    $post_types = get_post_types($args, 'object', 'and');
    foreach ($post_types as $post_type) {
        // On compte le nombre de posts par CPT
        $num_posts = wp_count_posts($post_type->name);
        // On formatte le nombre suivant la locale de WordPress (pour afficher une virgule pour les milliers par exemple)
        $num = number_format_i18n($num_posts->publish);
        // On formatte le texte pour utiliser soit le singulier soit le pluriel suivant le nombre de posts
        $text = _n($post_type->labels->singular_name, $post_type->labels->name, intval($num_posts->publish));
        // Si l'utilisateur actuel a le droit d'éditer les types de contenus, on créé des liens
        if (current_user_can('edit_posts')) {
            // On affiche un lien pour éditer si l'utilisateur a les droits
            $glance = '<a class="' . $post_type->name . '-count" href="' . admin_url('edit.php?post_type=' . $post_type->name) . '">' . $num . ' ' . $text . '</a>';
        } else {
            // Sinon on affiche simplement le nombre sans liens.
            $glance = '<span class="' . $post_type->name . '-count">' . $num . ' ' . $text . '</span>';
        }
        // On sauvegarde tout ça dans un tableau
        $glances[] = $glance;
    }
    // On récupère le tableau
    return $glances;
}