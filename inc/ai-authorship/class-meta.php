<?php
/**
 * Meta handling for AI Authorship.
 *
 * @package mrmurphy-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MRMurphy_Authorship_Meta {

	/**
	 * Get authorship data for a post.
	 *
	 * @param int $post_id Post ID.
	 * @return array
	 */
	public function get( $post_id ) {
		$data = get_post_meta( $post_id, MRMURPHY_AUTHORSHIP_META_KEY, true );

		if ( ! is_array( $data ) ) {
			return array();
		}

		return $this->normalize( $data );
	}

	/**
	 * Save authorship data for a post.
	 *
	 * @param int   $post_id Post ID.
	 * @param array $data    Authorship data.
	 * @return array|WP_Error Saved data or error.
	 */
	public function save( $post_id, $data ) {
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return new WP_Error( 'permission_denied', __( 'You do not have permission to edit this post.', 'mrmurphy-theme' ) );
		}

		$validated = $this->validate( $data );
		if ( is_wp_error( $validated ) ) {
			return $validated;
		}

		$normalized = $this->normalize( $validated );
		$json = wp_json_encode( $normalized );

		if ( empty( $json ) || '[]' === $json ) {
			delete_post_meta( $post_id, MRMURPHY_AUTHORSHIP_META_KEY );
		} else {
			update_post_meta( $post_id, MRMURPHY_AUTHORSHIP_META_KEY, $json );
		}

		return $normalized;
	}

	/**
	 * Add an entry to a category.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $category Category slug.
	 * @param string $name Entry name.
	 * @param string $link Entry URL (optional).
	 * @return array|WP_Error
	 */
	public function add_entry( $post_id, $category, $name, $link = '' ) {
		$data = $this->get( $post_id );

		$category = sanitize_key( $category );
		$name     = sanitize_text_field( $name );
		$link     = esc_url_raw( $link );

		$entry = array(
			'name' => $name,
		);
		if ( ! empty( $link ) ) {
			$entry['link'] = $link;
		}

		if ( ! isset( $data[ $category ] ) ) {
			$data[ $category ] = array();
		}

		$data[ $category ][] = $entry;

		return $this->save( $post_id, $data );
	}

	/**
	 * Remove an entry from a category.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $category Category slug.
	 * @param int    $index Entry index.
	 * @return array|WP_Error
	 */
	public function remove_entry( $post_id, $category, $index ) {
		$data = $this->get( $post_id );

		$category = sanitize_key( $category );
		$index    = absint( $index );

		if ( ! isset( $data[ $category ] ) ) {
			return new WP_Error( 'invalid_category', __( 'Invalid category.', 'mrmurphy-theme' ) );
		}

		if ( $index < 0 || $index >= count( $data[ $category ] ) ) {
			return new WP_Error( 'invalid_index', __( 'Invalid entry index.', 'mrmurphy-theme' ) );
		}

		array_splice( $data[ $category ], $index, 1 );

		if ( empty( $data[ $category ] ) ) {
			unset( $data[ $category ] );
		}

		return $this->save( $post_id, $data );
	}

	/**
	 * Validate authorship data structure.
	 *
	 * @param array $data Data to validate.
	 * @return array|WP_Error
	 */
	public function validate( $data ) {
		if ( ! is_array( $data ) ) {
			return new WP_Error( 'invalid_data', __( 'Data must be an array.', 'mrmurphy-theme' ) );
		}

		$categories = new MRMurphy_Authorship_Categories();
		$all_categories = array_keys( $categories->get_all() );

		foreach ( $data as $category => $entries ) {
			$category = sanitize_key( $category );

			if ( ! in_array( $category, $all_categories, true ) ) {
				return new WP_Error( 'invalid_category', sprintf( __( 'Invalid category: %s', 'mrmurphy-theme' ), $category ) );
			}

			if ( ! is_array( $entries ) ) {
				return new WP_Error( 'invalid_entries', sprintf( __( 'Entries for %s must be an array.', 'mrmurphy-theme' ), $category ) );
			}

			foreach ( $entries as $i => $entry ) {
				if ( ! is_array( $entry ) ) {
					return new WP_Error( 'invalid_entry', sprintf( __( 'Entry %d in %s must be an array.', 'mrmurphy-theme' ), $i, $category ) );
				}

				if ( empty( $entry['name'] ) || ! is_string( $entry['name'] ) ) {
					return new WP_Error( 'missing_name', sprintf( __( 'Entry %d in %s must have a name.', 'mrmurphy-theme' ), $i, $category ) );
				}

				$data[ $category ][ $i ]['name'] = sanitize_text_field( $entry['name'] );

				if ( isset( $entry['link'] ) ) {
					$data[ $category ][ $i ]['link'] = esc_url_raw( $entry['link'] );
				}
			}
		}

		return $data;
	}

	/**
	 * Normalize authorship data (ensure consistent structure).
	 *
	 * @param array $data Data to normalize.
	 * @return array
	 */
	public function normalize( $data ) {
		$normalized = array();

		foreach ( $data as $category => $entries ) {
			$category = sanitize_key( $category );
			if ( empty( $entries ) || ! is_array( $entries ) ) {
				continue;
			}

			$normalized[ $category ] = array();
			foreach ( $entries as $entry ) {
				if ( ! is_array( $entry ) || empty( $entry['name'] ) ) {
					continue;
				}

				$item = array(
					'name' => sanitize_text_field( $entry['name'] ),
				);

				if ( ! empty( $entry['link'] ) ) {
					$item['link'] = esc_url_raw( $entry['link'] );
				}

				$normalized[ $category ][] = $item;
			}
		}

		return $normalized;
	}

	/**
	 * Get a flat list of all entries across categories.
	 *
	 * @param int $post_id Post ID.
	 * @return array
	 */
	public function get_all_entries( $post_id ) {
		$data = $this->get( $post_id );
		$entries = array();

		foreach ( $data as $category => $items ) {
			foreach ( $items as $item ) {
				$entries[] = array(
					'category' => $category,
					'name'     => $item['name'],
					'link'     => isset( $item['link'] ) ? $item['link'] : '',
				);
			}
		}

		return $entries;
	}

	/**
	 * Get count of entries per category.
	 *
	 * @param int $post_id Post ID.
	 * @return array
	 */
	public function get_counts( $post_id ) {
		$data = $this->get( $post_id );
		$counts = array();

		foreach ( $data as $category => $items ) {
			if ( ! empty( $items ) && is_array( $items ) ) {
				$counts[ $category ] = count( $items );
			}
		}

		return $counts;
	}

	/**
	 * Check if a post has any authorship data.
	 *
	 * @param int $post_id Post ID.
	 * @return bool
	 */
	public function has_data( $post_id ) {
		$counts = $this->get_counts( $post_id );
		return ! empty( $counts );
	}
}
