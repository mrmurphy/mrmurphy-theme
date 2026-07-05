<?php
/**
 * Microblog likes REST controller and storage.
 *
 * Single source of truth for likes on microblog cards. Anonymous visitors
 * are identified by a once-per-browser clientId stored in localStorage;
 * logged-in users are identified by `user:<user_id>` so their likes follow
 * them across devices.
 *
 * @package MrMurphy
 */

defined( 'ABSPATH' ) || exit;

define( 'MRMURPHY_LIKES_DB_VERSION', '1.1' );

/**
 * Register REST routes for the mrmurphy/v1 likes controller.
 */
function mrmurphy_likes_register_routes() {
	register_rest_route(
		'mrmurphy/v1',
		'/likes',
		array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => 'mrmurphy_likes_get_batch',
				'permission_callback' => 'mrmurphy_rest_require_nonce',
				'args'                => array(
					'post_ids'  => array(
						'type'              => 'string',
						'required'          => false,
						'sanitize_callback' => 'sanitize_text_field',
					),
					'client_id' => array(
						'type'              => 'string',
						'required'          => false,
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
			),
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => 'mrmurphy_likes_toggle',
				'permission_callback' => 'mrmurphy_rest_require_nonce',
				'args'                => array(
					'post_id'   => array(
						'type'              => 'integer',
						'required'          => true,
						'sanitize_callback' => 'absint',
					),
					'client_id' => array(
						'type'              => 'string',
						'required'          => false,
						'sanitize_callback' => 'sanitize_text_field',
					),
					'action'    => array(
						'type'              => 'string',
						'required'          => true,
						'enum'              => array( 'like', 'unlike' ),
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
			),
		)
	);
}
add_action( 'rest_api_init', 'mrmurphy_likes_register_routes' );

/**
 * Create the likes index table on theme activation / update.
 */
function mrmurphy_likes_create_table() {
	global $wpdb;
	$table   = $wpdb->prefix . 'mmb_likes';
	$charset = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE {$table} (
		like_id    BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
		post_id    BIGINT(20) UNSIGNED NOT NULL,
		identifier VARCHAR(64) NOT NULL,
		created_at DATETIME NOT NULL,
		PRIMARY KEY  (like_id),
		UNIQUE KEY uniq_post_identifier (post_id, identifier),
		KEY          identifier (identifier),
		KEY          post_created (post_id, created_at)
	) {$charset};";

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta( $sql );
}
add_action( 'after_switch_theme', 'mrmurphy_likes_create_table' );

/**
 * Check if the likes table schema is up to date and create/upgrade if not.
 *
 * Standard WordPress pattern for custom tables: store a DB version option
 * and compare against a hardcoded constant on every request. dbDelta only
 * runs when the version has changed (initial install or schema upgrade).
 */
function mrmurphy_likes_check_table() {
	$current_version = get_option( 'mrmurphy_likes_db_version', '' );

	if ( $current_version !== MRMURPHY_LIKES_DB_VERSION ) {
		// dbDelta handles table creation and column changes but does NOT
		// add indexes to existing tables. Run a separate ALTER for the
		// identifier index added in v1.1.
		if ( version_compare( $current_version, '1.1', '<' ) && '' !== $current_version ) {
			global $wpdb;
			$table = $wpdb->prefix . 'mmb_likes';
			$wpdb->query( "ALTER TABLE {$table} ADD INDEX identifier (identifier)" );
		}

		mrmurphy_likes_create_table();
		update_option( 'mrmurphy_likes_db_version', MRMURPHY_LIKES_DB_VERSION );
	}
}
add_action( 'init', 'mrmurphy_likes_check_table' );

/**
 * Create an HMAC-signed anonymous token.
 *
 * Format: srv:<base64url_random>:<expiry>:<truncated_base64url_hmac>
 * No server storage needed — validate with hash_equals and expiry check.
 *
 * @return string
 */
function mrmurphy_likes_create_token() {
	$random   = substr( strtr( base64_encode( random_bytes( 9 ) ), '+/', '-_' ), 0, 12 );
	$exp      = time() + YEAR_IN_SECONDS;
	$payload  = $random . ':' . $exp;
	$hmac_raw = hash_hmac( 'sha256', $payload, NONCE_KEY, true );
	$hmac_b64 = substr( rtrim( strtr( base64_encode( $hmac_raw ), '+/', '-_' ), '=' ), 0, 22 );
	return 'srv:' . $payload . ':' . $hmac_b64;
}

/**
 * Validate an HMAC-signed anonymous token.
 *
 * Returns the token's jti (random component) on success, false on failure.
 *
 * @param string $token Raw token string.
 * @return string|false
 */
function mrmurphy_likes_validate_token( $token ) {
	if ( strpos( $token, 'srv:' ) !== 0 ) {
		return false;
	}
	$parts = explode( ':', $token );
	if ( count( $parts ) !== 4 ) {
		return false;
	}
	$random  = $parts[1];
	$exp     = (int) $parts[2];
	$hmac_b64 = $parts[3];

	if ( $exp < time() ) {
		return false;
	}

	$payload      = $random . ':' . $exp;
	$expected_raw = hash_hmac( 'sha256', $payload, NONCE_KEY, true );
	$expected_b64 = substr( rtrim( strtr( base64_encode( $expected_raw ), '+/', '-_' ), '=' ), 0, 22 );

	if ( ! hash_equals( $expected_b64, $hmac_b64 ) ) {
		return false;
	}

	return $random;
}

/**
 * Check the per-token rate limit (50 like actions per hour per token).
 *
 * Relies on the caller having already validated the token. Extracts the
 * jti from the second `:`-delimited field without re-computing the HMAC.
 *
 * @param string $token Pre-validated token string.
 * @return bool True if under the limit; false if rate-limited.
 */
function mrmurphy_likes_check_token_rate_limit( $token ) {
	$parts = explode( ':', $token );
	if ( count( $parts ) !== 4 ) {
		return false;
	}
	$key   = 'mmb_like_token_' . $parts[1];
	$count = (int) get_transient( $key );
	if ( $count >= 50 ) {
		return false;
	}
	set_transient( $key, $count + 1, HOUR_IN_SECONDS );
	return true;
}

/**
 * Resolve the like identifier for the current request.
 *
 * Logged-in users get `user:<id>`. Anonymous visitors with a valid
 * HMAC-signed token get the token itself as the identifier.
 *
 * @param string $client_id Client-provided token.
 * @return string
 */
function mrmurphy_likes_resolve_identifier( $client_id ) {
	if ( is_user_logged_in() ) {
		return 'user:' . get_current_user_id();
	}
	if ( ! empty( $client_id ) && mrmurphy_likes_validate_token( $client_id ) ) {
		return $client_id;
	}
	return '';
}

/**
 * Get the like count for a post.
 *
 * @param int $post_id Post ID.
 * @return int
 */
function mrmurphy_like_count( $post_id ) {
	return absint( get_post_meta( $post_id, '_mmb_like_count', true ) );
}

/**
 * Whether a given identifier has liked a post.
 *
 * @param int    $post_id     Post ID.
 * @param string $identifier  Like identifier.
 * @return bool
 */
function mrmurphy_has_liked( $post_id, $identifier ) {
	global $wpdb;
	$table = $wpdb->prefix . 'mmb_likes';

	$found = $wpdb->get_var(
		$wpdb->prepare(
			"SELECT COUNT(*) FROM {$table} WHERE post_id = %d AND identifier = %s",
			$post_id,
			$identifier
		)
	);

	return $found > 0;
}

/**
 * Recompute the cached like count for a post from the index table.
 *
 * @param int $post_id Post ID.
 */
function mrmurphy_likes_recount( $post_id ) {
	global $wpdb;
	$table = $wpdb->prefix . 'mmb_likes';

	$count = (int) $wpdb->get_var(
		$wpdb->prepare(
			"SELECT COUNT(*) FROM {$table} WHERE post_id = %d",
			$post_id
		)
	);

	update_post_meta( $post_id, '_mmb_like_count', $count );
}

/**
 * Apply a like/unlike mutation for a post + identifier pair.
 *
 * Idempotent: a repeated like is a no-op; an unlike for a row that doesn't
 * exist is also a no-op. Returns the resulting { count, liked } state.
 *
 * @param int    $post_id     Post ID.
 * @param string $identifier  Like identifier.
 * @param string $action      'like' or 'unlike'.
 * @return array { count: int, liked: bool }
 */
function mrmurphy_likes_apply( $post_id, $identifier, $action ) {
	global $wpdb;
	$table = $wpdb->prefix . 'mmb_likes';

	if ( 'like' === $action ) {
		$result = $wpdb->query( $wpdb->prepare(
			"INSERT INTO {$table} (post_id, identifier, created_at)
			 VALUES (%d, %s, %s)
			 ON DUPLICATE KEY UPDATE like_id = like_id",
			$post_id,
			$identifier,
			current_time( 'mysql' )
		) );

		if ( false === $result ) {
			error_log( 'MrMurphy Likes: DB write failed for post ' . $post_id );
		}

		mrmurphy_likes_recount( $post_id );
	} elseif ( 'unlike' === $action ) {
		$result = $wpdb->delete(
			$table,
			array(
				'post_id'    => $post_id,
				'identifier' => $identifier,
			),
			array( '%d', '%s' )
		);

		if ( false === $result ) {
			error_log( 'MrMurphy Likes: DB write failed for post ' . $post_id );
		} else {
			mrmurphy_likes_recount( $post_id );
		}
	}

	return array(
		'count' => mrmurphy_like_count( $post_id ),
		'liked' => mrmurphy_has_liked( $post_id, $identifier ),
	);
}

/**
 * GET /wp-json/mrmurphy/v1/likes — batch hydrate visible cards.
 *
 * @param WP_REST_Request $request Request.
 * @return WP_REST_Response
 */
function mrmurphy_likes_get_batch( WP_REST_Request $request ) {
	$post_ids_raw = $request->get_param( 'post_ids' );
	$client_id   = $request->get_param( 'client_id' );

	$post_ids = array();
	if ( ! empty( $post_ids_raw ) ) {
		foreach ( explode( ',', $post_ids_raw ) as $raw_id ) {
			$id = absint( $raw_id );
			if ( $id ) {
				$post_ids[] = $id;
			}
		}
	}
	$post_ids = array_unique( $post_ids );

	// Bound the batch to keep the request cheap.
	$post_ids = array_slice( $post_ids, 0, 50 );

	$identifier = mrmurphy_likes_resolve_identifier( $client_id );
	$can_like   = ! empty( $identifier );

	$out = array();
	foreach ( $post_ids as $post_id ) {
		$post = get_post( $post_id );
		if ( ! $post || 'publish' !== $post->post_status ) {
			continue;
		}
		$out[ $post_id ] = array(
			'count' => mrmurphy_like_count( $post_id ),
			'liked' => $can_like ? mrmurphy_has_liked( $post_id, $identifier ) : false,
		);
	}

	$response = new WP_REST_Response( array( 'likes' => $out ) );
	$response->header( 'Cache-Control', 'private, max-age=60' );
	return $response;
}

/**
 * POST /wp-json/mrmurphy/v1/likes — toggle a like for a single post.
 *
 * @param WP_REST_Request $request Request.
 * @return WP_REST_Response|WP_Error
 */
function mrmurphy_likes_toggle( WP_REST_Request $request ) {
	$post_id   = absint( $request->get_param( 'post_id' ) );
	$client_id = sanitize_text_field( (string) $request->get_param( 'client_id' ) );
	$action    = sanitize_text_field( (string) $request->get_param( 'action' ) );

	if ( ! $post_id ) {
		return new WP_Error( 'mmb_likes_no_post', __( 'Missing post_id.', 'mrmurphy' ), array( 'status' => 400 ) );
	}

	$post = get_post( $post_id );
	if ( ! $post || 'publish' !== $post->post_status ) {
		return new WP_Error( 'mmb_likes_post_missing', __( 'Post not found.', 'mrmurphy' ), array( 'status' => 404 ) );
	}

	$identifier = mrmurphy_likes_resolve_identifier( $client_id );
	if ( '' === $identifier ) {
		return new WP_Error(
			'mmb_likes_auth_required',
			__( 'You must be logged in to like posts.', 'mrmurphy' ),
			array( 'status' => 401 )
		);
	}

	if ( strpos( $identifier, 'srv:' ) === 0 && ! mrmurphy_likes_check_token_rate_limit( $identifier ) ) {
		return new WP_Error( 'mmb_likes_rate_limited', __( 'Slow down a moment.', 'mrmurphy' ), array( 'status' => 429 ) );
	}

	$state = mrmurphy_likes_apply( $post_id, $identifier, $action );

	return new WP_REST_Response(
		array(
			'post_id' => $post_id,
			'count'   => $state['count'],
			'liked'   => $state['liked'],
		)
	);
}