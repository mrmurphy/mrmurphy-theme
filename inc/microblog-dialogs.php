<?php
/**
 * Render the shared microblog dialogs on wp_footer.
 *
 * Two `<dialog>` shells are rendered once per page. The Comment and Reblog
 * controllers hydrate their inner content via the partials in
 * template-parts/microblog/ when a card button is clicked.
 *
 * @package MrMurphy
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register REST routes that return rendered dialog partials on demand.
 */
function mrmurphy_microblog_dialog_routes() {
	register_rest_route(
		'mrmurphy/v1',
		'/dialog/comment',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'mrmurphy_microblog_comment_dialog_response',
			'permission_callback' => '__return_true',
			'args'                => array(
				'post_id' => array(
					'type'              => 'integer',
					'required'          => true,
					'sanitize_callback' => 'absint',
				),
			),
		)
	);

	register_rest_route(
		'mrmurphy/v1',
		'/dialog/share',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'mrmurphy_microblog_share_dialog_response',
			'permission_callback' => '__return_true',
			'args'                => array(
				'post_id' => array(
					'type'              => 'integer',
					'required'          => true,
					'sanitize_callback' => 'absint',
				),
			),
		)
	);

	register_rest_route(
		'mrmurphy/v1',
		'/comments',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'mrmurphy_microblog_comments_list',
			'permission_callback' => '__return_true',
			'args'                => array(
				'post_id' => array(
					'type'              => 'integer',
					'required'          => true,
					'sanitize_callback' => 'absint',
				),
			),
		)
	);

	register_rest_route(
		'mrmurphy/v1',
		'/comments',
		array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => 'mrmurphy_microblog_comment_submit',
			'permission_callback' => '__return_true',
		)
	);
}
add_action( 'rest_api_init', 'mrmurphy_microblog_dialog_routes' );

/**
 * Render the comment dialog partial to a string.
 *
 * @param int $post_id Post ID.
 * @return string
 */
function mrmurphy_microblog_comment_dialog_html( $post_id ) {
	if ( ! $post_id ) {
		return '';
	}
	ob_start();
	get_template_part(
		'template-parts/microblog/comment-dialog-form',
		null,
		array( 'post_id' => $post_id )
	);
	return (string) ob_get_clean();
}

/**
 * Render the share dialog partial to a string.
 *
 * @param int $post_id Post ID.
 * @return string
 */
function mrmurphy_microblog_share_dialog_html( $post_id ) {
	if ( ! $post_id ) {
		return '';
	}
	ob_start();
	get_template_part(
		'template-parts/microblog/share-dialog',
		null,
		array( 'post_id' => $post_id )
	);
	return (string) ob_get_clean();
}

/**
 * REST: GET /mrmurphy/v1/dialog/comment?post_id=...
 */
function mrmurphy_microblog_comment_dialog_response( WP_REST_Request $request ) {
	$post_id = absint( $request->get_param( 'post_id' ) );
	$post    = get_post( $post_id );
	if ( ! $post || 'publish' !== $post->post_status ) {
		return new WP_Error( 'mmb_no_post', __( 'Post not found.', 'mrmurphy' ), array( 'status' => 404 ) );
	}

	return new WP_REST_Response( array(
		'html'         => mrmurphy_microblog_comment_dialog_html( $post_id ),
		'commentCount' => (int) get_comments_number( $post_id ),
	) );
}

/**
 * REST: GET /mrmurphy/v1/dialog/share?post_id=...
 */
function mrmurphy_microblog_share_dialog_response( WP_REST_Request $request ) {
	$post_id = absint( $request->get_param( 'post_id' ) );
	$post    = get_post( $post_id );
	if ( ! $post || 'publish' !== $post->post_status ) {
		return new WP_Error( 'mmb_no_post', __( 'Post not found.', 'mrmurphy' ), array( 'status' => 404 ) );
	}

	return new WP_REST_Response( array(
		'html' => mrmurphy_microblog_share_dialog_html( $post_id ),
	) );
}

/**
 * REST: GET /mrmurphy/v1/comments?post_id=...
 *
 * Returns a plain list of approved comments for the post (comment_ID,
 * author, date_gmt, content rendered). Mirrors just enough of the WP REST
 * shape to render in the dialog without pulling full WP REST controller
 * overhead.
 */
