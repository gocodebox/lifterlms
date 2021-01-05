<?php
/**
 * Shared template functions
 *
 * A "shared" function is any function used by more than one post type.
 *
 * @package LifterLMS/Functions
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'llms_template_instructors' ) ) {

	/**
	 * Get single post instructors template
	 *
	 * Used by courses and membership.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	function llms_template_instructors() {

		$llms_post = llms_get_post( get_the_ID() );
		if ( ! $post || ! $post instanceof LLMS_Post_Model ) {
			return;
		}

		$instructors = $llms_post->get_instructors( true );
		if ( ! $instructors ) {
			return;
		}

		$count = count( $instructors );

		llms_get_template( 'shared/instructors.php', compact( 'llms_post', 'instructors', 'count' ) );

	}
}
