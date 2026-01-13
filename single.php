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
        $categories_list = get_the_category_list( ', ' );
        $tags_list = get_the_tag_list( '', ', ' );

        if ( $categories_list ) :
            printf(
                '<span class="cat-links">%s %s</span>',
                esc_html__( 'Posted in:', 'mrmurphy' ),
                $categories_list
            );
        endif;

        if ( $tags_list ) :
            printf(
                '<span class="tag-links">%s %s</span>',
                esc_html__( 'Tagged:', 'mrmurphy' ),
                $tags_list
            );
        endif;
        ?>
    </footer>

    <!-- Author Bio -->
    <aside class="author-bio surface-raised-1 container" aria-label="<?php esc_attr_e( 'Author information', 'mrmurphy' ); ?>">
        <div class="author-bio__avatar">
            <?php echo get_avatar( get_the_author_meta( 'ID' ), 80 ); ?>
        </div>
        <div class="author-bio__content">
            <h3 class="author-bio__name">
                <?php the_author_posts_link(); ?>
            </h3>
            <?php if ( get_the_author_meta( 'description' ) ) : ?>
                <p class="author-bio__description">
                    <?php echo wp_kses_post( get_the_author_meta( 'description' ) ); ?>
                </p>
            <?php endif; ?>
        </div>
    </aside>

    <!-- Post Navigation -->
    <?php
    the_post_navigation( array(
        'prev_text' => '<span class="nav-subtitle">' . esc_html__( 'Previous:', 'mrmurphy' ) . '</span> <span class="nav-title">%title</span>',
        'next_text' => '<span class="nav-subtitle">' . esc_html__( 'Next:', 'mrmurphy' ) . '</span> <span class="nav-title">%title</span>',
    ) );
    ?>
</article>

<?php
// Comments
if ( comments_open() || get_comments_number() ) :
    comments_template();
endif;
?>

<?php endwhile; ?>

<?php get_template_part( 'template-parts/footer' ); ?>
