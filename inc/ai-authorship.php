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
 * Register authorship meta for REST API access (wpcom MCP, external tools).
 */
add_action( 'init', function () {
	$categories = array( 'human', 'model', 'agent', 'skill', 'harness' );

	$properties = array();
	foreach ( $categories as $cat ) {
		$properties[ $cat ] = array(
			'type'  => 'array',
			'items' => array(
				'type'       => 'object',
				'properties' => array(
					'name' => array( 'type' => 'string' ),
					'link' => array( 'type' => 'string', 'format' => 'uri' ),
				),
			),
		);
	}

	register_post_meta( 'post', MRMURPHY_AUTHORSHIP_META_KEY, array(
		'show_in_rest' => array(
			'schema' => array(
				'type'       => 'object',
				'properties' => $properties,
			),
		),
		'type'          => 'object',
		'default'       => array(),
		'single'        => true,
		'auth_callback' => function () { return current_user_can( 'edit_posts' ); },
	) );
} );

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
	MRMurphy_Authorship_Render::render_post( $post_id );
}
