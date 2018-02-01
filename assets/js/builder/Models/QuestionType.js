/**
 * Quiz Question Type
 * @since    [version]
 * @version  [version]
 */
define( [], function() {

	return Backbone.Model.extend( {

		/**
		 * Get model default attributes
		 * @return   obj
		 * @since    [version]
		 * @version  [version]
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
		 * @return   array
		 * @since    [version]
		 * @version  [version]
		 */
		get_keywords: function() {

			var name = this.get( 'name' ),
				words = [ name ];

			return words.concat( this.get( 'keywords' ) ).concat( name.split( ' ' ) );

		},

		/**
		 * Get marker array for the question choices
		 * @return   array
		 * @since    [version]
		 * @version  [version]
		 */
		get_choice_markers: function() {

			return this._get_choice_option( 'markers' );

		},

		/**
		 * Determine if the question's choices are selectable
		 * @return   bool
		 * @since    [version]
		 * @version  [version]
		 */
		get_choice_selectable: function() {

			return this._get_choice_option( 'selectable' );

		},

		/**
		 * Get the choice type (text,image)
		 * @return   string
		 * @since    [version]
		 * @version  [version]
		 */
		get_choice_type: function() {

			return this._get_choice_option( 'type' );

		},

		/**
		 * Retrieve defined min. choices
		 * @return   int
		 * @since    [version]
		 * @version  [version]
		 */
		get_min_choices: function() {

			return this._get_choice_option( 'min' );

		},

		/**
		 * Get type-defined max choices
		 * @return   string
		 * @since    [version]
		 * @version  [version]
		 */
		get_max_choices: function() {

			return this._get_choice_option( 'max' );

		},

		/**
		 * Determine if multi-choice selection is enabled
		 * @return   bool
		 * @since    [version]
		 * @version  [version]
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
		 * @param    string   option  name of the choice option to retrieve
		 * @return   mixed
		 * @since    [version]
		 * @version  [version]
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
