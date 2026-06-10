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
<?php get_template_part( 'template-parts/content/post-preview' ); ?>

<?php endif; ?>
