( ( $ ) => {
	const llmsAdminNotifications = window?.llmsAdminNotifications || {};

	if ( ! llmsAdminNotifications ) {
		return;
	}

	if ( llmsAdminNotifications?.paused === 'true' ) {
		return;
	}

	$.post(
		llmsAdminNotifications.ajaxurl,
		{
			'action': 'llms_show_notification',
			'nonce': llmsAdminNotifications.nonce,
		},
		( response ) => {
			console.log(response);

			if ( ! response?.success ) {
				return;
			}

			const data = response?.data ?? {};

			if ( ! data ) {
				return;
			}

			let insideWrap = document.querySelectorAll( '.lifterlms-settings form > .llms-inside-wrap' )[0];

			if ( ! insideWrap ) {
				insideWrap = document.querySelectorAll( '.lifterlms-settings .llms-inside-wrap > *' )[0];
			}

			if ( ! insideWrap ) {
				return;
			}

			// Convert response to DOM element.
			const parser= new DOMParser();
			const parsed = parser.parseFromString( data, 'text/html' );
			const div= parsed.querySelector( 'div' );

			if ( ! div ) {
				return;
			}

			insideWrap.parentNode.insertBefore( div, insideWrap );

			const id = div.getAttribute( 'id' )?.replace( 'llms-notice-', '' );

			const dismissButton = div.querySelector( '.notice-dismiss' );

			if ( ! dismissButton ) {
				return;
			}

			dismissButton.addEventListener(
				'click',
				() => {
					$.post(
						llmsAdminNotifications.ajaxurl,
						{
							'action': 'llms_dismiss_notification',
							'nonce': llmsAdminNotifications.nonce,
							'id': id,
						},
						( response ) => {
							if ( response?.success ) {
								div.remove();
							}
						}
					);
				}
			);
		}
	);
} )( jQuery );
