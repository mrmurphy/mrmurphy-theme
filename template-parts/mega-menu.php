<?php
/**
 * Mega menu overlay template part
 *
 * @package MrMurphy
 */

defined( 'ABSPATH' ) || exit;

// Get categories
$categories = get_categories( array(
    'orderby'    => 'count',
    'order'      => 'DESC',
    'hide_empty' => true,
    'number'     => 10,
) );

// Get latest posts
$latest_posts = new WP_Query( array(
    'posts_per_page'      => 3,
    'post_status'         => 'publish',
    'ignore_sticky_posts' => true,
    'no_found_rows'       => true,
) );
?>

<div class="mega-menu" id="mega-menu" aria-hidden="true" role="dialog" aria-label="<?php esc_attr_e( 'Navigation menu', 'mrmurphy' ); ?>">
    <div class="mega-menu__body">
        <div class="mega-menu__column mega-menu__column--categories">
            <span class="mega-menu__label"><?php esc_html_e( 'Explore', 'mrmurphy' ); ?></span>

            <ul class="mega-menu__categories">
                <?php if ( ! empty( $categories ) ) : ?>
                    <?php foreach ( $categories as $category ) : ?>
                        <li>
                            <a href="<?php echo esc_url( get_category_link( $category->term_id ) ); ?>">
                                <?php echo esc_html( $category->name ); ?>
                                <span class="mega-menu__category-count"><?php echo esc_html( $category->count ); ?></span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                <?php elseif ( has_nav_menu( 'primary' ) ) : ?>
                    <?php
                    wp_nav_menu( array(
                        'theme_location' => 'primary',
                        'container'      => false,
                        'items_wrap'     => '%3$s',
                        'depth'          => 1,
                        'fallback_cb'    => false,
                    ) );
                    ?>
                <?php else : ?>
                    <li>
                        <a href="<?php echo esc_url( home_url( '/' ) ); ?>">
                            <?php esc_html_e( 'Home', 'mrmurphy' ); ?>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo esc_url( mrmurphy_get_blog_url() ); ?>">
                            <?php esc_html_e( 'Blog', 'mrmurphy' ); ?>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>

        <div class="mega-menu__column mega-menu__column--posts">
            <span class="mega-menu__label"><?php esc_html_e( 'Latest Posts', 'mrmurphy' ); ?></span>

            <?php if ( $latest_posts->have_posts() ) : ?>
                <div class="mega-menu__posts">
                    <?php while ( $latest_posts->have_posts() ) : $latest_posts->the_post(); ?>
                        <div class="mega-menu__post">
                            <?php
                            get_template_part(
                                'template-parts/content/post-preview',
                                null,
                                array( 'meta_class' => 'post-meta' )
                            );
                            ?>
                        </div>
                    <?php endwhile; ?>
                    <?php wp_reset_postdata(); ?>
                </div>

                <a href="<?php echo esc_url( mrmurphy_get_blog_url() ); ?>" class="mega-menu__see-more">
                    <?php esc_html_e( 'See more', 'mrmurphy' ); ?>
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>
