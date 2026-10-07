<?php
/**
 * Minimal wp-env-free tests for the embed provider registry + bare-link pass.
 *
 * Run: php tests/test-microblog-embeds.php
 *
 * @package MrMurphy
 */

$wp_stub_root = dirname( __DIR__ );
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', $wp_stub_root . '/' );
}
define( 'WPINC', 'wp-includes' );

require __DIR__ . '/wp-stubs.php';
require dirname( __DIR__ ) . '/inc/microblog-embeds.php';
require dirname( __DIR__ ) . '/inc/microblog.php';

$failures = 0;

function check( $name, $condition ) {
	global $failures;
	if ( $condition ) {
		echo "  ok: {$name}\n";
	} else {
		echo "  FAIL: {$name}\n";
		$failures++;
	}
}

// 1. Registry + matching -----------------------------------------------------

$cases = array(
	'https://www.youtube.com/watch?v=R_ErmTs7g9I'       => 'R_ErmTs7g9I',
	'https://youtu.be/R_ErmTs7g9I'                      => 'R_ErmTs7g9I',
	'https://www.youtube.com/shorts/AbCdEfGhIjK'        => 'AbCdEfGhIjK',
	'https://www.youtube.com/embed/R_ErmTs7g9I?si=x'    => 'R_ErmTs7g9I',
);

foreach ( $cases as $url => $expected_id ) {
	$result = mrmurphy_match_embed_provider( $url );
	$is_shorts = false !== stripos( $url, '/shorts/' );
	check(
		"registry matches {$url}",
		$result && 'youtube' === $result['slug'] && $result['match']['video_id'] === $expected_id
		&& ( ! $is_shorts || ! empty( $result['match']['is_shorts'] ) )
	);
}

check( 'registry does NOT match non-youtube', null === mrmurphy_match_embed_provider( 'https://example.com/watch?v=x' ) );
check( 'empty url safe', null === mrmurphy_match_embed_provider( '' ) );

$facade = trim( mrmurphy_render_embed_provider_facade( mrmurphy_match_embed_provider( 'https://youtu.be/R_ErmTs7g9I' ) ) );
check( 'facade HTML from registry render', 0 === strpos( $facade, '<figure class="wp-block-embed embed-facade' )
	&& false !== strpos( $facade, 'data-embed-src="https://www.youtube.com/embed/R_ErmTs7g9I' )
	&& false !== strpos( $facade, 'wp-embed-aspect-16-9' ) );

$shorts_facade = mrmurphy_render_embed_provider_facade( mrmurphy_match_embed_provider( 'https://www.youtube.com/shorts/AbCdEfGhIjK' ) );
check( 'shorts facade gets 9-16 aspect', false !== strpos( $shorts_facade, 'wp-embed-aspect-9-16' ) );

// 2. Bare-link detection ------------------------------------------------------

$xml = '<?xml encoding="utf-8"?><body>%s</body>';
$bare_dom = new DOMDocument();
$bare_dom->loadHTML( sprintf( $xml, '<a href="https://www.youtube.com/watch?v=R_ErmTs7g9I">https://www.youtube.com/watch?v=R_ErmTs7g9I</a>' ) );
check( 'bare link (identical text) detected', mrmurphy_is_bare_provider_link( $bare_dom->getElementsByTagName( 'a' )->item( 0 ) ) );

$labeled_dom = new DOMDocument();
$labeled_dom->loadHTML( sprintf( $xml, '<a href="https://www.youtube.com/watch?v=R_ErmTs7g9I">watch my video here</a>' ) );
check( 'anchor-text link NOT bare', ! mrmurphy_is_bare_provider_link( $labeled_dom->getElementsByTagName( 'a' )->item( 0 ) ) );

$img_dom = new DOMDocument();
$img_dom->loadHTML( sprintf( $xml, '<a href="https://youtu.be/R_ErmTs7g9I"><img src="t.jpg" alt="" /></a>' ) );
check( 'link wrapping <img> NOT bare', ! mrmurphy_is_bare_provider_link( $img_dom->getElementsByTagName( 'a' )->item( 0 ) ) );

$punct_dom = new DOMDocument();
$punct_dom->loadHTML( sprintf( $xml, '<a href="https://youtu.be/R_ErmTs7g9I">https://youtu.be/R_ErmTs7g9I.</a>' ) );
check( 'bare link with trailing punctuation still bare', mrmurphy_is_bare_provider_link( $punct_dom->getElementsByTagName( 'a' )->item( 0 ) ) );

// 3. Full pass over rendered HTML (preview flag ON) ---------------------------

mrmurphy_in_microblog_preview( true );

$content = '<p>check this: <a href="https://www.youtube.com/watch?v=R_ErmTs7g9I" rel="nofollow">https://www.youtube.com/watch?v=R_ErmTs7g9I</a> fun!</p>';
$out = mrmurphy_facade_links_in_preview_content( $content );
check( 'bare youtube link converted to facade', false !== strpos( $out, 'class="wp-block-embed embed-facade' ) && false !== strpos( $out, 'data-embed-src' ) );
check( 'surrounding text preserved', false !== strpos( $out, 'check this:' ) && false !== strpos( $out, 'fun!' ) );

$out2 = mrmurphy_facade_links_in_preview_content(
	'<p><a href="https://www.youtube.com/watch?v=R_ErmTs7g9I" rel="nofollow">watch it here</a></p>'
);
check( 'non-bare link left untouched', false !== strpos( $out2, 'watch it here' ) && false === strpos( $out2, 'embed-facade' ) );

$out3 = mrmurphy_facade_links_in_preview_content(
	'<p><a href="https://example.com/foo" rel="nofollow">https://example.com/foo</a></p>'
);
check( 'non-provider bare link untouched', false !== strpos( $out3, 'https://example.com/foo' ) && false === strpos( $out3, 'embed-facade' ) );

$out4 = mrmurphy_facade_links_in_preview_content( '' );
check( 'empty content safe', '' === $out4 );
$out5 = mrmurphy_facade_links_in_preview_content( "   \n\t " );
check( 'whitespace content safe', '' === trim( $out5 ) );

// 4. No preview flag -> no transformation -------------------------------------

mrmurphy_in_microblog_preview( false );
$out6 = mrmurphy_facade_links_in_preview_content( $content );
check( 'outside preview flag content unchanged', $out6 === $content );

echo $failures ? "\n{$failures} failure(s)\n" : "\nAll tests passed\n";
exit( $failures ? 1 : 0 );
