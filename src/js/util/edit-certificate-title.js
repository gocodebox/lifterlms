import { dispatch, select } from '@wordpress/data';
import { store as editorStore } from '@wordpress/editor';

/**
 * Edits the title of the current certificate post type in the block editor.
 *
 * This utility is used to allow sharing functionality between `llms_certificate` and `llms_my_certificate`
 * post types. Depending on the post type, the certificate title is stored in a different post field.
 *
 * To edit the actual post title of a `llms_certificate` post (not the awarded certificate's title), use
 * `wp.data.dispatch( 'core/editor' ).editPost()` directly.
 *
 * @since 6.0.0
 *
 * @param {string} title    The desired certificate title.
 * @param {string} postType The current post type, automatically reads it from the current post if omitted.
 * @return {Promise} Promise that resolves when the title edits have been made to the current post.
 */
export function editCertificateTitle( title, postType = null ) {
	if ( ! postType ) {
		const { getCurrentPostType } = select( editorStore );
		postType = getCurrentPostType();
	}

	const { editPost } = dispatch( editorStore ),
		edits = {};

	if ( 'llms_certificate' === postType ) {
		edits.certificate_title = title;
	} else if ( 'llms_my_certificate' === postType ) {
		edits.title = title;
	}

	return editPost( edits );
}
