<?php

//require_once plugin_dir_path(__FILE__) . '../Event/event-type.php';

/**
 * Effectue la fonction register_event_place à l'initialisation de wordpress
 */
//add_action( 'init', 'register_event_place' );

/**
 * Créer un attribut lieu personalisé
 */
//function register_event_place() {
    // Nom et texte en rapport au type créé
//    $labels = array(
//        'name'              => _x( 'Lieux d\'événements', 'taxonomy general name' ),
//        'singular_name'     => _x( 'Lieu d\'événement', 'taxonomy singular name' ),
//        'search_items'      => __( 'Rechercher un lieu d\'événement' ),
//        'all_items'         => __( 'Tous les lieux d\'événements' ),
//        'parent_item'       => __( 'Lieu parent' ),
//        'parent_item_colon' => __( 'Lieu parent :' ),
//        'edit_item'         => __( 'Modifier le lieu d\'événement' ),
//        'update_item'       => __( 'Mettre à jour le lieu d\'événement' ),
//        'add_new_item'      => __( 'Ajouter un nouveau lieu d\'événement' ),
//        'new_item_name'     => __( 'Nom du nouveau lieu d\'événement' ),
//        'menu_name'         => __( 'Lieux d\'événements' ),
//    );

    // Requête pour la création d'un type personalisé
//    $args = array(
//        'hierarchical'      => true,
//        'labels'            => $labels,
//        'show_ui'           => true,
//        'show_admin_column' => true,
//        'query_var'         => true,
//        'rewrite'           => array( 'slug' => 'event_place' ),
//    );

//    register_taxonomy( 'event_place', 'event', $args ); // Création du type personalisé Event_place
//}

/**
 * Appel la fonction display_event_place avec le contenu
 */
//add_filter('the_content', 'display_event_place');

/**
 * Affiche le lieu de l'événement dans la page d'un événement
 */
//function display_event_place($content)
//{
    // Vérification qu'il s'agit bien d'un événement
//    if (get_post_type() !== 'event') {
//        return $content;
//    }

    // Récupération des termes de Lieu (ici tous les lieux)
//    $terms = get_the_terms( get_the_ID(), 'event_place' );

    // Vérifie s'il y a des termes et retourne le nom du premier terme
//    $event_place_text = "<p><strong>Lieu de l'événement : </strong>";
//    if ( $terms && ! is_wp_error( $terms ) ) {
//        $i = 0;
//        foreach($terms as $term)
//        {
//            $event_place_name = $term->name;
//            if($i === count($terms)-1)
//            {
//                $event_place_text .= $event_place_name;
//            }
//            else
//            {
//                $event_place_text .= $event_place_name . " - ";
//            }
//            $i++;
//        }
//        $event_place_text .= '</p>';
//        return $event_place_text . $content;
//    }

//    return $content;
//}

/**
 * Crée un camp lieu dans la création ou modification d'un événement
 */
function ajouter_champ_lieu_event() {
    global $post;
    $lieu_id = get_post_meta( $post->ID, '_lieu_id', true );
    ?>
    <div class="misc-pub-section">
        <label for="lieu_id"><?php _e( 'Lieu', 'text-domain' ); ?></label>
        <select id="lieu_id" name="lieu_id">
            <option value=""><?php _e( 'Choisir un lieu', 'text-domain' ); ?></option>
            <?php
            global $wpdb;
            $lieux = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}annuaire_lieu ORDER BY annuaire_lieu_nom" );
            foreach ( $lieux as $lieu ) {
                echo '<option value="' . $lieu->annuaire_lieu_id . '" ' . selected( $lieu_id, $lieu->annuaire_lieu_id, false ) . '>' . esc_html( $lieu->annuaire_lieu_nom ) . '</option>';
            }
            ?>
        </select>
    </div>
    <?php
}
/**
 * Ajouter un champ de méta-données pour le lieu sur les événements
 */
function ajouter_meta_box_lieu_event() {
    add_meta_box(
        'lieu_event_meta_box',
        __( 'Lieu de l\'événement', 'text-domain' ),
        'afficher_meta_box_lieu_event',
        'event',
        'side',
        'default'
    );
}
add_action( 'add_meta_boxes', 'ajouter_meta_box_lieu_event' );

/**
 * Afficher la méta-box de lieu pour les événements
 */
function afficher_meta_box_lieu_event( $post ) {
    $lieu_id = get_post_meta( $post->ID, '_lieu_id', true );
    ?>
    <div class="misc-pub-section">
        <label for="lieu_id"><?php _e( 'Lieu', 'text-domain' ); ?></label>
        <select id="lieu_id" name="lieu_id">
            <option value=""><?php _e( 'Choisir un lieu', 'text-domain' ); ?></option>
            <?php
            global $wpdb;
            $lieux = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}annuaire_lieu ORDER BY annuaire_lieu_nom" );
            foreach ( $lieux as $lieu ) {
                $selected = ($lieu_id == $lieu->annuaire_lieu_id) ? 'selected="selected"' : '';
                echo '<option value="' . $lieu->annuaire_lieu_id . '" ' . $selected . '>' . esc_html( $lieu->annuaire_lieu_nom ) . '</option>';
            }
            ?>
        </select>
    </div>
    <?php
}

/**
 * Sauvegarder la valeur de méta-données de lieu pour les événements
*/
function sauvegarder_meta_box_lieu_event( $post_id ) {
    if ( isset( $_POST['lieu_id'] ) ) {
        update_post_meta( $post_id, '_lieu_id', sanitize_text_field( $_POST['lieu_id'] ) );
    }
}
add_action( 'save_post', 'sauvegarder_meta_box_lieu_event' );

/**
 *  Ajouter une colonne "Lieu" à la liste des événements dans le menu WordPress
 */
function custom_event_list_columns($columns) {
    $new_columns = array();
    foreach ($columns as $key => $value) {
        $new_columns[$key] = $value;
        if ($key == 'tags') {
            $new_columns['lieu'] = 'Lieu';
        }
    }
    return $new_columns;
}
add_filter('manage_edit-event_columns', 'custom_event_list_columns');

/**
 * Afficher le nom du lieu correspondant à chaque événement
 */
function custom_event_list_column_content($column_name, $post_id) {
    if ($column_name == 'lieu') {
        $lieu_id = get_post_meta($post_id, '_lieu_id', true);
        global $wpdb;
        $table_name = $wpdb->prefix . 'annuaire_lieu';
        if ($lieu_id) {
            $lieu = $wpdb->get_row("SELECT * FROM $table_name WHERE annuaire_lieu_id = $lieu_id");
            if ($lieu) {
                echo $lieu->annuaire_lieu_nom;
            } else {
                echo '-';
            }
        } else {
            echo '-';
        }
    }
}

add_action('manage_event_posts_custom_column', 'custom_event_list_column_content', 10, 2);
