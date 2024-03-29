<?php

/**
 * Création d'un shortcorde utilisable avec [slider_article] créé par la fonction slider_article_shortcode
 */
add_shortcode('slider_article', 'slider_article_shortcode');

/**
 * Shortcode pour afficher une sorte de carrousel qui affiche les événements d'une certaine période donnée
 */
function slider_article_shortcode($atts) {
    ob_start(); // Met en mémoire tampon la sortie du code
       // Paramètres par défaut du shortcode
       $defaults = array(
        'idcat' => 0,
        'nbslide' => 3,
        'format' => 1,
        'speeddefil' => 3000,
        'speed' => 300
    );

    // Fusionner les paramètres par défaut avec les paramètres du shortcode
    $atts = shortcode_atts($defaults, $atts);
    $args = array(
        'posts_per_page' => 15,
        'cat' => isset($atts['idcat']) ? $atts['idcat'] : 0,
        'orderby' => 'post_modified'
    );

    $articles = new WP_Query($args);
    ?>

    <link rel="stylesheet" href="<?php echo plugins_url('../slick/slick-theme.css', __FILE__); ?>" />
    <!-- Lien vers le fichier theme css de slick -->
    <link rel="stylesheet" href="<?php echo plugins_url('../slick/slick.css', __FILE__); ?>" />
    <!-- Lien vers le fichier css de slick -->
    <link rel="stylesheet" type="text/css" href="<?php echo plugin_dir_url(__FILE__) . '../CSS/style.css'; ?>">
    <!-- Lien vers notre fichier css -->
    <div class="responsive">
    <?php
        while ($articles->have_posts()) {
            $articles->the_post(); // Séléctionne puis enlève le premier événement de la liste 
            if ($atts['format'] == 1) {
                $atts['nbslide'] = 3;
                ?>
                <div class="slider-cat">
                    <div class="img-cat">
                        <a href="<?php the_permalink(); ?>"><?php the_post_thumbnail(array(150, 150)); ?></a>
                    </div>
                    <div class="content-cat">
                        <h1><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h1>
                    </div>
                </div>
                <?php
            } else {
                ?>
                <div>
                    <div >
                        <a href="<?php the_permalink(); ?>"><?php the_post_thumbnail(array(150, 150)); ?></a>
                    </div>
                    <div>
                        <h1><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h1>
                    </div>
                </div>
                <?php
            }
        }
        ?>
    </div>
    <!-- On Récupère ici tous les scripts js nécessaires pour le slider -->
    <script type="text/javascript" src="<?php echo plugins_url('../JS/jquery-3.6.4.min.js', __FILE__); ?>"></script>
    <script type="text/javascript" src="<?php echo plugins_url('../JS/jquery-migrate-1.4.1.min.js', __FILE__); ?>"></script>
    <script type="text/javascript" src="<?php echo plugins_url('../slick/slick.min.js', __FILE__); ?>"></script>
    <!-- Début du script slick (carrousel)-->
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            $('.responsive').slick({
                dots: false, //Affichage ou non des points en bas du slider
                infinite: true,  //Boucle infinie ou non des slides
                speed: <?php echo $atts['speed'] ?>, //Vitesse de défilement entre deux slides
                autoplay: true, // Défilement automatique
                autoplaySpeed: <?php echo $atts['speeddefil'] ?>, // Vitesse du défilement automatique
                slidesToShow: <?php echo $atts['nbslide'] ?>, // Nombre de slide à afficher
                slidesToScroll: 1, // Nombre de slide qui défile
                responsive: [ // Adaptation avec des écrans plus petits
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
        });
    </script>
    <?php
    wp_reset_postdata();
    return ob_get_clean();
}
