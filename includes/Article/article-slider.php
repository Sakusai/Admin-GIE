<?php

/**
 * Création d'un shortcorde utilisable avec [slider_article] créé par la fonction slider_article_shortcode
 */
add_shortcode('slider_article', 'slider_article_shortcode');

/**
 * Shortcode pour afficher une sorte de carrousel qui affiche les événements d'une certaine période donnée
 */
function slider_article_shortcode($idCat)
{
    ob_start(); // Met en mémoire tampon la sortie du code
    $args = array(
        'showposts' => 15,
        'cat' => $idCat,
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
        $slides_to_show = get_option('post_slides_to_show', 4);
        $slides_speed = get_option('post_slides_speed', 3000);
        $slides_speed_pass = get_option('post_slides_speed_pass', 300);
        $slides_format = get_option('post_slides_format', 1);
        while ($articles->have_posts()) {
            $articles->the_post(); // Séléctionne puis enlève le premier événement de la liste 
            if ($slides_format == 1) {
                $slides_to_show=3;
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
    <script type="text/javascript"
        src="<?php echo plugins_url('../JS/jquery-migrate-1.4.1.min.js', __FILE__); ?>"></script>
    <script type="text/javascript" src="<?php echo plugins_url('../slick/slick.min.js', __FILE__); ?>"></script>
    <!-- Début du script slick (carrousel)-->
    <script type="text/javascript">
        $('.responsive').slick({
            dots: false, //Affichage ou non des points en bas du slider
            infinite: true,  //Boucle infini ou non des slides
            speed: <?php echo $slides_speed_pass ?>, //Vitesse de défilement entre deux slides
            autoplay: true, // Défilement automatique
            autoplaySpeed: <?php echo $slides_speed ?>, // Vitesse du défilement automatique
            slidesToShow: <?php echo $slides_to_show ?>, // Nombre de slide à afficher
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
    </script>
    <?php
    wp_reset_postdata();
    return ob_get_clean();
}