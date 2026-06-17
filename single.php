<?php
/**
 * Single post template
 *
 * @package MrMurphy
 */

get_template_part( 'template-parts/header' );
?>

<?php while ( have_posts() ) : the_post(); ?>

<?php
$is_microblog = function_exists( 'wp_microblog_is' ) && wp_microblog_is();
$has_title    = function_exists( 'wp_microblog_post_has_title' ) ? wp_microblog_post_has_title() : ( '' !== trim( (string) get_the_title() ) );
$categories   = get_the_category();
$display_categories = array();

if ( ! empty( $categories ) ) {
    $display_categories = array_filter(
        $categories,
        function ( $category ) use ( $is_microblog ) {
            return ! $is_microblog || wp_microblog_category_slug() !== $category->slug;
        }
    );
}

$display_category = ! empty( $display_categories ) ? reset( $display_categories ) : null;
?>

<article id="post-<?php the_ID(); ?>" <?php post_class( $is_microblog ? 'single-post single-post--microblog' : 'single-post' ); ?>>
    <?php if ( has_post_thumbnail() ) : ?>
        <!-- Floating header with square featured image -->
        <header class="entry-header post-header--floating container">
            <div class="post-header__image featured-image--square">
                <?php the_post_thumbnail( 'mrmurphy-square-lg' ); ?>
            </div>

            <div class="post-header__content">
                <?php if ( $has_title ) : ?>
                    <?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
                <?php endif; ?>

                <div class="post-meta">
                    <?php if ( $is_microblog ) : ?>
                        <span class="post-meta__item post-meta__microblog-tag">
                            <a href="<?php echo esc_url( wp_microblog_category_url() ); ?>">#microblog</a>
                        </span>
                    <?php endif; ?>

                    <span class="post-meta__item">
                        <time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
                            <?php echo esc_html( get_the_date() ); ?>
                        </time>
                    </span>

                    <?php if ( $display_category ) : ?>
                        <span class="post-meta__item">
                            <a href="<?php echo esc_url( get_category_link( $display_category->term_id ) ); ?>">
                                <?php echo esc_html( $display_category->name ); ?>
                            </a>
                        </span>
                    <?php endif; ?>

                    <span class="post-meta__item">
                        <?php echo esc_html( mrmurphy_reading_time() ); ?>
                    </span>

                    <?php if ( function_exists( 'mrmurphy_authorship_render' ) ) : ?>
                    <?php mrmurphy_authorship_render( get_the_ID() ); ?>
                    <?php endif; ?>
                </div>
            </div>
        </header>
    <?php else : ?>
        <!-- Standard header without featured image -->
        <header class="entry-header <?php echo $is_microblog ? 'entry-header--microblog' : ''; ?> container">
            <?php if ( $has_title ) : ?>
                <?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
            <?php endif; ?>

            <div class="post-meta">
                <?php if ( $is_microblog ) : ?>
                    <span class="post-meta__item post-meta__microblog-tag">
                        <a href="<?php echo esc_url( wp_microblog_category_url() ); ?>">#microblog</a>
                    </span>
                <?php endif; ?>

                <span class="post-meta__item">
                    <time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
                        <?php echo esc_html( get_the_date() ); ?>
                    </time>
                </span>

                <?php if ( $display_category ) : ?>
                    <span class="post-meta__item">
                        <a href="<?php echo esc_url( get_category_link( $display_category->term_id ) ); ?>">
                            <?php echo esc_html( $display_category->name ); ?>
                        </a>
                    </span>
                <?php endif; ?>

                <span class="post-meta__item">
                    <?php echo esc_html( mrmurphy_reading_time() ); ?>
                </span>

                <?php if ( function_exists( 'mrmurphy_authorship_render' ) ) : ?>
                <?php mrmurphy_authorship_render( get_the_ID() ); ?>
                <?php endif; ?>
            </div>
        </header>
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
