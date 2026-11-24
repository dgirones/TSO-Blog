<?php
/**
 * Night mode: contrast fix for inline text colors in old content.
 *
 * @package tso-blog
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ============================================================
   34b. MODE NIT — COLORS DE TEXT EN LÍNIA DEL CONTINGUT ANTIC
   ============================================================
   El contingut importat (Blogger, TinyMCE, etc.) porta colors escrits
   en línia (style="color:#000000", <font color="…">) que guanyen sobre
   el color del tema i deixen el text il·legible sobre fons fosc.
   En mode nit, aquest script aclareix només els colors que no arriben
   a un contrast mínim (WCAG AA 4.5:1) contra el fons real, mantenint
   el to; els que ja es llegeixen bé no es toquen. En mode dia es
   restaura el color original.
   ============================================================ */

/**
 * Encola (com a script inline) el corrector de contrast del mode nit.
 */
function tsothm_enqueue_dark_content_colors_script() {
    if ( is_admin() ) {
        return;
    }
    wp_register_script( 'tsothm-dark-content-colors', false, array(), wp_get_theme()->get( 'Version' ), true );
    wp_enqueue_script( 'tsothm-dark-content-colors' );
    wp_add_inline_script( 'tsothm-dark-content-colors', tsothm_get_dark_content_colors_js() );
}
add_action( 'wp_enqueue_scripts', 'tsothm_enqueue_dark_content_colors_script' );

/**
 * JS que corregeix el contrast dels colors de text en línia en mode nit.
 *
 * @return string
 */
