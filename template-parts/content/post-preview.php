<?php
/**
 * Shared post preview card for timelines and menus.
 *
 * @package MrMurphy
 */

defined( 'ABSPATH' ) || exit;

$is_microblog = mrmurphy_is_microblog();
$permalink    = get_permalink();
$preview_id   = 'post-preview-' . get_the_ID();
$meta_class   = ! empty( $args['meta_class'] ) ? $args['meta_class'] : 'post-preview__meta';
?>

<article
	id="post-<?php the_ID(); ?>"
	<?php post_class( $is_microblog ? 'post-preview post-preview--microblog' : 'post-preview' ); ?>
>
	<?php if ( $is_microblog ) : ?>
		<a
			class="post-preview__stretched-link"
			href="<?php echo esc_url( $permalink ); ?>"
			aria-labelledby="<?php echo esc_attr( $preview_id ); ?>"
		>
			<span class="screen-reader-text"><?php esc_html_e( 'Read microblog post', 'mrmurphy' ); ?></span>
		</a>

		<?php if ( has_post_thumbnail() ) : ?>
			<div class="post-preview__image featured-image--square" aria-hidden="true">
				<?php the_post_thumbnail( 'mrmurphy-square-md' ); ?>
			</div>
		<?php endif; ?>

		<div id="<?php echo esc_attr( $preview_id ); ?>" class="post-preview__body">
			<?php echo mrmurphy_get_microblog_preview_content(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</div>

		<footer class="post-preview__footer">
			<time class="post-preview__date" datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
				<?php echo esc_html( get_the_date() ); ?>
			</time>
			<div class="post-preview__microblog-tag">
				<a href="<?php echo esc_url( mrmurphy_get_microblog_category_url() ); ?>">#microblog</a>
			</div>
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
