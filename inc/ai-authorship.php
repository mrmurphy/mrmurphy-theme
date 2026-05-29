<?php
/**
 * AI Authorship feature.
 *
 * Manages AI-generated content attribution via expandable pill buttons,
 * a Gutenberg sidebar panel, and a JSON post meta field for AI tools.
 *
 * @package mrmurphy-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'MRMURPHY_AUTHORSHIP_VERSION', '1.0.0' );
define( 'MRMURPHY_AUTHORSHIP_DIR', get_theme_file_path( '/inc/ai-authorship/' ) );
define( 'MRMURPHY_AUTHORSHIP_URL', get_theme_file_uri( '/inc/ai-authorship/' ) );
define( 'MRMURPHY_AUTHORSHIP_META_KEY', '_mrmurphy_authorship' );

/**
 * Load AI-authorship classes.
 */
require_once MRMURPHY_AUTHORSHIP_DIR . 'class-categories.php';
require_once MRMURPHY_AUTHORSHIP_DIR . 'class-meta.php';
require_once MRMURPHY_AUTHORSHIP_DIR . 'class-render.php';
require_once MRMURPHY_AUTHORSHIP_DIR . 'class-admin.php';

/**
 * Initialize AI-authorship.
 */
class MRMurphy_Authorship {

	/** @var MRMurphy_Authorship_Categories */
	public $categories;

	/** @var MRMurphy_Authorship_Meta */
	public $meta;

	/** @var MRMurphy_Authorship_Render */
	public $render;

	/** @var MRMurphy_Authorship_Admin */
	public $admin;

	public function __construct() {
		$this->categories = new MRMurphy_Authorship_Categories();
		$this->meta       = new MRMurphy_Authorship_Meta();
		$this->render     = new MRMurphy_Authorship_Render( $this->categories, $this->meta );
		$this->admin      = new MRMurphy_Authorship_Admin( $this->categories, $this->meta, $this->render );
	}
}

new MRMurphy_Authorship();

/**
 * Public wrapper for rendering authorship.
 *
 * @param int $post_id Post ID.
 */
function mrmurphy_authorship_render( $post_id ) {
	MRMurphy_Authorship_Render::render( $post_id );
}

/**
 * Migrate old meta keys to new JSON format.
 *
 * Runs on theme activation via a flag.
 */
function mrmurphy_authorship_migrate() {
	global $wpdb;

	// Find all posts that have old meta keys.
	$post_ids = $wpdb->get_col( "
		SELECT DISTINCT post_id FROM {$wpdb->postmeta}
		WHERE meta_key IN (
			'mrmurphy_post_human',
			'mrmurphy_post_human_name',
			'mrmurphy_post_model',
			'mrmurphy_post_model_link',
			'mrmurphy_post_skills',
			'mrmurphy_post_skills_links'
		)
	" );

	if ( empty( $post_ids ) ) {
		return;
	}

	$meta = new MRMurphy_Authorship_Meta();

	foreach ( $post_ids as $post_id ) {
		$data = array();

		// Human.
		$human = get_post_meta( $post_id, 'mrmurphy_post_human', true );
		$human_name = get_post_meta( $post_id, 'mrmurphy_post_human_name', true );

		if ( $human || $human_name ) {
			$human_value = $human_name ?: ( $human === '1' ? 'Human Author' : $human );
			$data['human'] = array(
				array( 'name' => sanitize_text_field( $human_value ) ),
			);
		}

		// Model.
		$model = get_post_meta( $post_id, 'mrmurphy_post_model', true );
		$model_link = get_post_meta( $post_id, 'mrmurphy_post_model_link', true );

		if ( $model ) {
			$item = array( 'name' => sanitize_text_field( $model ) );
			if ( $model_link ) {
				$item['link'] = esc_url_raw( $model_link );
			}
			$data['model'] = array( $item );
		}

		// Skills.
		$skills = get_post_meta( $post_id, 'mrmurphy_post_skills', true );
		$skill_links = get_post_meta( $post_id, 'mrmurphy_post_skills_links', true );

		if ( $skills ) {
			$skill_names = array_map( 'sanitize_text_field', array_filter( array_map( 'trim', explode( ',', $skills ) ) ) );
			$skill_urls  = array_filter( array_map( 'esc_url_raw', array_map( 'trim', explode( ',', $skill_links ) ) ) );

			$skill_items = array();
			foreach ( $skill_names as $i => $name ) {
				$item = array( 'name' => $name );
				if ( isset( $skill_urls[ $i ] ) && ! empty( $skill_urls[ $i ] ) ) {
					$item['link'] = $skill_urls[ $i ];
				}
				$skill_items[] = $item;
			}

			if ( ! empty( $skill_items ) ) {
				$data['skill'] = $skill_items;
			}
		}

		if ( ! empty( $data ) ) {
			$meta->save( $post_id, $data );

			// Delete old meta keys.
			delete_post_meta( $post_id, 'mrmurphy_post_human' );
			delete_post_meta( $post_id, 'mrmurphy_post_human_name' );
			delete_post_meta( $post_id, 'mrmurphy_post_model' );
			delete_post_meta( $post_id, 'mrmurphy_post_model_link' );
			delete_post_meta( $post_id, 'mrmurphy_post_skills' );
			delete_post_meta( $post_id, 'mrmurphy_post_skills_links' );
		}
	}
}

// Run migration on theme activation.
add_action( 'after_switch_theme', 'mrmurphy_authorship_migrate' );
