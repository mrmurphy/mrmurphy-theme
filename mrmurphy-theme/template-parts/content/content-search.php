<?php
/**
 * Template part for displaying search results
 *
 * @package MrMurphy
 */

defined( 'ABSPATH' ) || exit;
?>

<article id="post-<?php the_ID(); ?>" <?php post_class( 'post-card' ); ?>>
    <div class="post-card__content">
        <header>
            <?php the_title( '<h2 class="post-card__title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' ); ?>

            <div class="post-card__meta">
                <span class="post-type">
                    <?php echo esc_html( get_post_type_object( get_post_type() )->labels->singular_name ); ?>
                </span>

                <span class="post-card__separator">&middot;</span>

                <time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
                    <?php echo esc_html( get_the_date() ); ?>
                </time>
            </div>
        </header>

        <div class="post-card__excerpt">
            <?php the_excerpt(); ?>
        </div>
    </div>
</article>
