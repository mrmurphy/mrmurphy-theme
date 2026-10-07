<?php
/**
 * Lightweight embed facades for microblog preview cards.
 *
 * Facades are provider-pluggable: each provider ships one registry entry
 * consumed by the render-time link-to-facade pass below (wired into
 * mrmurphy_get_microblog_preview_content() in inc/microblog.php).
 *
 * @package MrMurphy
 */

defined( 'ABSPATH' ) || exit;

/**
 * Whether embed facades should replace preview iframes.
 *
 * @param bool|null $set Optional. Set the flag.
 * @return bool
 */
function mrmurphy_in_microblog_preview( $set = null ) {
	static $in_preview = false;

	if ( null !== $set ) {
		$in_preview = (bool) $set;
	}

	return $in_preview;
}

/**
 * Extract a YouTube video ID from a URL.
 *
 * @param string $url Video URL.
 * @return string Empty string when not found.
 */
function mrmurphy_get_youtube_video_id( $url ) {
	if ( ! is_string( $url ) || '' === $url ) {
		return '';
	}

	$patterns = array(
		'#youtube\.com/shorts/([\w-]{11})#i',
		'#youtube\.com/embed/([\w-]{11})#i',
		'#youtube\.com/watch\?[^"\']*v=([\w-]{11})#i',
		'#youtu\.be/([\w-]{11})#i',
	);

	foreach ( $patterns as $pattern ) {
		if ( preg_match( $pattern, $url, $matches ) ) {
			return $matches[1];
		}
	}

	return '';
}

/**
 * Render a click-to-play YouTube facade for preview cards.
 *
 * @param string $video_id YouTube video ID.
 * @param array  $block    Block data.
 * @return string
 */
function mrmurphy_render_youtube_embed_facade( $video_id, $block ) {
	$embed_url = 'https://www.youtube.com/embed/' . rawurlencode( $video_id );
	$thumb_url = 'https://i.ytimg.com/vi/' . rawurlencode( $video_id ) . '/hqdefault.jpg';

	$classes = array(
		'wp-block-embed',
		'embed-facade',
		'is-type-video',
		'is-provider-youtube',
		'wp-block-embed-youtube',
	);

	if ( ! empty( $block['attrs']['className'] ) ) {
		$classes[] = $block['attrs']['className'];
	}

	$classes = array_merge( $classes, mrmurphy_get_embed_aspect_classes( $block ) );

	$figure_class = implode( ' ', array_map( 'sanitize_html_class', array_unique( $classes ) ) );

	ob_start();
	?>
	<figure class="<?php echo esc_attr( $figure_class ); ?>" data-embed-src="<?php echo esc_url( $embed_url ); ?>">
		<div class="wp-block-embed__wrapper embed-facade__frame">
			<button type="button" class="embed-facade__play" aria-label="<?php esc_attr_e( 'Play video', 'mrmurphy' ); ?>">
				<img
					class="embed-facade__thumb"
					src="<?php echo esc_url( $thumb_url ); ?>"
					alt=""
					loading="lazy"
					decoding="async"
					width="480"
					height="360"
				/>
				<span class="embed-facade__icon" aria-hidden="true"></span>
			</button>
		</div>
	</figure>
	<?php
	return (string) ob_get_clean();
}

/**
 * Guess the responsive embed aspect-ratio classes for a block.
 *
 * @param array $block Block data.
 * @return string[]
 */
function mrmurphy_get_embed_aspect_classes( $block ) {
	$url = $block['attrs']['url'] ?? '';

	if ( is_string( $url ) && false !== stripos( $url, '/shorts/' ) ) {
		return array( 'wp-embed-aspect-9-16', 'wp-has-aspect-ratio' );
	}

	return array( 'wp-embed-aspect-16-9', 'wp-has-aspect-ratio' );
}

/**
 * Fetch note data for a Substack note share URL.
 *
 * Server-side only, cached in a transient (6h) so repeated page renders do
 * not hammer Substack. Returns an array with any of:
 * - 'handle'    string  Author handle (from the URL, always present).
 * - 'note_id'   string  Note id (from the URL, always present).
 * - 'title'     string  og:title (author name) or ''.
 * - 'text'      string  og:description (note text excerpt) or ''.
 * - 'image_url' string  og:image (substackcdn poster/avatar) or ''.
 *
 * A network failure degrades to URL-derived fields only; the facade still
 * renders as a clean link-card (graceful fallback).
 *
 * @param string $url Note URL.
 * @return array
 */
