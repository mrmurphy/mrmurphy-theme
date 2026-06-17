<?php
/**
 * Microblog preview card rendering (theme presentation).
 *
 * @package MrMurphy
 */

defined( 'ABSPATH' ) || exit;

/**
 * Plain-text preview for microblog cards.
 *
 * @param int|null $post_id Post ID.
 * @return string
 */
function mrmurphy_get_microblog_preview_text( $post_id = null ) {
	if ( ! $post_id ) {
		$post_id = get_the_ID();
	}

	if ( ! $post_id ) {
		return '';
	}

	$content = get_post_field( 'post_content', $post_id );

	return trim( wp_strip_all_tags( (string) $content ) );
}

/**
 * Full rendered content for microblog preview cards.
 *
 * @param int|null $post_id Post ID.
 * @return string
 */
function mrmurphy_get_microblog_preview_content( $post_id = null ) {
	if ( ! $post_id ) {
		$post_id = get_the_ID();
	}

	if ( ! $post_id ) {
		return '';
	}

	$content = get_post_field( 'post_content', $post_id );

	mrmurphy_in_microblog_preview( true );
	$rendered = apply_filters( 'the_content', (string) $content );
	mrmurphy_in_microblog_preview( false );

	return $rendered;
}
