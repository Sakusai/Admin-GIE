<?php

/**
 * Effectue la fonction register_event_taxonomy à l'initialisation de wordpress
 */
add_action( 'init', 'register_event_taxonomy' );

/**
 * Créer une catégory personalisé
 */
function register_event_taxonomy() {
    $labels = array(
        'name'              => _x( 'Catégories d\'événements', 'taxonomy general name' ),
        'singular_name'     => _x( 'Catégorie d\'événement', 'taxonomy singular name' ),
        'search_items'      => __( 'Rechercher une catégorie d\'événement' ),
        'all_items'         => __( 'Toutes les catégories d\'événements' ),
        'parent_item'       => __( 'Catégorie parente' ),
        'parent_item_colon' => __( 'Catégorie parente :' ),
        'edit_item'         => __( 'Modifier la catégorie d\'événement' ),
        'update_item'       => __( 'Mettre à jour la catégorie d\'événement' ),
        'add_new_item'      => __( 'Ajouter une nouvelle catégorie d\'événement' ),
        'new_item_name'     => __( 'Nom de la nouvelle catégorie d\'événement' ),
        'menu_name'         => __( 'Catégories d\'événements' ),
    );

    $args = array(
        'hierarchical'      => true,
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array( 'slug' => 'event_category' ),
    );

    register_taxonomy( 'event_category', 'event', $args );
}
