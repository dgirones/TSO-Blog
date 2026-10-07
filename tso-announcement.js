/**
 * tso-announcement.js — Slide-in announcements + optional rotation.
 */
( function () {
	'use strict';

	var root = document.querySelector( '.tso-announcement' );
	if ( ! root ) {
		return;
	}

	var slides = Array.prototype.slice.call( root.querySelectorAll( '.tso-announcement-slide' ) );
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

	function playEnter( slide ) {
		slide.classList.remove( 'is-leaving', 'is-active' );
		// Force reflow so the enter animation restarts.
		void slide.offsetWidth;
		slide.classList.add( 'is-entering' );
		requestAnimationFrame( function () {
			slide.classList.add( 'is-active' );
			slide.classList.remove( 'is-entering' );
		} );
	}

	function show( nextIndex ) {
		if ( animating || nextIndex === index ) {
			return;
		}
		animating = true;
		var current = slides[ index ];
		var next = slides[ nextIndex ];

		current.classList.remove( 'is-active' );
		current.classList.add( 'is-leaving' );

		playEnter( next );

		window.setTimeout( function () {
			current.classList.remove( 'is-leaving' );
			index = nextIndex;
			animating = false;
		}, reduceMotion ? 0 : 700 );
	}

	function next() {
		show( ( index + 1 ) % slides.length );
	}

	// First load: animate into center.
	if ( ! reduceMotion ) {
		slides.forEach( function ( slide, i ) {
			if ( i === 0 ) {
				slide.classList.remove( 'is-active' );
				playEnter( slide );
			}
		} );
	}

	if ( rotate && ! reduceMotion ) {
		timer = window.setInterval( next, intervalSec * 1000 );
	} else if ( rotate && reduceMotion ) {
		timer = window.setInterval( function () {
			slides[ index ].classList.remove( 'is-active' );
			index = ( index + 1 ) % slides.length;
			slides[ index ].classList.add( 'is-active' );
		}, intervalSec * 1000 );
	}

	document.addEventListener( 'visibilitychange', function () {
		if ( ! rotate ) {
			return;
		}
		if ( document.hidden ) {
			if ( timer ) {
				window.clearInterval( timer );
				timer = null;
			}
		} else if ( ! timer ) {
			timer = window.setInterval( next, intervalSec * 1000 );
		}
	} );
}() );
