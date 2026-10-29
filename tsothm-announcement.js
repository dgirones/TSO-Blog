/**
 * tsothm-announcement.js — Announcement bar enter/leave + optional rotation.
 *
 * Effects (data-effect / class tsothm-announcement--*):
 * - slide: from the right to center, then out to the left
 * - fade: fade in / fade out
 * - rise: from below to center, then out upward
 */
( function () {
	'use strict';

	function syncBodyClass() {
		document.body.classList.toggle(
			'tsothm-has-announcement',
			!! document.querySelector( '.tsothm-announcement' )
		);
	}

	function boot() {
		syncBodyClass();

		var root = document.querySelector( '.tsothm-announcement' );
		if ( ! root ) {
			return;
		}

		if ( typeof root._tsothmAnnounceCleanup === 'function' ) {
			root._tsothmAnnounceCleanup();
			root._tsothmAnnounceCleanup = null;
		}

		var slides = Array.prototype.slice.call( root.querySelectorAll( '.tsothm-announcement-slide' ) );
		if ( ! slides.length ) {
			return;
		}

		var reduceMotion = window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
		var rotate = root.getAttribute( 'data-rotate' ) === '1' && slides.length > 1;
		var intervalSec = parseInt( root.getAttribute( 'data-interval' ), 10 );
		if ( isNaN( intervalSec ) || intervalSec < 15 ) {
			intervalSec = 60;
		}

		var index = 0;
		var timer = null;
		var animating = false;
		// Keep in sync with the slowest CSS transition (~1.35s + buffer).
		var animMs = reduceMotion ? 0 : 1500;

		function clearSlideState( slide ) {
			slide.classList.remove( 'is-active', 'is-entering', 'is-leaving' );
		}

		function playEnter( slide ) {
			clearSlideState( slide );
			void slide.offsetWidth;
			slide.classList.add( 'is-entering' );
			requestAnimationFrame( function () {
				requestAnimationFrame( function () {
					slide.classList.add( 'is-active' );
					slide.classList.remove( 'is-entering' );
				} );
			} );
		}

		function show( nextIndex ) {
			if ( animating || nextIndex === index || ! slides[ nextIndex ] ) {
				return;
			}
			animating = true;
			var current = slides[ index ];
			var next = slides[ nextIndex ];

			current.classList.remove( 'is-active', 'is-entering' );
			current.classList.add( 'is-leaving' );

			playEnter( next );

			window.setTimeout( function () {
				current.classList.remove( 'is-leaving' );
				index = nextIndex;
				animating = false;
			}, animMs );
		}

		function next() {
			show( ( index + 1 ) % slides.length );
		}

		function startTimer() {
			if ( ! rotate || timer ) {
				return;
			}
			timer = window.setInterval( next, intervalSec * 1000 );
		}

		function stopTimer() {
			if ( timer ) {
				window.clearInterval( timer );
				timer = null;
			}
		}

		function onVisibility() {
			if ( ! rotate ) {
				return;
			}
			if ( document.hidden ) {
				stopTimer();
			} else {
				startTimer();
			}
		}

		slides.forEach( function ( slide, i ) {
			clearSlideState( slide );
			if ( 0 === i ) {
				if ( reduceMotion ) {
					slide.classList.add( 'is-active' );
				} else {
					playEnter( slide );
				}
			}
		} );

		if ( rotate ) {
			startTimer();
		}

		document.addEventListener( 'visibilitychange', onVisibility );

		root._tsothmAnnounceCleanup = function () {
			stopTimer();
			document.removeEventListener( 'visibilitychange', onVisibility );
		};
	}

	function onReady( fn ) {
		if ( 'loading' === document.readyState ) {
			document.addEventListener( 'DOMContentLoaded', fn );
		} else {
			fn();
		}
	}

	onReady( boot );

	if ( window.wp && wp.customize && wp.customize.selectiveRefresh ) {
		wp.customize.selectiveRefresh.bind( 'partial-content-rendered', function ( placement ) {
			if ( placement.partial && placement.partial.id === 'tsothm_announcement' ) {
				boot();
			}
		} );
	}
}() );
