/**
 * Handle Password Strength Meter for registration and password update fields
 *
 * @package LifterLMS/Scripts
 *
 * @since 3.0.0
 * @version  3.7.0
 */

$.extend( LLMS.PasswordStrength, {

	$pass: $( '.llms-password' ),
	$conf: $( '.llms-password-confirm' ),
	$meter: $( '.llms-password-strength-meter' ),
	$form: null,

	/**
	 * Init
	 * loads class methods
	 *
	 * @since    3.0.0
	 * @version  3.7.0
	 */
	init: function() {

		if ( $( 'body' ).hasClass( 'wp-admin' ) ) {
			return;
		}

		if ( this.$meter.length ) {

			this.$form = this.$pass.closest( 'form' );

			// our asset enqueue is all screwed up and I'm too tired to fix it
			// so we're going to run this little dependency check
			// and wait for matchHeight to be available before binding
			var self    = this,
				counter = 0,
				interval;

			interval = setInterval( function() {

				// if we get to 30 seconds log an error message
				// and really who cares if the element heights aren't matched
				if ( counter >= 300 ) {

					console.log( 'cannot do password strength meter.' );

					// if we can't access ye, increment and wait...
				} else if ( 'undefined' === typeof wp && 'undefined' === typeof wp.passwordStrength ) {

					counter++;
					return;

					// bind the events, we're good!
				} else {

					self.bind();
					self.$form.trigger( 'llms-password-strength-ready' );

				}

				clearInterval( interval );

			}, 100 );

		}

	},

	/**
	 * Bind Method
	 * Handles dom binding on load
	 *
	 * @return void
	 * @since 3.0.0
	 */
	bind: function() {

		var self = this;

		// add submission event handlers when not on a checkout form
		if ( ! this.$form.hasClass( 'llms-checkout' ) ) {
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
	 * @return void
	 * @since 3.0.0
	 * @version 3.0.0
	 */
	check_strength: function() {

		var $pass_field = this.$pass.closest( '.llms-form-field' ),
			$conf_field = this.$conf.closest( '.llms-form-field' ),
			pass_length = this.$pass.val().length,
			conf_length = this.$conf.val().length;

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
	 * @param    obj       self      instance of this class
	 * @param    Function  callback  callback function, passes error message or success back to checkout handler
	 * @return   void
	 * @since    3.0.0
	 * @version  3.0.0
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
	 * We'll add a filter to this later so that developers can add their own blacklist to the default WP list
	 *
	 * @return array
	 * @since 3.0.0
	 */
	get_blacklist: function() {
		var blacklist = wp.passwordStrength.userInputBlacklist();
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
		if ( pass.length < 6 ) {
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
