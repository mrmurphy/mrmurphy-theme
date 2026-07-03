<?php
/**
 * Share / reblog dialog for microblog cards.
 *
 * Rendered server-side and injected into the shared share `<dialog>` when a
 * card's Reblog button is clicked. Top block = intent URLs that always work;
 * bottom block = Jetpack Publicize mirror links only when they actually exist.
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

$post = get_post( $post_id );
if ( ! $post ) {
	return;
}

$permalink = get_permalink( $post );
$title     = get_the_title( $post );
if ( '' === trim( (string) $title ) ) {
	$site_title = get_bloginfo( 'name' );
	$title      = wp_strip_all_tags( wp_trim_words( wp_strip_all_tags( $post->post_content ), 12, '…' ) );
	if ( '' === trim( $title ) ) {
		$title = $site_title;
	}
}

$encoded_url   = rawurlencode( $permalink );
$encoded_title = rawurlencode( $title );

$author_id = (int) $post->post_author;

$intent_rows = array(
	array(
		'platform' => __( 'Mastodon', 'mrmurphy' ),
		'url'      => 'https://toot.kytta.dev/?text=' . $encoded_title . '&url=' . $encoded_url,
		'icon'     => 'M',
		'icon_bg'  => '#6364ff',
	),
	array(
		'platform' => __( 'Bluesky', 'mrmurphy' ),
		'url'      => 'https://bsky.app/intent/compose?text=' . $encoded_title . '%20' . $encoded_url,
		'icon'     => 'B',
		'icon_bg'  => '#1185fe',
	),
	array(
		'platform' => __( 'X (Twitter)', 'mrmurphy' ),
		'url'      => 'https://twitter.com/intent/tweet?text=' . $encoded_title . '&url=' . $encoded_url,
		'icon'     => 'X',
		'icon_bg'  => '#000000',
	),
	array(
		'platform' => __( 'Threads', 'mrmurphy' ),
		'url'      => 'https://www.threads.net/intent?post_text=' . $encoded_title . '%20' . $encoded_url,
		'icon'     => 'T',
		'icon_bg'  => '#000000',
	),
	array(
		'platform' => __( 'LinkedIn', 'mrmurphy' ),
		'url'      => 'https://www.linkedin.com/sharing/share-offsite/?url=' . $encoded_url,
		'icon'     => 'in',
		'icon_bg'  => '#0a66c2',
	),
	array(
		'platform' => __( 'WhatsApp', 'mrmurphy' ),
		'url'      => 'https://wa.me/?text=' . $encoded_title . '%20' . $encoded_url,
		'icon'     => 'W',
		'icon_bg'  => '#25d366',
	),
	array(
		'platform' => __( 'Email', 'mrmurphy' ),
		'url'      => 'mailto:?subject=' . $encoded_title . '&body=' . $encoded_url,
		'icon'     => 'email',
		'icon_bg'  => '#727072',
	),
);

$mirrors = function_exists( 'mrmurphy_get_jetpack_publicize_mirrors' )
	? mrmurphy_get_jetpack_publicize_mirrors( $post_id )
	: array();
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
	<button type="button" class="mb-dialog__close" data-mb-dialog-close aria-label="<?php esc_attr_e( 'Close share dialog', 'mrmurphy' ); ?>">×</button>
</div>

<p class="mb-dialog__intro"><?php esc_html_e( 'Pick the platform where you’d like to reshare this post, and follow the link to do it there.', 'mrmurphy' ); ?></p>

<ul class="mb-dialog__list">
	<?php foreach ( $intent_rows as $row ) : ?>
		<li class="mb-dialog__row">
			<a class="mb-dialog__link" href="<?php echo esc_url( $row['url'] ); ?>" target="_blank" rel="noopener noreferrer">
				<span class="mb-dialog__icon" style="background:<?php echo esc_attr( $row['icon_bg'] ); ?>">
					<?php
					$icon_html = mrmurphy_get_icon( $row['icon'] );
					echo '' !== $icon_html ? $icon_html : esc_html( $row['icon'] );
					?>
				</span>
				<span class="mb-dialog__platform"><?php echo esc_html( $row['platform'] ); ?></span>
				<span class="mb-dialog__arrow" aria-hidden="true">→</span>
			</a>
		</li>
	<?php endforeach; ?>
	<li class="mb-dialog__row">
		<button type="button" class="mb-dialog__link" data-mb-copy-link data-permalink="<?php echo esc_url( $permalink ); ?>">
			<span class="mb-dialog__icon" style="background:#727072"><?php echo mrmurphy_get_icon( 'link' ); ?></span>
			<span class="mb-dialog__platform"><?php esc_html_e( 'Copy link', 'mrmurphy' ); ?></span>
			<span class="mb-dialog__arrow" data-mb-copy-status aria-hidden="true">→</span>
		</button>
	</li>
</ul>

<?php if ( ! empty( $mirrors ) ) : ?>
	<div class="mb-dialog__sep" role="separator"></div>
	<p class="mb-dialog__mirror-label"><?php esc_html_e( 'Also published on (via Jetpack)', 'mrmurphy' ); ?></p>
	<ul class="mb-dialog__list">
		<?php foreach ( $mirrors as $m ) : ?>
			<li class="mb-dialog__row mb-dialog__row--mirror">
				<a class="mb-dialog__link" href="<?php echo esc_url( $m['url'] ); ?>" target="_blank" rel="noopener noreferrer">
					<span class="mb-dialog__mirror-pill"><?php echo esc_html( $m['platform'] ); ?></span>
					<span class="mb-dialog__platform"><?php esc_html_e( 'View this post on', 'mrmurphy' ); ?> <?php echo esc_html( $m['platform'] ); ?></span>
					<span class="mb-dialog__arrow" aria-hidden="true">→</span>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
<?php endif; ?>