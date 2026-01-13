<?php
/**
 * MrMurphy Theme functions and definitions
 *
 * @package MrMurphy
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;

define( 'MRMURPHY_VERSION', '1.0.0' );
define( 'MRMURPHY_DIR', get_template_directory() );
define( 'MRMURPHY_URI', get_template_directory_uri() );

// Theme setup and supports
require_once MRMURPHY_DIR . '/inc/setup.php';

// Asset enqueueing
require_once MRMURPHY_DIR . '/inc/enqueue.php';

// Custom post types
require_once MRMURPHY_DIR . '/inc/custom-post-types.php';

// Customizer settings
require_once MRMURPHY_DIR . '/inc/customizer.php';

// Template helper functions
require_once MRMURPHY_DIR . '/inc/template-functions.php';

// Mega menu walker
require_once MRMURPHY_DIR . '/inc/mega-menu-walker.php';

/**
 * Filter navigation menu items to remove "coming soon" links
 */
function mrmurphy_filter_menu_items( $items, $args ) {
    if ( ! isset( $args->theme_location ) || 'primary' !== $args->theme_location ) {
        return $items;
    }

    foreach ( $items as $key => $item ) {
        $title_lower = strtolower( $item->title );
        // Remove items with "coming soon" in title or URL
        if ( strpos( $title_lower, 'coming soon' ) !== false || 
             strpos( $item->url, 'coming-soon' ) !== false ||
             strpos( $item->url, '#coming-soon' ) !== false ) {
            unset( $items[ $key ] );
        }
    }

    return $items;
}
add_filter( 'wp_nav_menu_objects', 'mrmurphy_filter_menu_items', 10, 2 );
