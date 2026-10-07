/**
 * tso-widget-css-fix.js — Removes rogue widget <style> rules that break table layouts.
 */
( function () {
	'use strict';

	function fixWidgetCss() {
		var styles = document.querySelectorAll( 'style' );
		styles.forEach( function ( s ) {
			if (
				s.textContent
				&& s.textContent.indexOf( '291' ) !== -1
				&& s.textContent.indexOf( 'max-width' ) !== -1
			) {
				s.parentNode.removeChild( s );
			}
		} );
	}

	/* Accessibility: give avatar-only links in tabbed widgets an accessible name. */
	function fixAvatarLinks() {
		document.querySelectorAll( '.wpt_avatar a' ).forEach( function ( a ) {
			if ( a.getAttribute( 'aria-label' ) || ( a.textContent || '' ).trim() ) {
				return;
			}
			var img = a.querySelector( 'img[alt]' );
			if ( img && img.getAttribute( 'alt' ) ) {
				return;
			}
			var li = a.closest( 'li' );
			var links = li ? li.querySelectorAll( '.wpt_comment_meta a' ) : [];
			var label = '';
			for ( var i = 0; i < links.length; i++ ) {
				label = ( links[ i ].textContent || '' ).trim();
				if ( label ) {
					break;
				}
			}
			if ( label ) {
				a.setAttribute( 'aria-label', label );
			}
		} );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', fixWidgetCss );
	} else {
		fixWidgetCss();
	}
	document.addEventListener( 'DOMContentLoaded', fixAvatarLinks );
	setTimeout( fixAvatarLinks, 500 );
	setTimeout( fixAvatarLinks, 1500 );
	setTimeout( fixWidgetCss, 500 );
	setTimeout( fixWidgetCss, 1500 );
}() );
