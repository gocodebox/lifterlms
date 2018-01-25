/**
 * Quiz Question Type
 * @since    [version]
 * @version  [version]
 */
define( [], function() {

	return Backbone.Model.extend( {

		defaults: function() {
			return {
				choices: false,
				clarifications: true,
				default_choices: [],
				description: true,
				icon: 'question',
				id: 'generic',
				image: true,
				keywords: [],
				name: 'Generic',
				placeholder: '',
				points: true,
				video: true,
			}
		},

		initialize: function() {

			// console.log( 'Question loaded: ' + this.get( 'name' ) );

		},

		/**
		 * Retrieve an array of keywords for the question type
		 * Used for filtering questions by search term in the quiz builder
		 * @return   array
		 * @since    [version]
		 * @version  [version]
		 */
		get_keywords: function() {

			var name = this.get( 'name' ),
				words = [ name ];

			return words.concat( this.get( 'keywords' ) ).concat( name.split( ' ' ) );

		},

		get_choice_type: function() {

			return this._get_choice_option( 'type' );

		},

		get_min_choices: function() {

			return this._get_choice_option( 'min' );

		},

		get_max_choices: function() {

			var max = this._get_choice_option( 'max' );

			if ( '-1' == max ) {
				return window.llms_builder.choice_markers.length;
			}

			return max;

		},

		get_multi_choices: function() {

			var choices = this.get( 'choices' );

			if ( ! choices  ) {
				return false;
			}

			return this._get_choice_option( 'multi' );

		},

		_get_choice_option: function( option ) {

			var choices = this.get( 'choices' );

			if ( ! choices || ! choices[ option ] ) {
				return false;
			}

			return choices[ option ];

		},

	} );

} );
