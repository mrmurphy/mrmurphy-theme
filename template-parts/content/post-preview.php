<?php
/**
 * Shared post preview card for timelines and menus.
 *
 * @package MrMurphy
 */

defined( 'ABSPATH' ) || exit;

$is_microblog = function_exists( 'wp_microblog_is' )
	? wp_microblog_is()
	: ( function_exists( 'mrmurphy_is_microblog' ) ? mrmurphy_is_microblog() : false );
$permalink    = get_permalink();
$preview_id   = 'post-preview-' . get_the_ID();
$meta_class   = ! empty( $args['meta_class'] ) ? $args['meta_class'] : 'post-preview__meta';
?>

<?php
$mb_classes = $is_microblog ? 'post-preview post-preview--microblog mb-card' : 'post-preview';
$mb_attrs   = $is_microblog ? ' data-microblog-card data-post-id="' . (int) get_the_ID() . '"' : '';
?>
<article
	id="post-<?php the_ID(); ?>"
	<?php post_class( $mb_classes ); ?>
	<?php echo $mb_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
>
	<?php if ( $is_microblog ) : ?>
		<a class="mb-card__link" href="<?php echo esc_url( $permalink ); ?>">
			<div class="mb-card__head">
				<span class="mb-card__avatar" aria-hidden="true">
					<?php echo get_avatar( get_the_author_meta( 'ID' ), 36 ); ?>
				</span>
				<div class="mb-card__who">
					<span class="mb-card__name">
						<?php echo esc_html( get_the_author_meta( 'display_name' ) ); ?>
					</span>
					<span class="mb-card__handle-line">
						<span class="mb-card__handle">@<?php echo esc_html( mrmurphy_author_handle() ); ?></span>
						<span class="mb-card__time"> · <?php echo esc_html( mrmurphy_relative_time() ); ?></span>
					</span>
				</div>
			</div>

			<?php if ( has_post_thumbnail() ) : ?>
				<div class="mb-card__image featured-image--square" aria-hidden="true">
					<?php the_post_thumbnail( 'mrmurphy-square-md' ); ?>
				</div>
			<?php endif; ?>

			<div id="<?php echo esc_attr( $preview_id ); ?>" class="mb-card__body">
				<?php echo mrmurphy_get_microblog_preview_content(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>
		</a>

		<footer class="mb-card__actions">
			<button
				type="button"
				class="mb-action mb-action--like"
				data-mb-like
				data-post-id="<?php the_ID(); ?>"
				aria-pressed="false"
				aria-label="<?php esc_attr_e( 'Like this post', 'mrmurphy' ); ?>"
			>
				<span class="mb-action__icon" aria-hidden="true"><?php echo mrmurphy_get_icon( 'heart' ); ?></span>
				<span class="mb-action__count" data-mb-like-count><?php echo esc_html( (int) get_post_meta( get_the_ID(), '_mmb_like_count', true ) ); ?></span>
			</button>

			<button
				type="button"
				class="mb-action mb-action--comment"
				data-mb-comment
				data-post-id="<?php the_ID(); ?>"
				aria-haspopup="dialog"
				aria-label="<?php esc_attr_e( 'Comment on this post', 'mrmurphy' ); ?>"
			>
				<span class="mb-action__icon" aria-hidden="true"><?php echo mrmurphy_get_icon( 'comment' ); ?></span>
				<span class="mb-action__count" data-mb-comment-count><?php echo esc_html( get_comments_number( get_the_ID() ) ); ?></span>
			</button>

			<button
				type="button"
				class="mb-action mb-action--reblog"
				data-mb-reblog
				data-post-id="<?php the_ID(); ?>"
				aria-haspopup="dialog"
				aria-label="<?php esc_attr_e( 'Share this post', 'mrmurphy' ); ?>"
			>
				<span class="mb-action__icon" aria-hidden="true"><?php echo mrmurphy_get_icon( 'reblog' ); ?></span>
			</button>
		</footer>
	<?php else : ?>
		<a
			class="post-preview__stretched-link"
			href="<?php echo esc_url( $permalink ); ?>"
			aria-labelledby="<?php echo esc_attr( $preview_id ); ?>"
		>
			<span class="screen-reader-text">
				<?php
				printf(
					/* translators: %s: Post title */
					esc_html__( 'Read %s', 'mrmurphy' ),
					wp_strip_all_tags( get_the_title() )
				);
				?>
			</span>
		</a>

		<?php if ( has_post_thumbnail() ) : ?>
			<div class="post-preview__image featured-image--square" aria-hidden="true">
				<?php the_post_thumbnail( 'mrmurphy-square-md' ); ?>
			</div>
		<?php endif; ?>

		<div class="post-preview__content">
			<h2 id="<?php echo esc_attr( $preview_id ); ?>" class="post-preview__title"><?php the_title(); ?></h2>

			<div class="<?php echo esc_attr( $meta_class ); ?>">
				<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
					<?php echo esc_html( get_the_date() ); ?>
				</time>
			</div>
		</div>

		<p class="post-preview__excerpt">
			<?php echo wp_trim_words( get_the_excerpt(), 30, '&hellip;' ); ?>
		</p>
	<?php endif; ?>
</article>
