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

	wp_localize_script(
		'mrmurphy-theme',
		'mrmurphyMicroblog',
		array(
			'nonce' => wp_create_nonce( 'wp_rest' ),
			'root'  => esc_url_raw( rest_url( 'mrmurphy/v1' ) ),
		)
	);

	// Lightweight flag cookie so returning visitors with a cached token
	// skip the (cheap but pointless) HMAC generation on every page load.
	if ( function_exists( 'mrmurphy_likes_create_token' ) ) {
		if ( isset( $_COOKIE['mmb_ready'] ) ) {
			// Returning visitor — no inline script needed, JS reads localStorage.
		} else {
			$client_token = mrmurphy_likes_create_token();
			setcookie( 'mmb_ready', '1', time() + 30 * DAY_IN_SECONDS, '/', '', is_ssl(), true );
			wp_add_inline_script(
				'mrmurphy-theme',
				'window.MMB_CLIENT_ID=' . wp_json_encode( $client_token ) . ';',
				'before'
			);
		}
	}

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
