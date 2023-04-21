<?php

/**
 * Appel la fonction create_new_page quand wordpress est initialisé
 */
add_action('init', 'create_event_page');


/**
 * Crée une page Evénement qui fait appel au shortcode upcoming_events
 * Ajoute cette page dans le menu de base
 */
function create_event_page() {
    // Récupère l'état de la case à cocher dans les réglages
    $events_page_auto = get_option( 'events_page_auto', false );
    
    // Vérifie si la page existe déjà
    $page_query = new WP_Query( array(
        'post_type'      => 'page',
        'post_status'    => 'publish',
        'pagename'       => 'event_upcoming'
    ));
  
    // Si la case à cocher est cochée et que la page n'existe pas, on la crée et on l'ajoute au menu
    if ( $events_page_auto && ! $page_query->have_posts() ) {
        // Crée la page
        $page_id = wp_insert_post(
            array(
                'post_title'    => 'Événement',
                'post_content'  => '[upcoming_events]', // Appel du shortcode permettant d'afficher les événements par mois
                'post_status'   => 'publish',
                'post_type'     => 'page',
                'post_name'     => 'event_upcoming'
            )
        );
  
        // Vérifie si la page a été créée avec succès
        if ( $page_id ) {
            echo 'Page created successfully!';

            // Ajoute la page au menu
            $menu_item_data = array(
                'menu-item-object-id' => $page_id,
                'menu-item-object' => 'page',
                'menu-item-type' => 'post_type',
                'menu-item-title' => get_the_title( $page_id ),
                'menu-item-status' => 'publish'
            );
            wp_update_nav_menu_item( 2, 0, $menu_item_data );
        }
    }
    // Si la case à cocher est décochée et que la page existe, on la supprime du menu et on la supprime
    elseif ( ! $events_page_auto && $page_query->have_posts() ) {
        $menu_items = wp_get_nav_menu_items( 'Menu principal', array( 'object_id' => $page_query->post->ID ) );
            if ( $menu_items ) {
                // Supprime la page du menu
                $menu_item_id = wp_get_nav_menu_items( 'Menu principal' )[2]->ID;
                foreach ( $menu_items as $menu_item ) {
                    wp_delete_post( $menu_item->ID, true );
                    wp_delete_post( $menu_item_id, true );
                }
            }
        // Supprime la page
        wp_delete_post( $page_query->post->ID, true );
    }
}