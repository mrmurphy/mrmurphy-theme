<?php
/**
 * Performance optimizations for main-thread work reduction.
 *
 * @package MrMurphy
 */

defined( 'ABSPATH' ) || exit;

/**
 * Skip Jetpack's Google Fonts catalog on the frontend.
 *
 * Jetpack registers ~60 font presets into global-styles-inline-css. This theme
 * only uses self-hosted JetBrains Mono, so the catalog is unused CSS weight.
 *
 * @param array|null $pre Font data, or null to use the default Jetpack fetch.
 * @return array|null
 */
function mrmurphy_skip_jetpack_google_fonts( $pre ) {
	if ( is_admin() ) {
		return $pre;
	}

	return array( 'fontFamilies' => array() );
}
add_filter( 'pre_jetpack_get_google_fonts_data', 'mrmurphy_skip_jetpack_google_fonts' );

/**
 * Strip any remaining default font-family presets from frontend global styles.
 *
 * @param WP_Theme_JSON_Data $theme_json Theme JSON data object.
 * @return WP_Theme_JSON_Data
 */
function mrmurphy_strip_default_font_presets( $theme_json ) {
	if ( is_admin() ) {
		return $theme_json;
	}

	$data = $theme_json->get_data();

	if ( isset( $data['settings']['typography']['fontFamilies']['default'] ) ) {
		$data['settings']['typography']['fontFamilies']['default'] = array();
	}

	return new WP_Theme_JSON_Data( $data, 'default' );
}
add_filter( 'wp_theme_json_data_default', 'mrmurphy_strip_default_font_presets', 999 );

/**
 * Remove WordPress emoji scripts and styles (unnecessary on a developer blog).
 */
function mrmurphy_disable_emojis() {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
	remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
}
add_action( 'init', 'mrmurphy_disable_emojis' );

/**
 * Dequeue plugin assets that are not needed on the current view.
 */
function mrmurphy_dequeue_unnecessary_assets() {
	if ( ! is_singular() || ! comments_open() ) {
		wp_dequeue_script( 'akismet-frontend' );
	}

	if ( ! is_singular( 'post' ) ) {
		wp_dequeue_style( 'mrmurphy-authorship' );
		wp_dequeue_script( 'mrmurphy-authorship' );
	}
}
add_action( 'wp_enqueue_scripts', 'mrmurphy_dequeue_unnecessary_assets', 999 );

/**
 * Add defer to non-critical theme scripts.
 *
 * @param string $tag    Script tag HTML.
 * @param string $handle Script handle.
 * @param string $src    Script URL.
 * @return string
 */
function mrmurphy_defer_theme_scripts( $tag, $handle, $src ) {
	$defer_handles = array(
		'mrmurphy-theme',
		'mrmurphy-authorship',
	);

	if ( in_array( $handle, $defer_handles, true ) && false === strpos( $tag, ' defer' ) ) {
		$tag = str_replace( ' src', ' defer src', $tag );
	}

	return $tag;
}
add_filter( 'script_loader_tag', 'mrmurphy_defer_theme_scripts', 10, 3 );

/**
 * Preload the self-hosted font to reduce layout shift after CSS parse.
 */
function mrmurphy_preload_font() {
	$font_url = MRMURPHY_URI . '/assets/fonts/jetbrains-mono-latin.woff2';
	echo '<link rel="preload" href="' . esc_url( $font_url ) . '" as="font" type="font/woff2" crossorigin>' . "\n";
}
add_action( 'wp_head', 'mrmurphy_preload_font', 1 );
