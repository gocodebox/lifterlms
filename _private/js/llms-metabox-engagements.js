;( function( $, undefined ) {

	window.llms = window.llms || {};

	var MetaboxEngagements = function() {

		this.init = function() {

			this.bind();

		};

		this.bind = function() {

			var self = this;

			console.log( this );

		};


		// go
		this.init();

	};

	// initalize the object
	window.llms.MetaboxEngagements = new MetaboxEngagements();

} )( jQuery );
