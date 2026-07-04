<?php
/**
 * Comment dialog content for microblog cards.
 *
 * Post-specific header, body, and comments stub. The comment form is
 * pre-rendered in the dialog shell itself and does not need to be fetched.
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
<?php endif; ?>

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