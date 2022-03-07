// WP deps.
import { render } from '@wordpress/element';
import { getQueryArg } from '@wordpress/url';

// Internal deps.
import { AwardCertificateButton } from './util';

const WRAPPER_ID = 'llms-award-certificate-wrapper',
	renderNodeStyles = {},
	buttonArgs = {},
	defaultBtn = getDefaultButton();

/**
 * Retrieves the DOM Element selector for the default button to be replaced by the component.
 *
 * @since 6.0.0
 *
 * @return {?Element} DOM element for the default button or null if no button found.
 */
function getDefaultButton() {
	// Certificates post table page.
	let btn = document.querySelector( '.page-title-action' );
	if ( btn ) {
		renderNodeStyles.top = '-3px';
		renderNodeStyles.position = 'relative';
	}

	// Student certificates reporting page.
	if ( ! btn ) {
		btn = document.getElementById( 'llms-new-award-button' );

		renderNodeStyles.marginBottom = '20px';
		renderNodeStyles.display = 'inline-block';

		buttonArgs.studentId = parseInt( getQueryArg( window.location, 'student_id' ) );
		buttonArgs.selectStudent = false;
	}

	// Reuse the original buttons label.
	if ( btn ) {
		buttonArgs.buttonLabel = btn.textContent;
	}

	return btn;
}

/**
 * Inserts the wrapper element node where the new component button will be rendered.
 *
 * @since 6.0.0
 *
 * @return {boolean} Returns `false` if no default button is found, otherwise returns true.
 */
function insertRenderNode() {
	if ( ! defaultBtn ) {
		return false;
	}

	const renderNode = document.createElement( 'span' );
	renderNode.id = WRAPPER_ID;

	Object.entries( renderNodeStyles ).forEach( ( [ rule, style ] ) => {
		renderNode.style[ rule ] = style;
	} );

	defaultBtn.style.display = 'none';
	defaultBtn.after( renderNode );

	return true;
}

// Render the component.
if ( insertRenderNode() ) {
	render( <AwardCertificateButton { ...buttonArgs } />, document.getElementById( WRAPPER_ID ) );
}
