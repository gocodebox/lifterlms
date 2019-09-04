/**
 * Quiz Question Type
 *
 * @since    3.16.0
 * @version  3.16.0
 */
define( [], function() {

	return Backbone.Model.extend( {

		/**
		 * Get model default attributes
		 *
		 * @return   obj
		 * @since    3.16.0
		 * @version  3.16.0
		 */
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

		/**
		 * Retrieve an array of keywords for the question type
		 * Used for filtering questions by search term in the quiz builder
		 *
		 * @return   array
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		get_keywords: function() {

			var name  = this.get( 'name' ),
				words = [ name ];

			return words.concat( this.get( 'keywords' ) ).concat( name.split( ' ' ) );

		},

		/**
		 * Get marker array for the question choices
		 *
		 * @return   array
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		get_choice_markers: function() {

			return this._get_choice_option( 'markers' );

		},

		/**
		 * Determine if the question's choices are selectable
		 *
		 * @return   bool
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		get_choice_selectable: function() {

			return this._get_choice_option( 'selectable' );

		},

		/**
		 * Get the choice type (text,image)
		 *
		 * @return   string
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		get_choice_type: function() {

			return this._get_choice_option( 'type' );

		},

		/**
		 * Retrieve defined min. choices
		 *
		 * @return   int
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		get_min_choices: function() {

			return this._get_choice_option( 'min' );

		},

		/**
		 * Get type-defined max choices
		 *
		 * @return   string
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		get_max_choices: function() {

			return this._get_choice_option( 'max' );

		},

		/**
		 * Determine if multi-choice selection is enabled
		 *
		 * @return   bool
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		get_multi_choices: function() {

			var choices = this.get( 'choices' );

			if ( ! choices  ) {
				return false;
			}

			return this._get_choice_option( 'multi' );

		},

		/**
		 * Retrieve data from the type's "choices" attribute
		 * Allows quick handling of types with no choice definitions w/o additional checks
		 *
		 * @param    string   option  name of the choice option to retrieve
		 * @return   mixed
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		_get_choice_option: function( option ) {

			var choices = this.get( 'choices' );

			if ( ! choices || ! choices[ option ] ) {
				return false;
			}

			return choices[ option ];

		},

	} );

} );
