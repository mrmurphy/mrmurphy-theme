<?php
/**
 * Microblog post helpers and auto-assignment.
 *
 * @package MrMurphy
 */

defined( 'ABSPATH' ) || exit;

/**
 * Microblog category slug.
 *
 * @return string
 */
function mrmurphy_microblog_category_slug() {
	return 'microblog';
}

/**
 * Microblog tag slug applied alongside the category.
 *
 * @return string
 */
function mrmurphy_microblog_tag_slug() {
	return 'microblog';
}

/**
 * Character limit for auto-microblog detection.
 *
 * @return int
 */
function mrmurphy_get_microblog_char_limit() {
	$limit = absint( get_theme_mod( 'mrmurphy_microblog_char_limit', 280 ) );

	if ( $limit < 1 ) {
		$limit = 280;
	}

	return $limit;
}

/**
 * Whether a post has a non-empty title.
 *
 * @param int|null $post_id Post ID.
 * @return bool
 */
function mrmurphy_post_has_title( $post_id = null ) {
	if ( ! $post_id ) {
		$post_id = get_the_ID();
	}

	if ( ! $post_id ) {
		return false;
	}

	$title = get_post_field( 'post_title', $post_id );

	return '' !== trim( (string) $title );
}

/**
 * Plain-text content length for a post.
 *
 * @param int|null $post_id Post ID.
 * @return int
 */
function mrmurphy_get_post_plain_content_length( $post_id = null ) {
	if ( ! $post_id ) {
		$post_id = get_the_ID();
	}

	if ( ! $post_id ) {
		return 0;
	}

	$content = get_post_field( 'post_content', $post_id );

	return strlen( wp_strip_all_tags( (string) $content ) );
}

/**
 * Whether a post qualifies as a microblog.
 *
 * @param int|null $post_id Post ID.
 * @return bool
 */
function mrmurphy_is_microblog( $post_id = null ) {
	if ( ! $post_id ) {
		$post_id = get_the_ID();
	}

	if ( ! $post_id ) {
		return false;
	}

	if ( has_category( mrmurphy_microblog_category_slug(), $post_id ) ) {
		return true;
	}

	return ! mrmurphy_post_has_title( $post_id )
		&& mrmurphy_get_post_plain_content_length( $post_id ) > 0
		&& mrmurphy_get_post_plain_content_length( $post_id ) <= mrmurphy_get_microblog_char_limit();
}

/**
 * Whether a post matches auto-microblog rules (no title + short body).
 *
 * @param int|null $post_id Post ID.
 * @return bool
 */
function mrmurphy_should_auto_microblog( $post_id = null ) {
	if ( ! $post_id ) {
		$post_id = get_the_ID();
	}

	if ( ! $post_id ) {
		return false;
	}

	return ! mrmurphy_post_has_title( $post_id )
		&& mrmurphy_get_post_plain_content_length( $post_id ) > 0
		&& mrmurphy_get_post_plain_content_length( $post_id ) <= mrmurphy_get_microblog_char_limit();
}

/**
 * Get the microblog category term ID, if it exists.
 *
 * @return int
 */
function mrmurphy_get_microblog_category_id() {
	static $category_id = null;

	if ( null !== $category_id ) {
		return $category_id;
	}

	$category = get_category_by_slug( mrmurphy_microblog_category_slug() );
	$category_id = $category ? (int) $category->term_id : 0;

	return $category_id;
}

/**
 * Get the microblog category archive URL.
 *
 * @return string
 */
function mrmurphy_get_microblog_category_url() {
	$url = get_category_link( mrmurphy_get_microblog_category_id() );

	return is_wp_error( $url ) ? home_url( '/' ) : $url;
}

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

/**
 * Assign microblog category and tag when a post qualifies.
 *
 * @param int     $post_id Post ID.
 * @param WP_Post $post    Post object.
 */
function mrmurphy_auto_assign_microblog( $post_id, $post ) {
	if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
		return;
	}

	if ( ! $post instanceof WP_Post || 'post' !== $post->post_type ) {
		return;
	}

	if ( in_array( $post->post_status, array( 'auto-draft', 'trash', 'inherit' ), true ) ) {
		return;
	}

	if ( ! mrmurphy_should_auto_microblog( $post_id ) ) {
		return;
	}

	$category_id = mrmurphy_get_microblog_category_id();

	if ( $category_id ) {
		wp_set_post_terms( $post_id, array( $category_id ), 'category', true );
	}

	wp_set_post_terms( $post_id, array( mrmurphy_microblog_tag_slug() ), 'post_tag', true );
}
add_action( 'save_post', 'mrmurphy_auto_assign_microblog', 20, 2 );
