<?php
/**
 * Newsletter signup form.
 *
 * Posts to the WordPress.com subscription endpoint (the same backend the
 * Jetpack newsletter popup drives), using a server-minted nonce so the flow
 * works with JavaScript disabled — no popup/overlay involved.
 *
 * After submission, subscribe.wordpress.com redirects back to the source page
 * with ?blogid={id}&blogsub={status}#fragment, where {status} is one of:
 * confirming | pending | subscribed | confirmed | flooded | spammed | blocked.
 * We render an in-theme status message based on that parameter.
 *
 * @package MrMurphy
 */

$blog_id  = get_current_blog_id();
$form_id  = 'mrmurphy-newsletter-signup';
$email_id = $form_id . '-email';

// Current URL, built the same way Jetpack's widget builds `source`.
$source = esc_url_raw(
	( is_ssl() ? 'https' : 'http' ) . '://'
	. ( isset( $_SERVER['HTTP_HOST'] ) ? wp_unslash( $_SERVER['HTTP_HOST'] ) : '' )
	. ( isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '' )
);

$status = isset( $_GET['blogsub'] ) ? sanitize_key( wp_unslash( $_GET['blogsub'] ) ) : '';
?>

<form
	action="https://subscribe.wordpress.com"
	method="post"
	accept-charset="utf-8"
	id="<?php echo esc_attr( $form_id ); ?>"
	class="newsletter-signup"
>
	<div class="newsletter-signup__row">
		<label class="screen-reader-text" for="<?php echo esc_attr( $email_id ); ?>">
			<?php esc_html_e( 'Email address', 'mrmurphy' ); ?>
		</label>
		<input
			type="email"
			name="email"
			id="<?php echo esc_attr( $email_id ); ?>"
			class="newsletter-signup__input"
			autocomplete="email"
			placeholder="<?php esc_attr_e( 'you@example.com', 'mrmurphy' ); ?>"
			required
		/>
		<button type="submit" class="btn btn--primary newsletter-signup__submit">
			<?php esc_html_e( 'Subscribe', 'mrmurphy' ); ?>
		</button>
	</div>

	<input type="hidden" name="action" value="subscribe" />
	<input type="hidden" name="blog_id" value="<?php echo esc_attr( $blog_id ); ?>" />
	<input type="hidden" name="source" value="<?php echo esc_url( $source ); ?>" />
	<input type="hidden" name="sub-type" value="widget" />
	<input type="hidden" name="redirect_fragment" value="<?php echo esc_attr( $form_id ); ?>" />
	<?php wp_nonce_field( 'blogsub_subscribe_' . $blog_id, '_wpnonce', false ); ?>

	<p class="newsletter-signup__note">
		<?php esc_html_e( 'No spam. Unsubscribe anytime.', 'mrmurphy' ); ?>
	</p>
</form>

<?php if ( $status ) : ?>
	<?php
	$status_config = array(
		'confirming' => array(
			'type' => 'success',
			/* translators: %s: email address entered. */
			'text' => __( 'Almost there! Check your inbox for a confirmation email.', 'mrmurphy' ),
		),
		'confirmed'  => array(
			'type' => 'success',
			'text' => __( 'You are all set — subscription confirmed. Welcome aboard!', 'mrmurphy' ),
		),
		'pending'    => array(
			'type' => 'info',
			'text' => __( 'Looks like you already tried to subscribe. We just sent you a fresh confirmation email.', 'mrmurphy' ),
		),
		'subscribed' => array(
			'type' => 'info',
			'text' => __( 'You are already subscribed. Nothing more to do!', 'mrmurphy' ),
		),
		'flooded'    => array(
			'type' => 'error',
			'text' => __( 'Too many pending subscriptions from this address. Confirm or unsubscribe a few first.', 'mrmurphy' ),
		),
		'spammed'    => array(
			'type' => 'error',
			'text' => __( 'This email was flagged as spam. Contact me if you think this is a mistake.', 'mrmurphy' ),
		),
		'blocked'    => array(
			'type' => 'error',
			'text' => __( 'This email has been blocked from subscribing.', 'mrmurphy' ),
		),
	);

	if ( isset( $status_config[ $status ] ) ) :
		$config = $status_config[ $status ];
		?>
		<div
			id="<?php echo esc_attr( $form_id . '-status' ); ?>"
			class="newsletter-signup__status newsletter-signup__status--<?php echo esc_attr( $config['type'] ); ?>"
			role="status"
		>
			<?php echo esc_html( $config['text'] ); ?>
		</div>
	<?php endif; ?>
<?php endif; ?>
