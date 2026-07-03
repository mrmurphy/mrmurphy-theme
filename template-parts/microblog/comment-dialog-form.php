<?php
/**
 * Comment dialog form for microblog cards.
 *
 * Rendered server-side and injected into the shared comment `<dialog>` when a
 * card's Comment button is clicked. Echoes the form fields appropriate to the
 * current viewer's logged-in state plus the existing comments for the post
 * being viewed.
 *
 * Expected $args keys:
 *   - post_id (int)  Required. ID of the post this dialog is bound to.
 *
 * @package MrMurphy
 */

defined( 'ABSPATH' ) || exit;

$post_id = isset( $args['post_id'] ) ? absint( $args['post_id'] ) : 0;
if ( ! $post_id ) {
	return;
}

$post      = get_post( $post_id );
$permalink = $post ? get_permalink( $post ) : home_url( '/' );

if ( $post ) :
	$author_id = (int) $post->post_author;
	?>
	<div class="mb-dialog__head">
		<a class="mb-dialog__avatar" href="<?php echo esc_url( get_author_posts_url( $author_id ) ); ?>" aria-hidden="true" tabindex="-1">
			<?php echo get_avatar( $author_id, 28 ); ?>
		</a>
		<div class="mb-dialog__who">
			<a class="mb-dialog__name" href="<?php echo esc_url( get_author_posts_url( $author_id ) ); ?>">
				<?php echo esc_html( get_the_author_meta( 'display_name', $author_id ) ); ?>
			</a>
			<span class="mb-dialog__handle-line">
				<a class="mb-dialog__handle" href="<?php echo esc_url( $permalink ); ?>">@<?php echo esc_html( mrmurphy_author_handle( $post_id ) ); ?></a>
				<span class="mb-dialog__time"> · <?php echo esc_html( mrmurphy_relative_time( $post_id ) ); ?></span>
			</span>
		</div>
		<button type="button" class="mb-dialog__close" data-mb-dialog-close aria-label="<?php esc_attr_e( 'Close comment dialog', 'mrmurphy' ); ?>">×</button>
	</div>

	<div class="mb-dialog__body">
		<?php echo wp_kses_post( wpautop( get_post_field( 'post_content', $post_id ) ) ); ?>
	</div>
	<?php
endif;

$current_count = (int) get_comments_number( $post_id );
?>
<div class="mb-dialog__comments" data-mb-comment-list data-post-id="<?php echo esc_attr( $post_id ); ?>">
	<div class="mb-dialog__comments-loading" role="status"><?php esc_html_e( 'Loading comments…', 'mrmurphy' ); ?></div>
	<template data-mb-comment-template>
		<div class="mb-comment">
			<span class="mb-comment__author"></span>
			<span class="mb-comment__time"></span>
			<p class="mb-comment__text"></p>
		</div>
	</template>
</div>

<form
	class="mb-dialog__form"
	data-mb-comment-form
	data-post-id="<?php echo esc_attr( $post_id ); ?>"
>
	<?php
	if ( is_user_logged_in() ) :
		$current_user = wp_get_current_user();
		?>
		<div class="mb-dialog__form-as">
			<span class="mb-dialog__form-avatar"><?php echo get_avatar( $current_user->ID, 24 ); ?></span>
			<span class="mb-dialog__form-name"><?php echo esc_html( $current_user->display_name ); ?></span>
		</div>
		<textarea
			class="mb-dialog__textarea"
			name="content"
			rows="3"
			placeholder="<?php esc_attr_e( 'Write a comment…', 'mrmurphy' ); ?>"
			required></textarea>

		<input type="hidden" name="author_email" value="<?php echo esc_attr( $current_user->user_email ); ?>" />
		<input type="hidden" name="author_name" value="<?php echo esc_attr( $current_user->display_name ); ?>" />
	<?php else : ?>
		<textarea
			class="mb-dialog__textarea"
			name="content"
			rows="3"
			placeholder="<?php esc_attr_e( 'Write a comment…', 'mrmurphy' ); ?>"
			required></textarea>

		<div class="mb-dialog__row" data-mb-comment-author-row>
			<div class="mb-dialog__field">
				<label class="mb-dialog__label" for="mb-comment-name-<?php echo esc_attr( $post_id ); ?>"><?php esc_html_e( 'Name', 'mrmurphy' ); ?></label>
				<input id="mb-comment-name-<?php echo esc_attr( $post_id ); ?>" class="mb-dialog__input" name="author_name" type="text" autocomplete="name" required />
			</div>
			<div class="mb-dialog__field">
				<label class="mb-dialog__label" for="mb-comment-email-<?php echo esc_attr( $post_id ); ?>"><?php esc_html_e( 'Email', 'mrmurphy' ); ?></label>
				<input id="mb-comment-email-<?php echo esc_attr( $post_id ); ?>" class="mb-dialog__input" name="author_email" type="email" autocomplete="email" required />
			</div>
		</div>

		<label class="mb-dialog__cookies">
			<input type="checkbox" name="wp-comment-cookies-consent" value="yes" />
			<span><?php esc_html_e( 'Save my name &amp; email for next time', 'mrmurphy' ); ?></span>
		</label>
	<?php endif; ?>

	<input type="text" name="mmb_hp" class="mb-dialog__honeypot" tabindex="-1" autocomplete="off" aria-hidden="true" />

	<div class="mb-dialog__form-actions">
		<span class="mb-dialog__form-error" data-mb-comment-error role="alert"></span>
		<button type="submit" class="mb-dialog__submit"><?php esc_html_e( 'Post comment', 'mrmurphy' ); ?></button>
	</div>
</form>