function tsothm_get_dark_content_colors_js() {
    return <<<'JS'
( function () {
    var root = document.documentElement;
    var SELECTOR = '.entry-content [style*="color"], .entry-content font[color], .comment-content [style*="color"], .comment-content font[color]';
    var ATTR = 'data-tso-orig-color';
    var MIN_CONTRAST = 4.5;
    var FALLBACK_BG = { r: 23, g: 24, b: 28, a: 1 };
    var timer = null;

    function parseRgb( str ) {
        var m = String( str || '' ).match( /rgba?\(\s*([\d.]+)[,\s]+([\d.]+)[,\s]+([\d.]+)(?:[,\s\/]+([\d.]+%?))?\s*\)/ );
        var a = 1;
        if ( ! m ) {
            return null;
        }
        if ( m[ 4 ] !== undefined ) {
            a = m[ 4 ].indexOf( '%' ) > -1 ? parseFloat( m[ 4 ] ) / 100 : parseFloat( m[ 4 ] );
        }
        return { r: parseFloat( m[ 1 ] ), g: parseFloat( m[ 2 ] ), b: parseFloat( m[ 3 ] ), a: a };
    }

    function lin( v ) {
        v = v / 255;
        return v <= 0.03928 ? v / 12.92 : Math.pow( ( v + 0.055 ) / 1.055, 2.4 );
    }

    function luminance( c ) {
        return 0.2126 * lin( c.r ) + 0.7152 * lin( c.g ) + 0.0722 * lin( c.b );
    }

    function contrast( a, b ) {
        var l1 = luminance( a );
        var l2 = luminance( b );
        return ( Math.max( l1, l2 ) + 0.05 ) / ( Math.min( l1, l2 ) + 0.05 );
    }

    function rgbToHsl( c ) {
        var r = c.r / 255, g = c.g / 255, b = c.b / 255;
        var max = Math.max( r, g, b ), min = Math.min( r, g, b );
        var l = ( max + min ) / 2, h = 0, s = 0, d = max - min;
        if ( d !== 0 ) {
            s = l > 0.5 ? d / ( 2 - max - min ) : d / ( max + min );
            if ( max === r ) {
                h = ( g - b ) / d + ( g < b ? 6 : 0 );
            } else if ( max === g ) {
                h = ( b - r ) / d + 2;
            } else {
                h = ( r - g ) / d + 4;
            }
            h = h / 6;
        }
        return { h: h, s: s, l: l };
    }

    function hslToRgb( h, s, l ) {
        function hue( p, q, t ) {
            if ( t < 0 ) { t += 1; }
            if ( t > 1 ) { t -= 1; }
            if ( t < 1 / 6 ) { return p + ( q - p ) * 6 * t; }
            if ( t < 1 / 2 ) { return q; }
            if ( t < 2 / 3 ) { return p + ( q - p ) * ( 2 / 3 - t ) * 6; }
            return p;
        }
        if ( s === 0 ) {
            var v = Math.round( l * 255 );
            return { r: v, g: v, b: v, a: 1 };
        }
        var q = l < 0.5 ? l * ( 1 + s ) : l + s - l * s;
        var p = 2 * l - q;
        return {
            r: Math.round( hue( p, q, h + 1 / 3 ) * 255 ),
            g: Math.round( hue( p, q, h ) * 255 ),
            b: Math.round( hue( p, q, h - 1 / 3 ) * 255 ),
            a: 1
        };
    }

    function effectiveBackground( el ) {
        var node = el;
        while ( node && node.nodeType === 1 ) {
            var c = parseRgb( window.getComputedStyle( node ).backgroundColor );
            if ( c && c.a >= 0.5 ) {
                return c;
            }
            node = node.parentElement;
        }
        return FALLBACK_BG;
    }

    function restore( el ) {
        var orig = el.getAttribute( ATTR );
        if ( orig === null ) {
            return;
        }
        if ( orig === '' ) {
            el.style.removeProperty( 'color' );
        } else {
            el.style.setProperty( 'color', orig );
        }
    }

    function adjust( el ) {
        var bg, fg, hsl, out, l, s, dir, i, inherited;
        restore( el );
        bg = effectiveBackground( el );
        fg = parseRgb( window.getComputedStyle( el ).color );
        if ( ! fg || contrast( fg, bg ) >= MIN_CONTRAST ) {
            return;
        }
        hsl = rgbToHsl( fg );

        // Colors neutres (negre, grisos): millor heretar el color de text del tema.
        if ( hsl.s < 0.2 && el.parentElement ) {
            inherited = parseRgb( window.getComputedStyle( el.parentElement ).color );
            if ( inherited && contrast( inherited, bg ) >= MIN_CONTRAST ) {
                el.style.setProperty( 'color', 'rgb(' + inherited.r + ', ' + inherited.g + ', ' + inherited.b + ')' );
                return;
            }
        }

        // Altres tons: es manté el to i es puja (o baixa) la lluminositat.
        dir = luminance( bg ) < 0.5 ? 1 : -1;
        l = hsl.l;
        s = Math.min( hsl.s, 0.9 );
        for ( i = 0; i < 40; i++ ) {
            l = Math.max( 0.03, Math.min( 0.97, l + dir * 0.03 ) );
            out = hslToRgb( hsl.h, s, l );
            if ( contrast( out, bg ) >= MIN_CONTRAST ) {
                break;
            }
        }
        el.style.setProperty( 'color', 'rgb(' + out.r + ', ' + out.g + ', ' + out.b + ')' );
    }

    function run() {
        var list, i, el;
        if ( root.getAttribute( 'data-theme' ) !== 'dark' ) {
            list = document.querySelectorAll( '[' + ATTR + ']' );
            for ( i = 0; i < list.length; i++ ) {
                restore( list[ i ] );
            }
            return;
        }
        list = document.querySelectorAll( SELECTOR );
        for ( i = 0; i < list.length; i++ ) {
            el = list[ i ];
            if ( el.getAttribute( ATTR ) === null ) {
                if ( ! el.style.color && ! ( el.tagName === 'FONT' && el.getAttribute( 'color' ) ) ) {
                    continue;
                }
                el.setAttribute( ATTR, el.style.color || '' );
            }
            adjust( el );
        }
    }

    function schedule() {
        if ( timer ) {
            window.clearTimeout( timer );
        }
        timer = window.setTimeout( run, 250 );
    }

    function init() {
        run();
        if ( window.MutationObserver ) {
            // Canvi de mode dia / nit.
            new MutationObserver( run ).observe( root, { attributes: true, attributeFilter: [ 'data-theme' ] } );
            // Contingut afegit dinàmicament (càrrega de més entrades, etc.).
            new MutationObserver( function () {
                if ( root.getAttribute( 'data-theme' ) === 'dark' ) {
                    schedule();
                }
            } ).observe( document.body, { childList: true, subtree: true } );
        }
    }

    if ( document.readyState === 'loading' ) {
        document.addEventListener( 'DOMContentLoaded', init );
    } else {
        init();
    }
} )();
JS;
}
