/**
 * Forms
 *
 * @package LifterLMS/Scripts
 *
 * @since [version]
 * @version [version]
 */

LLMS.Forms = {

	/**
	 * Stores locale information.
	 *
	 * Added via PHP.
	 *
	 * @type {Object}
	 */
	locale: {},

	/**
	 * Stores references to the default locale strings
	 * as configured by users in the form editor.
	 *
	 * @type {Object}
	 */
	locale_defaults: {},

	/**
	 * jQuery ref. to the countries select field..
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
 	 * @since [version]
 	 *
 	 * @return {void}
	 */
	init: function() {

		if ( $( 'body' ).hasClass( 'wp-admin' ) ) {
			return;
		}

		var self = this;

		self.bind_matching_fields();
		self.bind_voucher_field();

		LLMS.wait_for( function() {
			return ( undefined !== $.fn.llmsSelect2 );
		}, function() {
			self.bind_l10n_selects();
		} );

	},

	/**
	 * Bind DOM Events fields with dynamic localization values and language.
	 *
	 * @since [version]
	 *
	 * @return {void}
	 */
	bind_l10n_selects: function() {

		var self = this;

		self.$countries = $( '.llms-l10n-country-select select' );
		self.$states    = $( '.llms-l10n-state-select select' );
		self.$zips      = $( '#llms_billing_zip' );

		if ( ! self.$countries.length ) {
			return;
		}

		if ( self.$states.length ) {
			self.prep_state_field();
		}

		self.$countries.add( self.$states ).llmsSelect2( { width: '100%' } );

		if ( window.llms.locale ) {
			self.locale = JSON.parse( window.llms.locale );
			self.locale_defaults = {
				state: (function(){
					var $label = self.get_field_parent( self.$states ).find( 'label' ).clone();
					$label.find( '*' ).remove();
					return $label.text().trim();
				})(),
			};
		}

		self.$countries.on( 'change', function() {

			var val = $( this ).val();
			self.update_state_options( val );
			self.update_locale_info( val );

		} ).trigger( 'change' );

	},

	/**
	 * Ensure "matching" fields match.
	 *
	 * @since [version]
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
	 * @since [version]
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
	 * Looks for a WP column wrapper and falls back to the field's
	 * wrapper div.
	 *
	 * @since [version]
	 *
	 * @see {Reference}
	 * @link {URL}
	 *
	 * @param {[type]} $field
	 * @return {[type]}
	 */
	get_field_parent: function( $field ) {

		var $block = $field.closest( '.wp-block-column' );
		if ( $block.length ) {
			return $block;
		}

		return $field.closest( '.llms-form-field' );

	},

	/**
	 * Prepares the state select field.
	 *
	 * Moves All optgroup elements into a hidden & disabled select element.
	 *
	 * @since [version]
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
	 * @since [version]
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
	 * @since [version]
	 *
	 * @param {String} country_code Currently selected country code.
	 * @return {void}
	 */
	update_locale_info: function( country_code ) {

		if ( ! this.locale || ! this.locale[ country_code ] ) {
			return;
		}

		var info = this.locale[ country_code ],
			state_text = info.state ? info.state : this.locale_defaults.state;

		this.update_label( this.$states, state_text );

		var $zips_parent = this.get_field_parent( this.$zips );
		this.$zips.removeAttr( 'disabled' );
		if ( info.zip ) {
			this.update_label( this.$zips, info.zip );
			$zips_parent.show();
		} else {
			this.$zips.attr( 'disabled', 'disabled' );
			$zips_parent.hide();
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
	 * @since [version]
	 *
	 * @see {Reference}
	 * @link {URL}
	 *
	 * @param {String} country_code Currently selected country code.
	 * @return {void}
	 */
	update_state_options: function( country_code ) {

		if ( ! this.$states.length ) {
			return;
		}

		var opts    = this.$holder.find( 'optgroup[data-key="' + country_code + '"] option' ).clone(),
			$parent = this.get_field_parent( this.$states );

		if ( ! opts.length ) {
			this.$states.html( '<option>&nbsp</option>' );
			this.$states.attr( 'disabled', 'disabled' );
			$parent.hide();
		} else {
			this.$states.html( opts );
			this.$states.removeAttr( 'disabled' );
			$parent.show();
		}

	},

};
