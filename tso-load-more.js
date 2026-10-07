/**
 * tso-load-more.js — Home grid "Cargar más" + back-navigation state restore.
 */
( function () {
	'use strict';

	var cfg       = window.tsothmLoadMore || {};
	var button    = document.getElementById( 'load-more-btn' );
	var container = document.getElementById( 'posts-container' );
	var CACHE_KEY = cfg.cacheKey || 'tsothm_loadmore_state';
	var loadText  = cfg.loadText || 'Cargar más';
	var loadingText = cfg.loadingText || 'Cargando...';

	if ( ! container ) {
		return;
	}

	function isBackNavigation() {
		var nav = performance.getEntriesByType && performance.getEntriesByType( 'navigation' )[0];
		return !!( nav && nav.type === 'back_forward' );
	}

	function saveState() {
		try {
			sessionStorage.setItem( CACHE_KEY, JSON.stringify( {
				html:    container.innerHTML,
				nextUrl: button ? button.getAttribute( 'data-next-page' ) : null,
				hasBtn:  !!button,
				scrollY: window.scrollY || window.pageYOffset,
			} ) );
		} catch ( e ) {}
	}

	function restoreState() {
		try {
			var raw = sessionStorage.getItem( CACHE_KEY );
			if ( ! raw ) {
				return false;
			}
			var state = JSON.parse( raw );
			if ( ! state || ! state.html ) {
				return false;
			}

			container.innerHTML = state.html;

			var wrapper = document.getElementById( 'load-more-wrapper' );
			if ( state.hasBtn && state.nextUrl && wrapper ) {
				var existingBtn = document.getElementById( 'load-more-btn' );
				if ( existingBtn ) {
					button = existingBtn;
				} else {
					button           = document.createElement( 'button' );
					button.id        = 'load-more-btn';
					button.className = 'btn-load-more';
					button.setAttribute( 'data-load-text', loadText );
					button.setAttribute( 'data-loading-text', loadingText );
					button.textContent = loadText;
					wrapper.appendChild( button );
				}
				button.setAttribute( 'data-next-page', state.nextUrl );
				button.disabled = false;
				bindButton();
			} else if ( wrapper ) {
				var oldBtn = wrapper.querySelector( '#load-more-btn' );
				if ( oldBtn ) {
					oldBtn.remove();
				}
				button = null;
			}

			if ( state.scrollY > 0 ) {
				requestAnimationFrame( function () {
					window.scrollTo( 0, state.scrollY );
				} );
			}
			return true;
		} catch ( e ) {
			return false;
		}
	}

	function clearState() {
		try {
			sessionStorage.removeItem( CACHE_KEY );
		} catch ( e ) {}
	}

	window.addEventListener( 'pageshow', function ( e ) {
		if ( e.persisted || isBackNavigation() ) {
			restoreState();
			return;
		}
		clearState();
	} );

	container.addEventListener( 'click', function ( e ) {
		var link = e.target.closest( 'a[href]' );
		if ( ! link ) {
			return;
		}
		var href = link.getAttribute( 'href' ) || '';
		if ( href && href.indexOf( '#' ) !== 0 && href.indexOf( '?' ) !== 0 ) {
			saveState();
		}
	} );

	function bindButton() {
		if ( ! button || button.dataset.tsothmBound === '1' ) {
			return;
		}
		button.dataset.tsothmBound = '1';
		button.addEventListener( 'click', function () {
			var nextUrl = button.getAttribute( 'data-next-page' );
			var label   = button.getAttribute( 'data-load-text' ) || loadText;
			if ( ! nextUrl ) {
				return;
			}
			button.textContent = button.getAttribute( 'data-loading-text' ) || loadingText;
			button.disabled    = true;
			fetch( nextUrl, {
				headers: {
					'X-Requested-With': 'XMLHttpRequest',
					'Cache-Control': 'no-cache',
				},
			} )
				.then( function ( r ) {
					if ( ! r.ok ) {
						throw new Error( 'HTTP ' + r.status );
					}
					return r.text();
				} )
				.then( function ( html ) {
					var parser   = new DOMParser();
					var doc      = parser.parseFromString( html, 'text/html' );
					var newPosts = doc.querySelectorAll( '#posts-container .home-post-card' );
					var nextBtn  = doc.querySelector( '#load-more-btn' );
					newPosts.forEach( function ( post ) {
						if ( post.id && container.querySelector( '#' + post.id ) ) {
							return;
						}
						container.appendChild( post );
					} );
					if ( nextBtn && nextBtn.getAttribute( 'data-next-page' ) ) {
						button.setAttribute( 'data-next-page', nextBtn.getAttribute( 'data-next-page' ) );
						button.textContent = label;
						button.disabled    = false;
					} else {
						button.remove();
						button = null;
					}
				} )
				.catch( function () {
					if ( ! button ) {
						return;
					}
					button.textContent = label;
					button.disabled    = false;
				} );
		} );
	}

	bindButton();
}() );
