;( function( $ ) {

	// $( 'button.llms-addon-action' ).on( 'click', function() {

	// 	var $btn = $( this ),
	// 		$container = $btn.closest( '.llms-add-on-item' ),
	// 		status;

	// 	switch ( $btn.attr( 'data-status' ) ) {
	// 		case 'active':
	// 			status = 'deactivate';
	// 		break;
	// 		case 'inactive':
	// 			status = 'activate';
	// 		break;
	// 		case 'none':
	// 			status = 'install';
	// 		break;
	// 	}

	// 	if ( ! status ) {
	// 		return;
	// 	}

	// 	LLMS.Spinner.start( $container );

	// 	LLMS.Ajax.call( {
	// 		data: {
	// 			action: 'llms_addon_toggle_activation',
	// 			addon: $btn.attr( 'data-addon' ),
	// 			status: status
	// 		},
	// 		success: function( res ) {
	// 			console.log( res );
	// 			LLMS.Spinner.stop( $container );
	// 		}

	// 	} );

	// } );

} )( jQuery );
