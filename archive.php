<?php
/**
 * Archive template (categories, tags, dates)
 *
 * @package MrMurphy
 */

get_template_part( 'template-parts/header' );
?>

<div class="archive-page">
    <header class="page-header container">
        <?php the_archive_title( '<h1 class="page-title">', '</h1>' ); ?>
        <?php the_archive_description( '<div class="archive-description">', '</div>' ); ?>
    </header>

    <div class="container">
        <?php if ( have_posts() ) : ?>
            <div class="posts-list">
                <?php
                while ( have_posts() ) :
                    the_post();
                    get_template_part( 'template-parts/content/content', 'excerpt' );
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
