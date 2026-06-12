<?php
/**
 * Lightweight embed facades for microblog preview cards.
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
