/**
 * Show an upgrade to custom fields notice when viewing the forms post type table
 *
 * @since 5.0.0
 * @version 5.0.0
 */

( function() {

	var __         = window.wp.i18n.__,
		BTN_CLASS  = 'page-title-action',
		HELP_CLASS = 'llms-forms-help-text',
		addNewBtn = document.querySelector( '.' + BTN_CLASS );

	// Don't do anything if the button already exists.
	if ( addNewBtn ) {
		return;
	}

	/**
	 * Create the disabled "Add New Form" button
	 *
	 * @since 5.0.0
	 *
	 * @return {Element} Button DOM node.
	 */
	function createNewButton() {

		var btn = document.createElement( 'button' );

		btn.className = BTN_CLASS + ' button';
		btn.innerHTML = __( 'Add New Form', 'lifterlms' );
		btn.disabled  = 'disabled';
		btn.style     = 'vertical-align: inherit';

		return btn;

	}

	/**
	 * Create the toggle "Help" icon button
	 *
	 * @since 5.0.0
	 *
	 * @return {Element} Button DOM node.
	 */
	function createHelpIcon() {

		var btn = document.createElement( 'button' ),
			txt = __( 'Help', 'lifterlms' );

		btn.className = 'button dashicons dashicons-editor-help';
		btn.style     = [
			'border-radius: 50%;',
			'border-color: #50575e',
			'color: #50575e',
			'font-size: 23px;',
			'height: 30px;',
			'line-height: 1;',
			'margin-left: 5px;',
			'padding: 0;',
			'position: relative;',
			'top: 3px',
			'vertical-align: baseline;',
			'width: 30px;',
		].join( ';' );

		btn.innerHTML = '<span class="screen-reader-text>' + txt + '</span>';
		btn.title     = __( 'Help', 'lifterlms' );

		btn.addEventListener( 'click', toggleHelpNode );

		return btn;

	}

	/**
	 * Create the help notice node
	 *
	 * @since 5.0.0
	 *
	 * @return {Element} Notice div DOM node.
	 */
	function createHelpNode() {

		var div = document.createElement( 'div' );

		div.className = HELP_CLASS;
		div.style     = 'display:none';

		div.innerHTML  = '<p><b>Want to create custom forms and custom fields for your forms?</b></p>';
		div.innerHTML += '<p>Create unique student information forms for specific courses and memberships. Also unlock the power of custom fields so you can collect and display any form field data you can imagine.</p>';
		div.innerHTML += '<p><a class="button-primary" target="_blank" rel="noopener" href="https://lifterlms.com/product/custom-fields/?utm_source=LifterLMS%20Plugin&utm_medium=Add%20Form%20Notice&utm_campaign=Add%20Form%20In%20App%20Upgrade%20Flow">Learn More</a></p>';

		return div;

	}

	/**
	 * Callback function for toggling the help notice dispaly
	 *
	 * @since 5.0.0
	 *
	 * @return {void}
	 */
	function toggleHelpNode() {

		var el = document.querySelector( '.' + HELP_CLASS );

		if ( 'none' === el.style.display ) {
			el.style.display = 'block';
			el.className += ' notice notice-info';
		} else {
			el.style.display = 'none';
		}

	}

	/**
	 * Initialize
	 *
	 * Creates and add elements to the dom and binds UI events.
	 *
	 * @since 5.0.0
	 *
	 * @return {void}
	 */
	function init() {

		var title = document.querySelector( '.wp-heading-inline' ),
			btn   = createNewButton();

		title.after( btn );
		btn.after( createHelpIcon() );

		document.querySelector( '.wrap' ).insertBefore( createHelpNode(), document.querySelector( '.wp-header-end' ) );

	}

	// Go.
	init();

} )();


