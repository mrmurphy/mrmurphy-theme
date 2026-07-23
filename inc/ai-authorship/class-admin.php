<?php
/**
 * Admin UI for AI Authorship.
 *
 * Adds a Gutenberg sidebar panel for editing authorship data.
 *
 * @package mrmurphy-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MRMurphy_Authorship_Admin {

	/** @var MRMurphy_Authorship_Categories */
	private $categories;

	/** @var MRMurphy_Authorship_Meta */
	private $meta;

	/** @var MRMurphy_Authorship_Render */
	private $render;

	public function __construct( $categories, $meta, $render ) {
		$this->categories = $categories;
		$this->meta       = $meta;
		$this->render     = $render;

		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_editor_assets' ) );
	}

	/**
	 * Enqueue Gutenberg editor assets.
	 */
	public function enqueue_editor_assets() {
		$screen = get_current_screen();
		if ( ! $screen ) {
			return;
		}

		$post_type = $screen->post_type;
		$supported_types = array( 'post', 'page' );
		if ( ! in_array( $post_type, $supported_types, true ) ) {
			return;
		}

		$asset_file = MRMURPHY_AUTHORSHIP_DIR . 'editor.asset.php';
		$deps = array();
		$version = MRMURPHY_AUTHORSHIP_VERSION;

		if ( file_exists( $asset_file ) ) {
			$asset = include $asset_file;
			$deps = $asset['dependencies'] ?? array();
			$version = $asset['version'] ?? MRMURPHY_AUTHORSHIP_VERSION;
		}

		wp_enqueue_script(
			'mrmurphy-authorship-editor',
			get_theme_file_uri( '/assets/js/ai-authorship-editor.js' ),
			array_merge( array( 'wp-block-editor', 'wp-blocks', 'wp-components', 'wp-compose', 'wp-element', 'wp-plugins', 'wp-editor' ), $deps ),
			$version,
			true
		);

		$categories = $this->categories->get_all();
		$icons = $this->categories->get_available_icons();

		wp_localize_script( 'mrmurphy-authorship-editor', 'mrmurphyAuthorship', array(
			'categories' => $categories,
			'icons'      => $icons,
			'meta_key'   => MRMURPHY_AUTHORSHIP_META_KEY,
		) );
	}

	/**
	 * Add a legacy meta box as a fallback for classic editor users.
	 */
	public function add_legacy_meta_box() {
		$supported_types = array( 'post', 'page' );
		foreach ( $supported_types as $post_type ) {
			if ( post_type_supports( $post_type, 'editor' ) ) {
				add_meta_box(
					'mrmurphy_authorship_meta_box',
					__( 'AI Authorship', 'mrmurphy-theme' ),
					array( $this, 'render_meta_box' ),
					$post_type,
					'side',
					'default'
				);
			}
		}
	}

	/**
	 * Render the legacy meta box.
	 *
	 * @param WP_Post $post Post object.
	 */
	public function render_meta_box( $post ) {
		wp_nonce_field( 'mrmurphy_authorship_save', 'mrmurphy_authorship_nonce' );

		$data = $this->meta->get( $post->ID );
		$categories = $this->categories->get_all();

		echo '<p class="description">' . esc_html__( 'Attribution for AI-generated or AI-assisted content.', 'mrmurphy-theme' ) . '</p>';
		echo '<p class="heroicon-attribution"><small>' . sprintf(
			/* translators: %s: license name */
			__( 'Icons by Heroicons, licensed under %s.', 'mrmurphy-theme' ),
			'<a href="https://heroicons.com" target="_blank" rel="noopener">MIT License</a>'
		) . '</small></p>';

		echo '<div class="mrmurphy-authorship-meta-box">';

		foreach ( $data as $category => $entries ) {
			$cat_config = isset( $categories[ $category ] ) ? $categories[ $category ] : null;
			if ( ! $cat_config ) {
				continue;
			}

			echo '<div class="mrmurphy-authorship-category">';
			echo '<h4>' . esc_html( $cat_config['label'] ) . '</h4>';

			if ( ! empty( $entries ) && is_array( $entries ) ) {
				echo '<ul class="mrmurphy-authorship-entries">';
				foreach ( $entries as $i => $entry ) {
					echo '<li>';
					echo '<input type="text" class="widefat" name="mrmurphy_authorship[' . esc_attr( $category ) . '][' . $i . '][name]" value="' . esc_attr( $entry['name'] ) . '" />';
					if ( ! empty( $entry['link'] ) ) {
						echo '<input type="url" class="widefat" name="mrmurphy_authorship[' . esc_attr( $category ) . '][' . $i . '][link]" value="' . esc_attr( $entry['link'] ) . '" placeholder="https://" />';
					}
					echo '</li>';
				}
				echo '</ul>';
			}

			echo '<input type="text" class="widefat mrmurphy-authorship-new-name" placeholder="' . esc_attr__( 'Add name...', 'mrmurphy-theme' ) . '" data-category="' . esc_attr( $category ) . '" />';
			echo '<input type="url" class="widefat mrmurphy-authorship-new-link" placeholder="https://" data-category="' . esc_attr( $category ) . '" />';
			echo '<button type="button" class="button mrmurphy-authorship-add" data-category="' . esc_attr( $category ) . '">' . esc_html__( 'Add', 'mrmurphy-theme' ) . '</button>';

			echo '</div>';
		}

		echo '</div>';
	}

	/**
	 * Save legacy meta box data.
	 *
	 * @param int $post_id Post ID.
	 */
	public function save_legacy_meta_box( $post_id ) {
		if ( ! isset( $_POST['mrmurphy_authorship_nonce'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $_POST['mrmurphy_authorship_nonce'], 'mrmurphy_authorship_save' ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		if ( ! isset( $_POST['mrmurphy_authorship'] ) || ! is_array( $_POST['mrmurphy_authorship'] ) ) {
			return;
		}

		$data = array();
		$categories = $this->categories->get_all();
		$allowed_categories = array_keys( $categories );

		foreach ( $_POST['mrmurphy_authorship'] as $category => $entries ) {
			$category = sanitize_key( $category );
			if ( ! in_array( $category, $allowed_categories, true ) ) {
				continue;
			}

			if ( ! is_array( $entries ) ) {
				continue;
			}

			$data[ $category ] = array();
			foreach ( $entries as $entry ) {
				if ( empty( $entry['name'] ) ) {
					continue;
				}

				$item = array(
					'name' => sanitize_text_field( $entry['name'] ),
				);

				if ( ! empty( $entry['link'] ) ) {
					$item['link'] = esc_url_raw( $entry['link'] );
				}

				$data[ $category ][] = $item;
			}
		}

		$this->meta->save( $post_id, $data );
	}
}
