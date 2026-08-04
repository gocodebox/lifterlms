/**
 * Utility to retrieve the current post type of the editor.
 *
 * This utility does not rely on waiting for full block editor initialization,
 * allowing primarily for us to conditionally register blocks based on
 * the current post type.
 *
 * @since 1.6.0
 * @version 1.6.0
 */

/**
 * Retrieve the current post type from LifterLMS script data.
 *
 * @since 1.5.0
 *
 * @return {string|boolean} Returns the post type name or false if not defined.
 */
export default () => {
	if ( window.llms && window.llms.post && window.llms.post.post_type ) {
		return window.llms.post.post_type;
	}

	return false;
};
