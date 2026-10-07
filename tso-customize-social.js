/**
 * Customizer: drag-and-drop social icon order.
 */
( function ( $ ) {
	'use strict';

	function tsoBindSocialOrder() {
		var $list = $( '#tso-social-order-list' );
		if ( ! $list.length || $list.data( 'tso-sortable' ) ) {
			return;
		}

		$list.data( 'tso-sortable', true );

		$list.sortable( {
			handle: '.tso-social-order-handle',
			axis: 'y',
			tolerance: 'pointer',
			update: function () {
				var keys = [];
				$list.children( '[data-key]' ).each( function () {
					keys.push( $( this ).attr( 'data-key' ) );
				} );
				var value = keys.join( ',' );
				var $input = $list.siblings( '.tso-social-order-input' );
				$input.val( value ).trigger( 'change' );
				if ( wp.customize && wp.customize( 'tso_social_order' ) ) {
					wp.customize( 'tso_social_order' ).set( value );
				}
			},
		} );
	}

	if ( wp.customize && wp.customize.bind ) {
		wp.customize.bind( 'ready', tsoBindSocialOrder );
	} else {
		$( tsoBindSocialOrder );
	}
}( jQuery ) );
