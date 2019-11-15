/**
 * Handle Password Strength Meter for registration and password update fields
 *
 * @package LifterLMS/Scripts
 *
 * @since 3.0.0
 * @version [version]
 */

$.extend( LLMS.PasswordStrength, {

	/**
	 * jQuery ref for the password strength meter object.
	 *
	 * @type {Object}
	 */
	$meter: $( '.llms-password-strength-meter' ),

	/**
	 * jQuery ref for the password field.
	 *
	 * @type {Object}
	 */
	$pass: null,

	/**
	 * jQuery ref for the password confirmation field
	 *
	 * @type {Object}
	 */
	$conf: null,

	/**
	 * jQuery ref for form element.
	 *
	 * @type {Object}
	 */
	$form: null,

	/**
	 * Init
	 * loads class methods
	 *
	 * @since 3.0.0
	 * @since 3.7.0 Unknown
	 * @since [version] Move reference setup to `setup_references()`.
	 *              Use `LLMS.wait_for()` for dependency waiting.
	 *
	 * @return {Void}
	 */
	init: function() {

		if ( $( 'body' ).hasClass( 'wp-admin' ) ) {
			return;
		}

		if ( ! this.setup_references() ) {
			return;
		}

		var self = this;

		LLMS.wait_for( function() {
			return ( 'undefined' !== typeof wp && 'undefined' !== typeof wp.passwordStrength );
		}, function() {
			self.bind();
			self.$form.trigger( 'llms-password-strength-ready' );
		} );

	},

	/**
	 * Bind DOM Events
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	bind: function() {
		var i = 0;

		var self = this;

		// add submission event handlers when not on a checkout form
		if ( ! this.$form.hasClass( 'llms-checkout' ) ) {
			console.log( self );
			this.$form.on( 'submit', self, self.submit );
		}

		// check password strength on keyup
		self.$pass.add( self.$conf ).on( 'keyup', function() {
			self.check_strength();
		} );

	},

	/**
	 * Check the strength of a user entered password
	 * and update elements depending on the current strength
	 *
	 * @since 3.0.0
	 * @since [version] Allow password confirmation to be optional when checking strength.
	 *
	 * @return void
	 */
	check_strength: function() {

		var $pass_field = this.$pass.closest( '.llms-form-field' ),
			$conf_field = this.$conf.closest( '.llms-form-field' ),
			pass_length = this.$pass.val().length,
			conf_length = this.$conf.length ? this.$conf.val().length : 0;

		// hide the meter if both fields are empty
		if ( ! pass_length && ! conf_length ) {
			$pass_field.removeClass( 'valid invalid' );
			$conf_field.removeClass( 'valid invalid' );
			this.$meter.hide();
			return;
		}

		if ( this.get_current_strength_status() ) {
			$pass_field.removeClass( 'invalid' ).addClass( 'valid' );
			if ( conf_length ) {
				$conf_field.removeClass( 'invalid' ).addClass( 'valid' );
			}
		} else {
			$pass_field.removeClass( 'valid' ).addClass( 'invalid' );
			if ( conf_length ) {
				$conf_field.removeClass( 'valid' ).addClass( 'invalid' );
			}
		}

		this.$meter.removeClass( 'too-short very-weak weak medium strong mismatch' );
		this.$meter.show().addClass( this.get_current_strength( 'slug' ) );
		this.$meter.html( this.get_current_strength( 'text' ) );

	},

	/**
	 * Form submission action called during registration on checkout screen
	 *
	 * @since    3.0.0
	 *
	 * @param    obj       self      instance of this class
	 * @param    Function  callback  callback function, passes error message or success back to checkout handler
	 * @return   void
	 */
	checkout: function( self, callback ) {

		if ( self.get_current_strength_status() ) {

			callback( true );

		} else {

			callback( LLMS.l10n.translate( 'There is an issue with your chosen password.' ) );

		}
	},

	/**
	 * Get the list of blacklisted strings
	 *
	 * @since 3.0.0
	 * @since [version] Add blacklisted words as configured via the php filter and automatically add values from all text inputs in the current form.
	 *
	 * @return array
	 */
	get_blacklist: function() {

		// Default values from WP Core + any values added via settings filter..
		var blacklist = wp.passwordStrength.userInputBlacklist().concat( this.get_setting( 'blacklist', [] ) );

		// Add values from all text fields in the form.
		this.$form.find( 'input[type="text"], input[type="email"], input[type="tel"], input[type="number"]' ).each( function() {
			var val = $( this ).val();
			if ( val ) {
				blacklist.push( val );
			}
		} );

		return blacklist;

	},

	/**
	 * Retrieve current strength as a number, a slug, or a translated text string
	 *
	 * @param    string   format  derived return format [int|slug|text] defaults to int
	 * @return   mixed
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	get_current_strength: function( format ) {

		format   = format || 'int';
		var pass = this.$pass.val(),
			conf = this.$conf.val(),
			val;

		// enforce custom length requirement
		if ( pass.length < this.get_setting( 'min_length', 6 ) ) {
			val = -1;
		} else {
			val = wp.passwordStrength.meter( pass, this.get_blacklist(), conf );
			// 0 & 1 are both very-weak
			if ( 0 === val ) {
				val = 1;
			}
		}

		if ( 'slug' === format ) {
			return this.get_strength_slug( val );
		} else if ( 'text' === format ) {
			return this.get_strength_text( val );
		} else {
			return val;
		}
	},

	/**
	 * Determines if the current password strength meets the user-defined
	 * minimum password strength requirements
	 *
	 * @return   boolean
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	get_current_strength_status: function() {
		var curr = this.get_current_strength(),
			min  = this.get_strength_value( this.get_minimum_strength() );
		return ( 5 === curr ) ? false : ( curr >= min );
	},

	/**
	 * Retrieve the minimum password strength for the current form.
	 *
	 * @since 3.0.0
	 * @since [version] Replaces the version output via an inline PHP script in favor of utilizing values configured in the settings object.
	 *
	 * @return {string}
	 */
	get_minimum_strength: function() {
		return this.get_setting( 'min_strength', 'strong' );
	},

	/**
	 * Get a setting and fallback to a default value.
	 *
	 * @since [version]
	 *
	 * @param {String} key Setting key.
	 * @param {mixed} default_val Default value when the requested setting cannot be located.
	 * @return {mixed}
	 */
	get_setting: function( key, default_val ) {
		var settings = this.get_settings();
		return settings[ key ] ? settings[ key ] : default_val;
	},

	/**
	 * Get the slug associated with a strength value
	 *
	 * @param    int   strength_val  strength value number
	 * @return   string
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	get_strength_slug: function( strength_val ) {

		var slugs = {
			'-1': 'too-short',
			1: 'very-weak',
			2: 'weak',
			3: 'medium',
			4: 'strong',
			5: 'mismatch',
		};

		return ( slugs[ strength_val ] ) ? slugs[ strength_val ] : slugs[5];

	},

	/**
	 * Gets the translated text associated with a strength value
	 *
	 * @param    int  strength_val  strength value
	 * @return   string
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	get_strength_text: function( strength_val ) {

		var texts = {
			'-1': LLMS.l10n.translate( 'Too Short' ),
			1: LLMS.l10n.translate( 'Very Weak' ),
			2: LLMS.l10n.translate( 'Weak' ),
			3: LLMS.l10n.translate( 'Medium' ),
			4: LLMS.l10n.translate( 'Strong' ),
			5: LLMS.l10n.translate( 'Mismatch' ),
		};

		return ( texts[ strength_val ] ) ? texts[ strength_val ] : texts[5];

	},

	/**
	 * Get the value associated with a strength slug
	 *
	 * @param    string   strength_slug  a strength slug
	 * @return   int
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	get_strength_value: function( strength_slug ) {

		var values = {
			'too-short': -1,
			'very-weak': 1,
			weak: 2,
			medium: 3,
			strong: 4,
			mismatch: 5,
		};

		return ( values[ strength_slug ] ) ? values[ strength_slug ] : values.mismatch;

	},

	/**
	 * Setup jQuery references to DOM elements needed to power the password meter.
	 *
	 * @since [version]
	 *
	 * @return {Boolean} Returns `true` if a meter element and password field are found, otherwise returns `false`.
	 */
	setup_references: function() {

		if ( ! this.$meter.length ) {
			return false;
		}

		this.$form = this.$meter.closest( 'form' );
		this.$pass = this.$form.find( 'input#password' );

		if ( this.$pass.length && this.$pass.attr( 'data-match' ) ) {
			this.$conf = this.$form.find( '#' + this.$pass.attr( 'data-match' ) );
		}

		return ( this.$pass.length > 0 );

	},

	/**
	 * Form submission handler for registration and update forms
	 *
	 * @param    obj    e         event data
	 * @return   void
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	submit: function( e ) {

		var self = e.data;
		e.preventDefault();
		self.$pass.trigger( 'keyup' );

		if ( self.get_current_strength_status() ) {
			self.$form.off( 'submit', self.submit );
			self.$form.trigger( 'submit' );
		} else {
			$( 'html, body' ).animate( {
				scrollTop: self.$meter.offset().top - 100,
			}, 200 );
			self.$meter.hide();
			setTimeout( function() {
				self.$meter.fadeIn( 400 );
			}, 220 );
		}
	}

} );
