(function() {
	debugger;
	if ( 'undefined' === typeof wp || 'undefined' === typeof wp.media || 'undefined' === typeof wp.media.view || 'undefined' === typeof wp.media.view.Attachment || 'undefined' === typeof wp.media.view.Attachment.Details ) {
		return;
	}
	wp.media.view.Attachment.Details = wp.media.view.Attachment.Details.extend({
		// Initialize select2 field if there is one.
		initialize: function() {
			wp.media.view.Attachment.prototype.initialize.apply( this, arguments );
			var $select = this.$el.find( '.llms-select2' );
			if ( $select.length ) {
				$select.llmsPostsSelect2();
			}
		},
	});
})();
