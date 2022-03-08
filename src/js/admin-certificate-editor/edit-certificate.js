import { dispatch } from '@wordpress/data';
import { store as editorStore } from '@wordpress/editor';

/**
 * Make changes to a custom field of a certificate post.
 *
 * @since 6.0.0
 *
 * @param {string} key Unprefixed field key. For example, to edit "certificate_size", pass "size".
 * @param {*}      val Field value.
 * @return {void}
 */
export default function editCertificate( key, val ) {
	const { editPost } = dispatch( editorStore ),
		edits = {};
	edits[ `certificate_${ key }` ] = val;
	editPost( edits );
}
