<?php
/**
 * Custom Walker for Mega Menu
 *
 * @package MrMurphy
 */

defined( 'ABSPATH' ) || exit;

/**
 * Note: This theme uses a custom mega menu implementation
 * that doesn't require a Walker class. The mega menu is
 * rendered directly in template-parts/mega-menu.php using
 * wp_nav_menu() for the category list and a custom WP_Query
 * for the latest posts.
 *
 * This file is included for potential future enhancements
 * if a more sophisticated menu structure is needed.
 */

/**
 * Filter the nav menu items to add custom classes or data attributes.
 *
 * @param array    $classes Array of class names.
 * @param WP_Post  $item    Current menu item object.
 * @param stdClass $args    Menu arguments.
 * @param int      $depth   Depth of menu item.
 * @return array Modified class array.
 */
function mrmurphy_nav_menu_css_class( $classes, $item, $args, $depth ) {
    // Add depth class
    $classes[] = 'menu-depth-' . $depth;

    // Add class for items with children
    if ( in_array( 'menu-item-has-children', $classes, true ) ) {
        $classes[] = 'has-submenu';
    }

    return $classes;
}
add_filter( 'nav_menu_css_class', 'mrmurphy_nav_menu_css_class', 10, 4 );

/**
 * Filter the nav menu link attributes.
 *
 * @param array    $atts    Link attributes.
 * @param WP_Post  $item    Menu item object.
 * @param stdClass $args    Menu arguments.
 * @param int      $depth   Depth of menu item.
 * @return array Modified attributes.
 */
function mrmurphy_nav_menu_link_attributes( $atts, $item, $args, $depth ) {
    // Add aria attributes for accessibility
    if ( in_array( 'menu-item-has-children', $item->classes, true ) ) {
        $atts['aria-haspopup'] = 'true';
        $atts['aria-expanded'] = 'false';
    }

    return $atts;
}
add_filter( 'nav_menu_link_attributes', 'mrmurphy_nav_menu_link_attributes', 10, 4 );
