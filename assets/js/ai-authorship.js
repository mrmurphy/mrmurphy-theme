/**
 * AI Authorship Frontend Interactions
 *
 * Pill-to-panel animation: the pill stays in-flow to reserve space.
 * On open, the pill fades out (opacity 0, keeps layout) and the panel
 * (positioned absolutely at the pill's top edge) animates from
 * pill-width to full panel width while the body grows from height 0.
 * On close, the reverse.
 *
 * @package mrmurphy-theme
 */

( function () {
	'use strict';

	var PANEL_TRANSITION_MS = 250;

	/**
	 * Whether the user prefers reduced motion.
	 *
	 * @return {boolean}
	 */
	function prefersReducedMotion() {
		return window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
	}

	/**
	 * Panel transition duration in milliseconds.
	 *
	 * @return {number}
	 */
	function getPanelTransitionMs() {
		return prefersReducedMotion() ? 0 : PANEL_TRANSITION_MS;
	}

	/**
	 * Apply the fully expanded panel state.
	 *
	 * @param {HTMLElement} details Panel element.
	 * @param {HTMLElement} body    Panel body element.
	 * @param {number}      panelW  Target panel width.
	 * @param {number}      bodyH   Target body height.
	 */
	function applyExpandedState( details, body, panelW, bodyH ) {
		details.classList.add( 'authorship-details--visible' );
		details.style.width = panelW + 'px';
		details.style.pointerEvents = 'auto';
		details.style.boxShadow = '';
		body.style.height = bodyH + 'px';
	}

	/**
	 * Hide the panel and restore the pill trigger.
	 *
	 * @param {HTMLElement}      pill    Pill button.
	 * @param {HTMLElement}      details Panel element.
	 * @param {HTMLElement|null} header  Panel header element.
	 */
	function finalizeClose( pill, details, header ) {
		details.classList.remove( 'authorship-details--visible' );
		details.style.visibility = 'hidden';
		details.style.pointerEvents = 'none';
		details.style.boxShadow = 'none';
		if ( header ) {
			header.style.visibility = 'hidden';
		}
		pill.style.visibility = '';
		pill.style.pointerEvents = '';
	}

	/**
	 * Open the authorship panel for a given pill.
	 *
	 * @param {HTMLElement} pill    The pill button.
	 * @param {HTMLElement} details The panel.
	 */
	function openPanel( pill, details ) {
		var wrapper = pill.closest( '.authorship-pill--wrapper' );
		if ( ! wrapper ) {
			return;
		}

		var header = details.querySelector( '.authorship-details__header' );
		var body   = details.querySelector( '.authorship-details__body' );

		if ( ! header || ! body ) {
			return;
		}

		// Measure pill dimensions.
		var pillRect = pill.getBoundingClientRect();
		var pillW    = pillRect.width;

		// Compute target panel width first (needed for accurate body measurement).
		var vw       = window.innerWidth;
		var vh       = window.innerHeight;
		var wrapperR = wrapper.getBoundingClientRect();
		var panelW   = Math.min( 380, Math.max( pillW, vw - wrapperR.left - 16 ) );
		panelW = Math.max( panelW, 260 );

		// Measure body content height at expanded width.
		// Disable transitions so width applies immediately.
		var detailsTransition = details.style.transition;
		details.style.transition = 'none';
		details.style.width = panelW + 'px';
		body.style.removeProperty( 'max-height' );
		body.style.height = 'auto';
		body.style.transition = 'none';
		void body.offsetHeight;
		// Sum children heights directly for consistent measurement.
		var bodyH = 0;
		var children = body.children;
		for ( var i = 0; i < children.length; i++ ) {
			bodyH += children[i].getBoundingClientRect().height;
		}
		details.style.transition = detailsTransition;
		body.style.transition = '';
		body.style.height = '0';

		// Horizontal positioning: default left-aligned.
		var spaceRight = vw - wrapperR.left;
		var spaceBelow = vh - wrapperR.bottom;
		var rightAlign = false;
		if ( spaceRight < panelW ) {
			details.style.right = '0';
			details.style.left = 'auto';
			rightAlign = true;
		} else {
			details.style.left = '0';
			details.style.right = 'auto';
		}

		// Vertical positioning: panel overlays pill, then expands.
		if ( spaceBelow < bodyH + pillW && wrapperR.top > bodyH + pillW + 8 ) {
			details.style.top = 'auto';
			details.style.bottom = '100%';
			details.style.marginBottom = '8px';
			details.style.marginTop = '0';
			details.style.transformOrigin = rightAlign ? 'bottom right' : 'bottom left';
		} else {
			details.style.top = '0';
			details.style.bottom = 'auto';
			details.style.marginTop = '0';
			details.style.marginBottom = '0';
			details.style.transformOrigin = rightAlign ? 'top right' : 'top left';
		}

		// Set collapsed state: pill-width, body at 0.
		details.style.setProperty( '--pill-w', pillW + 'px' );
		details.style.width = pillW + 'px';
		details.style.visibility = 'visible';
		details.style.pointerEvents = 'none';
		details.style.boxShadow = 'none';

		// Hide pill (keeps layout), show header overlay.
		pill.style.visibility = 'hidden';
		pill.style.pointerEvents = 'none';
		header.style.visibility = 'visible';

		// Apply expanded state — instantly when reduced motion is preferred.
		if ( prefersReducedMotion() ) {
			applyExpandedState( details, body, panelW, bodyH );
			return;
		}

		requestAnimationFrame( function () {
			applyExpandedState( details, body, panelW, bodyH );
		} );
	}

	/**
	 * Close the authorship panel for a given pill.
	 *
	 * @param {HTMLElement} pill    The pill button.
	 * @param {HTMLElement} details The panel.
	 */
	function closePanel( pill, details ) {
		var wrapper = pill.closest( '.authorship-pill--wrapper' );
		if ( ! wrapper ) {
			return;
		}

		var header = details.querySelector( '.authorship-details__header' );
		var body   = details.querySelector( '.authorship-details__body' );

		if ( ! body ) {
			return;
		}

		var pillW = parseFloat( details.style.getPropertyValue( '--pill-w' ) ) || pill.getBoundingClientRect().width;

		if ( prefersReducedMotion() ) {
			details.style.width = pillW + 'px';
			body.style.height = '0';
			finalizeClose( pill, details, header );
			return;
		}

		// Animate height and width simultaneously.
		details.style.width = pillW + 'px';
		body.style.height = '0';

		// After transition, hide panel and restore pill.
		setTimeout( function () {
			finalizeClose( pill, details, header );
		}, getPanelTransitionMs() );
	}

	document.addEventListener( 'DOMContentLoaded', function () {
		var pills = document.querySelectorAll( '.authorship-pill' );

		pills.forEach( function ( pill ) {
			// Only attach handler to the actual button, not the header clone.
			if ( ! pill.getAttribute( 'aria-expanded' ) ) {
				return;
			}

			pill.addEventListener( 'click', function () {
				var isExpanded = pill.getAttribute( 'aria-expanded' ) === 'true';
				var detailsId  = pill.getAttribute( 'aria-controls' );
				var details    = document.getElementById( detailsId );

				if ( ! details ) {
					return;
				}

				pill.setAttribute( 'aria-expanded', String( ! isExpanded ) );

				if ( ! isExpanded ) {
					pill.classList.add( 'authorship-pill--expanded' );
					openPanel( pill, details );
				} else {
					pill.classList.remove( 'authorship-pill--expanded' );
					closePanel( pill, details );
				}
			} );

			pill.addEventListener( 'keydown', function ( e ) {
				if ( e.key === 'Escape' && pill.getAttribute( 'aria-expanded' ) === 'true' ) {
					pill.click();
					pill.focus();
				}
			} );
		} );

		// Header click toggles the panel.
		document.addEventListener( 'click', function ( e ) {
			var header = e.target.closest( '.authorship-details__header' );
			if ( header ) {
				var wrapper = header.closest( '.authorship-pill--wrapper' );
				if ( wrapper ) {
					var pill = wrapper.querySelector( '.authorship-pill[aria-expanded]' );
					if ( pill && pill.getAttribute( 'aria-expanded' ) === 'true' ) {
						pill.click();
					}
				}
				return;
			}

			// Clicking outside closes.
			var openPill = document.querySelector( '.authorship-pill[aria-expanded="true"]' );
			if ( ! openPill ) {
				return;
			}

			var wrapper = openPill.closest( '.authorship-pill--wrapper' );
			if ( wrapper && wrapper.contains( e.target ) ) {
				return;
			}

			openPill.click();
		} );
	} );
} )();
