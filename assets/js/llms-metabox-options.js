/**
 * JS for the Course and Membership metabox options
 *
 * @since [version]
 */

( function( $ ){

	$( '.llms-mb-container .llms-input-formatting[data-formatting]' ).each( function() {

		const formatting = $( this ).attr( 'data-formatting' ).split( ',' ),
			attr       = $( this ).attr( 'data-attribute' ),
			name 	   = $( this ).attr( 'data-name' );

		const ed = new Quill( this, {
			modules: {
				toolbar: ['bold', 'italic', 'underline', 'strike', { 'script': 'sub'}, { 'script': 'super' }],
				keyboard: {
					bindings: {
						tab: {
							key: 9,
							handler: function( range, context ) {
								return true;
							},
						},
						13: {
							key: 13,
							handler: function( range, context ) {
								ed.root.blur();
								return false;
							},
						},
					},
				},
			},
			placeholder: $( this ).attr( 'data-placeholder' ),
			theme: 'bubble',
		} );

		ed.on( 'text-change', function() {
			$( 'input[name="' + name + '"]' ).val( ed.getSemanticHTML() );
		});

	} );
} )( jQuery );
