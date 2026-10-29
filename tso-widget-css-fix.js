/**
 * tso-widget-css-fix.js - Removes rogue widget <style> rules that break table layouts.
 * Also rehomes CurrencyRate cards when LiteSpeed defers the loader (insertBefore uses
 * document scripts[last], which is no longer the widget script).
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

	/**
	 * CurrencyRate loader does: t = scripts[scripts.length-1]; t.parentNode.insertBefore(card, t).
	 * Inline (blog2): t is the widget script -> card stays in the footer column.
	 * LiteSpeed deferred data-URI (production guest): t is some other late script -> card lands on body.
	 */
	function findCurrencyHost() {
		var footer = document.querySelector( '.site-footer' );
		if ( ! footer ) {
			return null;
		}
		var attr = footer.querySelector( '.crcpw-attribution' );
		if ( attr ) {
			var host = attr.closest( '.widget' );
			if ( host ) {
				return host;
			}
		}
		var scripts = footer.querySelectorAll( 'script' );
		for ( var i = 0; i < scripts.length; i++ ) {
			var s = scripts[ i ];
			var src = s.getAttribute( 'src' ) || '';
			var txt = s.textContent || '';
			if (
				src.indexOf( 'co-in.io' ) !== -1
				|| src.indexOf( 'currencyrate' ) !== -1
				|| txt.indexOf( 'crCryptocoinPriceWidget' ) !== -1
				|| ( src.indexOf( 'data:text/javascript' ) === 0 && src.indexOf( 'Y3JDcnlwdG9jb2lu' ) !== -1 )
			) {
				var w = s.closest( '.widget' );
				if ( w ) {
					return w;
				}
			}
		}
		return null;
	}

	function findCurrencyCard() {
		/* Require CurrencyRate markers — do not match arbitrary a[data-tab-id]. */
		var tab = document.querySelector(
			'.crcpw-attribution ~ div a[href="#EUR"], .site-footer a[href="#EUR"], body > div a[href="#EUR"]'
		);
		if ( ! tab ) {
			tab = document.querySelector( 'a[href="#EUR"]' );
		}
		if ( ! tab ) {
			return null;
		}
		/* Prefer a card that also has USD (CurrencyRate tabs). */
		var root = tab;
		while ( root && root !== document.body ) {
			if ( root.querySelector && root.querySelector( 'a[href="#USD"]' ) ) {
				break;
			}
			root = root.parentElement;
		}
		if ( ! root || root === document.body ) {
			root = tab;
		}
		var el = root;
		var best = null;
		while ( el && el !== document.body ) {
			if ( el.parentElement === document.body || ( el.parentElement && el.parentElement.id === 'page-wrapper' ) ) {
				best = el;
				break;
			}
			if ( el.parentElement && el.parentElement.classList && el.parentElement.classList.contains( 'widget' ) ) {
				best = el;
				break;
			}
			if ( el.parentElement && el.parentElement.classList && (
				el.parentElement.classList.contains( 'textwidget' )
				|| el.parentElement.classList.contains( 'custom-html-widget' )
			) ) {
				best = el;
				break;
			}
			el = el.parentElement;
		}
		return best;
	}

	function rehomeCurrencyWidget() {
		var host = findCurrencyHost();
		var card = findCurrencyCard();
		if ( ! host || ! card ) {
			return;
		}
		if ( host.contains( card ) ) {
			return;
		}
		var attr = host.querySelector( '.crcpw-attribution' );
		var wrap = host.querySelector( '.textwidget, .custom-html-widget' ) || host;
		if ( attr && attr.parentNode === wrap ) {
			wrap.insertBefore( card, attr );
		} else if ( attr && attr.parentNode === host ) {
			host.insertBefore( card, attr );
		} else {
			/* Avoid inserting above .widget-title when wrap is the whole widget. */
			var title = wrap.querySelector( '.widget-title' );
			if ( title && title.parentNode === wrap && title.nextSibling ) {
				wrap.insertBefore( card, title.nextSibling );
			} else {
				wrap.appendChild( card );
			}
		}
	}

	function runAll() {
		fixWidgetCss();
		fixAvatarLinks();
		rehomeCurrencyWidget();
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', runAll );
	} else {
		runAll();
	}
	setTimeout( runAll, 500 );
	setTimeout( runAll, 1500 );
	setTimeout( rehomeCurrencyWidget, 3000 );
	setTimeout( rehomeCurrencyWidget, 6000 );

	/* Late LiteSpeed / async widget inject. */
	if ( typeof MutationObserver === 'function' ) {
		var scheduled = false;
		var obs = new MutationObserver( function () {
			if ( scheduled ) {
				return;
			}
			scheduled = true;
			setTimeout( function () {
				scheduled = false;
				rehomeCurrencyWidget();
			}, 100 );
		} );
		obs.observe( document.documentElement, { childList: true, subtree: true } );
		setTimeout( function () {
			obs.disconnect();
		}, 15000 );
	}
}() );