function mrmurphy_fetch_substack_note_data( $url ) {
	$data = array(
		'handle'    => '',
		'note_id'   => '',
		'title'     => '',
		'text'      => '',
		'image_url' => '',
	);

	if ( preg_match( '#^https?://(?:www\.)?substack\.com/@([\w.-]+)/note/(c-[\w-]+)(?:[/?\#]|$)#i', (string) $url, $m ) ) {
		$data['handle']  = $m[1];
		$data['note_id'] = $m[2];
	}

	if ( '' === $data['handle'] ) {
		return $data;
	}

	if ( is_string( $data['note_id'] ) && false !== strpos( $data['note_id'], "'" ) ) {
		return $data; // Never happen with the regex above; safety net only.
	}

	$has_wp = function_exists( 'get_transient' ) && function_exists( 'set_transient' ) && function_exists( 'wp_remote_get' ) && null !== $data['handle'];
	if ( ! $has_wp ) {
		return $data;
	}

	$cache_key = 'mrmurphy_substack_' . md5( $data['handle'] . ':' . $data['note_id'] );
	$cached    = get_transient( $cache_key );
	if ( is_array( $cached ) ) {
		return $cached;
	}

	$response = wp_remote_get(
		"https://substack.com/@{$data['handle']}/note/{$data['note_id']}",
		array( 'timeout' => 5 )
	);

	if ( ! is_wp_error( $response ) && 200 === (int) wp_remote_retrieve_response_code( $response ) ) {
		$body = (string) wp_remote_retrieve_body( $response );

		foreach ( array(
			'title'     => '#<meta[^>]+property=["\']og:title["\'][^>]+content=["\']([^"\']+)["\']#i',
			'text'      => '#<meta[^>]+property=["\']og:description["\'][^>]+content=["\']([^"\']+)["\']#i',
			'image_url' => '#<meta[^>]+property=["\']og:image["\'][^>]+content=["\']([^"\']+)["\']#i',
		) as $field => $pattern ) {
			if ( preg_match( $pattern, $body, $m2 ) ) {
				$data[ $field ] = html_entity_decode( $m2[1], ENT_QUOTES | ENT_XML1, 'UTF-8' );
			}
		}
	}

	// Cache even the failure case so a down Substack doesn't cause per-render fetches.
	set_transient( $cache_key, $data, 6 * HOUR_IN_SECONDS );

	return $data;
}

/**
 * Render a Substack note quote-card facade for preview cards.
 *
 * A plain outbound link-card (avatar/poster, name, note text); engaging with
 * the note happens on Substack — there is no iframe embed for notes.
 *
 * @param string $url Note URL.
 * @return string
 */
function mrmurphy_render_substack_note_facade( $url ) {
	$note = mrmurphy_fetch_substack_note_data( $url );

	$name_clean = '' !== (string) $note['title'] ? $note['title'] : '@' . $note['handle'];
	$text_clean = wp_strip_all_tags( (string) $note['text'] );

	// Trim to a readable excerpt on a word boundary.
	if ( mb_strlen( $text_clean ) > 280 ) {
		$text_clean = rtrim( mb_substr( $text_clean, 0, 280 ) ) . '…';
	}

	$path = sprintf( 'https://substack.com/@%s/note/%s', rawurlencode( (string) $note['handle'] ), rawurlencode( (string) $note['note_id'] ) );

	ob_start();
	?>
	<figure class="wp-block-embed embed-facade embed-facade--substack is-provider-substack" data-embed-src="substack-note:<?php echo esc_attr( (string) $note['note_id'] ); ?>">
		<a class="embed-facade--substack__card" href="<?php echo esc_url( $path ); ?>" target="_blank" rel="noopener noreferrer nofollow">
			<?php if ( '' !== $note['image_url'] ) : ?>
				<span class="embed-facade--substack__poster" aria-hidden="true">
					<img src="<?php echo esc_url( $note['image_url'] ); ?>" alt="" loading="lazy" decoding="async" />
				</span>
			<?php endif; ?>
			<span class="embed-facade--substack__meta">
				<span class="embed-facade--substack__name"><?php echo esc_html( $name_clean ); ?></span>
				<?php if ( '' !== $text_clean ) : ?>
					<span class="embed-facade--substack__text"><?php echo esc_html( $text_clean ); ?></span>
				<?php endif; ?>
				<span class="embed-facade--substack__brand" aria-hidden="true">Substack note ↗</span>
			</span>
		</a>
	</figure>
	<?php
	return (string) ob_get_clean();
}
/**
 * Built-in embed provider registry.
 *
 * Each provider handler:
 * - 'match'  => callable( string $url ): ?array|null  Returns provider match
 *               data (anything truthy, e.g. array('video_id' => ...)) when the
 *               URL belongs to this provider, null/false otherwise.
 * - 'render' => callable( array|true $match, string $url ): string  Returns
 *               the facade HTML for the matched URL.
 *
 * Adding a future provider is a matter of pushing ONE entry via the
 * mrmurphy_embed_provider_registry filter, e.g. for a Bluesky facade:
 *
 *   add_filter( 'mrmurphy_embed_provider_registry', function ( $registry ) {
 *       $registry['bluesky'] = array(
 *           'match'  => function ( $url ) {
 *               if ( ! preg_match( '#^https?://bsky\.app/profile/[\w.-]+/post/(\w+)#i', $url, $m ) ) {
 *                   return null;
 *               }
 *               return array( 'post_id' => $m[1] );
 *           },
 *           'render' => function ( $match, $url ) {
 *               return mrmurphy_render_bluesky_facade( $match['post_id'], $url );
 *           },
 *       );
 *       return $registry;
 *   } );
 *
 * A Substack facade works the same way. Entries are keyed by provider slug;
 * later entries win on collision so hosts can override a built-in handler.
 *
 * @return array<string, array<string, callable>>
 */
