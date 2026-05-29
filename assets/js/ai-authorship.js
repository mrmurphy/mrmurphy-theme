/**
 * AI Authorship Frontend Interactions
 *
 * Handles expand/collapse animation for pill buttons.
 *
 * @package mrmurphy-theme
 */

( function () {
	'use strict';

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

				// Toggle expanded state.
				pill.setAttribute( 'aria-expanded', String( ! isExpanded ) );

				if ( ! isExpanded ) {
					// Opening: show details.
					pill.classList.add( 'authorship-pill--expanded' );
					details.classList.add( 'authorship-details--visible' );
				} else {
					// Closing: hide details.
					pill.classList.remove( 'authorship-pill--expanded' );
					details.classList.remove( 'authorship-details--visible' );
				}
			} );

			// Keyboard support: Enter and Space are handled natively by button.
			// Escape closes.
			pill.addEventListener( 'keydown', function ( e ) {
				if ( e.key === 'Escape' ) {
					var isExpanded = pill.getAttribute( 'aria-expanded' ) === 'true';
					if ( isExpanded ) {
						pill.click();
						pill.focus();
					}
				}
			} );
		} );
	} );
} )();
