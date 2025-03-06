(function() {
	if ( 'undefined' === typeof wp || 'undefined' === typeof wp.media || 'undefined' === typeof wp.media.view || 'undefined' === typeof wp.media.view.Attachment || 'undefined' === typeof wp.media.view.Attachment.Details ) {
		return;
	}

	var originalCompat = wp.media.view.AttachmentCompat;
	wp.media.view.AttachmentCompat = originalCompat.extend({
		initialize: function() {
			// Call the parent initialize.
			originalCompat.prototype.initialize.apply(this, arguments);

			// Bind to the render event.
			this.on('compatRendered', this.initializeLifterlmsSelect2);

			// Listen for changes to the protection dropdown.
			this.listenTo(this.model, 'change', this.refreshAttachmentUrl);
		},

		render: function() {
			// Call the parent render
			originalCompat.prototype.render.apply(this, arguments);

			// Trigger our custom event after render
			_.defer(() => {
				this.trigger('compatRendered');
			});

			return this;
		},

		initializeLifterlmsSelect2: function() {
			var $select = jQuery( '.media-modal .llms-posts-select2' );
			if ( $select.length && ! $select.data( 'select2' )) {
				$select.llmsPostsSelect2();

				// Set the initial value if it exists
				// var productId = this.model.get('llms_media_protection_post');
				// if (productId) {
				// 	$select.val(productId).trigger('change');
				// }
			}
		},

		refreshAttachmentUrl: function() {
			debugger;
			// When the protection setting changes, we need to refresh the URL
			if ( this.model.hasChanged( 'url' ) ) {
				// Force a refresh of the attachment details
				var attachment = this.model;

				// We need to wait for the server to process the change
//				setTimeout(function() {
					// Refresh the attachment from the server



				// TODO: Just update the File URL input with the url value?


					// wp.media.attachment(attachment.id).fetch({
					// 	success: function() {
					// 		// This will update the URL in the UI
					// 		attachment.trigger('change');
					// 	}
					// });
//				}, 1000); // Give the server a second to process
			}
		}
	});

	//
	// wp.media.view.Attachment.Details = wp.media.view.Attachment.Details.extend({
	// 	// Initialize select2 field if there is one.
	// 	initialize: function() {
	// 		wp.media.view.Attachment.prototype.initialize.apply( this, arguments );
	//
	// 		// listen for selecting an image in the WP media attachment and the fields being rendered.
	//
	// 	}
	// });
	//
	// // Listen for attachment selection in media modal
	// wp.media.frame.on('selection:toggle', function() {
	// 	setTimeout(function() {
	// 		var $select = jQuery('.media-modal .llms-select2');
	// 		if ($select.length && !$select.data('select2')) {
	// 			$select.llmsPostsSelect2();
	// 		}
	// 	}, 100);
	// });
	//
	// // Also initialize when switching between grid and list view
	// wp.media.frame.on('content:activate', function() {
	// 	setTimeout(function() {
	// 		var $select = jQuery('.media-modal .llms-select2');
	// 		if ($select.length && !$select.data('select2')) {
	// 			$select.llmsPostsSelect2();
	// 		}
	// 	}, 100);
	// });
})();
