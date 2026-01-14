<?php
/**
 * Theme setup functions
 *
 * @package MrMurphy
 */

defined( 'ABSPATH' ) || exit;

/**
 * Sets up theme defaults and registers support for various WordPress features.
 */
function mrmurphy_setup() {
    // Make theme available for translation
    load_theme_textdomain( 'mrmurphy', MRMURPHY_DIR . '/languages' );

    // Add default posts and comments RSS feed links to head
    add_theme_support( 'automatic-feed-links' );

    // Let WordPress manage the document title
    add_theme_support( 'title-tag' );

    // Enable support for Post Thumbnails
    add_theme_support( 'post-thumbnails' );
    set_post_thumbnail_size( 1200, 630, true );
    add_image_size( 'mrmurphy-card', 600, 400, true );
    add_image_size( 'mrmurphy-thumbnail', 150, 150, true );

    // Square featured images (app icon style) - 2x sizes for retina
    add_image_size( 'mrmurphy-square-lg', 320, 320, true );  // Displays up to 150px
    add_image_size( 'mrmurphy-square-md', 240, 240, true );  // Displays at 120px
    add_image_size( 'mrmurphy-square-sm', 160, 160, true );  // Displays at 80px

    // Register navigation menus
    register_nav_menus( array(
        'primary' => esc_html__( 'Primary Menu', 'mrmurphy' ),
        'footer'  => esc_html__( 'Footer Menu', 'mrmurphy' ),
        'social'  => esc_html__( 'Social Links', 'mrmurphy' ),
    ) );

    // HTML5 support
    add_theme_support( 'html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script',
        'navigation-widgets',
    ) );

    // Block editor support
    add_theme_support( 'wp-block-styles' );
    add_theme_support( 'align-wide' );
    add_theme_support( 'editor-styles' );
    add_editor_style( 'assets/css/editor.css' );

    // Responsive embeds
    add_theme_support( 'responsive-embeds' );

    // Custom logo
    add_theme_support( 'custom-logo', array(
        'height'      => 100,
        'width'       => 100,
        'flex-height' => true,
        'flex-width'  => true,
    ) );

    // Add support for block editor color palette from theme.json
    add_theme_support( 'editor-color-palette' );

    // Disable custom colors in block editor (use theme palette only)
    add_theme_support( 'disable-custom-colors' );
}
add_action( 'after_setup_theme', 'mrmurphy_setup' );

/**
 * Set the content width in pixels.
 */
function mrmurphy_content_width() {
    $GLOBALS['content_width'] = apply_filters( 'mrmurphy_content_width', 960 );
}
add_action( 'after_setup_theme', 'mrmurphy_content_width', 0 );

/**
 * Register widget areas.
 */
function mrmurphy_widgets_init() {
    register_sidebar( array(
        'name'          => esc_html__( 'Footer Widget Area', 'mrmurphy' ),
        'id'            => 'footer-1',
        'description'   => esc_html__( 'Add widgets to the footer.', 'mrmurphy' ),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ) );
}
add_action( 'widgets_init', 'mrmurphy_widgets_init' );

/**
 * Customize excerpt length.
 */
function mrmurphy_excerpt_length( $length ) {
    return 25;
}
add_filter( 'excerpt_length', 'mrmurphy_excerpt_length' );

/**
 * Customize excerpt more string.
 */
function mrmurphy_excerpt_more( $more ) {
    return '&hellip;';
}
add_filter( 'excerpt_more', 'mrmurphy_excerpt_more' );

/**
 * Add custom body classes.
 */
function mrmurphy_body_classes( $classes ) {
    // Add class if no sidebar
    if ( ! is_active_sidebar( 'sidebar-1' ) ) {
        $classes[] = 'no-sidebar';
    }

    // Add class for singular pages
    if ( is_singular() ) {
        $classes[] = 'singular';
    }

    return $classes;
}
add_filter( 'body_class', 'mrmurphy_body_classes' );
