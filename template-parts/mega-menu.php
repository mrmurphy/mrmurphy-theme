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
                        <a href="<?php echo esc_url( get_permalink( get_option( 'page_for_posts' ) ) ); ?>">
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
                        <article class="mega-menu__post">
                            <?php if ( has_post_thumbnail() ) : ?>
                                <div class="mega-menu__post-thumbnail">
                                    <a href="<?php the_permalink(); ?>">
                                        <?php the_post_thumbnail( 'mrmurphy-thumbnail' ); ?>
                                    </a>
                                </div>
                            <?php endif; ?>
                            <div class="mega-menu__post-content">
                                <h3 class="mega-menu__post-title">
                                    <a href="<?php the_permalink(); ?>">
                                        <?php the_title(); ?>
                                    </a>
                                </h3>
                                <time class="mega-menu__post-date" datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
                                    <?php echo esc_html( get_the_date() ); ?>
                                </time>
                            </div>
                        </article>
                    <?php endwhile; ?>
                    <?php wp_reset_postdata(); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
