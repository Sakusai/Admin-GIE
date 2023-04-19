<?php

/**
 * Création d'un shortcorde utilisable avec [slider_events] créé par la fonction slider_events_shortcode
 */
add_shortcode( 'slider_events', 'slider_events_shortcode' );

/**
 * Shortcode pour afficher une sorte de carrousel qui affiche les événements d'une certaine période donnée
 */
function slider_events_shortcode() {
    ob_start(); // Met en mémoire tampon la sortie du code
    $args = array(
        'post_type'      => 'event',
        'posts_per_page' => -1,
        'orderby'        => 'meta_value',
        'meta_key'       => 'event_start_date',
        'order'          => 'ASC'
      );
    
    $events = new WP_Query( $args ); // Exécution de la requête
    ?>

    <link rel="stylesheet" href="<?php echo plugins_url( 'slick/slick-theme.css', __FILE__ ); ?>"/>
    <link rel="stylesheet" href="<?php echo plugins_url( 'slick/slick.css', __FILE__ ); ?>"/>
    <link rel="stylesheet" type="text/css" href="<?php echo plugin_dir_url(__FILE__) . 'CSS/style.css'; ?>">   
    <div class="responsive">
    <?php
    while ( $events->have_posts() ) {
        $events->the_post(); // Enlève le premier événement de la liste 
        $event_id = get_the_ID();
        $event_start_date =  date('d-m-Y', strtotime(get_post_meta( $event_id, 'event_start_date', true )));
        $event_start_hour = get_post_meta( $event_id, 'event_start_hour', true );
        $slides_to_show = get_option( 'events_slides_to_show', 4 );
        $content = get_the_content($event_id);
        $apercu_description = strip_tags(substr($content, 0, 480/$slides_to_show));
        $terms = get_the_terms( get_the_ID(), 'event_place' );

        // Vérifie s'il y a des termes et retourne le nom du premier terme
        $event_place_text = "";
        if ( $terms && ! is_wp_error( $terms ) ) {
            $i = 0;
            foreach($terms as $term)
            {
                $event_place_name = $term->name;
                if($i === count($terms)-1)
                {
                    $event_place_text .= $event_place_name;
                }
                else
                {
                    $event_place_text .= $event_place_name . " - ";
                }
                $i++;
            }
        }
        ?>
        
        <div class="slider">
            <p class="img">
                <a href="<?php the_permalink(); ?>"><?php the_post_thumbnail( array( 300, 180 ) ); ?></a>
            </p>
            <h1>    
                <a href="<?php the_permalink(); ?>"> <strong><?php the_title();?> </strong> </a>
            </h1>
            <h2 class="date"> <?php echo $event_start_date . " à " . str_replace(":", "h", $event_start_hour)  ; ?> </h2>
            <h2><strong><?php echo $event_place_text?></strong></h2>
            <p><?php echo $apercu_description . "..."; ?></p>
            <p class="read-more">
                <a  href="<?php the_permalink(); ?>">Lire la suite</a>
            </p>
        </div>
        

    <?php }
    ?>
    </div>
    <script type="text/javascript" src="<?php echo plugins_url( 'JS/jquery-3.6.4.min.js', __FILE__ ); ?>"></script>
    <script type="text/javascript" src="<?php echo plugins_url( 'JS/jquery-migrate-1.4.1.min.js', __FILE__ ); ?>"></script>
    <script type="text/javascript" src="<?php echo plugins_url( 'slick/slick.min.js', __FILE__ ); ?>"></script>
        <!-- Début du script slick (carrousel)-->
        <script type="text/javascript">
            $('.responsive').slick({
                dots: true,
                infinite: true,
                speed: 300,
                autoplay: true,
                autoplaySpeed:3000,
                slidesToShow: <?php echo $slides_to_show ?>,
                slidesToScroll: 1,
                responsive: [
                    {
                    breakpoint: 1024,
                    settings: {
                        slidesToShow: 3,
                        slidesToScroll: 1,
                        infinite: true,
                        dots: false
                        }
                        },
                        {
                        breakpoint: 600,
                        settings: {
                            slidesToShow: 2,
                            slidesToScroll: 2
                        }
                        },
                        {
                        breakpoint: 480,
                        settings: {
                            slidesToShow: 1,
                            slidesToScroll: 1
                        }
                        }
                    ]
                    });
        </script>

    <?php
    wp_reset_postdata();
    return ob_get_clean();
}