<?php
/**
 * Front-page SEO meta from Customizer profile fields.
 *
 * The homepage uses front-page.php (not page content). Jetpack otherwise falls
 * back to the static front page post_content for meta description.
 *
 * @package MrMurphy
 */

defined( 'ABSPATH' ) || exit;

/**
 * Build a meta description from Customizer profile settings.
 *
 * @return string
 */
function mrmurphy_get_front_page_meta_description() {
	$name  = get_theme_mod( 'mrmurphy_profile_name', get_bloginfo( 'name' ) );
	$title = get_theme_mod( 'mrmurphy_profile_title', '' );
	$bio   = wp_strip_all_tags( (string) get_theme_mod( 'mrmurphy_profile_bio', '' ) );
	$bio   = trim( preg_replace( '/\s+/', ' ', $bio ) );

	$lead = '';
	if ( $name && $title ) {
		$lead = sprintf( '%s, %s.', $name, $title );
	} elseif ( $name ) {
		$lead = rtrim( $name, '.' ) . '.';
	}

	if ( $lead && $bio ) {
		$description = $lead . ' ' . $bio;
	} elseif ( $bio ) {
		$description = $bio;
	} else {
		$description = $lead;
	}

	if ( ! $description ) {
		$description = get_bloginfo( 'description' );
	}

	$max = (int) apply_filters( 'jetpack_seo_front_page_description_max_length', 300 );

	if ( function_exists( 'mb_strlen' ) && mb_strlen( $description ) > $max ) {
		return mb_substr( $description, 0, $max - 3 ) . '...';
	}

	if ( strlen( $description ) > $max ) {
		return substr( $description, 0, $max - 3 ) . '...';
	}

	return $description;
}

/**
 * Override Jetpack SEO meta description on the front page.
 *
 * @param array $meta Meta tags keyed by name.
 * @return array
 */
function mrmurphy_filter_jetpack_seo_meta_tags( $meta ) {
	if ( ! is_front_page() ) {
		return $meta;
	}

	$meta['description'] = mrmurphy_get_front_page_meta_description();

	return $meta;
}
add_filter( 'jetpack_seo_meta_tags', 'mrmurphy_filter_jetpack_seo_meta_tags' );

/**
 * Override Jetpack Open Graph description on the front page.
 *
 * @param array $tags Open Graph tags.
 * @return array
 */
function mrmurphy_filter_jetpack_open_graph_tags( $tags ) {
	if ( ! is_front_page() ) {
		return $tags;
	}

	$tags['og:description'] = mrmurphy_get_front_page_meta_description();

	return $tags;
}
add_filter( 'jetpack_open_graph_tags', 'mrmurphy_filter_jetpack_open_graph_tags', 20 );

/**
 * Output meta description when Jetpack SEO is not handling it.
 */
function mrmurphy_output_front_page_meta_description() {
	if ( ! is_front_page() || class_exists( 'Jetpack_SEO' ) ) {
		return;
	}

	$description = mrmurphy_get_front_page_meta_description();
	if ( ! $description ) {
		return;
	}

	printf(
		'<meta name="description" content="%s" />' . "\n",
		esc_attr( $description )
	);
}
add_action( 'wp_head', 'mrmurphy_output_front_page_meta_description', 1 );
