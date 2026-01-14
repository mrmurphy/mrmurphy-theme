<?php
/**
 * Template part for displaying post excerpts with square featured image
 *
 * @package MrMurphy
 */

defined( 'ABSPATH' ) || exit;
?>

<article id="post-<?php the_ID(); ?>" <?php post_class( 'post-preview' ); ?>>
    <?php if ( has_post_thumbnail() ) : ?>
        <a href="<?php the_permalink(); ?>" class="post-preview__image featured-image--square">
            <?php the_post_thumbnail( 'mrmurphy-square-md' ); ?>
        </a>
    <?php endif; ?>

    <div class="post-preview__content">
        <?php the_title( '<h2 class="post-preview__title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' ); ?>

        <div class="post-preview__meta">
            <time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
                <?php echo esc_html( get_the_date() ); ?>
            </time>
        </div>
    </div>

    <p class="post-preview__excerpt">
        <?php echo wp_trim_words( get_the_excerpt(), 30, '&hellip;' ); ?>
    </p>
</article>
