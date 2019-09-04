/**
 * Product Options MetaBox
 * Displays on Course & Membership Post Types
 *
 * @since 3.0.0
 * @version 3.30.3
 */
( function( $ ) {

	window.llms = window.llms || {};

	window.llms.metabox_product = function() {

		/**
		 * jQuery obj for the main $( '#llms-access-plans' ) element.
		 *
		 * @type obj
		 */
		this.$plans = null;

		/**
		 * jQuery obj for the main $( '#llms-save-access-plans' ) save button element.
		 *
		 * @type obj
		 */
		this.$save = null;

		/**
		 * A randomly generated temporary ID used for the tinyMCE editor's id
		 * when a new plan is added
		 *
		 * @type int
		 */
		this.temp_id = Math.floor( ( Math.random() * 7777 ) + 777 );

		/**
		 * CSS class name used to highlight validation errors for plan fields
		 *
		 * @type string
		 */
		this.validation_class = 'llms-invalid';

		/**
		 * Initialize
		 *
		 * @param   bool skip_dep_checks if true, skips dependency checks.
		 * @return  void
		 * @since   3.0.0
		 * @version 3.29.3
		 */
		this.init = function( skip_dep_checks ) {

			var self = this;

			self.$plans = $( '#llms-access-plans' );
			self.$save  = $( '#llms-save-access-plans' );

			self.bind_visibility();

			var $mb = $( '#lifterlms-product #llms-product-options-access-plans' );

			if ( ! $mb.length ) {
				return;
			} else if ( skip_dep_checks ) {
				self.bind();
				return;
			}

			LLMS.Spinner.start( $mb );

			// we rely on TinyMCE but WordPress doesn't register TinyMCE
			// like every other admin script so we'll do a little dependency check here...
			var counter = 0,
				interval;

			interval = setInterval( function() {

				// if we get to 30 seconds display an error message
				if ( counter >= 300 ) {

					$mb.html( LLMS.l10n.translate( 'There was an error loading the necessary resources. Please try again.' ) );

				}
				// if we can't access tinyMCE, increment and wait...
				else if ( 'undefined' === typeof tinyMCE ) {

					counter++;
					return;

				}
				// bind the events, we're good!
				else {

					self.bind();

				}

				clearInterval( interval );
				LLMS.Spinner.stop( $mb );

			}, 100 );

		};

		/**
		 * Bind DOM Events
		 *
		 * @since  3.0.0
		 * @since  3.30.0 Add checkout redirect fields events.
		 * @version 3.30.0
		 *
		 * @return void
		 */
		this.bind = function() {

			var self = this;

			setTimeout( function() {
				if ( self.has_plan_limit_been_reached() ) {
					self.toggle_create_button( 'disable' );
				}
			}, 500 );

			if ( 0 === self.get_current_plan_count() ) {
				self.toggle_save_button( 'disable' );
			}

			// save access plans button.
			self.$save.on( 'click', function( e ) {
				e.preventDefault();
				self.save_plans();
			} );

			// bind change events to form element that controls another form element
			self.$plans.on( 'change', '[data-controller-id]', function() {
				self.controller_change( $( this ) );
			} );

			// @todo Replace this with multiple data-controller functionality in llms-metaboxes.js
			self.$plans.on( 'change', 'select[name$="[availability]"]', function() {
				var $plan_container         = $( this ).closest( '.llms-access-plan' ),
					$plan_redirect_forced   = $plan_container.find( 'input[name$="[checkout_redirect_forced]"]' ),
					$plan_redirect_settings = $plan_container.find( '.llms-checkout-redirect-settings' );

				if ( 'members' === $( this ).val() ) {
					if ( ! $plan_redirect_forced.prop( 'checked' ) ) {
						$plan_redirect_settings.hide();
					} else {
						$plan_redirect_settings.show();
					}

					$plan_redirect_forced.on( 'change', function() {
						$plan_redirect_settings.toggle();
					} );

				} else {
					$plan_redirect_forced.off( 'change' );
					$plan_redirect_settings.show();
				}

			} );

			$( '#llms-access-plans .llms-access-plan-datepicker' ).datepicker( {
				dateFormat: "mm/dd/yy"
			} );

			// trigger changes on load for all existing plans
			$( '#llms-access-plans [data-controller-id]' ).trigger( 'change' );

			// add a new empty plan interface on new plan button click.
			$( '#llms-new-access-plan' ).on( 'click', function() {
				self.init_plan();
				self.toggle_create_button( 'disable' );
				self.toggle_save_button( 'enable' );
				setTimeout( function() {
					if ( ! self.has_plan_limit_been_reached() ) {
						self.toggle_create_button( 'enable' );
					}
				}, 500 );
			} );

			self.$plans.sortable( {
				handle: '.llms-drag-handle',
				items: '.llms-access-plan',
				start: function( event, ui ) {
					self.$plans.addClass( 'dragging' );
				},
				stop: function( event, ui ) {
					self.$plans.removeClass( 'dragging' );
					self.update_plan_orders();
				},
			} );

			// bind text entered into the title to the displayed title for fancy fun
			self.$plans.on( 'keyup', 'input.llms-plan-title', function( ) {

				var $input   = $( this ),
					$plan    = $input.closest( '.llms-access-plan' ),
					$display = $plan.find( 'span.llms-plan-title' ),
					val      = $input.val(),
					title    = ( val ) ? val : $display.attr( 'data-default' );

				$display.text( title );

			} );

			// Record that a field has been focused so we can tweak validation to only validate "edited" fields.
			self.$plans.on( 'focusin', 'input', function( e, data ) {
				$( this ).addClass( 'llms-has-been-focused' );
			} );

			// Validate a single input field
			self.$plans.on( 'keyup focusout llms-validate-plan-field', 'input', function( e, data ) {

				var $input = $( this );

				if ( $input[0].checkValidity() ) {
					$input.removeClass( self.validation_class );
				} else {
					$input.addClass( self.validation_class );
					if ( 'keyup' === e.type ) {
						$input[0].reportValidity();
					}
				}

				if ( ! data || data.cascade ) {
					$input.closest( '.llms-access-plan' ).trigger( 'llms-validate-plan', { original_event: e.type } );
				}

			} );

			self.$plans.on( 'llms-validate-plan', '.llms-access-plan', function( e, data ) {

				data = data || {};

				var $plan = $( this ),
					// only validate "edited" fields during cascading validation from input validations.
					selector = data.original_event ? 'input.llms-has-been-focused' : 'input';

				$plan.find( selector ).each( function() {
					$( this ).trigger( 'llms-validate-plan-field', { cascade: false } );
				} );

				if ( $plan.find( '.' + self.validation_class ).length ) {
					$plan.addClass( self.validation_class );
				} else {
					$plan.removeClass( self.validation_class );
				}

			} );

			self.$plans.on( 'llms-collapsible-toggled', '.llms-access-plan', function() {

				var $plan = $( this );

				if ( $plan.hasClass( 'opened' ) ) {
					// wait for animation to complete to prevent focusable errors in the console.
					setTimeout( function() {
						$plan.find( 'input.llms-invalid' ).each( function() {
							$( this )[0].reportValidity();
						} );
					}, 500 );
				}

			} );

			self.$plans.on( 'click', '.llms-plan-delete', function( e ) {
				e.stopPropagation();
				self.delete_plan( $( this ) );
			} );

			// select2ify membership availability fields
			window.llms.metaboxes.post_select( $( '#llms-access-plans .llms-availability-restrictions' ) );

			// select2ify redirection page fields
			window.llms.metaboxes.post_select( $( '#llms-access-plans .llms-checkout-redirect-page' ) );

			// disable the textarea generated by the wp_editor function
			// can't figure out how to do this during initialization
			$( '#_llms_plans_content_llms-new-access-plan-model' ).attr( 'disabled', 'disabled' );
			tinyMCE.EditorManager.execCommand( 'mceRemoveEditor', true, '_llms_plans_content_llms-new-access-plan-model' );

		};

		/**
		 * Bind DOM events for editing product visibility
		 *
		 * @return   void
		 * @since    3.6.0
		 * @version  3.6.0
		 */
		this.bind_visibility = function() {

			var $radios = $( '#llms-catalog-visibility-select' ),
				$toggle = $( 'a.llms-edit-catalog-visibility' ),
				$save   = $( 'a.llms-save-catalog-visibility' ),
				$cancel = $( 'a.llms-cancel-catalog-visibility' );

			$toggle.on( 'click', function( e ) {
				e.preventDefault();
				$radios.slideDown( 'fast' );
				$toggle.hide();
			} );

			$save.on( 'click', function( e ) {
				e.preventDefault();
				$radios.slideUp( 'fast' );
				$toggle.show();
				$( '#llms-catalog-visibility-display' ).text( $( 'input[name="_llms_visibility"]:checked' ).attr( 'data-label' ) );
			} );

			$cancel.on( 'click', function( e ) {
				e.preventDefault();
				$radios.slideUp( 'fast' );
				$toggle.show();
			} );

		};

		/**
		 * Handle physical deletion of a plan element
		 * If the plan hasn't be persisted to the database it's removed from the dom
		 * if it already exists in the database a confirm modal is displayed
		 * upon confirmation AJAX call will be made to move the plan to the trash
		 * and upon success the element will be removed from the dom
		 *
		 * @param  obj $btn jQuery selector of the "X" button clicked to initiate deletion
		 * @return void
		 * @since  3.0.0
		 * @version 3.29.1
		 */
		this.delete_plan = function( $btn ) {

			var self    = this,
				$plan   = $btn.closest( '.llms-access-plan' ),
				plan_id = $plan.attr( 'data-id' ),
				warning = LLMS.l10n.translate( 'After deleting this access plan, any students subscribed to this plan will still have access and will continue to make recurring payments according to the access plan\'s settings. If you wish to terminate their plans you must do so manually. This action cannot be reversed.' );

			// if there's no ID just remove the element from the DOM
			if ( ! plan_id ) {

				self.remove_plan_el( $plan );

				// Throw a confirmation warning
			} else if ( window.confirm( warning ) ) {

				LLMS.Spinner.start( $plan );
				window.LLMS.Ajax.call( {
					data: {
						action: 'delete_access_plan',
						plan_id: plan_id,
					},
					success: function( r ) {
						setTimeout( function() {
							LLMS.Spinner.stop( $plan );
						}, 550 );
						if ( r.success ) {
							self.remove_plan_el( $plan );
							self.trigger_update_hook();
							setTimeout( function() {
								self.update_plan_orders();
							}, 500 );
						} else if ( r.message ) {
							alert( r.message );
						}
					}

				} );

			}

		};

		/**
		 * Handle hiding & showing various pieces of an access plan form
		 * This is bound to any form element with a "data-controller-id" property
		 *
		 * @param  obj  $el   jQuery selector for the changed form element
		 * @return void
		 * @since  3.0.0
		 */
		this.controller_change = function( $el ) {

			var id        = $el.attr( 'data-controller-id' ),
				val       = $el.val(),
				$form     = $el.closest( '.llms-access-plan' ),
				$controls = $form.find( '[data-controller="' + id + '"]' );

			if ( 'checkbox' === $el.attr( 'type' ) ) {
				val = ( $el.is( ':checked' ) ) ? val : 'no';
			}

			$controls.each( function() {

				var $c         = $( this ),
					$els       = ( 'SELECT' === $c[0].nodeName || 'INPUT' === $c[0].nodeName || 'TEXTAREA' === $c[0].nodeName ) ? $c : $c.find( 'input, select, textarea' ),
					equals     = $c.attr( 'data-value-is' ),
					not_equals = $c.attr( 'data-value-is-not' ),
					action, operator;

				if ( typeof equals !== typeof undefined && equals !== false ) {

					operator = '==';

				} else if ( typeof not_equals !== typeof undefined && not_equals !== false ) {

					operator = '!=';

				}

				switch ( operator ) {

					case '==':

						if ( val == equals ) {
							action = 'show';
						} else {
							action = 'hide';
						}

					break;

					case '!=':

						if ( val != not_equals ) {
							action = 'show';
						} else {
							action = 'hide';
						}

					break;

				}

				if ( 'show' === action ) {
					$c.show();
					$els.removeAttr( 'disabled' ).trigger( 'change' );
				} else if ( 'hide' === action ) {
					$c.hide();
					$els.attr( 'disabled', 'disabled' );
				}

			} );

		};

		/**
		 * Retrieve the current number of access plans for the course / membership (saved or unsaved)
		 *
		 * @return  int
		 * @since   3.29.0
		 * @version 3.29.0
		 */
		this.get_current_plan_count = function() {
			return this.$plans.find( '.llms-access-plan' ).length;
		}

		/**
		 * Retrieve access plan data as an array of JSON built from the dom element field values.
		 *
		 * @return  array
		 * @since   3.29.0
		 * @version 3.29.0
		 */
		this.get_plans_array = function() {

			// ensure all content editors are saved properly.
			tinyMCE.triggerSave();

			var self  = this,
				form  = self.$plans.closest( 'form' ).serializeArray(),
				plans = [];

			for ( var i = 0; i < form.length; i++ ) {

				// Skip non plan data from the form.
				if ( -1 === form[ i ].name.indexOf( '_llms_plans' ) ) {
					continue;
				}

				var keys  = form[ i ].name.replace( '_llms_plans[', '' ).split( '][' ),
					index = ( keys[0] * 1 ) - 1,
					name  = keys[1].replace( ']', '' ),
					type  = 3 === keys.length ? 'array' : 'single';

				if ( ! plans[ index ] ) {
					plans[ index ] = {};
				}

				if ( 'array' === type ) {

					if ( ! plans[ index ][ name ] ) {
						plans[ index ][ name ] = [];
					}
					plans[ index ][ name ].push( form[ i ].value );

				} else {

					plans[ index ][ name ] = form[ i ].value;

				}

			}

			return plans;

		};

		/**
		 * Determine if the access plan limit has been reached
		 *
		 * @return Boolean
		 * @since  3.0.0
		 * @version  3.29.0
		 */
		this.has_plan_limit_been_reached = function() {

			var limit = window.llms.product.access_plan_limit;
			return this.get_current_plan_count() >= limit;

		};

		/**
		 * Initializes a new plan and adds it to the list of plans in the DOM
		 *
		 * @since 3.0.0
		 * @since 3.30.0 Initialize select2 on checkout redirect fields.
		 * @version 3.30.0
		 *
		 * @return   void
		 */
		this.init_plan = function() {

			// don't do anything if we've reached the plan limit
			if ( this.has_plan_limit_been_reached() ) {
				return;
			}

			var $clone          = $( '#llms-new-access-plan-model' ).clone()
				$existing_plans = $( '#llms-access-plans .llms-access-plan' ),
				$editor         = $clone.find( '#_llms_plans_content_llms-new-access-plan-model' );

			// remove ID from the item
			$clone.removeAttr( 'id' );

			// give a temporary id to the editor element
			$editor.removeAttr( 'id' ).attr( 'id', '_llms_plans_content_' + this.temp_id );
			this.temp_id++; // increment the temp_id ID so we don't use it again

			// activate all elements
			$clone.find( 'select, input, textarea' ).each( function() {
				$( this ).removeAttr( 'disabled' ); // enabled the field
			} );

			$clone.find( '.llms-access-plan-datepicker' ).datepicker( {
				dateFormat: "mm/dd/yy"
			} );

			$clone.appendTo( '#llms-access-plans' );

			// rewrite the order of all elements
			this.update_plan_orders();

			$clone.find( '.llms-collapsible-header' ).trigger( 'click' );

			// check if the limit has been reached and toggle the button if it has
			if ( this.has_plan_limit_been_reached() ) {
				this.toggle_create_button( 'disable' );
			}

			// select2ify membership availability field
			window.llms.metaboxes.post_select( $clone.find( '.llms-availability-restrictions' ) );

			// select2ify redirection page fields
			window.llms.metaboxes.post_select( $clone.find( '.llms-checkout-redirect-page' ) );

			$clone.find( '[data-controller-id]' ).trigger( 'change' );
			$( document ).trigger( 'llms-plan-init', $clone );

		};

		/**
		 * Persist access plans to the DB if they pass validation
		 *
		 * @since 3.29.0
		 * @since 3.30.3 Fixed typo in error message.
		 *
		 * @return void
		 */
		this.save_plans = function() {

			var self = this;

			self.$plans.find( '.llms-access-plan' ).not( '#llms-new-access-plan-model' ).each( function() {
				$( this ).trigger( 'llms-validate-plan' );
			} );

			if ( self.$plans.find( '.' + self.validation_class ).length ) {
				self.$plans.find( '.llms-access-plan.' + self.validation_class ).not( '.opened' ).first().find( '.llms-collapsible-header' ).trigger( 'click' );
				$( document ).trigger( 'llms-access-plan-validation-errors' );
				return;
			}

			LLMS.Spinner.start( self.$plans );
			self.$save.attr( 'disabled', 'disabled' );
			window.LLMS.Ajax.call( {
				data: {
					action: 'llms_update_access_plans',
					plans: self.get_plans_array(),
				},
				complete: function() {
					LLMS.Spinner.stop( self.$plans );
					self.$save.removeAttr( 'disabled' );
				},
				error: function( jqXHR, textStatus, errorThrown ) {
					console.error( 'llms access plan save error encounterd:', jqXHR );
					alert( LLMS.l10n.translate( 'An error was encountered during the save attempt. Please try again.' ) + ' [' + textStatus + ': ' + errorThrown + ']' );
				},
				success: function( res ) {

					if ( ! res.success && res.code && 'error' === res.code ) {
						alert( res.message );
					} else if ( res.data && res.data.html ) {

						// replace the metabox with updated data from the server.
						$( '#llms-product-options-access-plans' ).replaceWith( res.data.html );

						// reinit.
						self.init( true );
						window.llms.metaboxes.init();
						self.update_plan_orders();

						// notify the block editor
						self.trigger_update_hook();

					}

				},

			} );
		};

		/**
		 * Toggle the status of a button
		 *
		 * @param   Object  $btn   jQuery selector of a button element
		 * @param  string status enable or disable
		 * @return  void
		 * @since   3.29.0
		 * @version 3.29.0
		 */
		this.toggle_button = function( $btn, status ) {

			if ( 'disable' === status ) {
				$btn.attr( 'disabled', 'disabled' );
			} else {
				$btn.removeAttr( 'disabled' );
			}

		};

		/**
		 * Control the status of the "New Access Plan" Button
		 *
		 * @param  string status enable or disable
		 * @return void
		 * @since  3.0.0
		 * @since  3.29.0
		 */
		this.toggle_create_button = function( status ) {
			this.toggle_button( $( '#llms-new-access-plan' ), status );
		};

		/**
		 * Control the status of the "Save Access Plans" Button
		 *
		 * @param  string status enable or disable
		 * @return void
		 * @since  3.0.0
		 * @since  3.29.0
		 */
		this.toggle_save_button = function( status ) {
			this.toggle_button( this.$save, status );
		}

		/**
		 * Visually hide and then physically remove a plan element from the DOM
		 * Additionally determines if the New Plan Button should be re-enabled
		 * after deletion
		 *
		 * @param  obj   $plan jQuery selector of the plan element
		 * @return void
		 * @since 3.0.0
		 * @version 3.29.0
		 */
		this.remove_plan_el = function( $plan ) {

			var self = this;

			// fade out nicely
			$plan.fadeOut( 400 );

			// remove from dom after it's hidden visually
			setTimeout(function() {

				$plan.remove();

				// check if we need to reenable the create button and hide the message
				if ( ! self.has_plan_limit_been_reached() ) {
					self.toggle_create_button( 'enable' );
				}

				if ( 0 === self.get_current_plan_count() ) {
					self.toggle_save_button( 'disable' );
				}

			}, 450 );

		};

		/**
		 * Trigger WP Block Editor hook so the pricing table block can be re-rendered with new plan information.
		 *
		 * @return  void
		 * @since   3.29.0
		 * @version 3.29.0
		 */
		this.trigger_update_hook = function() {

			$( document ).trigger( 'llms-access-plans-updated' );

		};

		/**
		 * Reorder the array indexes and the menu order hidden inputs
		 * Called by jQuery UI Sortable on sort completion
		 * Also called after adding a new plan to the DOM so the newest item is always
		 * persisted as the last in the database if no UX reorders the item
		 *
		 * @return void
		 * @since  3.0.0
		 */
		this.update_plan_orders = function() {

			$( '#llms-access-plans .llms-access-plan' ).each( function() {

				var $p        = $( this ),
					$order    = $p.find( '.plan-order' ),
					$editor   = $p.find( 'textarea[id^="_llms_plans_content_"]' ),
					editor_id = $editor.attr( 'id' ),
					orig      = $order.val() * 1,
					curr      = $p.index();

				// de-init tinyMCE from the editor
				tinyMCE.EditorManager.execCommand( 'mceRemoveEditor', true, editor_id );

				// update the order of each field in the plan
				$p.find( 'select, input, textarea' ).each( function() {

					var name = $( this ).attr( 'name' );
					if ( name ) {
						$( this ).attr( 'name', name.replace( orig, curr ) );
					}

				} );

				// re-init tinyMCE on the editor
				tinyMCE.EditorManager.execCommand( 'mceAddEditor', true, editor_id );

				$order.val( curr );

			} );

		};

		// go
		this.init();

	};

	var a = new window.llms.metabox_product();

} )( jQuery );
