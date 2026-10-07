<?php
/**
 * Substack note facade tests: registry match, quote-card render, bare-link
 * pass, graceful fallback without HTTP, and YouTube coexistence.
 *
 * Run: php tests/test-microblog-substack.php
 *
 * @package MrMurphy
 */

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __DIR__ ) . '/' );
}

require __DIR__ . '/wp-stubs.php';
require dirname( __DIR__ ) . '/inc/microblog-embeds.php';

$failures = 0;

function check( $name, $condition ) {
	global $failures;
	echo ( $condition ? '  ok: ' : '  FAIL: ' ) . $name . "\n";
	if ( ! $condition ) {
		$failures++;
	}
}

$note_url = 'https://substack.com/@justindeschamps/note/c-326587767';

// 1. Registry matching -------------------------------------------------------

$result = mrmurphy_match_embed_provider( $note_url );
check(
	'registry matches substack note url',
	$result && 'substack' === $result['slug'] && 'justindeschamps' === $result['match']['handle'] && 'c-326587767' === $result['match']['note_id']
);

check( 'www variant matches', null !== mrmurphy_match_embed_provider( 'https://www.substack.com/@justindeschamps/note/c-326587767' ) );
check( 'non-note substack url NOT matched', null === mrmurphy_match_embed_provider( 'https://substack.com/@someone/post/abc' ) );
check( 'other domains NOT matched', null === mrmurphy_match_embed_provider( 'https://example.com/@someone/note/c-1' ) );

// 2. Graceful fallback: no HTTP available (wp_remote_get returns 404 body) ---

// A fresh note id so the shared transient store has no cache entry.
$fresh_url = 'https://substack.com/@fallbackuser/note/c-999';
$facade    = mrmurphy_render_substack_note_facade( $fresh_url );
check( 'fallback facade renders figure', false !== strpos( $facade, '<figure class="wp-block-embed embed-facade embed-facade--substack is-provider-substack"' ) );
check( 'fallback name falls back to handle', false !== strpos( $facade, '@fallbackuser' ) );
check( 'fallback omits poster img', false === strpos( $facade, '<img' ) );
check( 'fallback links out to note', false !== strpos( $facade, 'href="https://substack.com/@fallbackuser/note/c-999"' ) );
check( 'fallback sets target=_blank', false !== strpos( $facade, 'target="_blank"' ) );
check( 'data-embed-src present (non-iframe marker)', false !== strpos( $facade, 'data-embed-src="substack-note:c-999"' ) );

// Fallback result was cached; render again and confirm same shape.
$facade2 = mrmurphy_render_substack_note_facade( $fresh_url );
check( 'cached fallback render stable', $facade2 === $facade );

// 3. Full SSR-driven render (mocked wp_remote_get through globals) ----------

$GLOBALS['__test_http_response'] = array(
	'response' => array( 'code' => 200 ),
	'body'     => '<html><head>'
		. '<meta property="og:title" content="Justin Deschamps" />'
		. '<meta property="og:description" content="Tomorrow, October 7th at 3pm ET, I will be discussing Health &amp; Wellness" />'
		. '<meta property="og:image" content="https://substackcdn.com/image/fetch/xyz.jpg" />'
		. '</head><body></body></html>',
);

// Different id again so nothing is cached yet.
$served_res = mrmurphy_render_substack_note_facade( 'https://substack.com/@justindeschamps/note/c-111' );
check( 'og:title used as name', false !== strpos( $served_res, 'Justin Deschamps' ) );
check( 'og:description used as text (entity decoded)', false !== strpos( $served_res, 'Health &amp; Wellness' ) );
check( 'og:image used as poster', false !== strpos( $served_res, 'https://substackcdn.com/image/fetch/xyz.jpg' ) );

// Long text truncation.
$GLOBALS['__test_http_response'] = array(
	'response' => array( 'code' => 200 ),
	'body'     => '<html><head><meta property="og:title" content="A Name" /><meta property="og:description" content="' . str_repeat( 'word ', 120 ) . '" /></head><body></body></html>',
);

$long_res = mrmurphy_render_substack_note_facade( 'https://substack.com/@justindeschamps/note/c-222' );
check( 'long note text truncated with ellipsis', false !== strpos( $long_res, '…' ) );



mrmurphy_in_microblog_preview( true ); // ON

$out = mrmurphy_facade_links_in_preview_content(
	'<p>see this: <a href="https://substack.com/@fallbackuser/note/c-999" rel="nofollow">https://substack.com/@fallbackuser/note/c-999</a></p>'
);
check( 'bare substack link converted in preview', false === strpos( $out, '<a href="https://substack.com' ) );
check( 'bare substack link converted facade present', false !== strpos( $out, 'is-provider-substack' ) );
check( 'conversion is click-through card not iframe', false === strpos( $out, '<iframe' ) );

$out2 = mrmurphy_facade_links_in_preview_content(
	'<p><a href="https://substack.com/@fallbackuser/note/c-999" rel="nofollow">read the note here</a></p>'
);
check( 'anchor-text substack link untouched', false !== strpos( $out2, 'read the note here' ) && false === strpos( $out2, 'embed-facade' ) );

mrmurphy_in_microblog_preview( false );
check( 'outside preview flag content unchanged', mrmurphy_facade_links_in_preview_content( $out2 ) === $out2 );

// 5. YouTube coexistence ------------------------------------------------------

mrmurphy_in_microblog_preview( true ); // back ON after the outside-preview check above

$out3 = mrmurphy_facade_links_in_preview_content(
	'<p><a href="https://www.youtube.com/watch?v=R_ErmTs7g9I" rel="nofollow">https://www.youtube.com/watch?v=R_ErmTs7g9I</a></p>'
);
check( 'youtube still works alongside substack', false !== strpos( $out3, 'wp-block-embed-youtube' ) );

// 6. Existing core/embed pass untouched: substack not a core provider --------

check( 'core-embed non-youtube untouched', null === mrmurphy_facade_embed_in_microblog_preview( null, array( 'blockName' => 'core/embed', 'attrs' => array( 'providerNameSlug' => 'wordpress', 'url' => 'https://wordpress.org' ) ) ) );

echo $failures ? "\n{$failures} failure(s)\n" : "\nAll substack tests passed\n";
exit( $failures ? 1 : 0 );
