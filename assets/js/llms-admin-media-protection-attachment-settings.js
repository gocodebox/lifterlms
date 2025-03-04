(function() {
	debugger;
	if ( 'undefined' === typeof wp || 'undefined' === typeof wp.media || 'undefined' === typeof wp.media.view || 'undefined' === typeof wp.media.view.Attachment || 'undefined' === typeof wp.media.view.Attachment.Details ) {
		return;
	}

	var originalDetails = wp.media.view.AttachmentCompat;
	wp.media.view.AttachmentCompat = originalDetails.extend({
		initialize: function() {
			// Call the parent initialize.
			originalDetails.prototype.initialize.apply(this, arguments);

			// Bind to the render event.
			this.on('render', this.initializeLifterlmsSelect2);
// 			this.listenTo(this.model, 'change:compat', function() {
// 				// The view will re-render after compat changes
// 				this.on('render', this.initializeLifterlmsSelect2);
// 			});
// //			this.controller.on( 'attachment:details:ready', this.initializeLifterlmsSelect2 );
			this.listenTo(this.model, 'change:compat', this.initializeLifterlmsSelect2);
		},

		initializeLifterlmsSelect2: function() {
			debugger;
			setTimeout(function() {
				var $select = jQuery( '.media-modal .llms-posts-select2' );
				if ( $select.length && ! $select.data( 'select2' )) {
					$select.llmsPostsSelect2();
				}
			}, 100);
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
