<?php
/**
 * Newsletter signup form.
 *
 * Submits via a plain GET navigation to the WordPress.com memberships
 * endpoint — the same backend the Jetpack newsletter popup's iframe drives,
 * without any popup, nonce, or JavaScript dependency:
 *
 *   https://subscribe.wordpress.com/memberships/?blog={id}&email={email}
 *     &plan=newsletter&display=alternate&source={referer}
 *
 * The server creates a pending subscriber, emails a tokenized confirmation
 * link, and shows a site-branded "You've got mail!" interstitial. Clicking
 * the email's activation link confirms the subscription and redirects back
 * to the blog.
 *
 * The blog ID must be the WordPress.com *platform* ID. On Atomic sites the
 * front-end runs on a shadow install where get_current_blog_id() returns 1,
 * so we use the Jetpack connection ID, which equals the platform blog ID.
 *
 * @package MrMurphy
 */

$blog_id = 0;

if ( class_exists( 'Jetpack_Options' ) ) {
	$blog_id = (int) Jetpack_Options::get_option( 'id' );
}

if ( ! $blog_id || 1 === $blog_id ) {
	// Jetpack connection unavailable — fall back to the known platform ID
	// for this site (mrmurphy.dev). Filterable for portability.
	$blog_id = (int) apply_filters( 'mrmurphy_newsletter_blog_id', 252160838 );
}

$source = esc_url_raw(
	( is_ssl() ? 'https' : 'http' ) . '://'
	. ( isset( $_SERVER['HTTP_HOST'] ) ? wp_unslash( $_SERVER['HTTP_HOST'] ) : '' )
	. ( isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '' )
);
?>

<form
	action="https://subscribe.wordpress.com/memberships/"
	method="get"
	accept-charset="utf-8"
	id="mrmurphy-newsletter-signup"
	class="newsletter-signup"
	target="_top"
>
	<div class="newsletter-signup__row">
		<label class="screen-reader-text" for="mrmurphy-newsletter-signup-email">
			<?php esc_html_e( 'Email address', 'mrmurphy' ); ?>
		</label>
		<input
			type="email"
			name="email"
			id="mrmurphy-newsletter-signup-email"
			class="newsletter-signup__input"
			autocomplete="email"
			placeholder="<?php esc_attr_e( 'you@example.com', 'mrmurphy' ); ?>"
			required
		/>
		<button type="submit" class="btn btn--primary newsletter-signup__submit">
			<?php esc_html_e( 'Subscribe', 'mrmurphy' ); ?>
		</button>
	</div>

	<input type="hidden" name="blog" value="<?php echo esc_attr( $blog_id ); ?>" />
	<input type="hidden" name="plan" value="newsletter" />
	<input type="hidden" name="display" value="alternate" />
	<input type="hidden" name="source" value="<?php echo esc_url( $source ); ?>" />
	<?php
	/*
	 * If present, post_id becomes the post-confirmation landing spot:
	 * the activation link in the email carries redirect_to_blog_post_id,
	 * and WordPress.com sends the newly-confirmed reader there.
	 */
	$landing = get_page_by_path( 'youre-in' );
	if ( $landing instanceof WP_Post ) :
		?>
		<input type="hidden" name="post_id" value="<?php echo esc_attr( $landing->ID ); ?>" />
	<?php endif; ?>

	<p class="newsletter-signup__note">
		<?php esc_html_e( 'No spam. Unsubscribe anytime.', 'mrmurphy' ); ?>
	</p>
</form>