function mrmurphy_microblog_comments_list( WP_REST_Request $request ) {
	$post_id = absint( $request->get_param( 'post_id' ) );
	$post    = get_post( $post_id );
	if ( ! $post || 'publish' !== $post->post_status ) {
		return new WP_Error( 'mmb_no_post', __( 'Post not found.', 'mrmurphy' ), array( 'status' => 404 ) );
	}

	$comments = get_comments( array(
		'post_id' => $post_id,
		'status'  => 'approve',
		'order'   => 'ASC',
		'orderby' => 'comment_date_gmt',
	) );

	$out = array();
	foreach ( $comments as $c ) {
		$out[] = array(
			'id'      => (int) $c->comment_ID,
			'author'  => $c->comment_author,
			'date'    => mysql2date( 'M j, Y g:ia', $c->comment_date ),
			'content'  => wpautop( wp_kses_post( $c->comment_content ) ),
			'pending' => false,
		);
	}

	return new WP_REST_Response( array( 'comments' => $out ) );
}

/**
 * REST: POST /mrmurphy/v1/comments
 *
 * Body fields: post_id, content, author_name?, author_email?, mmb_hp? (honeypot).
 * Falls through to wp_handle_comment_submission() so all standard flood/dupe
 * and moderation rules apply.
 */
function mrmurphy_microblog_comment_submit( WP_REST_Request $request ) {
	$post_id = absint( $request->get_param( 'post_id' ) );
	$content = (string) $request->get_param( 'content' );
	$hp      = (string) $request->get_param( 'mmb_hp' );

	if ( '' !== trim( $hp ) ) {
		return new WP_Error( 'mmb_hp', __( 'Spam detected.', 'mrmurphy' ), array( 'status' => 400 ) );
	}

	$post = get_post( $post_id );
	if ( ! $post || 'publish' !== $post->post_status ) {
		return new WP_Error( 'mmb_no_post', __( 'Post not found.', 'mrmurphy' ), array( 'status' => 404 ) );
	}

	if ( '' === trim( $content ) ) {
		return new WP_Error( 'mmb_empty', __( 'Please write a comment.', 'mrmurphy' ), array( 'status' => 400 ) );
	}

	$comment_data = array(
		'comment_post_ID'      => $post_id,
		'comment_content'      => $content,
		'comment_type'         => 'comment',
		'comment_parent'       => 0,
	);

	if ( is_user_logged_in() ) {
		$user                 = wp_get_current_user();
		$comment_data['user_id'] = $user->ID;
		$comment_data['comment_author']       = $user->display_name;
		$comment_data['comment_author_email'] = $user->user_email;
		$comment_data['comment_author_url']   = $user->user_url;
	} else {
		$author_name  = trim( (string) $request->get_param( 'author_name' ) );
		$author_email = trim( (string) $request->get_param( 'author_email' ) );

		if ( '' === $author_name || '' === $author_email ) {
			return new WP_Error( 'mmb_fields', __( 'Please enter your name and email.', 'mrmurphy' ), array( 'status' => 400 ) );
		}
		if ( ! is_email( $author_email ) ) {
			return new WP_Error( 'mmb_email', __( 'Please enter a valid email address.', 'mrmurphy' ), array( 'status' => 400 ) );
		}

		$comment_data['comment_author']       = $author_name;
		$comment_data['comment_author_email'] = $author_email;
		$comment_data['comment_author_url']   = '';
	}

	$comment = wp_handle_comment_submission( $comment_data );

	if ( is_wp_error( $comment ) ) {
		return $comment;
	}

	$pending = '1' === (string) $comment->comment_approved;
	return new WP_REST_Response( array(
		'comment' => array(
			'id'      => (int) $comment->comment_ID,
			'author'  => $comment->comment_author,
			'date'    => mysql2date( 'M j, Y g:ia', $comment->comment_date ),
			'content' => wpautop( wp_kses_post( $comment->comment_content ) ),
			'pending' => $pending,
		),
	) );
}

/**
 * Echo the two empty `<dialog>` shells.
 */
function mrmurphy_render_microblog_dialogs() {
	// Only emit when the page actually contains microblog cards. Mirrors the
	// contexts where post-preview.php renders the microblog branch.
	if ( ! ( is_home() || is_archive() || is_singular( 'post' ) || is_front_page() ) ) {
		return;
	}

	?>
	<dialog id="mmb-comment-dialog" class="mb-dialog" aria-labelledby="mmb-comment-dialog-title">
		<div class="mb-dialog__inner" data-mb-dialog-content></div>
	</dialog>

	<dialog id="mmb-share-dialog" class="mb-dialog" aria-labelledby="mmb-share-dialog-title">
		<div class="mb-dialog__inner" data-mb-dialog-content></div>
	</dialog>
	<?php
}
add_action( 'wp_footer', 'mrmurphy_render_microblog_dialogs' );