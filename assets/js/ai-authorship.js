/**
 * AI Authorship Frontend Interactions
 *
 * Floating panel with backdrop overlay. No content reflow.
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
				var backdrop = document.getElementById( detailsId + '-backdrop' );

				if ( ! details ) {
					return;
				}

				// Toggle expanded state.
				pill.setAttribute( 'aria-expanded', String( ! isExpanded ) );

				if ( ! isExpanded ) {
					// Opening: show panel + backdrop.
					pill.classList.add( 'authorship-pill--expanded' );
					details.classList.add( 'authorship-details--visible' );
					if ( backdrop ) {
						backdrop.classList.add( 'authorship-backdrop--visible' );
					}
				} else {
					// Closing: hide panel + backdrop.
					pill.classList.remove( 'authorship-pill--expanded' );
					details.classList.remove( 'authorship-details--visible' );
					if ( backdrop ) {
						backdrop.classList.remove( 'authorship-backdrop--visible' );
					}
				}
			} );

			// Keyboard support: Escape closes.
			pill.addEventListener( 'keydown', function ( e ) {
				if ( e.key === 'Escape' && pill.getAttribute( 'aria-expanded' ) === 'true' ) {
					pill.click();
					pill.focus();
				}
			} );
		} );

		// Clicking backdrop closes.
		document.addEventListener( 'click', function ( e ) {
			if ( e.target && e.target.classList.contains( 'authorship-backdrop' ) ) {
				var detailsId = e.target.id.replace( '-backdrop', '' );
				var pill = document.getElementById( detailsId + '-toggle' );
				if ( pill && pill.getAttribute( 'aria-expanded' ) === 'true' ) {
					pill.click();
				}
			}
		} );
	} );
} )();
