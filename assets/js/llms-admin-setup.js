( function( $ ) {

	$( '#llms-setup-for-somene-else' ).on( 'change', function() {

		var checked  = $( this ).is( ':checked' ),
			display  = checked ? 'block' : 'none',
			$options = $( '#llms-setup-role-options' ),
			$hidden  = $( '#llms-setup-role-default' );

		$options.css( 'display', display );

		if ( checked ) {
			$options.find( 'select' ).removeAttr( 'disabled' );
			$hidden.attr( 'disabled', 'disabled' );
		} else {
			$hidden.removeAttr( 'disabled' );
			$options.find( 'select' ).attr( 'disabled', 'disabled' );
		}

	} ).trigger( 'change' );

	$( '#llms-setup-content-courses' ).on( 'change', function() {

		var display = $( this ).is( ':checked' ) ? 'block' : 'none';
		$( '.llms-setup-row.show-for-courses' ).css( 'display', display );

	} ).trigger( 'change' );


} ( jQuery ) );
