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
                        <div class="post-navigation__thumbnail featured-image--square">
                            <?php echo get_the_post_thumbnail( $prev_id, 'mrmurphy-square-sm', array( 'class' => 'post-navigation__image' ) ); ?>
                        </div>
                    <?php endif; ?>
                    <div class="post-navigation__text">
                        <h3 class="post-navigation__title"><?php echo esc_html( get_the_title( $prev_id ) ); ?></h3>
                        <div class="post-navigation__meta">
                            <time datetime="<?php echo esc_attr( get_the_date( 'c', $prev_id ) ); ?>">
                                <?php echo esc_html( get_the_date( '', $prev_id ) ); ?>
                            </time>
                        </div>
                    </div>
                    <?php
                    $prev_excerpt = get_the_excerpt( $prev_id );
                    if ( $prev_excerpt ) :
                    ?>
                        <p class="post-navigation__excerpt"><?php echo wp_trim_words( $prev_excerpt, 25, '&hellip;' ); ?></p>
                    <?php endif; ?>
                </div>
            </a>
        <?php else : ?>
            <div class="post-navigation__item post-navigation__item--placeholder">
                <span class="post-navigation__placeholder-text"><?php esc_html_e( 'That\'s all, folks!', 'mrmurphy' ); ?></span>
            </div>
        <?php endif; ?>

        <?php if ( $next_post ) : ?>
            <?php $next_id = $next_post->ID; ?>
            <a href="<?php echo esc_url( get_permalink( $next_id ) ); ?>" class="post-navigation__item post-navigation__item--next">
                <h2 class="post-navigation__header"><?php esc_html_e( 'Next', 'mrmurphy' ); ?></h2>
                <div class="post-navigation__content">
                    <?php if ( has_post_thumbnail( $next_id ) ) : ?>
                        <div class="post-navigation__thumbnail featured-image--square">
                            <?php echo get_the_post_thumbnail( $next_id, 'mrmurphy-square-sm', array( 'class' => 'post-navigation__image' ) ); ?>
                        </div>
                    <?php endif; ?>
                    <div class="post-navigation__text">
                        <h3 class="post-navigation__title"><?php echo esc_html( get_the_title( $next_id ) ); ?></h3>
                        <div class="post-navigation__meta">
                            <time datetime="<?php echo esc_attr( get_the_date( 'c', $next_id ) ); ?>">
                                <?php echo esc_html( get_the_date( '', $next_id ) ); ?>
                            </time>
                        </div>
                    </div>
                    <?php
                    $next_excerpt = get_the_excerpt( $next_id );
                    if ( $next_excerpt ) :
                    ?>
                        <p class="post-navigation__excerpt"><?php echo wp_trim_words( $next_excerpt, 25, '&hellip;' ); ?></p>
                    <?php endif; ?>
                </div>
            </a>
        <?php else : ?>
            <div class="post-navigation__item post-navigation__item--placeholder">
                <span class="post-navigation__placeholder-text"><?php esc_html_e( 'That\'s all, folks!', 'mrmurphy' ); ?></span>
            </div>
        <?php endif; ?>
    </div>
</nav>
