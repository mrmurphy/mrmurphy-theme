<?php
/**
 * Enqueue scripts and styles
 *
 * @package MrMurphy
 */

defined( 'ABSPATH' ) || exit;

/**
 * Enqueue bundled styles and scripts.
 */
function mrmurphy_scripts() {
	$bundle_css = MRMURPHY_DIR . '/assets/css/theme.bundle.css';
	$bundle_js  = MRMURPHY_DIR . '/assets/js/theme.bundle.js';

	wp_enqueue_style(
		'mrmurphy-theme',
		MRMURPHY_URI . '/assets/css/theme.bundle.css',
		array(),
		file_exists( $bundle_css ) ? (string) filemtime( $bundle_css ) : MRMURPHY_VERSION
	);

	wp_enqueue_script(
		'mrmurphy-theme',
		MRMURPHY_URI . '/assets/js/theme.bundle.js',
		array(),
		file_exists( $bundle_js ) ? (string) filemtime( $bundle_js ) : MRMURPHY_VERSION,
		true
	);

	if ( is_singular( 'post' ) ) {
		wp_enqueue_style(
			'mrmurphy-authorship',
			MRMURPHY_URI . '/assets/css/ai-authorship.css',
			array( 'mrmurphy-theme' ),
			MRMURPHY_VERSION
		);

		wp_enqueue_script(
			'mrmurphy-authorship',
			MRMURPHY_URI . '/assets/js/ai-authorship.js',
			array(),
			MRMURPHY_VERSION,
			true
		);
	}

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'mrmurphy_scripts' );

/**
 * Enqueue block editor styles.
 */
function mrmurphy_editor_styles() {
	add_editor_style( 'assets/css/editor.css' );
	add_editor_style( 'assets/css/ai-authorship-editor.css' );
}
add_action( 'after_setup_theme', 'mrmurphy_editor_styles' );
