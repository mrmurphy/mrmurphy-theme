<?php
/**
 * Search results template
 *
 * @package MrMurphy
 */

get_template_part( 'template-parts/header' );
?>

<div class="search-page">
    <header class="page-header container">
        <h1 class="page-title">
            <?php
            printf(
                /* translators: %s: search query */
                esc_html__( 'Search Results for: %s', 'mrmurphy' ),
                '<span class="search-query">' . get_search_query() . '</span>'
            );
            ?>
        </h1>

        <?php get_search_form(); ?>
    </header>

    <div class="container">
        <?php if ( have_posts() ) : ?>
            <div class="posts-list">
                <?php
                while ( have_posts() ) :
                    the_post();
                    get_template_part( 'template-parts/content/content', 'search' );
                endwhile;
                ?>
            </div>

            <?php the_posts_pagination( array(
                'mid_size'  => 2,
                'prev_text' => esc_html__( 'Previous', 'mrmurphy' ),
                'next_text' => esc_html__( 'Next', 'mrmurphy' ),
            ) ); ?>

        <?php else : ?>
            <?php get_template_part( 'template-parts/content/content', 'none' ); ?>
        <?php endif; ?>
    </div>
</div>

<?php get_template_part( 'template-parts/footer' ); ?>
