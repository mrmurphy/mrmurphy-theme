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
				'permission_callback' => '__return_true',
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
				'permission_callback' => '__return_true',
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
 * Resolve the like identifier for the current request.
 *
 * Logged-in users get `user:<id>` so their likes follow them across devices.
 * Anonymous visitors use the client_id they sent.
 *
 * @param string $client_id Client-provided identifier.
 * @return string
 */
function mrmurphy_likes_resolve_identifier( $client_id ) {
	if ( is_user_logged_in() ) {
		return 'user:' . get_current_user_id();
	}
	return $client_id;
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
 * Migrate anonymous (client-id) likes to a logged-in user identifier.
 *
 * When a user who previously liked posts while logged out logs in, their
 * anonymous likes (stored under the localStorage client_id) are transferred
 * to `user:<id>`. If a `user:<id>` row already exists for a post, the
 * anonymous row is removed (no double-counting).
 *
 * @param string $client_id The anonymous client_id from localStorage.
 */
function mrmurphy_likes_migrate_identifier( $client_id ) {
	if ( ! is_user_logged_in() || empty( $client_id ) ) {
		return;
	}

	global $wpdb;
	$table    = $wpdb->prefix . 'mmb_likes';
	$user_uid = 'user:' . get_current_user_id();

	// Find all anonymous likes for this client_id.
	$anon_likes = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT like_id, post_id FROM {$table} WHERE identifier = %s",
			$client_id
		)
	);

	if ( empty( $anon_likes ) ) {
		return;
	}

	foreach ( $anon_likes as $like ) {
		// Does the user already have a like for this post?
		$existing = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table} WHERE post_id = %d AND identifier = %s",
				$like->post_id,
				$user_uid
			)
		);

		if ( $existing > 0 ) {
			// User already liked this post while logged in — remove the
			// anonymous duplicate so the count stays accurate.
			$wpdb->delete( $table, array( 'like_id' => $like->like_id ), array( '%d' ) );
		} else {
			// Transfer the anonymous like to the user identifier.
			$wpdb->update(
				$table,
				array( 'identifier' => $user_uid ),
				array( 'like_id' => $like->like_id ),
				array( '%s' ),
				array( '%d' )
			);
		}

		mrmurphy_likes_recount( $like->post_id );
	}
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

	$has = mrmurphy_has_liked( $post_id, $identifier );

	if ( 'like' === $action && ! $has ) {
		$result = $wpdb->insert(
			$table,
			array(
				'post_id'    => $post_id,
				'identifier' => $identifier,
				'created_at' => current_time( 'mysql' ),
			),
			array( '%d', '%s', '%s' )
		);

		if ( false === $result ) {
			error_log( 'MrMurphy Likes: insert failed for post ' . $post_id . ' — ' . $wpdb->last_error );
		} else {
			mrmurphy_likes_recount( $post_id );
		}
	} elseif ( 'unlike' === $action && $has ) {
		$result = $wpdb->delete(
			$table,
			array(
				'post_id'    => $post_id,
				'identifier' => $identifier,
			),
			array( '%d', '%s' )
		);

		if ( false === $result ) {
			error_log( 'MrMurphy Likes: delete failed for post ' . $post_id . ' — ' . $wpdb->last_error );
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
 * Simple per-IP rate limit so a single source can't hammer the toggle
 * endpoint. Limits one mutation per IP per 2 seconds per post.
 *
 * @param int $post_id Post ID.
 * @return bool True when the request is allowed; false when rate-limited.
 */
function mrmurphy_likes_rate_limit( $post_id ) {
	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? wp_unslash( $_SERVER['REMOTE_ADDR'] ) : '';
	$key = 'mmb_like_ip_' . md5( $ip . ':' . $post_id );

	if ( false !== get_transient( $key ) ) {
		return false;
	}

	set_transient( $key, 1, 2 );
	return true;
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

	$out = array();
	foreach ( $post_ids as $post_id ) {
		$out[ $post_id ] = array(
			'count' => mrmurphy_like_count( $post_id ),
			'liked' => $identifier ? mrmurphy_has_liked( $post_id, $identifier ) : false,
		);
	}

	$response = new WP_REST_Response( array( 'likes' => $out ) );
	$response->header( 'Cache-Control', 'public, max-age=60' );
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
	if ( '' === $identifier || strlen( $identifier ) > 64 ) {
		return new WP_Error( 'mmb_likes_bad_id', __( 'Invalid client identifier.', 'mrmurphy' ), array( 'status' => 400 ) );
	}

	// Merge anonymous likes from this client_id into the user's account.
	// This runs at most once per login session (until all anon likes are
	// migrated).
	mrmurphy_likes_migrate_identifier( $client_id );

	if ( ! mrmurphy_likes_rate_limit( $post_id ) ) {
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