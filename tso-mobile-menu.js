/**
 * tso-mobile-menu.js — Primary menu toggle and optional mobile panels.
 * The header button opens and closes the primary menu. Dock, fullscreen,
 * tab bar, and drawer replace it only after their own toggle is bound.
 */
( function () {
	'use strict';

	/**
	 * Header toggle for the primary menu (classic style and fallback).
	 * The hiding class is added only after the click handler exists.
	 */
	function initPrimaryNavToggle() {
		if ( document.body.classList.contains( 'tsothm-legacy-widget-preview' ) ) {
			return;
		}

		var btn = document.querySelector( '.tsothm-nav-toggle' );
		var nav = document.getElementById( 'site-navigation' );
		var mq  = window.matchMedia( '(max-width: 768px)' );

		if ( ! btn || ! nav ) {
			return;
		}

		function setPrimaryOpen( open ) {
			nav.classList.toggle( 'is-open', open );
			btn.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
		}

		btn.addEventListener( 'click', function () {
			setPrimaryOpen( btn.getAttribute( 'aria-expanded' ) !== 'true' );
		} );

		nav.addEventListener( 'click', function ( e ) {
			if ( e.target.closest && e.target.closest( 'a' ) ) {
				setPrimaryOpen( false );
			}
		} );

		document.addEventListener( 'click', function ( e ) {
			if ( ! nav.classList.contains( 'is-open' ) ) {
				return;
			}
			if ( nav.contains( e.target ) || btn.contains( e.target ) ) {
				return;
			}
			setPrimaryOpen( false );
		} );

		document.addEventListener( 'keydown', function ( e ) {
			if ( 'Escape' !== e.key || ! nav.classList.contains( 'is-open' ) ) {
				return;
			}
			setPrimaryOpen( false );
			btn.focus();
		} );

		function onPrimaryViewport() {
			if ( ! mq.matches ) {
				setPrimaryOpen( false );
			}
		}

		if ( 'function' === typeof mq.addEventListener ) {
			mq.addEventListener( 'change', onPrimaryViewport );
		} else if ( 'function' === typeof mq.addListener ) {
			mq.addListener( onPrimaryViewport );
		}

		document.documentElement.classList.add( 'tsothm-nav-js' );
	}

	initPrimaryNavToggle();

	var root = document.querySelector( '.tsothm-mm' );
	if ( ! root ) {
		return;
	}

	var toggle   = root.querySelector( '.tsothm-mm__toggle' );
	var close    = root.querySelector( '.tsothm-mm__close' );
	var backdrop = root.querySelector( '.tsothm-mm__backdrop' );
	var panel    = root.querySelector( '.tsothm-mm__panel' );
	var mq       = window.matchMedia( '(max-width: 768px)' );

	if ( ! toggle || ! close || ! backdrop || ! panel ) {
		return;
	}

	function isOpen() {
		return root.classList.contains( 'is-open' );
	}

	function getFocusable() {
		return Array.prototype.slice.call(
			panel.querySelectorAll( 'a[href], button:not([disabled])' )
		).filter( function ( el ) {
			return el.offsetWidth > 0 || el.offsetHeight > 0;
		} );
	}

	function setOpen( open, restoreFocus ) {
		if ( open === isOpen() ) {
			return;
		}
		root.classList.toggle( 'is-open', open );
		document.body.classList.toggle( 'tsothm-mm-locked', open );
		toggle.setAttribute( 'aria-expanded', open ? 'true' : 'false' );

		if ( open ) {
			backdrop.hidden = false;
			// Forzar reflow para que la transición de entrada se ejecute.
			void backdrop.offsetWidth;
			close.focus();
		} else {
			window.setTimeout( function () {
				if ( ! isOpen() ) {
					backdrop.hidden = true;
				}
			}, 350 );
			if ( false !== restoreFocus ) {
				toggle.focus();
			}
		}
	}

	// Versión C: pestañas inferiores generadas a partir del menú (máx. 5 huecos).
	function buildTabs() {
		var tabs  = root.querySelector( '.tsothm-mm__tabs' );
		var items = panel.querySelectorAll( '.tsothm-mm__list > li' );
		if ( ! tabs || ! items.length ) {
			return;
		}

		var showMore = items.length > 5;
		var max      = showMore ? 4 : 5;

		Array.prototype.slice.call( items, 0, max ).forEach( function ( li ) {
			var src = li.querySelector( ':scope > a' );
			if ( ! src ) {
				return;
			}
			var href = src.getAttribute( 'href' ) || '#';
			var a    = document.createElement( 'a' );
			a.className   = 'tsothm-mm__tab';
			a.href        = href;
			a.textContent = src.textContent;

			if ( li.classList.contains( 'current-menu-item' ) || li.classList.contains( 'current-menu-ancestor' ) ) {
				a.classList.add( 'is-current' );
				a.setAttribute( 'aria-current', 'page' );
			}
			if ( '#' === href || '' === href ) {
				a.addEventListener( 'click', function ( e ) {
					e.preventDefault();
					setOpen( true );
				} );
			}
			tabs.appendChild( a );
		} );

		if ( showMore ) {
			tabs.appendChild( toggle );
		} else if ( tabs.childElementCount ) {
			toggle.hidden = true;
		}
	}

	if ( root.classList.contains( 'tsothm-mm--tabbar' ) ) {
		buildTabs();
	}

	// Submenús: botón de despliegue junto a cada enlace con hijos.
	var subLabel = root.getAttribute( 'data-sub-label' ) || '';
	Array.prototype.forEach.call(
		panel.querySelectorAll( '.menu-item-has-children' ),
		function ( li ) {
			var link = li.querySelector( ':scope > a' );
			var btn  = document.createElement( 'button' );
			btn.type = 'button';
			btn.className = 'tsothm-mm__sub-toggle';
			btn.setAttribute( 'aria-expanded', 'false' );
			if ( subLabel ) {
				btn.setAttribute( 'aria-label', subLabel );
			}
			if ( link ) {
				link.insertAdjacentElement( 'afterend', btn );
			} else {
				li.insertBefore( btn, li.firstChild );
			}

			function toggleSub() {
				var openSub = ! li.classList.contains( 'is-sub-open' );
				li.classList.toggle( 'is-sub-open', openSub );
				btn.setAttribute( 'aria-expanded', openSub ? 'true' : 'false' );
			}

			btn.addEventListener( 'click', toggleSub );

			// Enlace «#» o vacío: el propio título despliega el submenú.
			if ( link ) {
				var href = link.getAttribute( 'href' ) || '';
				if ( '' === href || '#' === href ) {
					link.addEventListener( 'click', function ( e ) {
						e.preventDefault();
						e.stopPropagation();
						toggleSub();
					} );
				}
			}
		}
	);

	toggle.addEventListener( 'click', function () {
		setOpen( ! isOpen() );
	} );
	close.addEventListener( 'click', function () {
		setOpen( false );
	} );
	backdrop.addEventListener( 'click', function () {
		setOpen( false );
	} );

	panel.addEventListener( 'click', function ( e ) {
		var link = e.target.closest( 'a' );
		if ( ! link ) {
			return;
		}
		var href = link.getAttribute( 'href' ) || '';
		if ( href && '#' !== href ) {
			// Evita el salto de foco antes de navegar.
			setOpen( false, false );
		}
	} );

	document.addEventListener( 'keydown', function ( e ) {
		if ( ! isOpen() ) {
			return;
		}

		if ( 'Escape' === e.key ) {
			e.preventDefault();
			setOpen( false );
			return;
		}

		if ( 'Tab' !== e.key ) {
			return;
		}

		var items = getFocusable();
		if ( ! items.length ) {
			return;
		}
		var first = items[ 0 ];
		var last  = items[ items.length - 1 ];

		if ( e.shiftKey && document.activeElement === first ) {
			e.preventDefault();
			last.focus();
		} else if ( ! e.shiftKey && document.activeElement === last ) {
			e.preventDefault();
			first.focus();
		}
	} );

	function onViewportChange() {
		if ( ! mq.matches ) {
			setOpen( false, false );
		}
	}

	if ( 'function' === typeof mq.addEventListener ) {
		mq.addEventListener( 'change', onViewportChange );
	} else if ( 'function' === typeof mq.addListener ) {
		mq.addListener( onViewportChange );
	}

	document.documentElement.classList.add( 'tsothm-mm-ready' );
}() );
