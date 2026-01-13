<?php
/**
 * The main template file
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 *
 * @package MrMurphy
 */

get_template_part( 'template-parts/header' );
?>

<div class="container">
    <?php if ( have_posts() ) : ?>
        <div class="posts-list">
            <?php
            while ( have_posts() ) :
                the_post();
                get_template_part( 'template-parts/content/content', 'excerpt' );
            endwhile;
            ?>
        </div>

        <?php the_posts_pagination( array(
            'mid_size'  => 2,
            'prev_text' => esc_html__( 'Previous', 'mrmurphy' ),
            'next_text' => esc_html__( 'Next', 'mrmurphy' ),
        ) ); ?>

    <?php else : ?>
        <?php get_template_part( 'template-parts/content/content', 'none' ); ?>
    <?php endif; ?>
</div>

<?php get_template_part( 'template-parts/footer' ); ?>
