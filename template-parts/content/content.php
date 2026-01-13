<?php
/**
 * Template part for displaying content
 *
 * @package MrMurphy
 */

defined( 'ABSPATH' ) || exit;
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    <header class="entry-header">
        <?php
        if ( is_singular() ) :
            the_title( '<h1 class="entry-title">', '</h1>' );
        else :
            the_title( '<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' );
        endif;
        ?>

        <div class="post-meta">
            <span class="post-meta__item">
                <time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
                    <?php echo esc_html( get_the_date() ); ?>
                </time>
            </span>

            <?php
            $categories = get_the_category();
            if ( ! empty( $categories ) ) :
            ?>
                <span class="post-meta__item">
                    <a href="<?php echo esc_url( get_category_link( $categories[0]->term_id ) ); ?>">
                        <?php echo esc_html( $categories[0]->name ); ?>
                    </a>
                </span>
            <?php endif; ?>
        </div>
    </header>

    <?php if ( has_post_thumbnail() && ! is_singular() ) : ?>
        <div class="entry-thumbnail">
            <a href="<?php the_permalink(); ?>">
                <?php the_post_thumbnail( 'mrmurphy-card' ); ?>
            </a>
        </div>
    <?php endif; ?>

    <div class="entry-content">
        <?php
        the_content( sprintf(
            /* translators: %s: Post title. */
            esc_html__( 'Continue reading %s', 'mrmurphy' ),
            '<span class="screen-reader-text">' . get_the_title() . '</span>'
        ) );

        wp_link_pages( array(
            'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'mrmurphy' ),
            'after'  => '</div>',
        ) );
        ?>
    </div>
</article>
