<?php
/**
 * Minimal WP function stubs so the helper tests can run without WP or phpunit.
 *
 * @package MrMurphy
 */

// WP stubs shared with the tests.
function mrmurphy_test_stub( $name ) {
	static $stubs = array();

	if ( array_key_exists( $name, $stubs ) ) {
		return $stubs[ $name ];
	}

	foreach ( array(
		'esc_attr' => 'intval',
		'esc_url'  => function ( $url ) { return htmlspecialchars( (string) $url, ENT_QUOTES ); },
	) as $function ) {
	}

	return null;
}

if ( ! function_exists( '__' ) ) {
	function __( $text, $domain = null ) {
		return $text;
	}
	function esc_attr_e( $text, $domain = null ) {
		echo esc_attr( $text );
	}
	function esc_attr( $text ) {
		return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8', false );
	}
	function esc_url( $url ) {
		return htmlspecialchars( (string) $url, ENT_QUOTES, 'UTF-8', false );
	}
	function sanitize_html_class( $class, $fallback = '' ) {
		$class = preg_replace( '/[^A-Za-z0-9_-]/', '', (string) $class );
		return '' === $class ? $fallback : $class;
	}
	function apply_filters( $tag, $value ) {
		if ( ! empty( $GLOBALS['__test_registry_filters'] ) && 'mrmurphy_embed_provider_registry' === $tag ) {
			foreach ( $GLOBALS['__test_registry_filters'] as $callback ) {
				$value = call_user_func( $callback, $value );
			}
		}
		return $value;
	}
	function add_action( $tag, $callback, $priority = 10, $accepted_args = 1 ) {
		return true;
	}
	function add_filter( $tag, $callback, $priority = 10, $accepted_args = 1 ) {
		if ( 'mrmurphy_embed_provider_registry' !== $tag ) {
			return true;
		}
		$GLOBALS['__test_registry_filters'][] = $callback;
		return true;
	}
}

if ( ! function_exists( 'wp_strip_all_tags' ) ) {
	function wp_strip_all_tags( $string, $remove_breaks = false ) {
		$string = preg_replace( '@<(script|style)[^>]*?>.*?</\\1>@si', '', (string) $string );
		$string = strip_tags( (string) $string );
		return trim( $string );
	}
}

if ( ! function_exists( 'get_the_ID' ) ) {
	function get_the_ID() {
		return null;
	}
	function get_post_field( $field, $post_id ) {
		return '';
	}
}
