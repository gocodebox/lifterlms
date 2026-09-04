/**
 * Course Streams Metabox
 *
 * @since [version]
 * @version [version]
 */
( function( $ ) {

	function slugify( name ) {
		return name.toLowerCase().replace( /[^a-z0-9]+/g, '-' ).replace( /^-|-$/g, '' );
	}

	function uniqueId( base, used ) {
		var id = base || 'stream',
			i  = 2;
		while ( -1 !== used.indexOf( id ) ) {
			id = base + '-' + i;
			i++;
		}
		return id;
	}

	function getUsedIds( $repeater, $except ) {
		var ids = [];
		$repeater.find( '.llms-repeater-rows .llms-repeater-row' ).each( function() {
			if ( $except && this === $except ) {
				return;
			}
			var val = $( this ).find( 'input[name^="_llms_stream_id"]' ).val();
			if ( val ) {
				ids.push( val );
			}
		} );
		return ids;
	}

	function syncDefaultSelect( $repeater ) {
		var $select = $( '#_llms_streams_default' ),
			current = $select.val();

		if ( ! $select.length ) {
			return;
		}

		$select.find( 'option' ).remove();

		$repeater.find( '.llms-repeater-rows .llms-repeater-row' ).each( function() {
			var id   = $( this ).find( 'input[name^="_llms_stream_id"]' ).val(),
				name = $( this ).find( 'input[name^="_llms_stream_name"]' ).val();
			if ( ! id || ! name ) {
				return;
			}
			$select.append( $( '<option />' ).val( id ).text( name ) );
		} );

		if ( current && $select.find( 'option[value="' + current + '"]' ).length ) {
			$select.val( current );
		} else {
			$select.find( 'option:first' ).prop( 'selected', true );
		}

		$select.trigger( 'change' );
	}

	function ensureRowId( $row, $repeater ) {
		var $id   = $row.find( 'input[name^="_llms_stream_id"]' ),
			$name = $row.find( 'input[name^="_llms_stream_name"]' ),
			id    = $id.val(),
			name  = $name.val();

		if ( id || ! name ) {
			return;
		}

		$id.val( uniqueId( slugify( name ), getUsedIds( $repeater, $row.get( 0 ) ) ) );
	}

	function bindRow( $row, $repeater ) {
		$row.find( 'input[name^="_llms_stream_name"]' ).on( 'keyup change blur', function() {
			ensureRowId( $row, $repeater );
			syncDefaultSelect( $repeater );
		} );
	}

	$( function() {
		var $repeater = $( '._llms_streams_data.repeater' );
		if ( ! $repeater.length ) {
			return;
		}

		$repeater.find( '.llms-repeater-rows .llms-repeater-row' ).each( function() {
			bindRow( $( this ), $repeater );
		} );

		$repeater.on( 'llms-new-repeater-row', function( e, params ) {
			bindRow( params.$row, $repeater );
		} );

		$repeater.on( 'click', '.llms-repeater-remove', function() {
			setTimeout( function() {
				syncDefaultSelect( $repeater );
			}, 10 );
		} );

		$repeater.on( 'llms-repeater-before-save', function() {
			$repeater.find( '.llms-repeater-rows .llms-repeater-row' ).each( function() {
				ensureRowId( $( this ), $repeater );
			} );
		} );
	} );

} )( jQuery );
