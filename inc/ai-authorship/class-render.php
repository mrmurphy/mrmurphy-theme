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
		if ( ! $this->meta->has_data( $post_id ) ) {
			return;
		}

		$counts = $this->meta->get_counts( $post_id );
		$data   = $this->meta->get( $post_id );
		$unique_id = 'authorship-' . $post_id;

		echo '<div class="authorship-pill--wrapper">';
		echo $this->get_pill( $counts, $unique_id );
		echo $this->get_details( $data, $counts, $unique_id );
		echo '</div>'; // .authorship-pill--wrapper
	}

	/**
	 * Get the pill button markup.
	 *
	 * @param array  $counts    Entry counts per category.
	 * @param string $unique_id Unique ID for aria attributes.
	 * @return string
	 */
	private function get_pill( $counts, $unique_id ) {
		$total = array_sum( $counts );
		$categories = $this->categories->get_all();

		$labels = array();
		$first_color = 'var(--color-green, #16a34a)';
		foreach ( $counts as $cat => $count ) {
			if ( isset( $categories[ $cat ] ) ) {
				$label = $this->categories->get_label( $cat, $count );
				$labels[] = sprintf( '%d %s', $count, $label );
				if ( 'var(--color-green, #16a34a)' === $first_color ) {
					$first_color = $categories[ $cat ]['color'];
				}
			}
		}

		$label_text = implode( ', ', $labels );

		return sprintf(
			'<button class="authorship-pill" aria-expanded="false" aria-controls="%s" id="%s-toggle" style="--pill-color:%s">' .
				'<span class="authorship-pill__icon" aria-hidden="true"></span>' .
				'<span class="authorship-pill__label">%s</span>' .
				'<span class="authorship-pill__chevron" aria-hidden="true"></span>' .
			'</button>',
			esc_attr( $unique_id ),
			esc_attr( $unique_id ),
			esc_attr( $first_color ),
			esc_html( $label_text )
		);
	}

	/**
	 * Get the details section markup.
	 *
	 * @param array  $data    Full authorship data.
	 * @param array  $counts  Entry counts per category.
	 * @param string $unique_id Unique ID for aria attributes.
	 * @return string
	 */
	private function get_details( $data, $counts, $unique_id ) {
		$categories = $this->categories->get_all();
		$total     = array_sum( $counts );

		$details = '<div class="authorship-details" id="' . esc_attr( $unique_id ) . '" role="region">';

		$details .= '<div class="authorship-details__header">';
		$details  .= sprintf(
			'<h3 class="authorship-details__title">%s</h3>',
			esc_html__( 'Authorship Info', 'mrmurphy-theme' )
		);
		$details  .= sprintf(
			'<p class="authorship-details__subtitle">%s</p>',
			esc_html__( 'This post was written by the following people, bots, and tools.', 'mrmurphy-theme' )
		);
		$details .= '</div>'; // .authorship-details__header

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

		$details .= '</div>'; // .authorship-details

		return $details;
	}
}
