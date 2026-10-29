/**
 * tso-mobile-menu.js — Menú móvil moderno (versiones A y B).
 * Abrir/cerrar, Escape, clic fuera, trampa de foco, ARIA y submenús desplegables.
 */
( function () {
	'use strict';

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
		} else {
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
		setOpen( true );
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
}() );
