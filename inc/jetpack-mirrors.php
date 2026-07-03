<?php
/**
 * Jetpack Publicize mirror-link helper for the reblog dialog.
 *
 * Reads `_publicize_shares` post meta (written by Jetpack Publicize after a
 * post is autopublished to a connected social network) and surfaces any
 * destination-platform URLs that are actually publicly viewable.
 *
 * Publicize's schema is `connection_id`, `external_id`, `service`, `message`,
 * `timestamp`. None of those is reliably the destination-platform URL of the
 * shared copy — Publicize records the *fact* of a share, not where the copy
 * lives. So this helper is deliberately conservative: a row is only emitted
 * when the share record carries a URL we can verify points somewhere offsite.
 *
 * When nothing usable is present the helper returns an empty array and the
 * share dialog falls back to its intent-URL top block only.
 *
 * @package MrMurphy
 */

defined( 'ABSPATH' ) || exit;

/**
 * Map a Jetpack Publicize service slug to a friendly platform name.
 *
 * @param string $service Service slug from Publicize meta.
 * @return string Human-readable platform label.
 */
function mrmurphy_jetpack_service_label( $service ) {
	$labels = array(
		'twitter'         => __( 'X (Twitter)', 'mrmurphy' ),
		'facebook'        => __( 'Facebook', 'mrmurphy' ),
		'linkedin'        => __( 'LinkedIn', 'mrmurphy' ),
		'tumblr'          => __( 'Tumblr', 'mrmurphy' ),
		'mastodon'        => __( 'Mastodon', 'mrmurphy' ),
		'bluesky'         => __( 'Bluesky', 'mrmurphy' ),
		'threads'         => __( 'Threads', 'mrmurphy' ),
		'instagram-business' => __( 'Instagram', 'mrmurphy' ),
		'nextdoor'        => __( 'Nextdoor', 'mrmurphy' ),
	);

	return $labels[ $service ] ?? ucwords( str_replace( array( '-', '_' ), ' ', $service ) );
}

/**
 * Get publicly-viewable mirror links for a post, sourced from Jetpack
 * Publicize's `_publicize_shares` post meta.
 *
 * Returns an array of `[ 'platform' => label, 'url' => url ]` rows, or an
 * empty array when no usable mirror URL is recorded (the common case).
 *
 * @param int $post_id Post ID.
 * @return array<int, array{platform: string, url: string}>
 */
function mrmurphy_get_jetpack_publicize_mirrors( $post_id ) {
	$post_id = absint( $post_id );
	if ( ! $post_id ) {
		return array();
	}

	if ( ! metadata_exists( 'post', $post_id, '_publicize_shares' ) ) {
		return array();
	}

	$shares = get_post_meta( $post_id, '_publicize_shares', true );
	if ( ! is_array( $shares ) ) {
		$shares = get_post_meta( $post_id, '_publicize_shares', false );
		if ( ! is_array( $shares ) ) {
			return array();
		}
	}

	$home_host = wp_parse_url( home_url(), PHP_URL_HOST );
	$out      = array();
	$seen     = array();

	foreach ( $shares as $share ) {
		if ( ! is_array( $share ) ) {
			continue;
		}

		$service = isset( $share['service'] ) ? sanitize_text_field( $share['service'] ) : '';
		if ( '' === $service ) {
			continue;
		}

		// The Publicize schema doesn't have a dedicated `url` field; `message`
		// and `external_id` occasionally carry one depending on the service.
		// Treat any http(s) value that points offsite as a candidate URL.
		$candidates = array( $share['message'] ?? '', $share['url'] ?? '' );
		$url        = '';

		foreach ( $candidates as $candidate ) {
			$candidate = trim( (string) $candidate );
			if ( '' === $candidate ) {
				continue;
			}
			if ( ! preg_match( '#^https?://#i', $candidate ) ) {
				continue;
			}
			$host = wp_parse_url( $candidate, PHP_URL_HOST );
			if ( ! $host || $host === $home_host ) {
				continue;
			}
			$url = esc_url_raw( $candidate );
			break;
		}

		if ( '' === $url ) {
			continue;
		}

		$key = $service . '|' . $url;
		if ( isset( $seen[ $key ] ) ) {
			continue;
		}
		$seen[ $key ] = true;

		$out[] = array(
			'platform' => mrmurphy_jetpack_service_label( $service ),
			'url'      => $url,
		);
	}

	return $out;
}