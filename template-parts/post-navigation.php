<?php
/**
 * Template part for displaying post navigation
 *
 * @package MrMurphy
 */

defined( 'ABSPATH' ) || exit;

$prev_post = get_previous_post();
$next_post = get_next_post();

if ( ! $prev_post && ! $next_post ) {
    return;
}
?>

<nav class="post-navigation container" aria-label="<?php esc_attr_e( 'Posts', 'mrmurphy' ); ?>">
    <div class="post-navigation__grid">
        <?php if ( $prev_post ) : ?>
            <?php $prev_id = $prev_post->ID; ?>
            <a href="<?php echo esc_url( get_permalink( $prev_id ) ); ?>" class="post-navigation__item post-navigation__item--prev">
                <h2 class="post-navigation__header"><?php esc_html_e( 'Previous', 'mrmurphy' ); ?></h2>
                <div class="post-navigation__content">
                    <?php if ( has_post_thumbnail( $prev_id ) ) : ?>
                        <div class="post-navigation__thumbnail">
                            <?php echo get_the_post_thumbnail( $prev_id, 'thumbnail', array( 'class' => 'post-navigation__image' ) ); ?>
                        </div>
                    <?php endif; ?>
                    <div class="post-navigation__text">
                        <h3 class="post-navigation__title"><?php echo esc_html( get_the_title( $prev_id ) ); ?></h3>
                        <?php
                        $prev_excerpt = get_the_excerpt( $prev_id );
                        if ( $prev_excerpt ) :
                        ?>
                            <p class="post-navigation__excerpt"><?php echo esc_html( $prev_excerpt ); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </a>
        <?php else : ?>
            <div class="post-navigation__item post-navigation__item--empty"></div>
        <?php endif; ?>

        <?php if ( $next_post ) : ?>
            <?php $next_id = $next_post->ID; ?>
            <a href="<?php echo esc_url( get_permalink( $next_id ) ); ?>" class="post-navigation__item post-navigation__item--next">
                <h2 class="post-navigation__header"><?php esc_html_e( 'Next', 'mrmurphy' ); ?></h2>
                <div class="post-navigation__content">
                    <?php if ( has_post_thumbnail( $next_id ) ) : ?>
                        <div class="post-navigation__thumbnail">
                            <?php echo get_the_post_thumbnail( $next_id, 'thumbnail', array( 'class' => 'post-navigation__image' ) ); ?>
                        </div>
                    <?php endif; ?>
                    <div class="post-navigation__text">
                        <h3 class="post-navigation__title"><?php echo esc_html( get_the_title( $next_id ) ); ?></h3>
                        <?php
                        $next_excerpt = get_the_excerpt( $next_id );
                        if ( $next_excerpt ) :
                        ?>
                            <p class="post-navigation__excerpt"><?php echo esc_html( $next_excerpt ); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </a>
        <?php else : ?>
            <div class="post-navigation__item post-navigation__item--empty"></div>
        <?php endif; ?>
    </div>
</nav>
