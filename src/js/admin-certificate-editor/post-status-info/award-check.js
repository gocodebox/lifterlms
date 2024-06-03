/**
 * Conditional wrapper component used to determine if the AwardFromTemplate component should be rendered.
 *
 * The component is rendered for certificate templates.
 *
 * @since 6.0.0
 *
 * @param {Object}   params          Component parameters.
 * @param {Object[]} params.postType Current post type.
 * @param {Object[]} params.children Child components.
 * @return {?Object[]} Returns the children or `null` if the check fails.
 */
export function AwardCheck( { postType, children } ) {
	if ( postType && 'llms_certificate' === postType ) {
		return children;
	}
	return null;
}