function mrmurphy_get_embed_provider_registry() {
	static $cache = null;

	if ( null !== $cache ) {
		return $cache;
	}

	$youtube_render = static function ( array $match, $url ) {
		$block = array(
			'attrs' => array(
				'url' => $url,
			),
		);

		return mrmurphy_render_youtube_embed_facade( $match['video_id'], $block );
	};

	$registry = array(
		'youtube' => array(
			'match'  => static function ( $url ) {
				$video_id = mrmurphy_get_youtube_video_id( $url );
				if ( ! $video_id ) {
					return null;
				}

				return array(
					'video_id'  => $video_id,
					'is_shorts' => (bool) preg_match( '#youtube\.com/shorts/#i', (string) $url ),
				);
			},
			'render' => $youtube_render,
		),
		'substack' => array(
			'match'  => static function ( $url ) {
				if ( ! preg_match( '#^https?://(?:www\.)?substack\.com/@([\w.-]+)/note/(c-[\w-]+)(?:[/?\#]|$)#i', (string) $url, $m ) ) {
					return null;
				}

				return array(
					'handle'  => $m[1],
					'note_id' => $m[2],
				);
			},
			'render' => static function ( array $match, $url ) {
				return mrmurphy_render_substack_note_facade( $url );
			},
		),
	);

	/**
	 * Filter the embed provider registry.
	 *
	 * See mrmurphy_get_embed_provider_registry() for the entry shape a new
	 * provider must push (a 'match' and a 'render' callable).
	 *
	 * @param array<string, array<string, callable>> $registry Provider handlers keyed by slug.
	 */
	$registry = (array) apply_filters( 'mrmurphy_embed_provider_registry', $registry );

	$cache = array();
	foreach ( $registry as $slug => $handler ) {
		if ( ! is_string( $slug ) || '' === $slug || ! is_array( $handler ) ) {
			continue;
		}

		if ( ! isset( $handler['match'], $handler['render'] ) || ! is_callable( $handler['match'] ) || ! is_callable( $handler['render'] ) ) {
			continue;
		}

		$cache[ $slug ] = $handler;
	}

	return $cache;
}

/**
 * Try to match a URL against the provider registry.
 *
 * @param string $url URL to test.
 * @return array{slug: string, url: string, match: array|true}|null Null when no provider matches.
 */
function mrmurphy_match_embed_provider( $url ) {
	if ( ! is_string( $url ) || '' === $url ) {
		return null;
	}

	foreach ( mrmurphy_get_embed_provider_registry() as $slug => $handler ) {
		$match = call_user_func( $handler['match'], $url );
		if ( $match ) {
			return array(
				'slug'  => $slug,
				'url'   => $url,
				'match' => is_array( $match ) ? $match : true,
			);
		}
	}

	return null;
}

/**
 * Render a provider facade from registry match data.
 *
 * @param array $result Match result from mrmurphy_match_embed_provider().
 * @return string
 */
function mrmurphy_render_embed_provider_facade( $result ) {
	if ( ! $result ) {
		return '';
	}

	$registry = mrmurphy_get_embed_provider_registry();
	$handler  = $registry[ $result['slug'] ] ?? null;

	if ( ! $handler ) {
		return '';
	}

	return (string) call_user_func( $handler['render'], $result['match'], $result['url'] );
}

/**
 * Whether an <a> element is a bare link (its text is the URL itself, modulo scheme/www).
 *
 * @param DOMElement $link Link element.
 * @return bool
 */
