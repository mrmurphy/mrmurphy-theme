<?php
/**
 * End-to-end-ish test through the REAL mrmurphy_get_microblog_preview_content().
 */
require __DIR__ . '/wp-stubs.php';
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __DIR__ ) . '/' );
}

// Simulate imported post content: bare youtube link inside a paragraph.
$post_content = "<!-- wp:paragraph --><p>check this out <a href=\"https://www.youtube.com/watch?v=R_ErmTs7g9I\" rel=\"nofollow\">https://www.youtube.com/watch?v=R_ErmTs7g9I</a> wild</p><!-- /wp:paragraph -->\n\n<!-- wp:paragraph --><p>and shorts: <a href=\"https://www.youtube.com/shorts/AbCdEfGhIjK\" rel=\"nofollow\">https://www.youtube.com/shorts/AbCdEfGhIjK</a></p><!-- /wp:paragraph -->";

$GLOBALS['mrmurphy_test_post_content'] = '';

function get_the_ID() {
	return 1484;
}
function get_post_field( $field, $post_id, $context = 'display' ) {
	return $GLOBALS['mrmurphy_test_post_content'];
}
function wp_strip_all_tags( $string, $remove_breaks = false ) {
	$string = preg_replace( '@<(script|style)[^>]*?>.*?</\\1>@si', '', (string) $string );
	return trim( strip_tags( (string) $string ) );
}
// the_content filter chain is absent in the stub env; emulate the render core would do:
if ( ! function_exists( 'apply_filters' ) ) {
	function apply_filters( $tag, $value ) {
		if ( 'the_content' === $tag ) {
			// Minimal stand-in for core paragraph/block rendering + wpautop on classic content.
			$value = preg_replace( '/<!-- \\w[^>]*-->\\s*/', '', (string) $value );

			return $value;
		}
		return $value;
	}
}

$GLOBALS['mrmurphy_test_post_content'] = $post_content;

require __DIR__ . '/../inc/microblog-embeds.php';
require __DIR__ . '/../inc/microblog.php';

$out = mrmurphy_get_microblog_preview_content( 1484 );

$failures = 0;
function check( $name, $condition ) {
	global $failures;
	echo ( $condition ? "  ok: " : "  FAIL: " ) . $name . "\n";
	if ( ! $condition ) {
		$failures++;
	}
}

check( 'bare watch link became facade', false !== strpos( $out, 'data-embed-src="https://www.youtube.com/embed/R_ErmTs7g9I"' ) );
check( 'shorts link became facade with 9-16 aspect', false !== strpos( $out, 'wp-embed-aspect-9-16' ) );
check( 'no <a> youtube links remain', false === strpos( $out, 'href="https://www.youtube.com' ) );
check( 'paragraph text preserved', false !== strpos( $out, 'check this out' ) && false !== strpos( $out, 'wild' ) );
check( 'flag reset after run', false === mrmurphy_in_microblog_preview() );

echo "--- rendered preview ---\n{$out}\n--- end ---\n";
echo $failures ? "\n{$failures} failure(s)\n" : "\nAll e2e tests passed\n";
exit( $failures ? 1 : 0 );
