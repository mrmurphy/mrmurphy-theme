<?php
/**
 * Enqueue scripts and styles
 *
 * @package MrMurphy
 */

defined( 'ABSPATH' ) || exit;

/**
 * Enqueue styles and scripts.
 */
function mrmurphy_scripts() {
    // Google Fonts - JetBrains Mono (variable font)
    wp_enqueue_style(
        'mrmurphy-fonts',
        'https://fonts.googleapis.com/css2?family=JetBrains+Mono:ital,wght@0,100..800;1,100..800&display=swap',
        array(),
        null
    );

    // CSS Variables
    wp_enqueue_style(
        'mrmurphy-variables',
        MRMURPHY_URI . '/assets/css/variables.css',
        array( 'mrmurphy-fonts' ),
        MRMURPHY_VERSION
    );

    // Base styles
    wp_enqueue_style(
        'mrmurphy-base',
        MRMURPHY_URI . '/assets/css/base.css',
        array( 'mrmurphy-variables' ),
        MRMURPHY_VERSION
    );

    // Layout styles
    wp_enqueue_style(
        'mrmurphy-layout',
        MRMURPHY_URI . '/assets/css/layout.css',
        array( 'mrmurphy-base' ),
        MRMURPHY_VERSION
    );

    // Component styles
    wp_enqueue_style(
        'mrmurphy-components',
        MRMURPHY_URI . '/assets/css/components.css',
        array( 'mrmurphy-layout' ),
        MRMURPHY_VERSION
    );

    // Navigation styles
    wp_enqueue_style(
        'mrmurphy-navigation',
        MRMURPHY_URI . '/assets/css/navigation.css',
        array( 'mrmurphy-components' ),
        MRMURPHY_VERSION
    );

    // Animation styles
    wp_enqueue_style(
        'mrmurphy-animations',
        MRMURPHY_URI . '/assets/css/animations.css',
        array( 'mrmurphy-navigation' ),
        MRMURPHY_VERSION
    );

    // Main stylesheet (for theme header recognition)
    wp_enqueue_style(
        'mrmurphy-style',
        get_stylesheet_uri(),
        array( 'mrmurphy-animations' ),
        MRMURPHY_VERSION
    );

    // Navigation JavaScript
    wp_enqueue_script(
        'mrmurphy-navigation',
        MRMURPHY_URI . '/assets/js/navigation.js',
        array(),
        MRMURPHY_VERSION,
        true
    );

    // Theme Toggle JavaScript
    wp_enqueue_script(
        'mrmurphy-theme-toggle',
        MRMURPHY_URI . '/assets/js/theme-toggle.js',
        array(),
        MRMURPHY_VERSION,
        true
    );

    // Color Cycling for Section Headers
    wp_enqueue_script(
        'mrmurphy-color-cycle',
        MRMURPHY_URI . '/assets/js/color-cycle.js',
        array(),
        MRMURPHY_VERSION,
        true
    );

    // Comment reply script
    if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
        wp_enqueue_script( 'comment-reply' );
    }
}
add_action( 'wp_enqueue_scripts', 'mrmurphy_scripts' );

/**
 * Enqueue block editor styles.
 */
function mrmurphy_editor_styles() {
    // Google Fonts for editor
    add_editor_style( 'https://fonts.googleapis.com/css2?family=JetBrains+Mono:ital,wght@0,100..800;1,100..800&display=swap' );

    // Editor-specific styles
    add_editor_style( 'assets/css/editor.css' );
}
add_action( 'after_setup_theme', 'mrmurphy_editor_styles' );

/**
 * Add preconnect for Google Fonts.
 */
function mrmurphy_resource_hints( $urls, $relation_type ) {
    if ( 'preconnect' === $relation_type ) {
        $urls[] = array(
            'href' => 'https://fonts.googleapis.com',
            'crossorigin' => 'anonymous',
        );
        $urls[] = array(
            'href' => 'https://fonts.gstatic.com',
            'crossorigin' => 'anonymous',
        );
    }

    return $urls;
}
add_filter( 'wp_resource_hints', 'mrmurphy_resource_hints', 10, 2 );
