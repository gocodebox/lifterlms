import { __ } from '@wordpress/i18n';
import { subscribe } from '@wordpress/data';
import { registerPlugin } from '@wordpress/plugins';

const buttonId = 'llms-dashboard-sections';

const addButton = () => {
	const editPostHeaderToolbarLeft = document.getElementsByClassName(
		'edit-post-header-toolbar__left'
	)[ 0 ];

	if ( ! editPostHeaderToolbarLeft ) {
		return;
	}

	setTimeout( () => {
		const existingButton = document.getElementById( buttonId );

		if ( existingButton ) {
			return;
		}

		const button = document.createElement( 'a' );

		button.id = buttonId;
		button.href = window.llms.admin_url + 'edit.php?post_type=llms_dashboard';
		button.className = 'llms-button-primary';
		button.style.marginLeft = '16px';
		button.innerHTML = __( 'Customize Dashboard Sections', 'lifterlms' );

		editPostHeaderToolbarLeft.appendChild( button );
	}, 1 );
}

registerPlugin( 'llms-dashboard', {
	icon: '',
	render: () => {

		subscribe( addButton );

		return <></>;
	},
} );
