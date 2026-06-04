/**
 * AI Authorship Frontend Interactions
 *
 * Pill-to-panel animation: the pill stays in-flow to reserve space.
 * On open, the pill is hidden and the panel (positioned absolutely at
 * the pill's top edge) animates from pill-width to full panel width
 * while the body grows from height 0. On close, the reverse.
 *
 * @package mrmurphy-theme
 */

( function () {
	'use strict';

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

		// Measure body content height (temporarily unclip).
		body.style.height = 'auto';
		var bodyH = body.scrollHeight;
		body.style.height = '0';

		// Compute target panel width.
		var vw       = window.innerWidth;
		var vh       = window.innerHeight;
		var wrapperR = wrapper.getBoundingClientRect();
		var panelW   = Math.min( 380, Math.max( pillW, vw - wrapperR.left - 16 ) );
		panelW = Math.max( panelW, 260 );

		// Horizontal positioning: default left-aligned.
		var spaceRight = vw - wrapperR.left;
		var rightAlign = false;
		if ( spaceRight < panelW ) {
			details.style.right = '0';
			details.style.left = 'auto';
			rightAlign = true;
		} else {
			details.style.left = '0';
			details.style.right = 'auto';
		}

		// Vertical positioning: default below pill (top: 0 + pill height gap).
		var spaceBelow = vh - wrapperR.bottom;
		var flipAbove  = false;
		if ( spaceBelow < bodyH + pillW && wrapperR.top > bodyH + pillW + 8 ) {
			details.style.top = 'auto';
			details.style.bottom = '100%';
			details.style.marginBottom = '8px';
			details.style.marginTop = '0';
			details.style.transformOrigin = rightAlign ? 'bottom right' : 'bottom left';
			flipAbove = true;
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
		details.style.opacity = '1';
		details.style.visibility = 'visible';
		details.style.pointerEvents = 'none';
		details.style.boxShadow = 'none';
		details.style.borderRadius = '9999px';

		// Hide pill, show header.
		pill.style.visibility = 'hidden';
		pill.style.pointerEvents = 'none';
		header.style.visibility = 'visible';

		// Apply expanded state on next frame to trigger transitions.
		requestAnimationFrame( function () {
			details.classList.add( 'authorship-details--visible' );
			details.style.width = panelW + 'px';
			details.style.pointerEvents = 'auto';
			body.style.height = bodyH + 'px';
		} );
	}

	/**
	 * Close the authorship panel for a given pill.
	 *
	 * @param {HTMLElement} pill    The pill button.
	 * @param {HTMLElement} details The panel.
	 */
	function closePanel( pill, details ) {
		var header = details.querySelector( '.authorship-details__header' );

		if ( ! header ) {
			return;
		}

		var body = details.querySelector( '.authorship-details__body' );

		// Phase 1: collapse body height.
		if ( body ) {
			body.style.height = '0';
		}

		// Phase 2: after body collapse, let width + opacity animate via CSS transitions.
		setTimeout( function () {
			details.classList.remove( 'authorship-details--visible' );
			details.style.pointerEvents = 'none';

			// Reset width to pill-width (animates via CSS transition).
			var pillW = details.style.getPropertyValue( '--pill-w' );
			if ( pillW ) {
				details.style.width = pillW;
			}

			// Phase 3: after width + opacity done, swap back to pill.
			setTimeout( function () {
				pill.style.visibility = 'visible';
				pill.style.pointerEvents = 'auto';
				header.style.visibility = 'hidden';
				details.style.visibility = 'hidden';
				details.style.opacity = '0';
				body.style.height = '0';
			}, 300 );
		}, 300 );
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
