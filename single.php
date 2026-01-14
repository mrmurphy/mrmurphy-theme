<?php
/**
 * Single post template
 *
 * @package MrMurphy
 */

get_template_part( 'template-parts/header' );
?>

<?php while ( have_posts() ) : the_post(); ?>

<article id="post-<?php the_ID(); ?>" <?php post_class( 'single-post' ); ?>>
    <header class="entry-header container">
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

            <span class="post-meta__item">
                <?php echo esc_html( mrmurphy_reading_time() ); ?>
            </span>
        </div>
    </header>

    <?php if ( has_post_thumbnail() ) : ?>
        <figure class="featured-image container container--wide">
            <?php the_post_thumbnail( 'large', array(
                'class'   => 'featured-image__img',
                'loading' => 'eager',
            ) ); ?>

            <?php if ( get_the_post_thumbnail_caption() ) : ?>
                <figcaption class="featured-image__caption">
                    <?php the_post_thumbnail_caption(); ?>
                </figcaption>
            <?php endif; ?>
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

    <footer class="entry-footer container">
        <?php
        $categories = get_the_category();
        $tags = get_the_tags();

        if ( ! empty( $categories ) ) :
            echo '<span class="cat-links">' . esc_html__( 'Posted in:', 'mrmurphy' ) . ' ';
            foreach ( $categories as $category ) {
                printf(
                    '<a href="%s">%s</a>',
                    esc_url( get_category_link( $category->term_id ) ),
                    esc_html( $category->name )
                );
            }
            echo '</span>';
        endif;

        if ( ! empty( $tags ) ) :
            echo '<span class="tag-links">' . esc_html__( 'Tagged:', 'mrmurphy' ) . ' ';
            foreach ( $tags as $tag ) {
                printf(
                    '<a href="%s">%s</a>',
                    esc_url( get_tag_link( $tag->term_id ) ),
                    esc_html( $tag->name )
                );
            }
            echo '</span>';
        endif;
        ?>
    </footer>

    <!-- Post Navigation -->
    <?php get_template_part( 'template-parts/post-navigation' ); ?>
</article>

<?php
// Comments
if ( comments_open() || get_comments_number() ) :
    comments_template();
endif;
?>

<?php endwhile; ?>

<?php get_template_part( 'template-parts/footer' ); ?>
