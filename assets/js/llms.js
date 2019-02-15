/****************************************************************
 *
 * Contributor's Notice
 * 
 * This is a compiled file and should not be edited directly!
 * The uncompiled script is located in the "assets/private" directory
 * 
 ****************************************************************/

/**
 * Main LLMS Namespace
 *
 * @type     {Object}
 * @since    1.0.0
 * @version  3.24.3
 */
var LLMS = window.LLMS || {};
(function($){

	'use strict';

	/**
	 * load all app modules
	 */
	/* global LLMS, $ */
	/* jshint strict: false */
	
	/**
	 * Front End Achievements
	 * @type     {Object}
	 * @since    3.14.0
	 * @version  3.14.0
	 */
	LLMS.Achievements = {
	
		/**
		 * Init
		 * @return   void
		 * @since    3.14.0
		 * @version  3.14.0
		 */
		init: function() {
	
			var self = this;
	
			if ( $( '.llms-achievement' ) ) {
				$( document ).on( 'ready', function() {
					self.bind();
					self.maybe_open();
				} );
			}
	
		},
	
		/**
		 * Bind DOM events
		 * @return   void
		 * @since    3.14.0
		 * @version  3.14.0
		 */
		bind: function() {
	
			var self = this;
	
			$( '.llms-achievement' ).each( function() {
	
				self.create_modal( $( this ) );
	
			} );
	
			$( '.llms-achievement' ).on( 'click', function() {
	
				var $this = $( this ),
					id = 'achievement-' + $this.attr( 'data-id' ),
					$modal = $( '#' + id );
	
				if ( !$modal.length ) {
					self.create_modal( $this );
				}
	
				$modal.iziModal( 'open' );
	
			} );
	
		},
	
		/**
		 * Creates modal a modal for an achiemvement
		 * @param    obj   $el  jQuery selector for the modal card
		 * @return   void
		 * @since    3.14.0
		 * @version  3.14.0
		 */
		create_modal: function( $el ) {
	
			var id = 'achievement-' + $el.attr( 'data-id' ),
				$modal = $( '#' + id );
	
			if ( !$modal.length ) {
				$modal = $( '<div class="llms-achievement-modal" id="' + id + '" />' );
				$( 'body' ).append( $modal );
			}
	
			$modal.iziModal( {
				headerColor: '#3a3a3a',
				group: 'achievements',
				history: true,
				loop: true,
				overlayColor: 'rgba( 0, 0, 0, 0.6 )',
				transitionIn: 'fadeInDown',
				transitionOut: 'fadeOutDown',
				width: 340,
				onOpening: function( modal ) {
	
					modal.setTitle( $el.find( '.llms-achievement-title' ).html() );
					modal.setSubtitle( $el.find( '.llms-achievement-date' ).html() );
					modal.setContent( '<div class="llms-achievement">' + $el.html() + '</div>' );
	
				},
	
				onClosing: function() {
					window.history.pushState( '', document.title, window.location.pathname + window.location.search );
				},
	
			} );
	
		},
	
		/**
		 * On page load, opens a modal if the URL contains an achievement in the location hash
		 * @return   void
		 * @since    3.14.0
		 * @version  3.14.0
		 */
		maybe_open: function() {
	
			var hash = window.location.hash;
			if ( hash && -1 !== hash.indexOf( 'achievement-' ) ) {
				$( 'a[href="' + hash + '"]').first().trigger( 'click' );
			}
	
		}
	
	};
	
		/* global LLMS, $, wp_ajax_data */
	/* jshint strict: false */
	
	/**
	 * Main Ajax class
	 * Handles Primary Ajax connection
	 * @type {Object}
	 */
	LLMS.Ajax = {
	
		/**
		 * url
		 * @type {String}
		 */
		url: window.ajaxurl || window.llms.ajaxurl,
	
		/**
		 * type
		 * @type {[type]}
		 */
		type: 'post',
	
		/**
		 * data
		 * @type {[type]}
		 */
		data: [],
	
		/**
		 * cache
		 * @type {[type]}
		 */
		cache: false,
	
		/**
		 * dataType
		 * defaulted to json
		 * @type {String}
		 */
		dataType: 'json',
	
		/**
		 * async
		 * default to false
		 * @type {Boolean}
		 */
		async: true,
	
		response:[],
	
		/**
		 * initilize Ajax methods
		 * loads class methods
		 */
		init: function(obj) {
	
			//if obj is not of type object or null return false;
			if( obj === null || typeof obj !== 'object' ) {
				return false;
			}
	
			//set object defaults if values are not supplied
			obj.url			= this.url;
			obj.type 		= 'type' 		in obj ? obj.type 		: this.type;
			obj.data 		= 'data' 		in obj ? obj.data 		: this.data;
			obj.cache 		= 'cache' 		in obj ? obj.cache 		: this.cache;
			obj.dataType 	= 'dataType'	in obj ? obj.dataType 	: this.dataType;
			obj.async 		= 'async'		in obj ? obj.async 		: this.async;
	
			//add nonce to data object
			obj.data._ajax_nonce = wp_ajax_data.nonce;
	
			//add post id to data object
			var $R = LLMS.Rest,
			query_vars = $R.get_query_vars();
			obj.data.post_id = 'post' in query_vars ? query_vars.post : null;
			if ( !obj.data.post_id && $( 'input#post_ID' ).length ) {
				obj.data.post_id = $( 'input#post_ID' ).val();
			}
	
			return obj;
		},
	
		/**
		 * Call
		 * Called by external classes
		 * Sets up jQuery Ajax object
		 * @param  {[object]} [object of ajax settings]
		 * @return {[mixed]} [false if not object or this]
		 */
		call: function(obj) {
	
			//get default variables if not included in call
			var settings = this.init(obj);
	
			//if init return a response of false
			if (!settings) {
				return false;
			} else {
				this.request(settings);
			}
	
			return this;
	
		},
	
		/**
		 * Calls jQuery Ajax on settings object
		 * @return {[object]} [this]
		 */
		request: function(settings) {
	
			$.ajax(settings);
	
			return this;
	
		}
	
	};
	
		/* global LLMS */
	/* jshint strict: false */
	
	/**
	 * Create a Donut Chart
	 * @source   https://gist.github.com/joeyinbox/8205962
	 * @param    obj   $el  jQuery element to draw a chart within
	 * @since    3.9.0
	 * @version  3.9.0
	 */
	LLMS.Donut = function( $el ) {
	
		function Donut(options) {
	
			this.settings = $.extend( {
				element: options.element,
				percent: 100
			}, options );
	
			this.circle = this.settings.element.find( 'path' );
			this.settings.stroke_width = parseInt( this.circle.css( 'stroke-width' ) );
			this.radius = ( parseInt( this.settings.element.css( 'width' ) ) - this.settings.stroke_width ) / 2;
			this.angle = -97.5; // Origin of the draw at the top of the circle
			this.i = Math.round( 0.75 * this.settings.percent );
			this.first = true;
	
			this.animate = function() {
				this.timer = setInterval( this.loop.bind( this ), 10 );
			};
	
			this.loop = function() {
				this.angle += 5;
				this.angle %= 360;
				var radians = ( this.angle / 180 ) * Math.PI,
					x = this.radius + this.settings.stroke_width / 2 + Math.cos( radians ) * this.radius,
					y = this.radius + this.settings.stroke_width / 2 + Math.sin( radians ) * this.radius,
					d;
				if (this.first === true) {
					d = this.circle.attr( 'd' ) + ' M ' + x + ' ' + y;
					this.first = false;
				} else {
					d = this.circle.attr( 'd' ) + ' L ' + x + ' ' + y;
				}
				this.circle.attr( 'd', d );
				this.i--;
	
				if(this.i<=0) {
					clearInterval(this.timer);
				}
			};
		}
	
		function draw( $el ) {
			var path = '<path d="M100,100" />';
			$el.append( '<svg preserveAspectRatio="xMidYMid" xmlns:xlink="http://www.w3.org/1999/xlink">' + path + '</svg>' );
			var donut = new Donut( {
				element: $el,
				percent: $el.attr('data-perc')
			} );
			donut.animate();
		}
	
		draw( $el );
	
	};
	
		/* global LLMS, $ */
	/* jshint strict: false */
	/**
	 * Instructors List
	 */
	LLMS.Instructors = {
	
		/**
		 * init
		 */
		init: function() {
	
			var self = this;
	
			if ( $( 'body' ).hasClass( 'wp-admin' ) ) {
				return;
			}
	
			if ( $( '.llms-instructors' ).length ) {
	
				LLMS.wait_for_matchHeight( function() {
					self.bind();
				} );
	
			}
	
		},
	
		/**
		 * Bind Method
		 * Handles dom binding on load
		 * @return {[type]} [description]
		 */
		bind: function() {
	
			$( '.llms-instructors .llms-author' ).matchHeight();
	
		},
	
	};
	
		/* global LLMS */
	
	/**
	 * Localization functions for LifterLMS Javascript
	 *
	 * @todo  we need more robust translation functions to handle sprintf and pluralization
	 *        at this moment we don't need those and haven't stubbed them out
	 *        those will be added when they're needed
	 *
	 * @type Object
	 *
	 * @since  2.7.3
	 */
	LLMS.l10n = LLMS.l10n || {};
	
	LLMS.l10n.translate = function ( string ) {
	
		var self = this;
	
		if ( self.strings[string] ) {
	
			return self.strings[string];
	
		} else {
	
			return string;
	
		}
	
	};
	
	/**
	 * Translate and replace placeholders in a string
	 *
	 * @example LLMS.l10n.replace( 'This is a %2$s %1$s String', {
	 *           	'%1$s': 'cool',
	 *    			'%2$s': 'very'
	 *    		} );
	 *    		Output: "This is a very cool String"
	 *
	 * @param    string   string        text string
	 * @param    object   replacements  object containing token => replacement pairs
	 * @return   string
	 * @since    3.16.0
	 * @version  3.16.0
	 */
	LLMS.l10n.replace = function( string, replacements ) {
	
		var str = this.translate( string );
	
		$.each( replacements, function( token, value ) {
	
			if ( -1 !== token.indexOf( 's' ) ) {
				value = value.toString();
			} else if ( -1 !== token.indexOf( 'd' ) ) {
				value = value * 1;
			}
	
			str = str.replace( token, value );
	
		} );
	
		return str;
	
	};
	
		/* global LLMS, $ */
	
	/**
	 * Handle Lesson Preview Elements
	 * @since    3.0.0
	 * @version  3.16.12
	 */
	LLMS.LessonPreview = {
	
		/**
		 * jQuery object of all outlines present on the current screen
		 * @type obj
		 */
		$els: null,
	
		/**
		 * Initilize
		 * @return void
		 */
		init: function() {
	
			var self = this;
	
			this.$locked = $( 'a[href="#llms-lesson-locked"]' );
	
			if ( this.$locked.length ) {
	
				self.bind();
	
			}
	
			if ( $( '.llms-course-navigation' ).length ) {
	
				LLMS.wait_for_matchHeight( function() {
	
					self.match_height();
	
				} );
	
			}
	
		},
	
		/**
		 * Bind DOM events
		 * @return void
		 * @since    3.0.0
		 * @version  3.16.12
		 */
		bind: function() {
	
			var self = this;
	
			this.$locked.on( 'click', function() {
				return false;
			} );
	
			this.$locked.on( 'mouseenter', function() {
	
				var $tip = $( this ).find( '.llms-tooltip' );
				if ( !$tip.length ) {
					var msg = $( this ).attr( 'data-tooltip-msg' );
					if ( !msg ) {
						msg = LLMS.l10n.translate( 'You do not have permission to access this content' );
					}
					$tip = self.get_tooltip( msg );
					$( this ).append( $tip );
				}
				setTimeout( function() {
					$tip.addClass( 'show' );
				}, 10 );
	
			} );
	
			this.$locked.on( 'mouseleave', function() {
	
				var $tip = $( this ).find( '.llms-tooltip' );
				$tip.removeClass( 'show' );
	
			} );
	
		},
	
		/**
		 * Match the height of lesson preview items in course navigation blocks
		 * @return   void
		 * @since    3.0.0
		 * @version  3.0.0
		 */
		match_height: function() {
	
			$( '.llms-course-navigation .llms-lesson-link' ).matchHeight();
	
		},
	
		/**
		 * Get a tooltip element
		 * @param    string   msg   message to display inside the tooltip
		 * @return   obj
		 * @since    3.0.0
		 * @version  3.2.4
		 */
		get_tooltip: function( msg ) {
			var $el = $( '<div class="llms-tooltip" />' );
			$el.append( '<div class="llms-tooltip-content">' + msg + '</div>' );
			return $el;
		},
	
	};
	
		/* global LLMS, $ */
	
	/**
	 * LifterLMS Loops JS
	 * @since    3.0.0
	 * @version  [versino]
	 */
	LLMS.Loops = {
	
		/**
		 * Initilize
		 * @return void
		 */
		init: function() {
	
			var self = this;
	
			if ( $( '.llms-loop' ).length ) {
	
				LLMS.wait_for_matchHeight( function() {
	
					self.match_height();
	
				} );
	
			}
	
		},
	
		/**
		 * Match the height of .llms-loop-item
		 * @return   void
		 * @since    3.0.0
		 * @version  3.14.0
		 */
		match_height: function() {
	
			$( '.llms-loop-item .llms-loop-item-content' ).matchHeight();
			$( '.llms-achievement-loop-item .llms-achievement' ).matchHeight();
			$( '.llms-certificate-loop-item .llms-certificate' ).matchHeight();
	
		},
	
	};
	
		/* global LLMS, $ */
	
	/**
	 * Handle the Collpasible Syllabus Widget / Shortcode
	 */
	LLMS.OutlineCollapse = {
	
		/**
		 * jQuery object of all outlines present on the current screen
		 * @type obj
		 */
		$outlines: null,
	
		/**
		 * Initilize
		 * @return void
		 */
		init: function() {
	
			this.$outlines = $( '.llms-widget-syllabus--collapsible' );
	
			if ( this.$outlines.length ) {
	
				this.bind();
	
			}
	
		},
	
		/**
		 * Bind DOM events
		 * @return void
		 */
		bind: function() {
	
			var self = this;
	
			this.$outlines.each( function() {
	
				var $outline = $( this ),
					$headers = $outline.find( '.llms-section .section-header' );
	
				// bind header clicks
				$headers.on( 'click', function( e ) {
	
					e.preventDefault();
	
					var $toggle = $( this ),
						$section = $toggle.closest( '.llms-section' ),
						state = self.get_section_state( $section );
	
					switch( state ) {
	
						case 'closed':
							self.open_section( $section );
						break;
	
						case 'opened':
							self.close_section( $section );
						break;
	
					}
	
				} );
	
				// bind optional toggle "buttons"
				$outline.find( '.llms-collapse-toggle' ).on( 'click', function( e ) {
	
					e.preventDefault();
	
					var $btn = $( this ),
						action = $btn.attr( 'data-action' ),
						opposite_action = ( 'close' === action ) ? 'opened' : 'closed';
	
					$headers.each( function() {
	
						var $section = $( this ).closest( '.llms-section' ),
							state = self.get_section_state( $section );
	
						if ( opposite_action !== state ) {
							return true;
						}
	
						switch( state ) {
	
							case 'closed':
								self.close_section( $section );
							break;
	
							case 'opened':
								self.open_section( $section );
							break;
	
						}
	
						$( this ).trigger( 'click' );
	
					} );
	
				} );
	
			} );
	
		},
	
		/**
		 * Close an outline section
		 * @param  obj    $section   jQuery selector of a '.llms-section'
		 * @return void
		 */
		close_section: function( $section ) {
	
			$section.removeClass( 'llms-section--opened' ).addClass( 'llms-section--closed' );
	
		},
	
		/**
		 * Open an outline section
		 * @param  obj    $section   jQuery selector of a '.llms-section'
		 * @return void
		 */
		open_section: function( $section ) {
	
			$section.removeClass( 'llms-section--closed' ).addClass( 'llms-section--opened' );
	
		},
	
		/**
		 * Get the current state (open or closed) of an outline section
		 * @param  obj    $section   jQuery selector of a '.llms-section'
		 * @return string            'opened' or 'closed'
		 */
		get_section_state: function( $section ) {
	
			return $section.hasClass( 'llms-section--opened' ) ? 'opened' : 'closed';
	
		}
	
	};
	
		/* global LLMS, $, wp */
	/* jshint strict: false */
	
	/**
	 * Handle Password Strength Meter for registration and password update fields
	 * @since 3.0.0
	 * @version  3.7.0
	 */
	
	$.extend( LLMS.PasswordStrength, {
	
		$pass: $( '.llms-password' ),
		$conf: $( '.llms-password-confirm' ),
		$meter: $( '.llms-password-strength-meter' ),
		$form: null,
	
		/**
		 * init
		 * loads class methods
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
						self.$form.trigger( 'llms-password-strength-ready' );
	
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
	
			var self = this;
	
			// add submission event handlers when not on a checkout form
			if ( !this.$form.hasClass( 'llms-checkout' ) ) {
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
			if ( !pass_length && !conf_length ) {
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
		 * form submission action called during registration on checkout screen
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
		 * @return array
		 * @since 3.0.0
		 */
		get_blacklist: function() {
			var blacklist = wp.passwordStrength.userInputBlacklist();
			return blacklist;
		},
	
		/**
		 * Retrieve current strength as a number, a slug, or a translated text string
		 * @param    string   format  derifed return format [int|slug|text] defaults to int
		 * @return   mixed
		 * @since    3.0.0
		 * @version  3.0.0
		 */
		get_current_strength: function( format ) {
	
			format = format || 'int';
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
		 * @return   boolean
		 * @since    3.0.0
		 * @version  3.0.0
		 */
		get_current_strength_status: function() {
			var curr = this.get_current_strength(),
				min = this.get_strength_value( this.get_minimum_strength() );
			return ( 5 === curr ) ? false : ( curr >= min );
		},
	
		/**
		 * Get the slug associated with a strength value
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
	
		/* global LLMS, $ */
	/* jshint strict: false */
	/**
	 * Pricing Table UI
	 */
	LLMS.Pricing_Tables = {
	
		/**
		 * init
		 */
		init: function() {
	
			var self = this;
	
			if ( $( 'body' ).hasClass( 'wp-admin' ) ) {
				return;
			}
	
			if ( $( '.llms-access-plans' ).length ) {
	
				LLMS.wait_for_matchHeight( function() {
					self.bind();
				} );
	
				this.$locked = $( 'a[href="#llms-plan-locked"]' );
	
				if ( this.$locked.length ) {
	
					LLMS.wait_for_popover( function() {
						self.bind_locked();
					} );
	
				}
	
			}
	
		},
	
		/**
		 * Bind Method
		 * Handles dom binding on load
		 * @return {[type]} [description]
		 */
		bind: function() {
	
			$( '.llms-access-plan-content' ).matchHeight();
			$( '.llms-access-plan-pricing.trial' ).matchHeight();
	
		},
	
		/**
		 * Setup a popover for members-only restricted plans
		 * @return void
		 * @since    3.0.0
		 * @version  3.9.1
		 */
		bind_locked: function() {
	
			this.$locked.each( function() {
	
				$( this ).webuiPopover( {
					animation: 'pop',
					closeable: true,
					content: function( e ) {
						var $content = $( '<div class="llms-members-only-restrictions" />' );
						$content.append( e.$element.closest( '.llms-access-plan' ).find( '.llms-access-plan-restrictions ul' ).clone() );
						return $content;
					},
					placement: 'top',
					style: 'inverse',
					title: LLMS.l10n.translate( 'Members Only Pricing' ),
					width: '280px',
				} );
	
			} );
	
		},
	
	};
	
		/* global LLMS, $, jQuery */
	/* jshint strict: false */
	/*jshint -W020 */
	
	LLMS.Review = {
		/**
		 * init
		 * loads class methods
		 */
		init: function()
		{
			// console.log('Initializing Review ');
			this.bind();
		},
	
		/**
		 * This function binds actions to the appropriate hooks
		 */
		bind: function()
		{
			$('#llms_review_submit_button').click(function()
			{
				if ($('#review_title').val() !== '' && $('#review_text').val() !== '')
				{
					jQuery.ajax({
			            type : 'post',
			            dataType : 'json',
			            url : window.llms.ajaxurl,
			            data : {
			            	action : 'LLMSSubmitReview',
			                review_title: $('#review_title').val(),
			                review_text: $('#review_text').val(),
			                pageID : $('#post_ID').val()
			            },
			            success: function()
			            {
			                console.log('Review success');
			                $('#review_box').hide('swing');
			                $('#thank_you_box').show('swing');
			            },
			            error: function(jqXHR, textStatus, errorThrown )
			            {
			                console.log(jqXHR);
			                console.log(textStatus);
			                console.log(errorThrown);
			            },
			        });
				} else {
					if ($('#review_title').val() === '')
					{
						$('#review_title_error').show('swing');
					} else {
						$('#review_title_error').hide('swing');
					}
					if ($('#review_text').val() === '')
					{
						$('#review_text_error').show('swing');
					} else {
						$('#review_text_error').hide('swing');
					}
				}
			});
			if ( $('#_llms_display_reviews').attr('checked') ) {
				$('.llms-num-reviews-top').addClass('top');
				$('.llms-num-reviews-bottom').show();
	
			} else {
				$('.llms-num-reviews-bottom').hide();
			}
			$('#_llms_display_reviews').change(function() {
				if ( $('#_llms_display_reviews').attr('checked') ) {
					$('.llms-num-reviews-top').addClass('top');
					$('.llms-num-reviews-bottom').show();
				} else {
					$('.llms-num-reviews-top').removeClass('top');
					$('.llms-num-reviews-bottom').hide();
				}
			});
	
		},
	};
	
		/* global LLMS, $ */
	
	/**
	 * Add Spinners for AJAX events
	 * @since 3.0.0
	 * @version 3.0.0
	 */
	LLMS.Spinner = {
	
		/**
		 * Get an exiting spinner element or create a new one
		 * @param    obj      $el   jQuery selector of the parent element that should hold and be mased by a spinnner
		 * @param    string   size  size or the spinner [default|small]
		 *                          default is 40px
		 *                          small is 20px
		 * @return   obj
		 * @since 3.0.0
		 * @version 3.0.0
		 */
		get: function( $el, size ) {
	
			// look for an existing spinner
			var $spinner = $el.find( '.llms-spinning' ).first();
	
			// no spinner inside $el
			if ( !$spinner.length ) {
	
				size = ( size ) ? size : 'default';
	
				// create the spinner
				$spinner = $( '<div class="llms-spinning"><i class="llms-spinner ' + size + '"></i></div>' );
	
				// add it to the dom
				$el.append( $spinner );
	
			}
	
			// return it
			return $spinner;
	
		},
	
		/**
		 * Start spinner(s) inr=side a given element
		 * Creates them if they don't exist!
		 * @param   obj      $el   jQuery selector of the parent element that should hold and be mased by a spinnner
		 * @param   string   size  size or the spinner [default|small]
		 *                          default is 40px
		 *                          small is 20px
		 * @return  void
		 * @since   3.0.0
		 * @version 3.0.0
		 */
		start: function( $el, size ) {
	
			var self = this;
	
			$el.each( function() {
	
				self.get( $( this ), size ).show();
	
			} );
	
		},
	
		/**
		 * Stor spinners within an element
		 * @param   obj      $el   jQuery selector of the parent element that should hold and be mased by a spinnner
		 * @return  void
		 * @since   3.0.0
		 * @version 3.0.0
		 */
		stop: function( $el ) {
	
			var self = this;
	
			$el.each( function() {
	
				self.get( $( this ) ).hide();
	
			} );
	
		}
	
	};
	
		/* global LLMS, $ */
	
	/**
	 * Student Dashboard related JS
	 * @type  {Object}
	 * @since    3.7.0
	 * @version  3.10.0
	 */
	LLMS.StudentDashboard = {
	
		/**
		 * Slug for the current screen/endpoint
		 * @type  {String}
		 */
		screen: '',
	
		/**
		 * Will show the number of meters on the page
		 * Used to conditionally bind meter-related events only when meters
		 * actually exist
		 * @type  int
		 */
		meter_exists: 0,
	
		/**
		 * Init
		 * @return   void
		 * @since    3.7.0
		 * @version  3.10.0
		 */
		init: function() {
	
			if ( $( '.llms-student-dashboard' ).length ) {
	
				this.meter_exists = $( '.llms-password-strength-meter' ).length;
				this.bind();
	
				if ( 'orders' === this.get_screen() ) {
	
					this.bind_orders();
	
				}
	
			}
	
		},
	
		/**
		 * Bind DOM events
		 * @return   void
		 * @since    3.7.0
		 * @version  3.7.4
		 */
		bind: function() {
	
			var self = this,
				$toggle = $( '.llms-student-dashboard a[href="#llms-password-change-toggle"]' );
	
			// click event for the change password link
			$toggle.on( 'click', function( e ) {
	
				e.preventDefault();
	
				var $this = $( this ),
					curr_text = $this.text(),
					curr_action = $this.attr( 'data-action' ),
					new_action = 'hide' === curr_action ? 'show' : 'hide',
					new_text = $this.attr( 'data-text' );
	
				self.password_toggle( curr_action );
	
				// prevent accidental cancels when users tab out of the confirm password field
				// and expect to hit submit with enter key immediately after
				if ( 'show' === curr_action ) {
					$this.attr( 'tabindex', '-777' );
				} else {
					$this.removeAttr( 'tabindex' );
				}
	
				$this.attr( 'data-action', new_action ).attr( 'data-text', curr_text ).text( new_text );
	
			} );
	
			// this will remove the required by default without having to mess with
			// conditionals in PHP and still allows the required * to show in the label
	
			if ( this.meter_exists ) {
	
				$( '.llms-person-form.edit-account' ).on( 'llms-password-strength-ready', function() {
					self.password_toggle( 'hide' );
				} );
	
			} else {
	
				self.password_toggle( 'hide' );
	
			}
	
			$( '.llms-donut' ).each( function() {
				LLMS.Donut( $( this ) );
			} );
	
		},
	
		/**
		 * Bind events related to the orders screen on the dashboard
		 * @return   void
		 * @since    3.10.0
		 * @version  3.10.0
		 */
		bind_orders: function() {
	
			$( '#llms-cancel-subscription-form' ).on( 'submit', this.order_cancel_warning );
			$( '#llms_update_payment_method' ).on( 'click', function() {
				$( 'input[name="llms_payment_gateway"]:checked' ).trigger( 'change' );
				$( this ).closest( 'form' ).find( '.llms-switch-payment-source-main' ).slideToggle( '200' );
			} );
	
		},
	
		/**
		 * Get the current dashboard endpoint/tab slug
		 * @return   void
		 * @since    3.10.0
		 * @version  3.10.0
		 */
		get_screen: function() {
			if ( !this.screen ) {
				this.screen = $( '.llms-student-dashboard' ).attr( 'data-current' );
			}
			return this.screen;
		},
	
		/**
		 * Show a confirmation warning when Cancel Subscription form is submitted
		 * @param    obj   e  JS event data
		 * @return   void
		 * @since    3.10.0
		 * @version  3.10.0
		 */
		order_cancel_warning: function( e ) {
			e.preventDefault();
			var msg = LLMS.l10n.translate( 'Are you sure you want to cancel your subscription?' );
			if ( window.confirm( LLMS.l10n.translate( msg ) ) ) {
				$( this ).off( 'submit', this.order_cancel_warning );
				$( this ).submit();
			}
		},
	
		/**
		 * Toggle password related fields on the account edit page
		 * @param    string   action  [show|hide]
		 * @return   void
		 * @since    3.7.0
		 * @version  3.7.4
		 */
		password_toggle: function( action ) {
	
			if ( !action ) {
				action = 'show';
			}
	
			var self = this,
				$pwds = $( '#password, #password_confirm, #current_password' ),
				$form = $( '#password' ).closest( 'form' );
	
			// hide or show the fields
			$( '.llms-change-password' )[ action ]();
	
			if ( 'show' === action ) {
				// make passwords required
				$pwds.attr( 'required', 'required' );
	
				if ( self.meter_exists ) {
					// add the strength check on form submission
					$form.on( 'submit', LLMS.PasswordStrength, LLMS.PasswordStrength.submit );
				}
	
			} else {
				// remove requirement so form can be submitted while fields are hidden
				// and clear the password out of the fields if typing started
				$pwds.removeAttr( 'required' ).val( '' );
	
				if ( self.meter_exists ) {
	
					// remove the password strength submission check
					$form.off( 'submit', LLMS.PasswordStrength.submit );
					// clears the meter
					LLMS.PasswordStrength.check_strength();
	
				}
	
			}
	
		},
	
	};
	
		/*global LLMS */
	/* jshint strict: false */
	
	/**
	 * Rest Methods
	 * Manages URL and Rest object parsing
	 * @type {Object}
	 */
	LLMS.Rest = {
	
		/**
		 * init
		 * loads class methods
		 */
		init: function() {
			this.bind();
		},
	
		/**
		 * Bind Method
		 * Handles dom binding on load
		 * @return {[type]} [description]
		 */
		bind: function() {
		},
	
		/**
		 * Searches for string matches in url path
		 * @param  {Array}  strings [Array of strings to search for matches]
		 * @return {Boolean}         [Was a match found?]
		 */
		is_path: function( strings ) {
	
			var path_exists = false,
				url = window.location.href;
	
			for( var i = 0; i < strings.length; i++ ) {
	
				if ( url.search( strings[i] ) > 0 && !path_exists ) {
	
					path_exists = true;
				}
			}
	
			return path_exists;
		},
	
		/**
		 * Retrieves query variables
		 * @return {[Array]} [array object of query variable key=>value pairs]
		 */
		get_query_vars: function() {
	
		    var vars = [], hash,
		    	hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
	
		    for(var i = 0; i < hashes.length; i++) {
		        hash = hashes[i].split('=');
		        vars.push(hash[0]);
		        vars[hash[0]] = hash[1];
		    }
	
	    	return vars;
		}
	
	};
	

	/**
	 * Initializes all classes within the LLMS Namespace
	 * @return {[type]} [description]
	 */
	LLMS.init = function() {

		for (var func in LLMS) {

			if ( typeof LLMS[func] === 'object' && LLMS[func] !== null ) {

				if ( LLMS[func].init !== undefined ) {

					if ( typeof LLMS[func].init === 'function') {
						LLMS[func].init();
					}

				}

			}

		}

	};

	/**
	 * Determine if the current device is touch-enabled
	 *
	 * @see     https://stackoverflow.com/a/4819886/400568 [2018 Update]
	 * @return  {Boolean}
	 * @since   3.24.3
	 * @version 3.24.3
	 */
	LLMS.is_touch_device = function() {

		var prefixes = ' -webkit- -moz- -o- -ms- '.split(' ');
		var mq = function( query ) {
			return window.matchMedia( query ).matches;
		}

		if ( ( 'ontouchstart' in window ) || window.DocumentTouch && document instanceof DocumentTouch ) {
			return true;
		}

		// include the 'heartz' as a way to have a non matching MQ to help terminate the join
		// https://git.io/vznFH
		var query = ['(', prefixes.join('touch-enabled),('), 'heartz', ')'].join('');
		return mq( query );

	};

	/**
	 * Wait for matchHeight to load
	 * @param    {Function}  cb  callback function to run when matchheight is ready
	 * @return   void
	 * @since    3.0.0
	 * @version  3.16.6
	 */
	LLMS.wait_for_matchHeight = function( cb ) {
		this.wait_for( function() {
			return ( undefined !== $.fn.matchHeight );
		}, cb );
	}

	/**
	 * Wait for webuiPopover to load
	 * @param    {Function}  cb  callback function to run when matchheight is ready
	 * @return   void
	 * @since    3.9.1
	 * @version  3.16.6
	 */
	LLMS.wait_for_popover = function( cb ) {
		this.wait_for( function() {
			return ( undefined !== $.fn.webuiPopover );
		}, cb );
	}

	/**
	 * Wait for a dependency to load and then run a callback once it has
	 * Temporary fix for a less-than-optimal assets loading function on the PHP side of things
	 * @param    {Function}    test  a function that returns a truthy if the dependency is loaded
	 * @param    {Function}    cb    a callback function executed once the dependency is loaded
	 * @return   void
	 * @since    3.9.1
	 * @version  3.9.1
	 */
	LLMS.wait_for = function( test, cb ) {

		var counter = 0,
			interval;

		interval = setInterval( function() {

			// if we get to 30 seconds log an error message
			if ( counter >= 300 ) {

				console.log( 'could not load dependency' );

			// if we can't access ye, increment and wait...
			} else {

				// bind the events, we're good!
				if ( test() ) {

					cb();

				} else {

					console.log( 'waiting...' );
					counter++;
					return;

				}

			}

			clearInterval( interval );

		}, 100 );

	};

	LLMS.init($);


})(jQuery);
