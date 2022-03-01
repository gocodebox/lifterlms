import { __ } from '@wordpress/i18n';
import { dispatch } from '@wordpress/data';
import domReady from '@wordpress/dom-ready';
import { store as noticeStore } from '@wordpress/notices';
import { getQueryArg } from '@wordpress/url';

domReady( () => {
	if ( '1' !== getQueryArg( window.location.href, 'newAwardMsg' ) ) {
		return;
	}

	const { createSuccessNotice } = dispatch( noticeStore );

	createSuccessNotice( __( 'The certificate award has been created as a draft.', 'lifterlms' ) );
} );
