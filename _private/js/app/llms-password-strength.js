/* global LLMS, $, wp */
/* jshint strict: false */

/**
 * Handle Password Strength Meter for registration and password update fields
 * @since 3.0.0
 */

$.extend( LLMS.PasswordStrength, {

	/**
	 * init
	 * loads class methods
	 */
	init: function() {

		if ( $( 'body' ).hasClass( 'wp-admin' ) ) {
			return;
		}

		if ( $( '.llms-password-strength-meter' ).length ) {

			// our asset enqueue is all screwed up and I'm too tired to fix it
			// so we're going to run this little dependency check
			// and wait for matchHeight to be available before binding
			var self = this,
				counter = 0,
				interval;

			interval = setInterval( function() {

				// if we get to 30 seconds log an error message
				// and really who cares if the element heights aren't matched
				if ( counter >= 300 ) {

					console.log( 'cannot do password strength meter.');

				// if we can't access ye, increment and wait...
				} else if ( 'undefined' === typeof wp && 'undefined' === typeof wp.passwordStrength ) {

					counter++;
					return;

				// bind the events, we're good!
				} else {

					self.bind();

				}

				clearInterval( interval );

			}, 100 );

		}

	},

	/**
	 * Bind Method
	 * Handles dom binding on load
	 * @return void
	 * @since 3.0.0
	 */
	bind: function() {

		var self = this,
			$pass = $( '.llms-password' ),
			$conf = $( '.llms-password-confirm' ),
			$meter = $( '.llms-password-strength-meter' );

		// determine if password meets minimum strength before submitting
		$pass.closest( 'form' ).on( 'submit', function( e ) {
			e.preventDefault();

			var options = [ 'too-short', 'mismatch', 'very-weak', 'weak', 'medium', 'strong' ],
				strength = options.indexOf( self.check_strength( $pass, $conf, $meter ) ),
				min = options.indexOf( self.get_minimum_strength() );

			// if the password is good, submit the form
			if ( strength >= min ) {
				$( this ).off( 'submit' );
				$( this ).submit();
			} else {
			// otherwise scroll to the meter and flash it
				$( 'html, body' ).animate( {
					scrollTop: $meter.offset().top - 100,
				}, 200 );
				$meter.hide();
				setTimeout( function() {
					$meter.fadeIn( 400 );
				}, 220 );
			}

		} );

		// check password strength on keyup
		$pass.add( $conf ).on( 'keyup', function() {
			self.check_strength( $pass, $conf, $meter );
		} );

	},

	/**
	 * Check the strength of a user entered password
	 * @param  obj   $pass   jQuery Selector for the password input
	 * @param  obj   $conf   jQuery Selector for the password confirm input
	 * @param  obj   $meter  jQuery Selector for the meter element
	 * @return string        string describing either the strength or error of the password
	 * @since 3.0.0
	 */
	check_strength: function( $pass, $conf, $meter ) {

		var pass = $pass.val(),
			conf = $conf.val(),
			strength = wp.passwordStrength.meter( pass, this.get_blacklist(), conf ),
			css_class = '',
			text = '';

		if ( !pass.length && !conf.length ) {
			$meter.hide();
			return;
		}

		if ( pass.length < 6 ) {
			strength = -1;
		}

		switch ( strength ) {

			case -1:
				css_class = 'too-short';
				text = 'Too Short';
			break;

			case 0:
			case 1:
				css_class = 'very-weak';
				text = 'Very Weak';
			break;

			case 2:
				css_class = 'weak';
				text = 'Weak';
			break;

			case 3:
				css_class = 'medium';
				text = 'Medium';
			break;

			case 4:
				css_class = 'strong';
				text = 'Strong';
			break;

			case 5:
				css_class = 'mismatch';
				text = 'Mismatch';
			break;

		}

		$meter.removeClass( 'too-short very-weak weak medium strong mismatch' );
		$meter.show().addClass( css_class );
		$meter.html( LLMS.l10n.translate( text ) );

		return css_class;

	},

	/**
	 * Get the list of blacklisted strings
	 * We'll add a filter to this later so that developers can add their own blacklist to the default WP list
	 * @return array
	 * @since 3.0.0
	 */
	get_blacklist: function() {
		var blacklist = wp.passwordStrength.userInputBlacklist();
		return blacklist;
	}

} );
