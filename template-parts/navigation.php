<?php
/**
 * Primary navigation template part
 *
 * @package MrMurphy
 */

defined( 'ABSPATH' ) || exit;

if ( ! has_nav_menu( 'primary' ) ) {
    return;
}
?>

<nav id="primary-navigation" class="primary-navigation" aria-label="<?php esc_attr_e( 'Primary menu', 'mrmurphy' ); ?>">
    <?php
    wp_nav_menu( array(
        'theme_location' => 'primary',
        'menu_class'     => '',
        'container'      => false,
        'depth'          => 1,
        'fallback_cb'    => false,
    ) );
    ?>
</nav>
