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
 * @since 1.0.0
 * @version 5.3.3
 */

var LLMS = window.LLMS || {};
( function( $ ){

	'use strict';

	/**
	 * Load all app modules
	 */
	/**
	 * Front End Achievements
	 *
	 * @package LifterLMS/Scripts
	 *
	 * @since 3.14.0
	 * @version 6.10.2
	 */
	
	LLMS.Achievements = {
	
		/**
		 * Init
		 *
		 * @since 3.14.0
		 * @since 4.5.1 Fix conditional loading check.
		 *
		 * @return {void}
		 */
		init: function() {
	
			if ( $( '.llms-achievement' ).length ) {
	
				var self = this;
	
				$( function() {
					self.bind();
					self.maybe_open();
				} );
			}
	
		},
	
		/**
		 * Bind DOM events
		 *
		 * @since 3.14.0
		 *
		 * @return {void}
		 */
		bind: function() {
	
			var self = this;
	
			$( '.llms-achievement' ).each( function() {
	
				self.create_modal( $( this ) );
	
			} );
	
			$( '.llms-achievement' ).on( 'click', function() {
	
				var $this  = $( this ),
					id     = 'achievement-' + $this.attr( 'data-id' ),
					$modal = $( '#' + id );
	
				if ( ! $modal.length ) {
					self.create_modal( $this );
				}
	
				$modal.iziModal( 'open' );
	
			} );
	
		},
	
		/**
		 * Creates modal a modal for an achievement
		 *
		 * @since 3.14.0
		 *
		 * @param obj $el The jQuery selector for the modal card.
		 * @return {void}
		 */
		create_modal: function( $el ) {
	
			var id     = 'achievement-' + $el.attr( 'data-id' ),
				$modal = $( '#' + id );
	
			if ( ! $modal.length ) {
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
		 *
		 * @since 3.14.0
		 * @since 6.10.2 Sanitize achievement IDs before using window.location.hash to trigger the modal open.
		 *
		 * @return {void}
		 */
		maybe_open: function() {
	
			let hash = window.location.hash.split( '-' );
			if ( 2 !== hash.length ) {
				return;
			}
	
			hash[1] = parseInt( hash[1] );
			if ( '#achievement-' !== hash[0] || ! Number.isInteger( hash[1] ) ) {
				return;
			}
	
			const a = document.querySelector( `a[href="${ hash.join( '-' ) }"]` )
			if ( ! a ) {
				return;
			}
	
			a.click();
	
		}
	
	};
	
		/**
	 * Main Ajax class
	 * Handles Primary Ajax connection
	 *
	 * @package LifterLMS/Scripts
	 *
	 * @since Unknown
	 * @version Unknown
	 */
	
	LLMS.Ajax = {
	
		/**
		 * Url
		 *
		 * @type {String}
		 */
		url: window.ajaxurl || window.llms.ajaxurl,
	
		/**
		 * Type
		 *
		 * @type {[type]}
		 */
		type: 'post',
	
		/**
		 * Data
		 *
		 * @type {[type]}
		 */
		data: [],
	
		/**
		 * Cache
		 *
		 * @type {[type]}
		 */
		cache: false,
	
		/**
		 * DataType
		 * defaulted to json
		 *
		 * @type {String}
		 */
		dataType: 'json',
	
		/**
		 * Async
		 * default to false
		 *
		 * @type {Boolean}
		 */
		async: true,
	
		response:[],
	
		/**
		 * Initialize Ajax methods
		 *
		 * @since Unknown
		 * @since 4.4.0 Update ajax nonce source.
		 *
		 * @param {Object} obj Options object.
		 * @return {Object}
		 */
		init: function( obj ) {
	
			// If obj is not of type object or null return false.
			if ( obj === null || typeof obj !== 'object' ) {
				return false;
			}
	
			// set object defaults if values are not supplied
			obj.url      = 'url'         in obj ? obj.url : this.url;
			obj.type     = 'type' 		in obj ? obj.type : this.type;
			obj.data     = 'data' 		in obj ? obj.data : this.data;
			obj.cache    = 'cache' 		in obj ? obj.cache : this.cache;
			obj.dataType = 'dataType'	in obj ? obj.dataType : this.dataType;
			obj.async    = 'async'		in obj ? obj.async : this.async;
	
			// Add nonce to data object.
			obj.data._ajax_nonce = window.llms.ajax_nonce || wp_ajax_data.nonce;
	
			// Add post id to data object.
			var $R           = LLMS.Rest,
			query_vars       = $R.get_query_vars();
			obj.data.post_id = 'post' in query_vars ? query_vars.post : null;
			if ( ! obj.data.post_id && $( 'input#post_ID' ).length ) {
				obj.data.post_id = $( 'input#post_ID' ).val();
			}
	
			return obj;
		},
	
		/**
		 * Call
		 * Called by external classes
		 * Sets up jQuery Ajax object
		 *
		 * @param  {[object]} [object of ajax settings]
		 * @return {[mixed]} [false if not object or this]
		 */
		call: function(obj) {
	
			// get default variables if not included in call
			var settings = this.init( obj );
	
			// if init return a response of false
			if ( ! settings) {
				return false;
			} else {
				this.request( settings );
			}
	
			return this;
	
		},
	
		/**
		 * Calls jQuery Ajax on settings object
		 *
		 * @return {[object]} [this]
		 */
		request: function(settings) {
	
			$.ajax( settings );
	
			return this;
	
		}
	
	};
	
		/**
	 * Create a Donut Chart
	 *
	 * @package LifterLMS/Scripts
	 *
	 * @since 3.9.0
	 * @version 4.15.0
	 *
	 * @link https://gist.github.com/joeyinbox/8205962
	 *
	 * @param {Object} $el jQuery element to draw a chart within.
	 */
	
	LLMS.Donut = function( $el ) {
	
		/**
		 * Constructor
		 *
		 * @since 3.9.0
		 * @since 4.15.0 Flip animation in RTL.
		 *
		 * @param {Object} options Donut options.
		 * @return {Void}
		 */
		function Donut(options) {
	
			this.settings = $.extend( {
				element: options.element,
				percent: 100
			}, options );
	
			this.circle                = this.settings.element.find( 'path' );
			this.settings.stroke_width = parseInt( this.circle.css( 'stroke-width' ) );
			this.radius                = ( parseInt( this.settings.element.css( 'width' ) ) - this.settings.stroke_width ) / 2;
			this.angle                 = $( 'body' ).hasClass( 'rtl' ) ? 82.5 : 97.5; // Origin of the draw at the top of the circle
			this.i                     = Math.round( 0.75 * this.settings.percent );
			this.first                 = true;
			this.increment             = $( 'body' ).hasClass( 'rtl' ) ? -5 : 5;
	
			this.animate = function() {
				this.timer = setInterval( this.loop.bind( this ), 10 );
			};
	
			this.loop = function() {
				this.angle += this.increment;
				this.angle %= 360;
				var radians = ( this.angle / 180 ) * Math.PI,
					x       = this.radius + this.settings.stroke_width / 2 + Math.cos( radians ) * this.radius,
					y       = this.radius + this.settings.stroke_width / 2 + Math.sin( radians ) * this.radius,
					d;
				if (this.first === true) {
					d          = this.circle.attr( 'd' ) + ' M ' + x + ' ' + y;
					this.first = false;
				} else {
					d = this.circle.attr( 'd' ) + ' L ' + x + ' ' + y;
				}
				this.circle.attr( 'd', d );
				this.i--;
	
				if (this.i <= 0) {
					clearInterval( this.timer );
				}
			};
		}
	
		/**
		 * Draw donut element
		 *
		 * @since 3.9.0
		 *
		 * @param {Object} $el jQuery element to draw a chart within.
		 * @return {Void}
		 */
		function draw( $el ) {
			var path = '<path d="M100,100" />';
			$el.append( '<svg preserveAspectRatio="xMidYMid" xmlns:xlink="http://www.w3.org/1999/xlink">' + path + '</svg>' );
			var donut = new Donut( {
				element: $el,
				percent: $el.attr( 'data-perc' )
			} );
			donut.animate();
		}
	
		draw( $el );
	
	};
	
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
	
		/**
	 * Instructors List
	 *
	 * @package LifterLMS/Scripts
	 *
	 * @since  Unknown
	 * @version  Unknown
	 */
	
	LLMS.Instructors = {
	
		/**
		 * Init
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
		 *
		 * @return {[type]} [description]
		 */
		bind: function() {
	
			$( '.llms-instructors .llms-author' ).matchHeight();
	
		},
	
	};
	
		/**
	 * Localization functions for LifterLMS Javascript
	 *
	 * @package LifterLMS/Scripts
	 *
	 * @since  2.7.3
	 * @version  2.7.3
	 *
	 * @todo  we need more robust translation functions to handle sprintf and pluralization
	 *        at this moment we don't need those and haven't stubbed them out
	 *        those will be added when they're needed
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
	
		/**
	 * Handle Lesson Preview Elements
	 *
	 * @package LifterLMS/Scripts
	 *
	 * @since    3.0.0
	 * @version  3.16.12
	 */
	
	LLMS.LessonPreview = {
	
		/**
		 * A jQuery object of all outlines present on the current screen
		 *
		 * @type obj
		 */
		$els: null,
	
		/**
		 * Initialize
		 *
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
		 *
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
				if ( ! $tip.length ) {
					var msg = $( this ).attr( 'data-tooltip-msg' );
					if ( ! msg ) {
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
		 *
		 * @return   void
		 * @since    3.0.0
		 * @version  3.0.0
		 */
		match_height: function() {
	
			$( '.llms-course-navigation .llms-lesson-link' ).matchHeight();
	
		},
	
		/**
		 * Get a tooltip element
		 *
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
	
		/**
	 * LifterLMS Loops JS
	 *
	 * @package LifterLMS/Scripts
	 *
	 * @since    3.0.0
	 * @version  3.14.0
	 */
	
	LLMS.Loops = {
	
		/**
		 * Initialize
		 *
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
		 *
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
	
		/**
	 * Handle the Collapsible Syllabus Widget / Shortcode
	 *
	 * @package LifterLMS/Scripts
	 *
	 * @since Unknown
	 * @version Unknown
	 */
	
	LLMS.OutlineCollapse = {
	
		/**
		 * A jQuery object of all outlines present on the current screen
		 *
		 * @type obj
		 */
		$outlines: null,
	
		/**
		 * Initialize
		 *
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
		 *
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
	
					var $toggle  = $( this ),
						$section = $toggle.closest( '.llms-section' ),
						state    = self.get_section_state( $section );
	
					switch ( state ) {
	
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
	
					var $btn            = $( this ),
						action          = $btn.attr( 'data-action' ),
						opposite_action = ( 'close' === action ) ? 'opened' : 'closed';
	
					$headers.each( function() {
	
						var $section = $( this ).closest( '.llms-section' ),
							state    = self.get_section_state( $section );
	
						if ( opposite_action !== state ) {
							return true;
						}
	
						switch ( state ) {
	
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
		 *
		 * @param  obj    $section   jQuery selector of a '.llms-section'
		 * @return void
		 */
		close_section: function( $section ) {
	
			$section.removeClass( 'llms-section--opened' ).addClass( 'llms-section--closed' );
	
		},
	
		/**
		 * Open an outline section
		 *
		 * @param  obj    $section   jQuery selector of a '.llms-section'
		 * @return void
		 */
		open_section: function( $section ) {
	
			$section.removeClass( 'llms-section--closed' ).addClass( 'llms-section--opened' );
	
		},
	
		/**
		 * Get the current state (open or closed) of an outline section
		 *
		 * @param  obj    $section   jQuery selector of a '.llms-section'
		 * @return string            'opened' or 'closed'
		 */
		get_section_state: function( $section ) {
	
			return $section.hasClass( 'llms-section--opened' ) ? 'opened' : 'closed';
	
		}
	
	};
	
		/**
	 * Handle Password Strength Meter for registration and password update fields
	 *
	 * @package LifterLMS/Scripts
	 *
	 * @since 3.0.0
	 * @version 5.0.0
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
		 * @since 5.0.0 Move reference setup to `setup_references()`.
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
	
			var self = this;
	
			// add submission event handlers when not on a checkout form
			if ( ! this.$form.hasClass( 'llms-checkout' ) ) {
				self.$form.on( 'submit', self, self.submit );
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
		 * @since 5.0.0 Allow password confirmation to be optional when checking strength.
		 *
		 * @return void
		 */
		check_strength: function() {
	
			var $pass_field = this.$pass.closest( '.llms-form-field' ),
				$conf_field = this.$conf && this.$conf.length ? this.$conf.closest( '.llms-form-field' ) : null,
				pass_length = this.$pass.val().length,
				conf_length = this.$conf && this.$conf.length ? this.$conf.val().length : 0;
	
			// hide the meter if both fields are empty
			if ( ! pass_length && ! conf_length ) {
				$pass_field.removeClass( 'valid invalid' );
				if ( $conf_field ) {
					$conf_field.removeClass( 'valid invalid' );
				}
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
		 * Get the list of blocklisted strings
		 *
		 * @since 5.0.0
		 *
		 * @return array
		 */
		get_blocklist: function() {
	
			// Default values from WP Core + any values added via settings filter..
			var blocklist = wp.passwordStrength.userInputDisallowedList().concat( this.get_setting( 'blocklist', [] ) );
	
			// Add values from all text fields in the form.
			this.$form.find( 'input[type="text"], input[type="email"], input[type="tel"], input[type="number"]' ).each( function() {
				var val = $( this ).val();
				if ( val ) {
					blocklist.push( val );
				}
			} );
	
			return blocklist;
	
		},
	
		/**
		 * Retrieve current strength as a number, a slug, or a translated text string
		 *
		 * @since 3.0.0
		 * @since 5.0.0 Allow password confirmation to be optional when checking strength.
		 *
		 * @param {String} format Derived return format [int|slug|text] defaults to int.
		 * @return mixed
		 */
		get_current_strength: function( format ) {
	
			format   = format || 'int';
			var pass = this.$pass.val(),
				conf = this.$conf && this.$conf.length ? this.$conf.val() : '',
				val;
	
			// enforce custom length requirement
			if ( pass.length < this.get_setting( 'min_length', 6 ) ) {
				val = -1;
			} else {
				val = wp.passwordStrength.meter( pass, this.get_blocklist(), conf );
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
		 * @since 3.0.0
		 *
		 * @return   boolean
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
		 * @since 5.0.0 Replaces the version output via an inline PHP script in favor of utilizing values configured in the settings object.
		 *
		 * @return {string}
		 */
		get_minimum_strength: function() {
			return this.get_setting( 'min_strength', 'strong' );
		},
	
		/**
		 * Get a setting and fallback to a default value.
		 *
		 * @since 5.0.0
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
		 * @since  3.0.0
		 *
		 * @param int strength_val Strength value number.
		 * @return string
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
		 * @since  3.0.0
		 *
		 * @param {Integer} strength_val Strength value
		 * @return {String}
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
		 * @since 3.0.0
		 *
		 * @param string strength_slug A strength slug.
		 * @return {Integer}
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
		 * @since 5.0.0
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
		 * @since 3.0.0
		 * @since 5.0.0 Allow the account edit for to bypass strength checking when the password field is disabled (not being submitted).
		 *
		 * @param obj e Event data.
		 * @return void
		 */
		submit: function( e ) {
	
			var self = e.data;
			e.preventDefault();
			self.$pass.trigger( 'keyup' );
	
			// Meets the status requirements OR we're on the account edit form and the password field is disabled.
			if ( self.get_current_strength_status() || ( self.$form.hasClass( 'edit-account' ) && 'disabled' === self.$pass.attr( 'disabled' ) ) ) {
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
		},
	
		/**
		 * Get the list of blocklist strings
		 *
		 * @since 3.0.0
		 * @deprecated 5.0.0 `LLMS.PasswordStrength.get_blacklist()` is deprecated in favor of `LLMS.PasswordStrength.get_blocklist()`.
		 *
		 * @return array
		 */
		get_blacklist: function() {
			console.log( 'Method `get_blacklist()` is deprecated in favor of `get_blocklist()`.' );
			return this.get_blacklist();
		},
	
	} );
	
		/**
	 * Pricing Table UI
	 *
	 * @package LifterLMS/Scripts
	 *
	 * @since  Unknown.
	 * @version  Unknown.
	 */
	
	LLMS.Pricing_Tables = {
	
		/**
		 * Init
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
		 *
		 * @return {[type]} [description]
		 */
		bind: function() {
	
			$( '.llms-access-plan-content' ).matchHeight();
			$( '.llms-access-plan-pricing.trial' ).matchHeight();
	
		},
	
		/**
		 * Setup a popover for members-only restricted plans
		 *
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
	
		/**
	 * Quiz Attempt
	 *
	 * @package LifterLMS/Scripts
	 *
	 * @since 7.3.0
	 * @version 7.3.0
	 */
	
	LLMS.Quiz_Attempt = {
		/**
		 * Initialize
		 *
		 * @return void
		 */
		init: function() {
	
			$( '.llms-quiz-attempt-question-header a.toggle-answer' ).on( 'click', function( e ) {
	
				e.preventDefault();
	
				var $curr = $( this ).closest( 'header' ).next( '.llms-quiz-attempt-question-main' );
	
				$( this ).closest( 'li' ).siblings().find( '.llms-quiz-attempt-question-main' ).slideUp( 200 );
	
				if ( $curr.is( ':visible' ) ) {
					$curr.slideUp( 200 );
				}  else {
					$curr.slideDown( 200 );
				}
	
			} );
		}
	
	}
	
		/**
	 * LifterLMS Reviews JS
	 *
	 * @package LifterLMS/Scripts
	 *
	 * @since Unknown
	 * @version Unknown
	 */
	
	LLMS.Review = {
		/**
		 * Init
		 * loads class methods
		 */
		init: function() {
			// console.log('Initializing Review ');
			this.bind();
		},
	
		/**
		 * This function binds actions to the appropriate hooks
		 */
		bind: function() {
			$( '#llms_review_submit_button' ).click(function()
				{
				if ($( '#review_title' ).val() !== '' && $( '#review_text' ).val() !== '') {
					jQuery.ajax({
						type : 'post',
						dataType : 'json',
						url : window.llms.ajaxurl,
						data : {
							action : 'LLMSSubmitReview',
							review_title: $( '#review_title' ).val(),
							review_text: $( '#review_text' ).val(),
							pageID : $( '#post_ID' ).val(),
							llms_review_nonce: $( '#llms_review_nonce' ).val()
						},
						success: function()
						{
							console.log( 'Review success' );
							$( '#review_box' ).hide( 'swing' );
							$( '#thank_you_box' ).show( 'swing' );
						},
						error: function(jqXHR, textStatus, errorThrown )
						{
							console.log( jqXHR );
							console.log( textStatus );
							console.log( errorThrown );
						},
					});
				} else {
					if ($( '#review_title' ).val() === '') {
						$( '#review_title_error' ).show( 'swing' );
					} else {
						$( '#review_title_error' ).hide( 'swing' );
					}
					if ($( '#review_text' ).val() === '') {
						$( '#review_text_error' ).show( 'swing' );
					} else {
						$( '#review_text_error' ).hide( 'swing' );
					}
				}
			});
			if ( $( '#_llms_display_reviews' ).attr( 'checked' ) ) {
				$( '.llms-num-reviews-top' ).addClass( 'top' );
				$( '.llms-num-reviews-bottom' ).show();
	
			} else {
				$( '.llms-num-reviews-bottom' ).hide();
			}
			$( '#_llms_display_reviews' ).change(function() {
				if ( $( '#_llms_display_reviews' ).attr( 'checked' ) ) {
					$( '.llms-num-reviews-top' ).addClass( 'top' );
					$( '.llms-num-reviews-bottom' ).show();
				} else {
					$( '.llms-num-reviews-top' ).removeClass( 'top' );
					$( '.llms-num-reviews-bottom' ).hide();
				}
			});
	
		},
	};
	
		/* global LLMS, $ */
	
	/*!
	 * JavaScript Cookie v2.2.1
	 * https://github.com/js-cookie/js-cookie
	 *
	 * Copyright 2006, 2015 Klaus Hartl & Fagner Brack
	 * Released under the MIT license
	 */
	;(function (factory) {
		var registeredInModuleLoader;
		if (typeof define === 'function' && define.amd) {
			define(factory);
			registeredInModuleLoader = true;
		}
		if (typeof exports === 'object') {
			module.exports = factory();
			registeredInModuleLoader = true;
		}
		if (!registeredInModuleLoader) {
			var OldCookies = window.Cookies;
			var api = window.Cookies = factory();
			api.noConflict = function () {
				window.Cookies = OldCookies;
				return api;
			};
		}
	}(function () {
		function extend () {
			var i = 0;
			var result = {};
			for (; i < arguments.length; i++) {
				var attributes = arguments[ i ];
				for (var key in attributes) {
					result[key] = attributes[key];
				}
			}
			return result;
		}
	
		function decode (s) {
			return s.replace(/(%[0-9A-Z]{2})+/g, decodeURIComponent);
		}
	
		function init (converter) {
			function api() {}
	
			function set (key, value, attributes) {
				if (typeof document === 'undefined') {
					return;
				}
	
				attributes = extend({
					path: '/'
				}, api.defaults, attributes);
	
				if (typeof attributes.expires === 'number') {
					attributes.expires = new Date(new Date() * 1 + attributes.expires * 864e+5);
				}
	
				// We're using "expires" because "max-age" is not supported by IE
				attributes.expires = attributes.expires ? attributes.expires.toUTCString() : '';
	
				try {
					var result = JSON.stringify(value);
					if (/^[\{\[]/.test(result)) {
						value = result;
					}
				} catch (e) {}
	
				value = converter.write ?
					converter.write(value, key) :
					encodeURIComponent(String(value))
						.replace(/%(23|24|26|2B|3A|3C|3E|3D|2F|3F|40|5B|5D|5E|60|7B|7D|7C)/g, decodeURIComponent);
	
				key = encodeURIComponent(String(key))
					.replace(/%(23|24|26|2B|5E|60|7C)/g, decodeURIComponent)
					.replace(/[\(\)]/g, escape);
	
				var stringifiedAttributes = '';
				for (var attributeName in attributes) {
					if (!attributes[attributeName]) {
						continue;
					}
					stringifiedAttributes += '; ' + attributeName;
					if (attributes[attributeName] === true) {
						continue;
					}
	
					// Considers RFC 6265 section 5.2:
					// ...
					// 3.  If the remaining unparsed-attributes contains a %x3B (";")
					//     character:
					// Consume the characters of the unparsed-attributes up to,
					// not including, the first %x3B (";") character.
					// ...
					stringifiedAttributes += '=' + attributes[attributeName].split(';')[0];
				}
	
				return (document.cookie = key + '=' + value + stringifiedAttributes);
			}
	
			function get (key, json) {
				if (typeof document === 'undefined') {
					return;
				}
	
				var jar = {};
				// To prevent the for loop in the first place assign an empty array
				// in case there are no cookies at all.
				var cookies = document.cookie ? document.cookie.split('; ') : [];
				var i = 0;
	
				for (; i < cookies.length; i++) {
					var parts = cookies[i].split('=');
					var cookie = parts.slice(1).join('=');
	
					if (!json && cookie.charAt(0) === '"') {
						cookie = cookie.slice(1, -1);
					}
	
					try {
						var name = decode(parts[0]);
						cookie = (converter.read || converter)(cookie, name) ||
							decode(cookie);
	
						if (json) {
							try {
								cookie = JSON.parse(cookie);
							} catch (e) {}
						}
	
						jar[name] = cookie;
	
						if (key === name) {
							break;
						}
					} catch (e) {}
				}
	
				return key ? jar[key] : jar;
			}
	
			api.set = set;
			api.get = function (key) {
				return get(key, false /* read as raw */);
			};
			api.getJSON = function (key) {
				return get(key, true /* read as json */);
			};
			api.remove = function (key, attributes) {
				set(key, '', extend(attributes, {
					expires: -1
				}));
			};
	
			api.defaults = {};
	
			api.withConverter = init;
	
			return api;
		}
	
		return init(function () {});
	}));
	
	/**
	 * Create a no conflict reference to JS Cookies.
	 *
	 * @type {Object}
	 */
	LLMS.CookieStore = Cookies.noConflict();
	
	/**
	 * Store information in Local Storage by group.
	 *
	 * @since 3.36.0
	 * @since 3.37.14 Use persistent reference to JS Cookies.
	 * @since 4.2.0 Set sameSite to `strict` for cookies.
	 *
	 * @param string group Storage group id/name.
	 */
	LLMS.Storage = function( group ) {
	
		var self = this,
			store = LLMS.CookieStore;
	
		/**
		 * Clear all data for the group.
		 *
		 * @since 3.36.0
		 *
		 * @return void
		 */
		this.clearAll = function() {
			store.remove( group );
		};
	
		/**
		 * Clear a single item from the group by key.
		 *
		 * @since 3.36.0
		 *
		 * @return obj
		 */
		this.clear = function( key ) {
			var data = self.getAll();
			delete data[ key ];
			return store.set( group, data );
		};
	
		/**
		 * Retrieve (and parse) all data stored for the group.
		 *
		 * @since 3.36.0
		 *
		 * @return obj
		 */
		this.getAll = function() {
			return store.getJSON( group ) || {};
		}
	
		/**
		 * Retrieve an item from the group by key.
		 *
		 * @since 3.36.0
		 *
		 * @param string key Item key/name.
		 * @param mixed default_val Item default value to be returned when item not found in the group.
		 * @return mixed
		 */
		this.get = function( key, default_val ) {
			var data = self.getAll();
			return data[ key ] ? data[ key ] : default_val;
		}
	
		/**
		 * Store an item in the group by key.
		 *
		 * @since 3.36.0
		 * @since 4.2.0 Set sameSite to `strict` for cookies.
		 *
		 * @param string key Item key name.
		 * @param mixed val Item value
		 * @return obj
		 */
		this.set = function( key, val ) {
			var data = self.getAll();
			data[ key ] = val;
			return store.set( group, data, { sameSite: 'strict' } );
		};
	
	}
	
		/**
	 * Student Dashboard related JS
	 *
	 * @package LifterLMS/Scripts
	 *
	 * @since 3.7.0
	 * @since 3.10.0 Bind events on the orders screen.
	 * @since 5.0.0 Removed redundant password toggle logic for edit account screen.
	 * @version 5.0.0
	 */
	LLMS.StudentDashboard = {
	
		/**
		 * Slug for the current screen/endpoint
		 *
		 * @type  {String}
		 */
		screen: '',
	
		/**
		 * Init
		 *
		 * @since 3.7.0
		 * @since 3.10.0 Unknown
		 * @since 5.0.0 Removed password toggle logic.
		 *
		 * @return void
		 */
		init: function() {
	
			if ( $( '.llms-student-dashboard' ).length ) {
				this.bind();
				if ( 'orders' === this.get_screen() ) {
					this.bind_orders();
				}
			}
	
		},
	
		/**
		 * Bind DOM events
		 *
		 * @since 3.7.0
		 * @since 3.7.4 Unknown.
		 * @since 5.0.0 Removed password toggle logic.
		 *
		 * @return   void
		 */
		bind: function() {
	
			$( '.llms-donut' ).each( function() {
				LLMS.Donut( $( this ) );
			} );
	
		},
	
		/**
		 * Bind events related to the orders screen on the dashboard
		 *
		 * @since 3.10.0
		 *
		 * @return void
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
		 *
		 * @since 3.10.0
		 *
		 * @return void
		 */
		get_screen: function() {
			if ( ! this.screen ) {
				this.screen = $( '.llms-student-dashboard' ).attr( 'data-current' );
			}
			return this.screen;
		},
	
		/**
		 * Show a confirmation warning when Cancel Subscription form is submitted
		 *
		 * @since 3.10.0
		 *
		 * @param obj e JS event data.
		 * @return void
		 */
		order_cancel_warning: function( e ) {
			e.preventDefault();
			var msg = LLMS.l10n.translate( 'Are you sure you want to cancel your subscription?' );
			if ( window.confirm( LLMS.l10n.translate( msg ) ) ) {
				$( this ).off( 'submit', this.order_cancel_warning );
				$( this ).submit();
			}
		},
	
	};
	
		/* global LLMS, $ */
	
	/**
	 * User event/interaction tracking.
	 *
	 * @since 3.36.0
	 * @since 3.36.2 Fix JS error when settings aren't loaded.
	 * @since 3.37.2 When adding an event to the storae also make sure the nonce is set for server-side verification.
	 * @since 3.37.9 Fix IE compatibility issue related to usage of `Object.assign()`.
	 * @since 3.37.14 Persist the tracking events via ajax when reaching the cookie size limit.
	 * @since 5.0.0 Set `settings` as an empty object when no settings supplied.
	 * @since 7.1.0 Only attempt to add a nonce to the datastore when a nonce exists in the settings object.
	 */
	LLMS.Tracking = function( settings ) {
	
		settings = settings || {};
	
		var self = this,
			store = new LLMS.Storage( 'llms-tracking' );
	
		settings = 'string' === typeof settings ? JSON.parse( settings ) : settings;
	
		/**
		 * Initialize / Bind all tracking event listeners.
		 *
		 * @since 3.36.0
		 * @since 5.0.0 Only attempt to add a nonce to the datastore when a nonce exists in the settings object.
		 * @since 7.1.0 Do not add a nonce to the datastore by default, will be added/updated
		 *              when storing an event to track.
		 *
		 * @return {void}
		 */
		function init() {
	
			self.addEvent( 'page.load' );
	
			window.addEventListener( 'beforeunload', onBeforeUnload );
			window.addEventListener( 'unload', onUnload );
	
			document.addEventListener( 'visibilitychange', onVisibilityChange );
	
		};
	
		/**
		 * Add an event.
		 *
		 * @since 3.36.0
		 * @since 3.36.2 Fix error when settings aren't loaded.
		 * @since 3.37.2 Always make sure the nonce is set for server-side verification.
		 * @since 3.37.14 Persist the tracking events via ajax when reaching the cookie size limit.
		 * @since 7.1.0 Only attempt to add a nonce to the datastore when a nonce exists in the settings object.
		 *
		 * @param string|obj event Event Id (type.event) or a full event object from `this.makeEventObj()`.
		 * @param int args Optional additional arguments to pass to `this.makeEventObj()`.
		 * @return {void}
		 */
		this.addEvent = function( event, args ) {
	
			args  = args || {};
			if ( 'string' === typeof event ) {
				args.event = event;
			}
	
			// If the event isn't registered in the settings don't proceed.
			if ( !settings.events || -1 === settings.events.indexOf( args.event ) ) {
				return;
			}
	
			// Make sure the nonce is set for server-side verification.
			if ( settings.nonce ) {
				store.set( 'nonce', settings.nonce );
			}
	
			event = self.makeEventObj( args );
	
			var all = store.get( 'events', [] );
			all.push( event );
			store.set( 'events', all );
	
			// If couldn't store the latest event because of size limits.
			if ( all.length > store.get( 'events', [] ).length ) {
	
				// Copy the cookie in a temporary variable.
				var _temp = store.getAll();
				// Clear the events from the cookie.
				store.clear('events');
	
				// Add the latest event to the temporary variable.
				_temp['events'].push( event );
	
				// Send the temporary variable as string via ajax.
				LLMS.Ajax.call( {
					data: {
						action: 'persist_tracking_events',
						'llms-tracking': JSON.stringify(_temp)
					},
	
					error: function( xhr, status, error ) {
	
						console.log( xhr, status, error );
	
					},
					success: function( r ) {
	
						if ( 'error' === r.code ) {
							console.log(r.code, r.message);
						}
	
					}
	
				} );
	
			}
	
		}
	
		/**
		 * Retrieve initialization settings.
		 *
		 * @since 3.36.0
		 *
		 * @return obj
		 */
		this.getSettings = function() {
			return settings;
		}
	
		/**
		 * Create an event object suitable to save as an event.
		 *
		 * @since 3.36.0
		 * @since 3.37.9 Use `$.extend()` in favor of `Object.assign()`.
		 *
		 * @param obj event {
		 *     Event hash
		 *
		 *     @param {string} event (Required) Event ID, eg: "page.load".
		 *     @param {url} url Event URL. (Optional, added automatically) Stored as metadata and used to infer an object_id for post events.
		 *     @param {time} float (Optional, added automatically) Timestamp (in milliseconds). Used for the event creation date.
		 *     @param {int} obj_id (Optional). The object ID. Inferred automatically via `url` if not provided.
		 *     @param {obj} meta (Optional) Hash of metadata to store with the event.
		 * }
		 * @return obj
		 */
		this.makeEventObj = function( event ) {
			return $.extend( event, {
				url: window.location.href,
				time: Math.round( new Date().getTime() / 1000 ),
			} );
		}
	
	
		/**
		 * Remove the visibility change event listener on window.beforeunload
		 *
		 * Prevents actual unloading from recording a blur event from the visibility change listener
		 *
		 * @param obj e JS event object.
		 * @return void
		 */
		function onBeforeUnload( e ) {
			document.removeEventListener( 'visibilitychange', onVisibilityChange );
		}
	
		/**
		 * Record a `page.exit` event on window.unload.
		 *
		 * @since 3.36.0
		 *
		 * @param obj e JS event object.
		 * @return void
		 */
		function onUnload( e ) {
			self.addEvent( 'page.exit' );
		}
	
		/**
		 * Record `page.blur` and `page.focus` events via document.visilibitychange events.
		 *
		 * @since 3.36.0
		 *
		 * @param obj e JS event object.
		 * @return void
		 */
		function onVisibilityChange( e ) {
	
			var event = document.hidden ? 'page.blur' : 'page.focus';
			self.addEvent( event );
	
		}
	
		// Initialize on the frontend only.
		if ( ! $( 'body' ).hasClass( 'wp-admin' ) ) {
			init();
		}
	
	};
	
	llms.tracking = new LLMS.Tracking( llms.tracking );
	
		/**
	 * Rest Methods
	 * Manages URL and Rest object parsing
	 *
	 * @package LifterLMS/Scripts
	 *
	 * @since Unknown
	 * @version  Unknown
	 */
	
	LLMS.Rest = {
	
		/**
		 * Init
		 * loads class methods
		 */
		init: function() {
			this.bind();
		},
	
		/**
		 * Bind Method
		 * Handles dom binding on load
		 *
		 * @return {[type]} [description]
		 */
		bind: function() {
		},
	
		/**
		 * Searches for string matches in url path
		 *
		 * @param  {Array}  strings [Array of strings to search for matches]
		 * @return {Boolean}         [Was a match found?]
		 */
		is_path: function( strings ) {
	
			var path_exists = false,
				url         = window.location.href;
	
			for ( var i = 0; i < strings.length; i++ ) {
	
				if ( url.search( strings[i] ) > 0 && ! path_exists ) {
	
					path_exists = true;
				}
			}
	
			return path_exists;
		},
	
		/**
		 * Retrieves query variables
		 *
		 * @return {[Array]} [array object of query variable key=>value pairs]
		 */
		get_query_vars: function() {
	
			var vars   = [], hash,
				hashes = window.location.href.slice( window.location.href.indexOf( '?' ) + 1 ).split( '&' );
	
			for (var i = 0; i < hashes.length; i++) {
				hash = hashes[i].split( '=' );
				vars.push( hash[0] );
				vars[hash[0]] = hash[1];
			}
	
			return vars;
		}
	
	};
	

	(()=>{"use strict";var t={d:(n,e)=>{for(var r in e)t.o(e,r)&&!t.o(n,r)&&Object.defineProperty(n,r,{enumerable:!0,get:e[r]})},o:(t,n)=>Object.prototype.hasOwnProperty.call(t,n),r:t=>{"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(t,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(t,"__esModule",{value:!0})}},n={};t.r(n),t.d(n,{get:()=>d,start:()=>p,stop:()=>c});const e="llms-spinning",r="default",o=window.wp.i18n;function i(t){let n=arguments.length>1&&void 0!==arguments[1]?arguments[1]:r;const i=document.createElement("div"),l=(0,o.__)("Loading…","lifterlms");return i.innerHTML=`<i class="llms-spinner ${n}" role="alert" aria-live="assertive"><span class="screen-reader-text">${l}</span></i>`,i.classList.add(e),t.appendChild(i),i}function l(t){if((t="string"==typeof t?document.querySelectorAll(t):t)instanceof NodeList)return Array.from(t);const n=[];return t instanceof Element?n.push(t):"undefined"!=typeof jQuery&&t instanceof jQuery&&t.toArray().forEach((t=>n.push(t))),n}function s(t){const n=t.querySelectorAll(".llms-spinning");return n.length?Array.from(n).find((n=>t===n.parentNode)):null}function a(){const t="llms-spinner-styles";if(!document.getElementById(t)){const n=document.createElement("style");n.textContent="\n\t.llms-spinning {\n\t\tbackground: rgba( 250, 250, 250, 0.7 );\n\t\tbottom: 0;\n\t\tdisplay: none;\n\t\tleft: 0;\n\t\tposition: absolute;\n\t\tright: 0;\n\t\ttop: 0;\n\t\tz-index: 2;\n\t}\n\n\t.llms-spinner {\n\t\tanimation: llms-spinning 1.5s linear infinite;\n\t\tbox-sizing: border-box;\n\t\tborder: 4px solid #313131;\n\t\tborder-radius: 50%;\n\t\theight: 40px;\n\t\tleft: 50%;\n\t\tmargin-left: -20px;\n\t\tmargin-top: -20px;\n\t\tposition: absolute;\n\t\ttop: 50%;\n\t\twidth: 40px;\n\n\t}\n\n\t.llms-spinner.small {\n\t\tborder-width: 2px;\n\t\theight: 20px;\n\t\tmargin-left: -10px;\n\t\tmargin-top: -10px;\n\t\twidth: 20px;\n\t}\n\n\t@keyframes llms-spinning {\n\t\t0% {\n\t\t\ttransform: rotate( 0deg )\n\t\t}\n\t\t50% {\n\t\t\tborder-radius: 5%;\n\t\t}\n\t\t100% {\n\t\t\ttransform: rotate( 220deg) \n\t\t}\n\t}\n".replace(/\n/g,"").replace(/\t/g," ").replace(/\s\s+/g," "),n.id=t,document.head.appendChild(n)}}function d(t){let n=arguments.length>1&&void 0!==arguments[1]?arguments[1]:r,e=!(arguments.length>2&&void 0!==arguments[2])||arguments[2];a();const o=l(t);if(!o.length)return null;const d=o[0],p=s(d)||i(d,n);return e&&"undefined"!=typeof jQuery?jQuery(p):p}function p(t){let n=arguments.length>1&&void 0!==arguments[1]?arguments[1]:r;l(t).forEach((t=>{const e=d(t,n,!1);e&&(e.style.display="block")}))}function c(t){l(t).forEach((t=>{const n=d(t,r,!1);n&&(n.style.display="none")}))}window.LLMS=window.LLMS||{},window.LLMS.Spinner=n})();

	/**
	 * Initializes all classes within the LLMS Namespace
	 *
	 * @since Unknown
	 *
	 * @return {void}
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
	 * @since 3.24.3
	 *
	 * @see {@link https://stackoverflow.com/a/4819886/400568}
	 *
	 * @return {Boolean} Whether or not the device is touch-enabled.
	 */
	LLMS.is_touch_device = function() {

		var prefixes = ' -webkit- -moz- -o- -ms- '.split( ' ' );
		var mq       = function( query ) {
			return window.matchMedia( query ).matches;
		}

		if ( ( 'ontouchstart' in window ) || window.DocumentTouch && document instanceof DocumentTouch ) {
			return true;
		}

		/**
		 * Include the 'heartz' as a way to have a non matching MQ to help terminate the join.
		 *
		 * @see {@link https://git.io/vznFH}
		 */
		var query = ['(', prefixes.join( 'touch-enabled),(' ), 'heartz', ')'].join( '' );
		return mq( query );

	};

	/**
	 * Wait for matchHeight to load
	 *
	 * @since 3.0.0
	 * @since 3.16.6 Unknown.
	 * @since 5.3.3 Pass a dependency name to `wait_for()`.
	 *
	 * @param {Function} cb Callback function to run when matchheight is ready.
	 * @return {void}
	 */
	LLMS.wait_for_matchHeight = function( cb ) {
		this.wait_for( function() {
			return ( undefined !== $.fn.matchHeight );
		}, cb, 'matchHeight' );
	}

	/**
	 * Wait for webuiPopover to load
	 *
	 * @since 3.9.1
	 * @since 3.16.6 Unknown.
	 *
	 * @param {Function} cb Callback function to run when matchheight is ready.
	 * @return {void}
	 */
	LLMS.wait_for_popover = function( cb ) {
		this.wait_for( function() {
			return ( undefined !== $.fn.webuiPopover );
		}, cb, 'webuiPopover' );
	}

	/**
	 * Wait for a dependency to load and then run a callback once it has
	 *
	 * Temporary fix for a less-than-optimal assets loading function on the PHP side of things.
	 *
	 * @since 3.9.1
	 * @since 5.3.3 Added optional `name` parameter.
	 *
	 * @param {Function} test A function that returns a truthy if the dependency is loaded.
	 * @param {Function} cb   A callback function executed once the dependency is loaded.
	 * @param {string}   name The dependency name.
	 * @return {void}
	 */
	LLMS.wait_for = function( test, cb, name ) {

		var counter = 0,
			interval;

		name = name ? name : 'unnamed';

		interval = setInterval( function() {

			// If we get to 30 seconds log an error message.
			if ( counter >= 300 ) {

				console.log( 'Unable to load dependency: ' + name );

				// If we can't access yet, increment and wait...
			} else {

				// Bind the events, we're good!
				if ( test() ) {
					cb();
				} else {
					// console.log( 'Waiting for dependency: ' + name );
					counter++;
					return;
				}

			}

			clearInterval( interval );

		}, 100 );

	};

	LLMS.init( $ );

} )( jQuery );

//# sourceMappingURL=../maps/js/llms.js.map
