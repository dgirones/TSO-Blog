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
		var nextAt = 0;
		// Keep in sync with the slowest CSS transition (~1.35s + buffer).
		var animMs = reduceMotion ? 0 : 1500;
		var storageKey = 'tsothmAnnounceSchedule';

		function scheduleSignature() {
			return [
				root.getAttribute( 'data-effect' ) || '',
				String( intervalSec ),
				String( slides.length )
			].join( '|' );
		}

		function readSchedule() {
			try {
				var data = JSON.parse( window.sessionStorage.getItem( storageKey ) || '' );
				if ( ! data || data.sig !== scheduleSignature() ) {
					return null;
				}
				if ( typeof data.index !== 'number' || typeof data.nextAt !== 'number' ) {
					return null;
				}
				if ( data.index < 0 || data.index >= slides.length ) {
					return null;
				}
				return data;
			} catch ( e ) {
				return null;
			}
		}

		function writeSchedule( slideIndex, dueAt ) {
			try {
				window.sessionStorage.setItem( storageKey, JSON.stringify( {
					sig: scheduleSignature(),
					index: slideIndex,
					nextAt: dueAt
				} ) );
			} catch ( e ) {}
		}

		function clearSlideState( slide ) {
			slide.classList.remove( 'is-active', 'is-entering', 'is-leaving', 'is-instant' );
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

		function showSettled( slideIndex ) {
			slides.forEach( clearSlideState );
			slides[ slideIndex ].classList.add( 'is-active', 'is-instant' );
			index = slideIndex;
		}

		function show( nextIndex ) {
			if ( animating || nextIndex === index || ! slides[ nextIndex ] ) {
				return false;
			}
			animating = true;
			var current = slides[ index ];
			var next = slides[ nextIndex ];

			current.classList.remove( 'is-active', 'is-entering', 'is-instant' );
			current.classList.add( 'is-leaving' );

			playEnter( next );

			window.setTimeout( function () {
				current.classList.remove( 'is-leaving', 'is-instant' );
				index = nextIndex;
				animating = false;
			}, animMs );
			return true;
		}

		function queueNext() {
			if ( ! rotate || timer ) {
				return;
			}
			var wait = Math.max( 0, nextAt - Date.now() );
			timer = window.setTimeout( function () {
				timer = null;
				var dest = ( index + 1 ) % slides.length;
				if ( ! show( dest ) ) {
					window.setTimeout( queueNext, Math.max( animMs, 50 ) );
					return;
				}
				nextAt = Date.now() + intervalSec * 1000;
				writeSchedule( dest, nextAt );
				queueNext();
			}, wait );
		}

		function stopTimer() {
			if ( timer ) {
				window.clearTimeout( timer );
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
				queueNext();
			}
		}

		slides.forEach( clearSlideState );

		if ( rotate ) {
			var now = Date.now();
			var saved = readSchedule();
			var period = intervalSec * 1000;
			if ( saved && now < saved.nextAt ) {
				index = saved.index;
				nextAt = saved.nextAt;
			} else if ( saved ) {
				var steps = Math.floor( ( now - saved.nextAt ) / period ) + 1;
				index = ( saved.index + steps ) % slides.length;
				var overdue = ( now - saved.nextAt ) % period;
				nextAt = now + ( overdue ? period - overdue : period );
			} else {
				index = 0;
				nextAt = now + period;
			}
			showSettled( index );
			writeSchedule( index, nextAt );
			queueNext();
		} else {
			showSettled( 0 );
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
