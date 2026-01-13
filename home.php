<?php
/**
 * Blog posts listing template
 *
 * @package MrMurphy
 */

get_template_part( 'template-parts/header' );
?>

<div class="blog-page">
    <header class="page-header container">
        <h1 class="page-title"><?php esc_html_e( 'Blog', 'mrmurphy' ); ?></h1>
        <?php if ( get_bloginfo( 'description' ) ) : ?>
            <p class="page-description"><?php bloginfo( 'description' ); ?></p>
        <?php endif; ?>
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
