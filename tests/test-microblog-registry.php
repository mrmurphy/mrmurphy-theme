<?php
/**
 * Registry extension test: a NEW provider (Bluesky stub) via ONE filter entry,
 * verified through the full bare-link pass.
 */
require __DIR__ . '/wp-stubs.php';
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __DIR__ ) . '/' );
}

require __DIR__ . '/../inc/microblog-embeds.php';

// FUTURE PROVIDER (fake Bluesky facade): one registry entry, no core edits.
add_filter( 'mrmurphy_embed_provider_registry', function ( $registry ) {
	$registry['bluesky-test'] = array(
		'match' => function ( $url ) {
			if ( ! preg_match( '#^https?://example\.com/post/(\w+)#i', $url, $m ) ) {
				return null;
			}
			return array( 'post_id' => $m[1] );
		},
		'render' => function ( $match, $url ) {
			return '<figure class="embed-facade is-provider-bluesky-test" data-embed-src="https://example.com/embed/' . $match['post_id'] . '"><div class="embed-facade__frame"></div></figure>';
		},
	);
	return $registry;
} );

mrmurphy_in_microblog_preview( true );

$failures = 0;
function check( $name, $condition ) {
	global $failures;
	echo ( $condition ? "  ok: " : "  FAIL: " ) . $name . "\n";
	if ( ! $condition ) {
		$failures++;
	}
}

$result = mrmurphy_match_embed_provider( 'https://example.com/post/3kx2' );
check( 'new provider matches via registry', $result && 'bluesky-test' === $result['slug'] && '3kx2' === $result['match']['post_id'] );

$facade = mrmurphy_render_embed_provider_facade( $result );
check( 'new provider renders facade HTML', false !== strpos( $facade, 'data-embed-src="https://example.com/embed/3kx2"' ) );

$out = mrmurphy_facade_links_in_preview_content( '<p><a href="https://example.com/post/3kx2" rel="nofollow">https://example.com/post/3kx2</a></p>' );
check( 'new provider bare link converted', false !== strpos( $out, 'is-provider-bluesky-test' ) && false === strpos( $out, '<a ' ) );

$out2 = mrmurphy_facade_links_in_preview_content( '<p><a href="https://youtube.com/watch?v=R_ErmTs7g9I" rel="nofollow">https://youtube.com/watch?v=R_ErmTs7g9I</a></p>' );
check( 'youtube still works alongside new provider', false !== strpos( $out2, 'data-embed-src="https://www.youtube.com/embed/R_ErmTs7g9I"' ) );

mrmurphy_in_microblog_preview( false );

echo $failures ? "\n{$failures} failure(s)\n" : "\nAll registry-extension tests passed\n";
exit( $failures ? 1 : 0 );
