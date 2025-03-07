( function() {
	if ( 'undefined' === typeof wp || 'undefined' === typeof wp.media || 'undefined' === typeof wp.media.view || 'undefined' === typeof wp.media.view.Attachment || 'undefined' === typeof wp.media.view.Attachment.Details ) {
		return;
	}

	var originalCompat = wp.media.view.AttachmentCompat;
	wp.media.view.AttachmentCompat = originalCompat.extend( {
		initialize: function() {
			// Call the parent initialize.
			originalCompat.prototype.initialize.apply( this, arguments );

			// Bind to the render event.
			this.on( 'compatRendered', this.initializeLifterlmsSelect2 );

			// Listen for changes to the protection dropdown.
			this.listenTo( this.model, 'change', this.refreshAttachmentUrl );
		},

		render: function() {
			// Call the parent render
			originalCompat.prototype.render.apply(this, arguments);

			// Trigger our custom event after render.
			// TODO: Check if we need the defer.
			_.defer(() => {
				this.trigger( 'compatRendered' );
			});

			return this;
		},

		initializeLifterlmsSelect2: function() {
			const $select = jQuery( '.media-modal .llms-posts-select2' );
			if ( $select.length && ! $select.data( 'select2' )) {
				$select.llmsPostsSelect2();
			}
		},

		refreshAttachmentUrl: function() {
			// When the protection setting changes, we need to refresh the URL
			if ( this.model.hasChanged( 'url' ) ) {
				// Update the URL in the UI.
				jQuery( '#attachment-details-copy-link' ).val( this.model.get( 'url' ) );
			}
		}
	} );
} )();