function mrmurphy_is_bare_provider_link( DOMElement $link ) {
	$text = trim( (string) $link->textContent );

	if ( '' === $text ) {
		return false;
	}

	// A bare link wraps only the URL itself: no child elements allowed, so
	// links wrapping real anchor text or an <img> stay links.
	if ( $link->childNodes->length > 1 ) {
		return false;
	}

	if ( $link->firstChild instanceof DOMElement ) {
		return false;
	}

	$href = trim( (string) $link->getAttribute( 'href' ) );

	// Compare modulo scheme, leading www., and trailing punctuation/slashes.
	$normalize = static function ( $value ) {
		$value = trim( (string) $value, " \t\n\r\x0B\x2F" );
		$value = preg_replace( '#^(https?:(?=//))?(//(www\.)?)?#i', '', $value );
		$value = preg_replace( '#[)\]}>.,;:!?"]+$#', '', (string) $value );

		return mb_strtolower( (string) $value );
	};

	return $normalize( $text ) !== '' && $normalize( $text ) === $normalize( $href );
}

/**
 * Rewrite bare provider links into embed facades inside rendered preview HTML.
 *
 * Render-time only: never touches stored post_content. Uses DOMDocument when
 * available and returns the input unchanged when it is missing or fails.
 *
 * @param string $content Rendered preview HTML.
 * @return string
 */
function mrmurphy_facade_links_in_preview_content( $content ) {
	if ( ! is_string( $content ) || '' === trim( $content ) ) {
		return (string) $content;
	}

	if ( ! mrmurphy_in_microblog_preview() || false === stripos( $content, '<a ' ) ) {
		return $content;
	}

	if ( ! class_exists( 'DOMDocument' ) || ! class_exists( 'DOMXPath' ) ) {
		return $content;
	}

	$document            = new DOMDocument();
	$previous_libxml_use = null;

	if ( function_exists( 'libxml_use_internal_errors' ) ) {
		$previous_libxml_use = libxml_use_internal_errors( true );
	}

	$wrapped = '<?xml encoding="utf-8"?><!DOCTYPE html><html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"></head><body>' . $content . '</body></html>';

	$loaded = $document->loadHTML( $wrapped, LIBXML_NONET | ( defined( 'LIBXML_HTML_NOIMPLIED' ) ? LIBXML_HTML_NOIMPLIED : 0 ) | ( defined( 'LIBXML_HTML_NODEFDTD' ) ? LIBXML_HTML_NODEFDTD : 0 ) );

	if ( function_exists( 'libxml_clear_errors' ) ) {
		libxml_clear_errors();
	}

	if ( null !== $previous_libxml_use && function_exists( 'libxml_use_internal_errors' ) ) {
		libxml_use_internal_errors( $previous_libxml_use );
	}

	if ( ! $loaded ) {
		return $content;
	}

	$xpath    = new DOMXPath( $document );
	$nodes    = iterator_to_array( $xpath->query( '//a[@href]' ) ?: array() );
	$replaced = false;

	foreach ( $nodes as $node ) {
		if ( ! $node instanceof DOMElement || ! mrmurphy_is_bare_provider_link( $node ) ) {
			continue;
		}

		$result = mrmurphy_match_embed_provider( trim( (string) $node->getAttribute( 'href' ) ) );
		if ( ! $result ) {
			continue;
		}

		$facade = mrmurphy_render_embed_provider_facade( $result );
		if ( '' === trim( $facade ) ) {
			continue;
		}

		$fragment = $document->createDocumentFragment();
		$fragment->appendXML( $facade );
		if ( $fragment->firstElementChild instanceof DOMElement ) {
			$node->parentNode->replaceChild( $fragment, $node );
			$replaced = true;
		}
	}

	if ( ! $replaced ) {
		return $content;
	}

	// Splice out just the body's inner HTML so wrapper tags we added stay out.
	$html = '';
	$body = $document->getElementsByTagName( 'body' )->item( 0 );
	if ( $body instanceof DOMElement ) {
		foreach ( $body->childNodes as $child ) {
			$html .= $document->saveHTML( $child );
		}
	} else {
		$html = (string) $document->saveHTML();
	}

	return (string) $html;
}

/**
 * Replace YouTube embed blocks with facades inside microblog previews.
 *
 * @param string|null $pre_render Pre-rendered content.
 * @param array       $block      Block data.
 * @return string|null
 */
function mrmurphy_facade_embed_in_microblog_preview( $pre_render, $block ) {
	if ( null !== $pre_render || ! mrmurphy_in_microblog_preview() ) {
		return $pre_render;
	}

	if ( ( $block['blockName'] ?? '' ) !== 'core/embed' ) {
		return $pre_render;
	}

	$provider = $block['attrs']['providerNameSlug'] ?? '';
	if ( 'youtube' !== $provider ) {
		return $pre_render;
	}

	$video_id = mrmurphy_get_youtube_video_id( $block['attrs']['url'] ?? '' );
	if ( ! $video_id ) {
		return $pre_render;
	}

	return mrmurphy_render_youtube_embed_facade( $video_id, $block );
}
add_filter( 'pre_render_block', 'mrmurphy_facade_embed_in_microblog_preview', 10, 2 );
