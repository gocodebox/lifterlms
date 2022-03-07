import { useSelect } from '@wordpress/data';
import { store as editorStore } from '@wordpress/editor';

/**
 * Conditional wrapper component used to determine if the ResetTemplate component should be rendered.
 *
 * The component is rendered for certificate templates and awarded certificates so long as the template
 * is *not* connected to a template.
 *
 * @since 6.0.0
 *
 * @param {Object}   params          Component parameters.
 * @param {Object[]} params.children Child components.
 * @return {?Object[]} Returns the children or `null` if the check fails.
 */
export function ResetTemplateCheck( { children } ) {
	const { getCurrentPost } = useSelect( editorStore ),
		post = getCurrentPost(),
		{ type, certificate_template: template } = post;

	if ( type && ( 'llms_certificate' === type || ( 'llms_my_certificate' === type && 0 === template ) ) ) {
		return children;
	}

	return null;
}
