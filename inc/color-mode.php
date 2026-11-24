<?php
/**
 * Day / night / automatic color mode: switch script and early boot script.
 *
 * @package tso-blog
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ============================================================
   34. MODE DIA / NIT / AUTOMÀTIC — SELECTOR DE COLOR
   ============================================================
   Afegeix un interruptor flotant (dia / nit / automàtic).
   El mode "automàtic" segueix la preferència del navegador
   (prefers-color-scheme) i es manté sincronitzat si l'usuari
   la canvia mentre navega. El mode "dia" no es toca: només
   s'afegeixen variables i regles noves per al mode "nit".
   ============================================================ */

/**
 * Whether the current request is a widgets-screen legacy preview iframe.
 * Those iframes load the front theme; night-mode must not restyle admin previews.
 *
 * @return bool
 */
function tsothm_is_legacy_widget_preview() {
    if ( filter_has_var( INPUT_GET, 'legacy-widget-preview' ) ) {
        return true;
    }
    if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
        $rest_route = isset( $GLOBALS['wp']->query_vars['rest_route'] )
            ? (string) $GLOBALS['wp']->query_vars['rest_route']
            : '';
        if ( '' === $rest_route && isset( $_SERVER['REQUEST_URI'] ) ) {
            $rest_route = (string) wp_unslash( $_SERVER['REQUEST_URI'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- path check only.
        }
        if ( false !== strpos( $rest_route, '/widget-types/' ) || false !== strpos( $rest_route, '/wp/v2/widgets' ) ) {
            return true;
        }
    }
    return false;
}

/**
 * Mark legacy widget preview iframes so CSS can neutralize the orange frame
 * without touching the real frontend (body.wp-embed-responsive).
 *
 * @param string[] $classes Body classes.
 * @return string[]
 */
function tsothm_legacy_widget_preview_body_class( $classes ) {
    if ( tsothm_is_legacy_widget_preview() ) {
        $classes[] = 'tsothm-legacy-widget-preview';
    }
    return $classes;
}
add_filter( 'body_class', 'tsothm_legacy_widget_preview_body_class' );

/**
 * Encola (com a script inline) el selector de mode de color.
 */
function tsothm_enqueue_color_mode_script() {
    if ( tsothm_is_legacy_widget_preview() ) {
        return;
    }
    wp_register_script( 'tsothm-color-mode', false, array(), wp_get_theme()->get( 'Version' ), true );
    wp_enqueue_script( 'tsothm-color-mode' );
    wp_add_inline_script( 'tsothm-color-mode', tsothm_get_color_mode_js() );
}
add_action( 'wp_enqueue_scripts', 'tsothm_enqueue_color_mode_script' );

/**
 * JS del selector de mode de color (dia / nit / automàtic).
 *
 * @return string
 */
function tsothm_get_color_mode_js() {
    return <<<'JS'
( function () {
    'use strict';

    var STORAGE_KEY = 'tsothmColorMode';
    var GEO_KEY = 'tsothmGeo';
    var root = document.documentElement;
    var media = window.matchMedia( '(prefers-color-scheme: dark)' );
    var buttons = [];
    var flipTimer = null;

    var TZ_COORDS = {
        'Europe/Madrid': [40.4, -3.7], 'Europe/London': [51.5, -0.1], 'Europe/Paris': [48.9, 2.3],
        'Europe/Berlin': [52.5, 13.4], 'Europe/Rome': [41.9, 12.5], 'Europe/Lisbon': [38.7, -9.1],
        'Europe/Amsterdam': [52.4, 4.9], 'Europe/Brussels': [50.8, 4.4], 'Europe/Vienna': [48.2, 16.4],
        'Europe/Zurich': [47.4, 8.5], 'Europe/Warsaw': [52.2, 21.0], 'Europe/Athens': [38.0, 23.7],
        'Europe/Moscow': [55.8, 37.6], 'Europe/Stockholm': [59.3, 18.1], 'Europe/Oslo': [59.9, 10.7],
        'Europe/Helsinki': [60.2, 24.9], 'Europe/Dublin': [53.3, -6.3], 'Europe/Budapest': [47.5, 19.0],
        'Europe/Bucharest': [44.4, 26.1], 'Europe/Kiev': [50.5, 30.5],
        'America/New_York': [40.7, -74.0], 'America/Chicago': [41.9, -87.6], 'America/Denver': [39.7, -105.0],
        'America/Los_Angeles': [34.1, -118.2], 'America/Mexico_City': [19.4, -99.1], 'America/Bogota': [4.7, -74.1],
        'America/Sao_Paulo': [-23.6, -46.6], 'America/Argentina/Buenos_Aires': [-34.6, -58.4], 'America/Santiago': [-33.5, -70.6],
        'America/Lima': [-12.0, -77.0], 'America/Toronto': [43.7, -79.4],
        'Asia/Tokyo': [35.7, 139.7], 'Asia/Shanghai': [31.2, 121.5], 'Asia/Hong_Kong': [22.3, 114.2],
        'Asia/Singapore': [1.35, 103.8], 'Asia/Dubai': [25.2, 55.3], 'Asia/Kolkata': [19.1, 72.9],
        'Asia/Bangkok': [13.8, 100.5], 'Asia/Seoul': [37.6, 127.0], 'Asia/Jakarta': [-6.2, 106.8],
        'Asia/Manila': [14.6, 121.0],
        'Australia/Sydney': [-33.9, 151.2], 'Australia/Melbourne': [-37.8, 144.9], 'Pacific/Auckland': [-36.8, 174.8],
        'Africa/Cairo': [30.0, 31.2], 'Africa/Johannesburg': [-26.2, 28.0], 'Africa/Lagos': [6.5, 3.4],
        'Africa/Nairobi': [-1.3, 36.8]
    };

    function getStoredMode() {
        try {
            var m = window.localStorage.getItem( STORAGE_KEY );
            if ( m === 'light' || m === 'dark' || m === 'auto' ) {
                return m;
            }
        } catch ( e ) {}
        return 'auto';
    }

    function getApproxCoords() {
        var tz = null;
        try {
            tz = Intl.DateTimeFormat().resolvedOptions().timeZone;
        } catch ( e ) {}
        if ( tz && TZ_COORDS[ tz ] ) {
            return TZ_COORDS[ tz ];
        }
        var offsetMin = -( new Date() ).getTimezoneOffset();
        var lon = offsetMin / 4;
        if ( lon > 180 ) { lon -= 360; }
        if ( lon < -180 ) { lon += 360; }
        return [ 20, lon ];
    }

    function getSunTimes( date, lat, lon ) {
        var rad = Math.PI / 180;
        var dayMs = 86400000;
        var J1970 = 2440588;
        var J2000 = 2451545;
        var e = rad * 23.4397;

        function toJulian( d ) { return d.valueOf() / dayMs - 0.5 + J1970; }
        function fromJulian( j ) { return new Date( ( j + 0.5 - J1970 ) * dayMs ); }
        function toDays( d ) { return toJulian( d ) - J2000; }
        function solarMeanAnomaly( d ) { return rad * ( 357.5291 + 0.98560028 * d ); }
        function eclipticLongitude( M ) {
            var C = rad * ( 1.9148 * Math.sin( M ) + 0.02 * Math.sin( 2 * M ) + 0.0003 * Math.sin( 3 * M ) );
            var P = rad * 102.9372;
            return M + C + P + Math.PI;
        }
        function declination( L ) { return Math.asin( Math.sin( L ) * Math.sin( e ) ); }
        function julianCycle( d, lw ) { return Math.round( d - 0.0009 - lw / ( 2 * Math.PI ) ); }
        function approxTransit( Ht, lw, n ) { return 0.0009 + ( Ht + lw ) / ( 2 * Math.PI ) + n; }
        function solarTransitJ( ds, M, L ) { return J2000 + ds + 0.0053 * Math.sin( M ) - 0.0069 * Math.sin( 2 * L ); }
        function hourAngle( h, phi, d ) {
            var cosH = ( Math.sin( h ) - Math.sin( phi ) * Math.sin( d ) ) / ( Math.cos( phi ) * Math.cos( d ) );
            cosH = Math.max( -1, Math.min( 1, cosH ) );
            return Math.acos( cosH );
        }

        var lw = rad * -lon;
        var phi = rad * lat;
        var d = toDays( date );
        var n = julianCycle( d, lw );
        var ds = approxTransit( 0, lw, n );
        var M = solarMeanAnomaly( ds );
        var L = eclipticLongitude( M );
        var dec = declination( L );
        var Jnoon = solarTransitJ( ds, M, L );
        var h0 = rad * -0.833;
        var w0 = hourAngle( h0, phi, dec );
        var a = approxTransit( w0, lw, n );
        var Jset = solarTransitJ( a, M, L );
        var Jrise = Jnoon - ( Jset - Jnoon );

        return { sunrise: fromJulian( Jrise ), sunset: fromJulian( Jset ) };
    }

    function isDaytime( lat, lon ) {
        var times = getSunTimes( new Date(), lat, lon );
        var now = new Date();
        return now >= times.sunrise && now < times.sunset;
    }

    function readGeoCache() {
        try {
            return JSON.parse( window.localStorage.getItem( GEO_KEY ) || 'null' );
        } catch ( e ) {
            return null;
        }
    }

    function writeGeoCache( lat, lon ) {
        try {
            window.localStorage.setItem( GEO_KEY, JSON.stringify( { lat: lat, lon: lon, date: ( new Date() ).toDateString() } ) );
        } catch ( e ) {}
    }

    function applyMode( mode ) {
        var effective;
        if ( mode === 'auto' ) {
            var cached = readGeoCache();
            if ( cached && typeof cached.lat === 'number' && typeof cached.lon === 'number' ) {
                effective = isDaytime( cached.lat, cached.lon ) ? 'light' : 'dark';
            } else {
                effective = media.matches ? 'dark' : 'light';
            }
        } else {
            effective = mode;
        }
        root.setAttribute( 'data-theme', effective );
    }

    function updateActiveButton( mode ) {
        buttons.forEach( function ( btn ) {
            var isActive = btn.getAttribute( 'data-mode' ) === mode;
            btn.classList.toggle( 'is-active', isActive );
            btn.setAttribute( 'aria-pressed', isActive ? 'true' : 'false' );
        } );
    }

    function setMode( mode ) {
        try {
            window.localStorage.setItem( STORAGE_KEY, mode );
        } catch ( e ) {}
        applyMode( mode );
        updateActiveButton( mode );
        if ( mode === 'auto' ) {
            resolveCoordsAndSchedule();
        } else if ( flipTimer ) {
            clearTimeout( flipTimer );
            flipTimer = null;
        }
    }

    function scheduleNextFlip( lat, lon ) {
        if ( flipTimer ) {
            clearTimeout( flipTimer );
        }
        var now = new Date();
        var times = getSunTimes( now, lat, lon );
        var next;
        if ( now < times.sunrise ) {
            next = times.sunrise;
        } else if ( now < times.sunset ) {
            next = times.sunset;
        } else {
            next = getSunTimes( new Date( now.getTime() + 86400000 ), lat, lon ).sunrise;
        }
        var delay = Math.min( Math.max( 60000, next.getTime() - now.getTime() ), 3600000 * 6 );
        flipTimer = setTimeout( function () {
            if ( getStoredMode() === 'auto' ) {
                applyMode( 'auto' );
            }
            scheduleNextFlip( lat, lon );
        }, delay );
    }

    function resolveCoordsAndSchedule() {
        var todayStr = ( new Date() ).toDateString();
        var cached = readGeoCache();
        if ( cached && cached.date === todayStr && typeof cached.lat === 'number' ) {
            if ( getStoredMode() === 'auto' ) {
                applyMode( 'auto' );
            }
            scheduleNextFlip( cached.lat, cached.lon );
            return;
        }

        var approx = getApproxCoords();

        function finish( lat, lon ) {
            writeGeoCache( lat, lon );
            if ( getStoredMode() === 'auto' ) {
                applyMode( 'auto' );
                updateActiveButton( 'auto' );
            }
            scheduleNextFlip( lat, lon );
        }

        if ( navigator.geolocation && navigator.permissions && navigator.permissions.query ) {
            navigator.permissions.query( { name: 'geolocation' } ).then( function ( status ) {
                if ( status.state === 'granted' ) {
                    navigator.geolocation.getCurrentPosition(
                        function ( pos ) { finish( pos.coords.latitude, pos.coords.longitude ); },
                        function () { finish( approx[ 0 ], approx[ 1 ] ); },
                        { timeout: 5000, maximumAge: 3600000 }
                    );
                } else {
                    finish( approx[ 0 ], approx[ 1 ] );
                }
            } ).catch( function () { finish( approx[ 0 ], approx[ 1 ] ); } );
        } else {
            finish( approx[ 0 ], approx[ 1 ] );
        }
    }

    applyMode( getStoredMode() );
    resolveCoordsAndSchedule();

    var onMediaChange = function () {
        if ( getStoredMode() === 'auto' && ! readGeoCache() ) {
            applyMode( 'auto' );
        }
    };
    if ( media.addEventListener ) {
        media.addEventListener( 'change', onMediaChange );
    } else if ( media.addListener ) {
        media.addListener( onMediaChange );
    }

    function wireSwitch() {
        var wrap = document.querySelector( '.tso-theme-switch' );
        if ( ! wrap ) {
            buttons = [];
            return;
        }
        buttons = Array.prototype.slice.call( wrap.querySelectorAll( '.tso-theme-switch-btn' ) );
        buttons.forEach( function ( btn ) {
            btn.addEventListener( 'click', function () {
                setMode( btn.getAttribute( 'data-mode' ) );
            } );
        } );
        updateActiveButton( getStoredMode() );
    }

    if ( document.readyState === 'loading' ) {
        document.addEventListener( 'DOMContentLoaded', wireSwitch );
    } else {
        wireSwitch();
    }

    if ( window.wp && wp.customize && wp.customize.selectiveRefresh ) {
        wp.customize.selectiveRefresh.bind( 'partial-content-rendered', function ( placement ) {
            if ( placement.partial && placement.partial.id === 'tsothm_color_mode_switch' ) {
                wireSwitch();
            }
        } );
    }
} )();
JS;
}

/**
 * Imprimeix un petit script inline al <head>, el més aviat possible,
 * per fixar l'atribut data-theme abans de pintar la pàgina i evitar
 * el "flash" del mode incorrecte.
 */
function tsothm_print_color_mode_boot_script() {
    if ( tsothm_is_legacy_widget_preview() ) {
        return;
    }
    $js = <<<'JS'
(function () {
    try {
        var m = window.localStorage.getItem( 'tsothmColorMode' );
        var effective = null;
        if ( m === 'light' || m === 'dark' ) {
            effective = m;
        } else {
            try {
                var geo = JSON.parse( window.localStorage.getItem( 'tsothmGeo' ) || 'null' );
                if ( geo && geo.date === ( new Date() ).toDateString() && typeof geo.lat === 'number' ) {
                    var rad = Math.PI / 180, dayMs = 86400000, J1970 = 2440588, J2000 = 2451545, e = rad * 23.4397;
                    var toJulian = function ( d ) { return d.valueOf() / dayMs - 0.5 + J1970; };
                    var toDays = function ( d ) { return toJulian( d ) - J2000; };
                    var d0 = toDays( new Date() );
                    var M = rad * ( 357.5291 + 0.98560028 * d0 );
                    var C = rad * ( 1.9148 * Math.sin( M ) + 0.02 * Math.sin( 2 * M ) + 0.0003 * Math.sin( 3 * M ) );
                    var L = M + C + rad * 102.9372 + Math.PI;
                    var dec = Math.asin( Math.sin( L ) * Math.sin( e ) );
                    var lw = rad * -geo.lon, phi = rad * geo.lat;
                    var n = Math.round( d0 - 0.0009 - lw / ( 2 * Math.PI ) );
                    var ds = 0.0009 + lw / ( 2 * Math.PI ) + n;
                    var Jnoon = J2000 + ds + 0.0053 * Math.sin( M ) - 0.0069 * Math.sin( 2 * L );
                    var h0 = rad * -0.833;
                    var cosH = Math.max( -1, Math.min( 1, ( Math.sin( h0 ) - Math.sin( phi ) * Math.sin( dec ) ) / ( Math.cos( phi ) * Math.cos( dec ) ) ) );
                    var w0 = Math.acos( cosH );
                    var a = 0.0009 + ( w0 + lw ) / ( 2 * Math.PI ) + n;
                    var Jset = J2000 + a + 0.0053 * Math.sin( M ) - 0.0069 * Math.sin( 2 * L );
                    var Jrise = Jnoon - ( Jset - Jnoon );
                    var sunrise = new Date( ( Jrise + 0.5 - J1970 ) * dayMs );
                    var sunset = new Date( ( Jset + 0.5 - J1970 ) * dayMs );
                    var now = new Date();
                    effective = ( now >= sunrise && now < sunset ) ? 'light' : 'dark';
                }
            } catch ( e3 ) {}
            if ( ! effective ) {
                effective = window.matchMedia( '(prefers-color-scheme: dark)' ).matches ? 'dark' : 'light';
            }
        }
        document.documentElement.setAttribute( 'data-theme', effective );
    } catch ( e ) {}
})();
JS;
    wp_print_inline_script_tag( $js );
}
add_action( 'wp_head', 'tsothm_print_color_mode_boot_script', 1 );
