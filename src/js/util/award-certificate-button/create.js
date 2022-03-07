// WP deps.
import { store as coreStore } from '@wordpress/core-data';
import { dispatch } from '@wordpress/data';

/**
 * Creates a new awarded certificate post for a given student with a specified parent template.
 *
 * @since 6.0.0
 *
 * @param {number} studentId  WP_User ID.
 * @param {number} templateId WP_Post ID.
 * @return {Promise<Object>} A promise that resolves to the WP_Post object api response on success.
 */
export default function( studentId, templateId ) {
	const { saveEntityRecord } = dispatch( coreStore );

	return saveEntityRecord(
		'postType',
		'llms_my_certificate',
		{
			author: studentId,
			certificate_template: templateId,
			status: 'draft',
		}
	);
}
