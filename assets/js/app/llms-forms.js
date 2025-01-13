/**
 * Forms
 *
 * @package LifterLMS/Scripts
 *
 * @since 5.0.0
 * @version 7.0.0
 */

LLMS.Forms = {

	/**
	 * Stores locale information.
	 *
	 * Added via PHP.
	 *
	 * @type {Object}
	 */
	address_info: {},

	/**
	 * jQuery ref. to the city text field.
	 *
	 * @type {Object}
	 */
	$cities: null,

	/**
	 * jQuery ref. to the countries select field.
	 *
	 * @type {Object}
	 */
	$countries: null,

	/**
	 * jQuery ref. to the states select field.
	 *
	 * @type {Object}
	 */
	$states: null,

	/**
	 * jQuery ref. to the hidden states holder field.
	 *
	 * @type {Object}
	 */
	$states_holder: null,

	/**
	 * Init
	 *
 	 * @since 5.0.0
 	 * @since 5.3.3 Move select2 dependency check into the `bind_l10_selects()` method.
 	 *
 	 * @return {void}
	 */
	init: function() {

		if ( $( 'body' ).hasClass( 'wp-admin' ) ) {
			if ( ! ( $( 'body' ).hasClass( 'profile-php' ) || $( 'body' ).hasClass( 'user-edit-php' ) ) ) {
				return;
			}
		}

		var self = this;

		self.bind_matching_fields();
		self.bind_voucher_field();
		self.bind_edit_account();
		self.bind_l10n_selects();

	},

	/**
	 * Bind DOM events for the edit account screen.
	 *
	 * @since 5.0.0
	 *
	 * @return {void}
	 */
	bind_edit_account: function() {

		// Not an edit account form.
		if ( ! $( 'form.llms-person-form.edit-account' ).length ) {
			return;
		}

		$( '.llms-toggle-fields' ).on( 'click', this.handle_toggle_click );

	},

	/**
	 * Bind DOM Events fields with dynamic localization values and language.
	 *
	 * @since 5.0.0
	 * @since 5.3.3 Bind select2-related events after ensuring select2 is available.
	 *
	 * @return {void}
	 */
	bind_l10n_selects: function() {

		var self = this;

		self.$cities    = $( '#llms_billing_city' );
		self.$countries = $( '.llms-l10n-country-select select' );
		self.$states    = $( '.llms-l10n-state-select select' );
		self.$zips      = $( '#llms_billing_zip' );

		if ( ! self.$countries.length ) {
			return;
		}

		var isSelect2Available = function() {
			return ( undefined !== $.fn.llmsSelect2 );
		};

		LLMS.wait_for( isSelect2Available, function() {

			if ( self.$states.length ) {
				self.prep_state_field();
			}

			self.$countries.add( self.$states ).llmsSelect2( { width: '100%' } );

			if ( window.llms.address_info ) {
				self.address_info = JSON.parse( window.llms.address_info );
			}

			self.$countries.on( 'change', function() {

				var val = $( this ).val();
				self.update_locale_info( val );

			} ).trigger( 'change' );

		}, 'llmsSelect2' );

	},

	/**
	 * Ensure "matching" fields match.
	 *
	 * @since 5.0.0
	 *
	 * @return {Void}
	 */
	bind_matching_fields: function() {

		var $fields = $( 'input[data-match]' ).not( '[type="password"]' );

		$fields.each( function() {

			var $field = $( this ),
				$match = $( '#' + $field.attr( 'data-match' ) ),
				$parents;

			if ( $match.length ) {

				$parents = $field.closest( '.llms-form-field' ).add( $match.closest( '.llms-form-field' ) );

				$field.on( 'input change', function() {

					var val_1 = $field.val(),
						val_2 = $match.val();

					if ( val_1 && val_2 && val_1 !== val_2 ) {
						$parents.addClass( 'invalid' );
					} else {
						$parents.removeClass( 'invalid' );
					}

				} );

			}

		} );

	},

	/**
	 * Bind DOM events for voucher toggles UX.
	 *
	 * @since 5.0.0
	 *
	 * @return {void}
	 */
	bind_voucher_field: function() {

		$( '#llms-voucher-toggle' ).on( 'click', function( e ) {
			e.preventDefault();
			$( '#llms_voucher' ).toggle();
		} );

	},

	/**
	 * Retrieve the parent element for a given field.
	 *
	 * The parent element is hidden when the field isn't required.
	 *
	 * @since 5.0.0
	 * @since 7.0.0 Do not look for a WP column wrapper anymore, always return the field's wrapper div.
	 *
	 * @param {Object} $field jQuery dom object.
	 * @return {Object}
	 */
	get_field_parent: function( $field ) {

		return $field.closest( '.llms-form-field' );

	},

	/**
	 * Retrieve the text of a label
	 *
	 * Removes any children HTML elements (eg: required span elements) and returns only the labels text.
	 *
	 * @since 5.0.0
	 *
	 * @param {Object} $label jQuery object for a label element.
	 * @return {String}
	 */
	get_label_text: function( $label ) {

		var $clone = $label.clone();
		$clone.find( '*' ).remove();
		return $clone.text().trim();

	},

	/**
	 * Callback function to handle the "toggle" button links for changing email address and password on account edit forms
	 *
	 * @since 5.0.0
	 *
	 * @param {Object} event Native JS event object.
	 * @return {void}
	 */
	handle_toggle_click: function( event ) {

		event.preventDefault();

		var $this       = $( this ),
			$fields     = $( $( this ).attr( 'data-fields' ) ),
			isShowing   = $this.attr( 'data-is-showing' ) || 'no',
			displayFunc = 'yes' === isShowing ? 'hide' : 'show',
			disabled    = 'yes' === isShowing ? 'disabled' : null,
			textAttr    = 'yes' === isShowing ? 'data-change-text' : 'data-cancel-text';

		$fields.each( function() {

			$( this ).closest( '.llms-form-field' )[ displayFunc ]();
			$( this ).attr( 'disabled', disabled );

		} );

		$this.text( $this.attr( textAttr ) );
		$this.attr( 'data-is-showing', 'yes' === isShowing ? 'no' : 'yes' );

	},

	/**
	 * Prepares the state select field.
	 *
	 * Moves All optgroup elements into a hidden & disabled select element.
	 *
	 * @since 5.0.0
	 *
	 * @return {void}
	 */
	prep_state_field: function() {

		var $parent = this.$states.closest( '.llms-form-field' );

		this.$holder = $( '<select disabled style="display:none !important;" />' );

		this.$holder.appendTo( $parent );
		this.$states.find( 'optgroup' ).appendTo( this.$holder );

	},

	/**
	 * Updates the text of a label for a given field.
	 *
	 * @since 5.0.0
	 *
	 * @param {Object} $field jQuery object of the form field.
	 * @param {String} text Label text.
	 * @return {void}
	 */
	update_label: function( $field, text ) {

		var $label = this.get_field_parent( $field ).find( 'label' ),
			$required = $label.find( '.llms-required' ).clone();

		$label.html( text );
		$label.append( $required );

	},

	/**
	 * Update form fields based on selected country
	 *
	 * Replaces label text with locale-specific language and
	 * hides or shows zip fields based on whether or not
	 * they are required for the given country.
	 *
	 * @since 5.0.0
	 *
	 * @param {String} country_code Currently selected country code.
	 * @return {void}
	 */
	update_locale_info: function( country_code ) {

		if ( ! this.address_info || ! this.address_info[ country_code ] ) {
			return;
		}

		var info = this.address_info[ country_code ];

		this.update_state_options( country_code );
		this.update_label( this.$states, info.state );

		this.update_locale_info_for_field( this.$cities, info.city );
		this.update_locale_info_for_field( this.$zips, info.postcode );

	},

	/**
	 * Update locale info for a given field.
	 *
	 * @since 5.0.0
	 *
	 * @param {Object}         $field The jQuery object for the field.
	 * @param {String|Boolean} label  The text of the label, or `false` when the field isn't supported.
	 * @return {Void}
	 */
	update_locale_info_for_field: function( $field, label ) {

		if ( label ) {
			this.update_label( $field, label );
			this.enable_field( $field );
		} else {
			this.disable_field( $field );
		}

	},

	/**
	 * Update the available options in the state field
	 *
	 * Removes existing options and copies the options
	 * for the requested country from the hidden select field.
	 *
	 * If there are no states for the given country the state
	 * field will be hidden.
	 *
	 * @since 5.0.0
	 *
	 * @param {String} country_code Currently selected country code.
	 * @return {void}
	 */
	update_state_options: function( country_code ) {

		if ( ! this.$states.length ) {
			return;
		}

		var opts = this.$holder.find( 'optgroup[data-key="' + country_code + '"] option' ).clone();

		if ( ! opts.length ) {
			this.$states.html( '<option>&nbsp</option>' );
			this.disable_field( this.$states );
		} else {
			this.enable_field( this.$states );
			this.$states.html( opts );
		}

	},

	/**
	 * Disable a given field
	 *
	 * It also hides the parent element, and adds an empty hidden input field
	 * with the same 'name' as teh being disabled field so to be sure to clear the field.
	 *
	 * @since 5.0.0
	 *
	 * @param {Object} $field The jQuery object for the field.
	 */
	disable_field: function( $field ) {
		$(
			'<input>',
			{ name: $field.attr('name'), class: $field.attr( 'class' ) + ' hidden', type: 'hidden' }
		).insertAfter( $field );
		$field.attr( 'disabled', 'disabled' );
		this.get_field_parent( $field ).hide();
	},

	/**
	 * Enable a given field
	 *
	 * It also shows the parent element, and removes the empty hidden input field
	 * previously added by disable_field().
	 *
	 * @since 5.0.0
	 *
	 * @param {Object} $field The jQuery object for the field.
	 */
	enable_field: function( $field ) {
		$field.removeAttr( 'disabled' );
		$field.next( '.hidden[name='+$field.attr('name')+']' ).detach();
		this.get_field_parent( $field ).show();
	}

};
