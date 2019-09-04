/**
 * UI & UX for the Admin add-ons management screen
 *
 * @package LifterLMS/Scripts
 *
 * @since    3.22.0
 * @version  3.22.0
 */

( function( $ ) {

	/**
	 * Tracks current # of each bulk action to be run upon form submission
	 *
	 * @type  {Object}
	 */
	var actions = {
		update: 0,
		install: 0,
		activate: 0,
		deactivate: 0,
	};

	/**
	 * When the bulk action modal is closed, clear all existing staged actions
	 *
	 * @since    3.22.0
	 * @version  3.22.0
	 */
	$( '.llms-bulk-close' ).on( 'click', function( e ) {
		e.preventDefault();
		$( 'input.llms-bulk-check' ).filter( ':checked' ).prop( 'checked', false ).trigger( 'change' );
	} );

	/**
	 * Update the UI and counters when a checkbox action is changed
	 *
	 * @since    3.22.0
	 * @version  3.22.0
	 */
	$( 'input.llms-bulk-check' ).on( 'change', function() {

		var action = $( this ).attr( 'data-action' );

		if ( $( this ).is( ':checked' ) ) {
			actions[ action ]++;
		} else {
			actions[ action ]--;
		}

		update_ui();

	} );

	/**
	 * Updates the UI when bulk actions are changed
	 * Shows # of each action to be applied & shows the form submission / cancel buttons
	 *
	 * @return   void
	 * @since    3.22.0
	 * @version  3.22.0
	 */
	function update_ui() {

		var $el = $( '#llms-addons-bulk-actions' );
		if ( actions.update || actions.install || actions.activate || actions.deactivate ) {
			$el.addClass( 'active' );
		} else {
			$el.removeClass( 'active' );
		}

		$.each( actions, function( key, count ) {

			var html  = '',
				$desc = $el.find( '.llms-bulk-desc.' + key );

			if ( actions[ key ] ) {
				if ( actions[ key ] > 1 ) {
					html = LLMS.l10n.replace( '%d add-ons', { '%d': actions[ key ] } );
				} else {
					html = LLMS.l10n.translate( '1 add-on' );
				}
				$desc.show();
			} else {
				$desc.hide();
			}
			$desc.find( 'span' ).html( html );

		} );

	}

	/**
	 * Show the keys management dropdown on click of the "My License Keys" button
	 *
	 * @since    3.22.0
	 * @version  3.22.0
	 */
	$( '#llms-active-keys-toggle' ).on( 'click', function() {
		$( '#llms-key-field-form' ).toggle();
	} );

} )( jQuery );
