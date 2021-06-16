( function() {

	var BTN_CLASS  = 'page-title-action',
		HELP_CLASS = 'llms-forms-help-text';

	function createNewButton() {

		var btn = document.createElement( 'button' );
		btn.className = BTN_CLASS + ' button';
		btn.innerHTML = window.wp.i18n.__( 'Add New Form', 'lifterlms' );
		btn.disabled = 'disabled';
		btn.style = 'vertical-align: inherit';

		return btn;

	}

	function createHelpIcon() {

		var btn = document.createElement( 'button' ),
			txt = window.wp.i18n.__( 'Help', 'lifterlms' );
		btn.className = 'button dashicons dashicons-editor-help';
		btn.style = [
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

		btn.addEventListener( 'click', toggleHelpNode );

		return btn;

	}

	function createHelpNode() {

		var div = document.createElement( 'div' );
		div.className = HELP_CLASS;
		div.innerHTML = '<p><b>Want to create custom forms?</b></p><p>Lorem ipsum dolor sit</p><p><a class="button-primary" href="#">Learn More</a></p>';
		div.style = 'display:none';

		return div;

	}

	function toggleHelpNode() {

		var el = document.querySelector( '.' + HELP_CLASS );

		if ( 'none' === el.style.display ) {
			el.style.display = 'block';
			el.className += ' notice notice-info';
		} else {
			el.style.display = 'none';
		}

	}

	var addNewBtn = document.querySelector( '.' + BTN_CLASS );

	// Don't do anything if the button already exists.
	if ( addNewBtn ) {
		return;
	}

	var title = document.querySelector( '.wp-heading-inline' ),
		btn   = createNewButton();

	title.after( btn );
	btn.after( createHelpIcon() );

	document.querySelector( '.wrap' ).insertBefore( createHelpNode(), document.querySelector( '.wp-header-end' ) );

} )();


