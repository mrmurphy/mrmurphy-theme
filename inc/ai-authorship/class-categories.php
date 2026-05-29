<?php
/**
 * Category definitions for AI Authorship.
 *
 * @package mrmurphy-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MRMurphy_Authorship_Categories {

	/**
	 * Default category definitions.
	 *
	 * @var array
	 */
	private $defaults = array(
		'human'  => array(
			'label'    => 'Human',
			'icon'     => 'user',
			'color'    => 'var(--color-green)',
			'bg_color' => 'var(--color-green-100, #ecfdf5)',
		),
		'model'  => array(
			'label'    => 'AI Model',
			'icon'     => 'cpu-chip',
			'color'    => 'var(--color-orange)',
			'bg_color' => 'var(--color-orange-100, #fff7ed)',
		),
		'agent'  => array(
			'label'    => 'AI Agent',
			'icon'     => 'sparkles',
			'color'    => 'var(--color-purple)',
			'bg_color' => 'var(--color-purple-100, #f5f3ff)',
		),
		'skill'  => array(
			'label'    => 'Skill',
			'icon'     => 'light-bulb',
			'color'    => 'var(--color-yellow)',
			'bg_color' => 'var(--color-yellow-100, #fefce8)',
		),
		'harness' => array(
			'label'    => 'Harness',
			'icon'     => 'server',
			'color'    => 'var(--color-cyan)',
			'bg_color' => 'var(--color-cyan-100, #ecfeff)',
		),
	);

	/**
	 * Registered categories (defaults + custom).
	 *
	 * @var array
	 */
	private $registered = array();

	public function __construct() {
		$this->registered = $this->defaults;

		$custom = get_option( 'mrmurphy_authorship_custom_categories', array() );
		if ( ! empty( $custom ) && is_array( $custom ) ) {
			foreach ( $custom as $slug => $config ) {
				if ( is_string( $slug ) && is_array( $config ) ) {
					$this->registered[ $slug ] = wp_parse_args(
						$config,
						array(
							'label'    => $slug,
							'icon'     => 'question-mark-circle',
							'color'    => 'var(--color-purple)',
							'bg_color' => 'var(--color-purple-100, #f5f3ff)',
						)
					);
				}
			}
		}
	}

	/**
	 * Get all registered categories.
	 *
	 * @return array
	 */
	public function get_all() {
		return $this->registered;
	}

	/**
	 * Get a single category by slug.
	 *
	 * @param string $slug Category slug.
	 * @return array|null
	 */
	public function get( $slug ) {
		return isset( $this->registered[ $slug ] ) ? $this->registered[ $slug ] : null;
	}

	/**
	 * Get default categories only.
	 *
	 * @return array
	 */
	public function get_defaults() {
		return $this->defaults;
	}

	/**
	 * Get all available heroicon names for custom category selection.
	 *
	 * @return array
	 */
	public function get_available_icons() {
		return array(
			'user',
			'users',
			'identification',
			'academic-cap',
			'briefcase',
			'cpu-chip',
			'cog',
			'globe-alt',
			'light-bulb',
			'sparkles',
			'sun',
			'moon',
			'server',
			'database',
			'cloud',
			'code',
			'puzzle',
			'wrench',
			'hammer',
			'swatch',
			'paint-brush',
			'pen',
			'pencil',
			'book-open',
			'book',
			'academic-cap',
			'chat-bubble-bottom-center-text',
			'chat-bubble-bottom-center',
			'chat-bubble-left-ellipsis',
			'chat-bubble-left-right',
			'chat-bubble-left',
			'chat-bubble-oval-left-ellipsis',
			'chat-bubble-oval-left',
			'command-line',
			'computer-desktop',
			'laptop',
			'mobile-device',
			'shape-badge',
			'shield-check',
			'shield-exclamation',
			'arrow-path',
			'arrow-trending',
			'chart-bar',
			'chart-pie',
			'clipboard-document-check',
			'clipboard-document-list',
			'clock',
			'calendar',
			'bell',
			'flag',
			'heart',
			'star',
			'thumbs-up',
			'thumbs-down',
			'eye',
			'eye-slash',
			'lock-closed',
			'lock-open',
			'key',
			'lock-closed',
			'folder',
			'folder-open',
			'document',
			'document-text',
			'folder-plus',
			'document-plus',
			'check',
			'x-mark',
			'plus',
			'minus',
			'chevron-up',
			'chevron-down',
			'chevron-left',
			'chevron-right',
			'chevron-double-up',
			'chevron-double-down',
			'arrow-up',
			'arrow-down',
			'arrow-left',
			'arrow-right',
		);
	}

	/**
	 * Get heroicon SVG markup.
	 *
	 * @param string $name Icon name.
	 * @param int    $width Width.
	 * @param int    $height Height.
	 * @return string
	 */
	public function get_icon_svg( $name, $width = 16, $height = 16 ) {
		$icons = array(
			'user' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 7.5 17.998A17.933 17.933 0 0 1 4.501 20.118Z"/>',
			'users' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.96 6.96 0 0 0 4.501 16.5m13.5-3a3 3 0 1 1-6 0 3 3 0 0 1 6 0ZM6.75 12a3 3 0 1 0-6 0 3 3 0 0 0 6 0Z"/>',
			'cpu-chip' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.25 3v1.5M4.5 8.25H3m18 0h-1.5M4.5 12H3m18 0h-1.5m-15 3.75H3m18 0h-1.5M8.25 19.5V21M12 3v1.5m0 15V21m3.75-18v1.5m0 15V21m-9-1.5h10.5a2.25 2.25 0 0 0 2.25-2.25V6.75a2.25 2.25 0 0 0-2.25-2.25H6.75A2.25 2.25 0 0 0 4.5 6.75v10.5a2.25 2.25 0 0 0 2.25 2.25Zm.75-12h9v9h-9v-9Z"/>',
			'sparkles' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456ZM16.894 20.567 16.5 21.75l-.394-1.183a2.25 2.25 0 0 0-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 0 0 1.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 0 0 1.423 1.423l1.183.394-1.183.394a2.25 2.25 0 0 0-1.423 1.423Z"/>',
			'light-bulb' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18v-1.5m0 0a7.5 7.5 0 1 0-3-5.85m3 5.85V12m-6.375 0h12.75m-12.75 3h12.75M12 3v1.5"/>',
			'server' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 7.5a2.25 2.25 0 0 0-2.25-2.25H5.25A2.25 2.25 0 0 0 3 7.5v9a2.25 2.25 0 0 0 2.25 2.25h13.5A2.25 2.25 0 0 0 21 16.5v-9ZM4.5 3v18m16.5-18v18"/>',
			'question-mark-circle' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.879 3.758a4.496 4.496 0 0 1 4.242 7.407c-1.056.807-2.59.807-3.646 0a2.248 2.248 0 0 0-3.476.952l-.152.457a1.124 1.124 0 0 0 1.736 1.278l.213-.154a.75.75 0 0 1 .933 1.178l-.213.154a2.625 2.625 0 0 1-4.061-2.993l.152-.457a3.748 3.748 0 0 1 5.772-1.988ZM10.5 15.75a1.5 1.5 0 1 0 3 0 1.5 1.5 0 0 0-3 0Z"/>',
		);

		if ( isset( $icons[ $name ] ) ) {
			return sprintf(
				'<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 16 16" width="%d" height="%d" aria-hidden="true">%s</svg>',
				$width,
				$height,
				$icons[ $name ]
			);
		}

		// Fallback: try to render a generic circle with the first letter.
		return sprintf(
			'<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 16 16" width="%d" height="%d" aria-hidden="true"><circle cx="8" cy="8" r="7" stroke="currentColor" stroke-width="1.5" fill="none"/></svg>',
			$width,
			$height
		);
	}

	/**
	 * Register a custom category.
	 *
	 * @param string $slug   Category slug.
	 * @param array  $config Category config.
	 * @return bool
	 */
	public function register_custom( $slug, $config ) {
		$slug = sanitize_key( $slug );
		if ( isset( $this->defaults[ $slug ] ) ) {
			return false;
		}

		$this->registered[ $slug ] = wp_parse_args(
			$config,
			array(
				'label'    => $slug,
				'icon'     => 'question-mark-circle',
				'color'    => 'var(--color-purple)',
				'bg_color' => 'var(--color-purple-100, #f5f3ff)',
			)
		);

		$custom = get_option( 'mrmurphy_authorship_custom_categories', array() );
		$custom[ $slug ] = $this->registered[ $slug ];
		update_option( 'mrmurphy_authorship_custom_categories', $custom );

		return true;
	}

	/**
	 * Unregister a custom category.
	 *
	 * @param string $slug Category slug.
	 * @return bool
	 */
	public function unregister_custom( $slug ) {
		$slug = sanitize_key( $slug );
		if ( isset( $this->defaults[ $slug ] ) ) {
			return false;
		}

		unset( $this->registered[ $slug ] );

		$custom = get_option( 'mrmurphy_authorship_custom_categories', array() );
		unset( $custom[ $slug ] );
		update_option( 'mrmurphy_authorship_custom_categories', $custom );

		return true;
	}
}
