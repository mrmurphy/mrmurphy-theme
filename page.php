<?php
/**
 * Generic page template
 *
 * @package MrMurphy
 */

get_template_part( 'template-parts/header' );
?>

<?php while ( have_posts() ) : the_post(); ?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    <header class="entry-header container">
        <?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
    </header>

    <?php if ( has_post_thumbnail() ) : ?>
        <figure class="featured-image container container--wide">
            <?php the_post_thumbnail( 'large', array(
                'class'   => 'featured-image__img',
                'loading' => 'eager',
            ) ); ?>
        </figure>
    <?php endif; ?>

    <div class="entry-content container">
        <?php
        the_content();

        wp_link_pages( array(
            'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'mrmurphy' ),
            'after'  => '</div>',
        ) );
        ?>
    </div>

    <?php if ( comments_open() || get_comments_number() ) : ?>
        <div class="container">
            <?php comments_template(); ?>
        </div>
    <?php endif; ?>
</article>

<?php endwhile; ?>

<?php get_template_part( 'template-parts/footer' ); ?>
