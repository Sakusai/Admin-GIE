<?php

require_once plugin_dir_path(__FILE__) . '../includes/event-type-category.php';
require_once plugin_dir_path(__FILE__) . '../includes/event-sort-by-dates.php';
require_once plugin_dir_path(__FILE__) . '../includes/event-upcoming.php';
require_once plugin_dir_path(__FILE__) . '../includes/event-upcoming-page.php';
require_once plugin_dir_path(__FILE__) . '../includes/event-slider.php';
require_once plugin_dir_path(__FILE__) . '../includes/event-settings.php';
/**
 *  Effectue la fonction register_event_post_type à l'initialisation de WordPress
 */
add_action('init', 'register_event_post_type');

/**
 * Enregistrement du type de publication 'event'
 */
function register_event_post_type()
{
    register_post_type(
        'event',
        array(
            'labels' => array(
                'name' => __('Événements', 'textdomain'),
                'singular_name' => __('Événement', 'textdomain'),
                'name_admin_bar' => __('Événements', 'textdomain'),
		        'parent_item_colon' => __('Parent Événements:', 'textdomain'),
                'all_items' => __('Tous les événements', 'textdomain'),
                'add_new_item' => __('Ajouter un événement', 'textdomain'), // Modifie le texte d'ajout d'un événement
                'new_item' => __('Nouvel événement', 'textdomain' ), // Modifie le texte pour un nouvel événement
                'edit_item' => __("Modifier l'événement", 'textdomain'), // Modifie le texte de modification d'un événement
                'update_item' => __("Mettre à jour l'événement", 'textdomain'), // Modifie le texte de mise à jour d'un événement
                'view_item' => __("Voir l'événement", 'textdomain'),
                'view_items' => __('Voir les événements', 'textdomain'),
		        'search_items' => __('Rechercher des événements', 'textdomain'),
            ),
            'public' => true,
            'has_archive' => true,
            'supports' => array(
                'title',
                'editor',
                'thumbnail',
                'excerpt',
            ),
            'taxonomies' => array(
                'post_tag',
            ),
            'show_in_nav_menus' => true, // Affiche les événements dans la création de menu
            'menu_icon' => 'dashicons-calendar-alt',  // URL de l'image
        )
    );
}


/**
 * Effectue la fonction migrate_posts_to_event_type à l'initialisation de WordPress
 */
add_action('init', 'migrate_posts_to_event_type');

/**
 * Migration des publications existantes de 'post' à 'event'
 */
function migrate_posts_to_event_type()
{
    $args = array(
        'post_type' => 'post',
        'meta_key' => 'is_event',
        'meta_value' => 'yes',
        'posts_per_page' => -1,
    );
    $posts = new WP_Query($args);

    if ($posts->have_posts()) {
        while ($posts->have_posts()) {
            $posts->the_post();

            $post_data = array(
                'ID' => get_the_ID(),
                'post_type' => 'event',
            );

            wp_update_post($post_data);
        }
    }

    wp_reset_postdata();
}

add_action('the_content', 'add_back_button');

function add_back_button($content) {
    if (is_singular('event')) { // Vérifier que c'est une page d'événement
        $back_button = "<button onclick='javascript:history.back()'>Retour</button>"; // Créer le bouton retour
        $content .= $back_button; // Ajouter le bouton à la fin du contenu
    }
    return $content;
}
