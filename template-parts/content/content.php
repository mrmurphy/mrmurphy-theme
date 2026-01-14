<?php
/**
 * Template part for displaying content
 *
 * @package MrMurphy
 */

defined( 'ABSPATH' ) || exit;

// Use different markup for singular vs archive/listing views
if ( is_singular() ) :
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    <header class="entry-header">
        <?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>

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

<?php else : ?>

<!-- Post preview with square featured image (archive/listing view) -->
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

<?php endif; ?>
