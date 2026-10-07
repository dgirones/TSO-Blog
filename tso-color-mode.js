/**
 * TSO Blog — selector de mode de color (dia / nit / automàtic).
 *
 * "Automàtic" segueix window.matchMedia('(prefers-color-scheme: dark)')
 * i es manté sincronitzat en viu si el sistema operatiu canvia de mode
 * mentre la pàgina és oberta.
 */
( function () {
    'use strict';

    var STORAGE_KEY = 'tsothmColorMode';
    var root = document.documentElement;
    var media = window.matchMedia( '(prefers-color-scheme: dark)' );
    var buttons = [];

    function getStoredMode() {
        try {
            var m = window.localStorage.getItem( STORAGE_KEY );
            if ( m === 'light' || m === 'dark' || m === 'auto' ) {
                return m;
            }
        } catch ( e ) {}
        return 'auto';
    }

    function applyMode( mode ) {
        var effective = ( mode === 'auto' ) ? ( media.matches ? 'dark' : 'light' ) : mode;
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
    }

    // Estat inicial (el <head> ja ha fixat data-theme per evitar el flash).
    applyMode( getStoredMode() );

    var onMediaChange = function () {
        if ( getStoredMode() === 'auto' ) {
            applyMode( 'auto' );
        }
    };
    if ( media.addEventListener ) {
        media.addEventListener( 'change', onMediaChange );
    } else if ( media.addListener ) {
        media.addListener( onMediaChange );
    }

    var ICONS = {
        light: '<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="4"></circle><path d="M12 2.5v2.5M12 19v2.5M4.2 4.2l1.8 1.8M18 18l1.8 1.8M1.5 12h2.5M20 12h2.5M4.2 19.8l1.8-1.8M18 6l1.8-1.8"/></svg>',
        dark: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20.5 14.7A8.5 8.5 0 0 1 9.3 3.5a8.5 8.5 0 1 0 11.2 11.2z"/></svg>',
        auto: '<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="8.5"></circle><path d="M12 3.5a8.5 8.5 0 0 1 0 17z" fill="currentColor" stroke="none"></path></svg>'
    };
    var LABELS = { light: 'Mode dia', dark: 'Mode nit', auto: 'Mode automàtic' };

    function buildSwitch() {
        var wrap = document.createElement( 'div' );
        wrap.className = 'tso-theme-switch';
        wrap.setAttribute( 'role', 'group' );
        wrap.setAttribute( 'aria-label', 'Mode de color' );

        [ 'light', 'dark', 'auto' ].forEach( function ( mode ) {
            var btn = document.createElement( 'button' );
            btn.type = 'button';
            btn.className = 'tso-theme-switch-btn';
            btn.setAttribute( 'data-mode', mode );
            btn.setAttribute( 'aria-label', LABELS[ mode ] );
            btn.setAttribute( 'title', LABELS[ mode ] );
            btn.innerHTML = ICONS[ mode ];
            btn.addEventListener( 'click', function () {
                setMode( mode );
            } );
            wrap.appendChild( btn );
            buttons.push( btn );
        } );

        document.body.appendChild( wrap );
        updateActiveButton( getStoredMode() );
    }

    if ( document.readyState === 'loading' ) {
        document.addEventListener( 'DOMContentLoaded', buildSwitch );
    } else {
        buildSwitch();
    }
} )();
