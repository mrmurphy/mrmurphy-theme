/**
 * AI Authorship Frontend Interactions
 *
 * Dropdown panel anchored to the pill. Closes on outside click or Escape.
 * Automatically repositions when it would overflow the viewport.
 *
 * @package mrmurphy-theme
 */

( function () {
	'use strict';

	/**
	 * Position the dropdown so it stays within the viewport.
	 *
	 * @param {HTMLElement} pill    The pill button.
	 * @param {HTMLElement} details The dropdown panel.
	 */
	function positionDropdown( pill, details ) {
		var wrapper = pill.closest( '.authorship-pill--wrapper' );
		if ( ! wrapper ) {
			return;
		}

		var wrapperRect = wrapper.getBoundingClientRect();
		var vw = window.innerWidth;
		var vh = window.innerHeight;

		// Make the panel briefly visible (but invisible) to measure it.
		details.style.visibility = 'hidden';
		details.style.opacity = '0';
		details.style.pointerEvents = 'none';
		details.classList.add( 'authorship-details--visible' );

		// Reset to default position first to get accurate measurements.
		details.style.top = '';
		details.style.left = '';
		details.style.right = '';
		details.style.bottom = '';
		details.style.transform = '';

		var panelRect = details.getBoundingClientRect();
		var panelW = panelRect.width;
		var panelH = panelRect.height;

		// Hide again before applying final position.
		details.classList.remove( 'authorship-details--visible' );
		details.style.visibility = '';
		details.style.opacity = '';
		details.style.pointerEvents = '';

		var gap = 8;

		// Horizontal: default left-aligned. Flip to right-aligned if it overflows.
		var spaceRight = vw - wrapperRect.left;
		if ( spaceRight < panelW && wrapperRect.right + gap + panelW <= vw ) {
			// Not enough room on the right, try aligning left edge of panel to right edge of wrapper.
			details.style.left = ( wrapperRect.width - 0 ) + 'px';
		} else if ( spaceRight < panelW ) {
			// Anchor to right edge of wrapper so panel extends leftward.
			details.style.right = '0';
			details.style.left = 'auto';
		} else {
			details.style.left = '0';
			details.style.right = 'auto';
		}

		// Vertical: default below. Flip above if it overflows.
		var spaceBelow = vh - wrapperRect.bottom;
		if ( spaceBelow < panelH && wrapperRect.top > panelH + gap ) {
			// Position above the pill.
			details.style.top = 'auto';
			details.style.bottom = '100%';
			details.style.marginBottom = gap + 'px';
			details.style.marginTop = '0';
			details.style.transformOrigin = 'bottom left';
		} else {
			details.style.top = '100%';
			details.style.bottom = 'auto';
			details.style.marginTop = gap + 'px';
			details.style.marginBottom = '0';
			details.style.transformOrigin = 'top left';
		}
	}

	document.addEventListener( 'DOMContentLoaded', function () {
		var pills = document.querySelectorAll( '.authorship-pill' );

		pills.forEach( function ( pill ) {
			pill.addEventListener( 'click', function () {
				var isExpanded = pill.getAttribute( 'aria-expanded' ) === 'true';
				var detailsId = pill.getAttribute( 'aria-controls' );
				var details = document.getElementById( detailsId );

				if ( ! details ) {
					return;
				}

				pill.setAttribute( 'aria-expanded', String( ! isExpanded ) );

				if ( ! isExpanded ) {
					pill.classList.add( 'authorship-pill--expanded' );
					positionDropdown( pill, details );
					// Use rAF so the transition picks up the new position.
					requestAnimationFrame( function () {
						details.classList.add( 'authorship-details--visible' );
					} );
				} else {
					pill.classList.remove( 'authorship-pill--expanded' );
					details.classList.remove( 'authorship-details--visible' );
				}
			} );

			pill.addEventListener( 'keydown', function ( e ) {
				if ( e.key === 'Escape' && pill.getAttribute( 'aria-expanded' ) === 'true' ) {
					pill.click();
					pill.focus();
				}
			} );
		} );

		// Clicking outside closes.
		document.addEventListener( 'click', function ( e ) {
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
