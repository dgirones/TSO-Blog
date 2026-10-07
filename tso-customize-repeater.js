/**
 * Customizer: control repetidor genérico (Equipo, Precios, Landing).
 */
( function ( $ ) {
	'use strict';

	function escAttr( str ) {
		return String( str === undefined || str === null ? '' : str )
			.replace( /&/g, '&amp;' )
			.replace( /"/g, '&quot;' )
			.replace( /</g, '&lt;' )
			.replace( />/g, '&gt;' );
	}

	function fieldRowHtml( field, value ) {
		var key = field.key;
		var label = escAttr( field.label || key );
		var html = '<div class="tso-repeater-field tso-repeater-field--' + field.type + '">';
		html += '<label>' + label + '</label>';

		if ( 'textarea' === field.type ) {
			html += '<textarea data-field="' + escAttr( key ) + '" rows="3">' + escAttr( value ) + '</textarea>';
		} else if ( 'checkbox' === field.type ) {
			html += '<input type="checkbox" data-field="' + escAttr( key ) + '"' + ( value ? ' checked' : '' ) + ' />';
		} else if ( 'image' === field.type ) {
			html += '<div class="tso-repeater-image">';
			html += '<div class="tso-repeater-image-preview"></div>';
			html += '<button type="button" class="button tso-repeater-image-select">' + ( window.tsoRepeaterL10n && window.tsoRepeaterL10n.selectImage ? window.tsoRepeaterL10n.selectImage : 'Seleccionar imagen' ) + '</button> ';
			html += '<button type="button" class="button-link tso-repeater-image-remove">' + ( window.tsoRepeaterL10n && window.tsoRepeaterL10n.removeImage ? window.tsoRepeaterL10n.removeImage : 'Quitar' ) + '</button>';
			html += '<input type="hidden" data-field="' + escAttr( key ) + '" value="' + escAttr( value || 0 ) + '" />';
			html += '</div>';
		} else {
			html += '<input type="text" data-field="' + escAttr( key ) + '" value="' + escAttr( value ) + '" />';
		}

		html += '</div>';
		return html;
	}

	function buildRowHtml( fields, row ) {
		var html = '<li class="tso-repeater-row">';
		html += '<span class="tso-repeater-handle dashicons dashicons-menu" aria-hidden="true"></span>';
		html += '<div class="tso-repeater-fields">';
		fields.forEach( function ( field ) {
			html += fieldRowHtml( field, row[ field.key ] );
		} );
		html += '</div>';
		html += '<button type="button" class="tso-repeater-remove dashicons dashicons-trash" aria-hidden="true"></button>';
		html += '</li>';
		return html;
	}

	function refreshImagePreview( $row, field, attachmentId ) {
		var $field = $row.find( '.tso-repeater-field--image [data-field="' + field.key + '"]' ).closest( '.tso-repeater-field' );
		var $preview = $field.find( '.tso-repeater-image-preview' );
		if ( ! attachmentId ) {
			$preview.empty();
			return;
		}
		var attachment = wp.media.attachment( attachmentId );
		attachment.fetch().done( function () {
			var url = attachment.get( 'url' );
			if ( attachment.get( 'sizes' ) && attachment.get( 'sizes' ).thumbnail ) {
				url = attachment.get( 'sizes' ).thumbnail.url;
			}
			$preview.html( '<img src="' + url + '" alt="" />' );
		} );
	}

	function initRepeater( el ) {
		var $el = $( el );
		if ( $el.data( 'tso-repeater-init' ) ) {
			return;
		}
		$el.data( 'tso-repeater-init', true );

		var fields = $el.data( 'fields' ) || [];
		var $input = $el.find( '.tso-repeater-input' );
		var $list = $el.find( '.tso-repeater-list' );
		var rows = [];
		try {
			rows = JSON.parse( $input.val() || '[]' );
		} catch ( e ) {
			rows = [];
		}
		if ( ! Array.isArray( rows ) ) {
			rows = [];
		}

		function sync() {
			var data = [];
			$list.children( '.tso-repeater-row' ).each( function () {
				var $row = $( this );
				var row = {};
				fields.forEach( function ( field ) {
					var $field = $row.find( '[data-field="' + field.key + '"]' );
					if ( 'checkbox' === field.type ) {
						row[ field.key ] = $field.is( ':checked' );
					} else {
						row[ field.key ] = $field.val();
					}
				} );
				data.push( row );
			} );
			var json = JSON.stringify( data );
			$input.val( json ).trigger( 'change' );
			var settingId = $input.attr( 'data-customize-setting-link' );
			if ( settingId && wp.customize && wp.customize( settingId ) ) {
				wp.customize( settingId ).set( json );
			}
		}

		function addRow( row ) {
			row = row || {};
			var $row = $( buildRowHtml( fields, row ) );
			$list.append( $row );
			fields.forEach( function ( field ) {
				if ( 'image' === field.type && row[ field.key ] ) {
					refreshImagePreview( $row, field, row[ field.key ] );
				}
			} );
		}

		rows.forEach( addRow );

		$list.sortable( {
			handle: '.tso-repeater-handle',
			axis: 'y',
			tolerance: 'pointer',
			update: sync,
		} );

		$el.on( 'click', '.tso-repeater-add', function ( e ) {
			e.preventDefault();
			addRow( {} );
			sync();
		} );

		$el.on( 'click', '.tso-repeater-remove', function ( e ) {
			e.preventDefault();
			$( this ).closest( '.tso-repeater-row' ).remove();
			sync();
		} );

		$el.on( 'input change', '.tso-repeater-field input[type="text"], .tso-repeater-field textarea, .tso-repeater-field input[type="checkbox"]', function () {
			sync();
		} );

		$el.on( 'click', '.tso-repeater-image-select', function ( e ) {
			e.preventDefault();
			var $btn = $( this );
			var $row = $btn.closest( '.tso-repeater-row' );
			var $field = $btn.closest( '.tso-repeater-field' );
			var $hidden = $field.find( 'input[type="hidden"]' );

			var frame = wp.media( {
				title: window.tsoRepeaterL10n ? window.tsoRepeaterL10n.chooseImage : 'Elegir imagen',
				multiple: false,
				library: { type: 'image' },
			} );
			frame.on( 'select', function () {
				var attachment = frame.state().get( 'selection' ).first().toJSON();
				$hidden.val( attachment.id );
				var url = attachment.url;
				if ( attachment.sizes && attachment.sizes.thumbnail ) {
					url = attachment.sizes.thumbnail.url;
				}
				$field.find( '.tso-repeater-image-preview' ).html( '<img src="' + url + '" alt="" />' );
				sync();
			} );
			frame.open();
		} );

		$el.on( 'click', '.tso-repeater-image-remove', function ( e ) {
			e.preventDefault();
			var $field = $( this ).closest( '.tso-repeater-field' );
			$field.find( 'input[type="hidden"]' ).val( '' );
			$field.find( '.tso-repeater-image-preview' ).empty();
			sync();
		} );
	}

	function initAll() {
		$( '.tso-repeater' ).each( function () {
			initRepeater( this );
		} );
	}

	if ( wp.customize && wp.customize.bind ) {
		wp.customize.bind( 'ready', initAll );
		wp.customize.control.bind( 'add', function ( control ) {
			control.deferred.embedded.done( function () {
				initAll();
			} );
		} );
	} else {
		$( initAll );
	}
}( jQuery ) );
