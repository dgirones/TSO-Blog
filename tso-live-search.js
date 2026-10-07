/**
 * tso-live-search.js — Live search dropdown (title-only results via AJAX).
 * Supports multiple .tso-search-wrapper instances (unique IDs).
 * Keyboard: ArrowUp/Down, Enter, Escape. ARIA combobox + highlight match.
 */
( function () {
	'use strict';

	var cacheKey = ( window.tsothmSearch && window.tsothmSearch.loadMoreCacheKey ) || 'tsothm_loadmore_state';
	var i18n = ( window.tsothmSearch && window.tsothmSearch.i18n ) || {};
	var msgSearching = i18n.searching || 'Buscando…';
	var msgNoResults = i18n.noResults || 'No se encontraron artículos.';
	var instances = [];

	function escHtml( str ) {
		return String( str )
			.replace( /&/g, '&amp;' )
			.replace( /</g, '&lt;' )
			.replace( />/g, '&gt;' )
			.replace( /"/g, '&quot;' );
	}

	function escHref( url ) {
		var href = String( url );
		if ( ! /^https?:\/\//i.test( href ) ) {
			return '#';
		}
		return href.replace( /"/g, '%22' );
	}

	function escRegExp( str ) {
		return String( str ).replace( /[.*+?^${}()|[\]\\]/g, '\\$&' );
	}

	function highlightTitle( title, term ) {
		var safe = escHtml( title );
		var q = String( term || '' ).trim();
		if ( q.length < 2 ) {
			return safe;
		}
		var re = new RegExp( '(' + escRegExp( q ) + ')', 'ig' );
		return safe.replace( re, '<mark class="tso-sr-mark">$1</mark>' );
	}

	function initWrapper( wrapper, instanceIndex ) {
		var input = wrapper.querySelector( '.tso-search-input' );
		var list  = wrapper.querySelector( '.tso-search-results' );
		var form  = wrapper.querySelector( '.searchform' );

		if ( ! input || ! list ) {
			return;
		}

		var timer = null;
		var last  = '';
		var reqId = 0;
		var activeIndex = -1;
		var idPrefix = 'tso-sr-' + instanceIndex + '-opt-';

		function optionItems() {
			return list.querySelectorAll( 'li[role="option"]' );
		}

		function setExpanded( open ) {
			input.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
			if ( open ) {
				list.removeAttribute( 'hidden' );
				list.style.display = 'block';
			} else {
				list.setAttribute( 'hidden', '' );
				list.style.display = 'none';
			}
		}

		function clearActive() {
			activeIndex = -1;
			input.removeAttribute( 'aria-activedescendant' );
			var items = optionItems();
			for ( var i = 0; i < items.length; i++ ) {
				items[ i ].classList.remove( 'is-active' );
				items[ i ].setAttribute( 'aria-selected', 'false' );
			}
		}

		function setActive( index ) {
			var items = optionItems();
			if ( ! items.length ) {
				clearActive();
				return;
			}
			if ( index < 0 ) {
				index = items.length - 1;
			} else if ( index >= items.length ) {
				index = 0;
			}
			clearActive();
			activeIndex = index;
			var el = items[ activeIndex ];
			el.classList.add( 'is-active' );
			el.setAttribute( 'aria-selected', 'true' );
			input.setAttribute( 'aria-activedescendant', el.id );
			if ( typeof el.scrollIntoView === 'function' ) {
				el.scrollIntoView( { block: 'nearest' } );
			}
		}

		function hide() {
			list.innerHTML = '';
			last = '';
			clearActive();
			setExpanded( false );
		}

		function search( term ) {
			if ( typeof window.tsothmSearch === 'undefined' ) {
				return;
			}

			var currentReq = ++reqId;

			clearActive();
			list.innerHTML = '<li><span class="tso-sr-msg">' + escHtml( msgSearching ) + '</span></li>';
			setExpanded( true );

			var url = window.tsothmSearch.ajaxurl
				+ '?action=tsothm_live_search'
				+ '&nonce=' + encodeURIComponent( window.tsothmSearch.nonce )
				+ '&term=' + encodeURIComponent( term );

			fetch( url )
				.then( function ( r ) {
					if ( ! r.ok ) {
						throw new Error( 'HTTP ' + r.status );
					}
					return r.json();
				} )
				.then( function ( data ) {
					if ( currentReq !== reqId ) {
						return;
					}
					if ( ! data || ! data.success ) {
						hide();
						return;
					}

					var items = data.data;
					if ( ! items || items.length === 0 ) {
						list.innerHTML = '<li><span class="tso-sr-msg">' + escHtml( msgNoResults ) + '</span></li>';
						setExpanded( true );
						return;
					}

					var html = '';
					items.forEach( function ( it, i ) {
						var tPlain = escHtml( it.title );
						var tMark  = highlightTitle( it.title, term );
						var u = escHref( it.url );
						var d = escHtml( it.date );
						var oid = idPrefix + i;
						html += '<li id="' + oid + '" role="option" aria-selected="false">'
							+ '<a href="' + u + '" title="' + tPlain + '">'
							+ '<span class="tso-sr-title">' + tMark + '</span>'
							+ '<span class="tso-sr-date">' + d + '</span>'
							+ '</a></li>';
					} );
					list.innerHTML = html;
					setExpanded( true );
				} )
				.catch( function () {
					if ( currentReq === reqId ) {
						hide();
					}
				} );
		}

		input.addEventListener( 'focus', function () {
			for ( var i = 0; i < instances.length; i++ ) {
				if ( instances[ i ].wrapper !== wrapper ) {
					instances[ i ].hide();
				}
			}
		} );

		input.addEventListener( 'input', function () {
			var val = this.value.trim();
			for ( var i = 0; i < instances.length; i++ ) {
				if ( instances[ i ].wrapper !== wrapper ) {
					instances[ i ].hide();
				}
			}
			clearTimeout( timer );
			if ( val.length < 2 ) {
				hide();
				return;
			}
			if ( val === last ) {
				return;
			}
			last  = val;
			timer = setTimeout( function () {
				search( val );
			}, 300 );
		} );

		if ( form ) {
			form.addEventListener( 'submit', function () {
				hide();
				try {
					sessionStorage.removeItem( cacheKey );
				} catch ( err ) {}
			} );
		}

		input.addEventListener( 'keydown', function ( e ) {
			var items = optionItems();
			var open  = input.getAttribute( 'aria-expanded' ) === 'true';

			if ( e.key === 'Escape' ) {
				if ( open ) {
					e.preventDefault();
					hide();
				}
				return;
			}

			if ( ! open || ! items.length ) {
				return;
			}

			if ( e.key === 'ArrowDown' ) {
				e.preventDefault();
				setActive( activeIndex + 1 );
				return;
			}

			if ( e.key === 'ArrowUp' ) {
				e.preventDefault();
				setActive( activeIndex - 1 );
				return;
			}

			if ( e.key === 'Home' ) {
				e.preventDefault();
				setActive( 0 );
				return;
			}

			if ( e.key === 'End' ) {
				e.preventDefault();
				setActive( items.length - 1 );
				return;
			}

			if ( e.key === 'Enter' && activeIndex >= 0 && items[ activeIndex ] ) {
				var link = items[ activeIndex ].querySelector( 'a' );
				if ( link && link.href ) {
					e.preventDefault();
					window.location.href = link.href;
				}
			}
		} );

		instances.push( { wrapper: wrapper, hide: hide } );
	}

	var wrappers = document.querySelectorAll( '.tso-search-wrapper' );
	for ( var w = 0; w < wrappers.length; w++ ) {
		initWrapper( wrappers[ w ], w );
	}

	document.addEventListener( 'mousedown', function ( e ) {
		if ( e.target.closest( '.tso-search-wrapper' ) ) {
			return;
		}
		for ( var i = 0; i < instances.length; i++ ) {
			instances[ i ].hide();
		}
	} );
}() );
