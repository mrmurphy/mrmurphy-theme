<?php
/**
 * Template part for displaying post excerpts
 *
 * @package MrMurphy
 */

defined( 'ABSPATH' ) || exit;
?>

<article id="post-<?php the_ID(); ?>" <?php post_class( 'post-card' . ( has_post_thumbnail() ? ' post-card--has-thumbnail' : '' ) ); ?>>
    <?php if ( has_post_thumbnail() ) : ?>
        <div class="post-card__thumbnail">
            <a href="<?php the_permalink(); ?>">
                <?php the_post_thumbnail( 'mrmurphy-card' ); ?>
            </a>
        </div>
    <?php endif; ?>

    <div class="post-card__content">
        <header>
            <?php the_title( '<h2 class="post-card__title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' ); ?>

            <div class="post-card__meta">
                <time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
                    <?php echo esc_html( get_the_date() ); ?>
                </time>

                <?php
                $categories = get_the_category();
                if ( ! empty( $categories ) ) :
                ?>
                    <span class="post-card__separator">&middot;</span>
                    <a href="<?php echo esc_url( get_category_link( $categories[0]->term_id ) ); ?>">
                        <?php echo esc_html( $categories[0]->name ); ?>
                    </a>
                <?php endif; ?>
            </div>
        </header>

        <div class="post-card__excerpt">
            <?php the_excerpt(); ?>
        </div>
    </div>
</article>
