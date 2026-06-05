<?php
/**
 * Frontend rendering for AI Authorship.
 *
 * @package mrmurphy-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MRMurphy_Authorship_Render {

	/** @var MRMurphy_Authorship_Categories */
	private $categories;

	/** @var MRMurphy_Authorship_Meta */
	private $meta;

	/** @var int */
	private $current_post_id;

	public function __construct( $categories, $meta ) {
		$this->categories = $categories;
		$this->meta       = $meta;
	}

	/**
	 * Public wrapper for rendering authorship.
	 *
	 * @param int $post_id Post ID.
	 */
	public static function render_post( $post_id ) {
		$categories = new MRMurphy_Authorship_Categories();
		$meta       = new MRMurphy_Authorship_Meta();
		$render     = new self( $categories, $meta );
		$render->render( $post_id );
	}

	/**
	 * Render the authorship pill button and details section.
	 *
	 * @param int $post_id Post ID.
	 */
	public function render( $post_id ) {
		$data = $this->meta->get( $post_id );

		// If no human author is explicitly set, add the WP post author as human.
		if ( empty( $data['human'] ) || ! is_array( $data['human'] ) || empty( $data['human'][0]['name'] ) ) {
			$author_id = get_post_field( 'post_author', $post_id );
			if ( $author_id > 0 ) {
				$author = get_userdata( $author_id );
				if ( $author && ! empty( $author->display_name ) ) {
					$data['human'] = array(
						array( 'name' => $author->display_name ),
					);
				}
			}
		}

		// If still no data after fallback, don't render.
		if ( empty( $data ) || ! is_array( $data ) ) {
			return;
		}

		$unique_id = 'authorship-' . $post_id;

		echo '<div class="authorship-pill--wrapper">';
		echo $this->get_pill( $data, $unique_id );
		echo $this->get_details( $data, $unique_id );
		echo '</div>'; // .authorship-pill--wrapper
	}

	/**
	 * Get count of entries per category from given data.
	 *
	 * @param array $data Authorship data.
	 * @return array
	 */
	private function get_data_counts( $data ) {
		$counts = array();
		foreach ( $data as $category => $items ) {
			if ( ! empty( $items ) && is_array( $items ) ) {
				$counts[ $category ] = count( $items );
			}
		}
		return $counts;
	}

	/**
	 * Get pill label and color data.
	 *
	 * @param array  $data      Authorship data.
	 * @param string $unique_id Unique ID for aria attributes.
	 * @return array { label: string, color: string }
	 */
	private function get_pill_data( $data ) {
		$counts     = $this->get_data_counts( $data );
		$categories = $this->categories->get_all();

		$labels      = array();
		$first_color = 'var(--color-green, #16a34a)';
		foreach ( $counts as $cat => $count ) {
			if ( isset( $categories[ $cat ] ) ) {
				$labels[]    = sprintf( '%d %s', $count, $this->categories->get_label( $cat, $count ) );
				$first_color = $categories[ $cat ]['color'];
			}
		}

		return array(
			'label' => implode( ', ', $labels ),
			'color' => $first_color,
		);
	}

	/**
	 * Get the pill button markup.
	 *
	 * @param array  $data        Authorship data.
	 * @param string $unique_id   Unique ID for aria attributes.
	 * @return string
	 */
	private function get_pill( $data, $unique_id ) {
		$pill = $this->get_pill_data( $data );

		return sprintf(
			'<button class="authorship-pill" aria-expanded="false" aria-controls="%s" id="%s-toggle" style="--pill-color:%s">' .
				'<span class="authorship-pill__icon" aria-hidden="true"></span>' .
				'<span class="authorship-pill__label">%s</span>' .
				'<span class="authorship-pill__chevron" aria-hidden="true"></span>' .
			'</button>',
			esc_attr( $unique_id ),
			esc_attr( $unique_id ),
			esc_attr( $pill['color'] ),
			esc_html( $pill['label'] )
		);
	}

	/**
	 * Get the details section markup.
	 *
	 * @param array  $data      Full authorship data.
	 * @param string $unique_id Unique ID for aria attributes.
	 * @return string
	 */
	private function get_details( $data, $unique_id ) {
		$categories = $this->categories->get_all();
		$pill       = $this->get_pill_data( $data );

		$details = '<div class="authorship-details" id="' . esc_attr( $unique_id ) . '" role="region">';

		// Header — matches pill appearance when collapsed.
		$details .= sprintf(
			'<div class="authorship-pill authorship-pill--expanded authorship-details__header" style="--pill-color:%s">',
			esc_attr( $pill['color'] )
		);
		$details .= '<span class="authorship-pill__icon" aria-hidden="true"></span>';
		$details .= sprintf( '<span class="authorship-pill__label">%s</span>', esc_html( $pill['label'] ) );
		$details .= '<span class="authorship-pill__chevron" aria-hidden="true"></span>';
		$details .= '</div>'; // .authorship-details__header

		// Body — animates from height 0.
		$details .= '<div class="authorship-details__body">';
		$details .= '<div class="authorship-details__info">';
		$details .= sprintf(
			'<h3 class="authorship-details__title">%s</h3>',
			esc_html__( 'Authorship Info', 'mrmurphy-theme' )
		);
		$details .= sprintf(
			'<p class="authorship-details__subtitle">%s</p>',
			esc_html__( 'This post was written by the following people, bots, and tools.', 'mrmurphy-theme' )
		);
		$details .= '</div>'; // .authorship-details__info

		$counts = $this->get_data_counts( $data );
		foreach ( $data as $category => $entries ) {
			if ( empty( $entries ) || ! is_array( $entries ) ) {
				continue;
			}

			$cat_config = isset( $categories[ $category ] ) ? $categories[ $category ] : null;
			if ( ! $cat_config ) {
				continue;
			}

			$icon_svg = $this->categories->get_icon_svg( $cat_config['icon'], 24, 24 );
			$count    = count( $entries );
			$label    = $this->categories->get_label( $category, $count );

			$details .= sprintf(
				'<div class="authorship-category" data-category="%s">',
				esc_attr( $category )
			);

			$details .= sprintf(
				'<div class="authorship-category__header" style="--cat-color: %s;">',
				esc_attr( $cat_config['color'] )
			);

			$details .= sprintf(
				'<span class="authorship-category__icon">%s</span>',
				$icon_svg
			);

			$details .= sprintf(
				'<span class="authorship-category__title">%s</span>',
				esc_html( $label )
			);

			$details .= sprintf(
				'<span class="authorship-category__count">%d</span>',
				$count
			);

			$details .= '</div>'; // .authorship-category__header

			$details .= '<ul class="authorship-category__list">';

			foreach ( $entries as $entry ) {
				$name = esc_html( $entry['name'] );
				if ( ! empty( $entry['link'] ) ) {
					$link = esc_url( $entry['link'] );
					$details .= sprintf(
						'<li class="authorship-category__item"><a href="%s" class="authorship-category__link">%s</a></li>',
						$link,
						$name
					);
				} else {
					$details .= sprintf(
						'<li class="authorship-category__item">%s</li>',
						$name
					);
				}
			}

			$details .= '</ul>'; // .authorship-category__list
			$details .= '</div>'; // .authorship-category
		}

		$details .= '</div>'; // .authorship-details__body
		$details .= '</div>'; // .authorship-details

		return $details;
	}
}
