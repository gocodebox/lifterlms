// WP deps.
import { addQueryArgs } from '@wordpress/url';

// LLMS deps.
import { getAdminUrl } from '@lifterlms/utils';

/**
 * Retrieves the redirect URL for the newly created certificate.
 *
 * @since [version]
 *
 * @param {number} post WP_Post ID.
 * @return {string} The edit post URL for awarded certificate draft.
 */
export function getRedirectUrl( post ) {
	return addQueryArgs(
		`${ getAdminUrl() }/post.php`,
		{
			post,
			action: 'edit',
			newAwardMsg: 1,
		}
	);
}

/**
 * Retrieves the url for the "Start from Scratch" button.
 *
 * @since [version]
 *
 * @param {?number} sid WP_User ID of the selected student.
 * @return {string} The URL.
 */
export function getScratchUrl( sid = null ) {
	const args = {
		post_type: 'llms_my_certificate',
	};

	if ( sid ) {
		args.sid = sid;
	}

	return addQueryArgs(
		`${ getAdminUrl() }/post-new.php`,
		args,
	);
}
