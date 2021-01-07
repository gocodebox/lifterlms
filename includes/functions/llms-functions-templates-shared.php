<?php
/**
 * Shared template functions
 *
 * A "shared" function is any function used by more than one post type.
 *
 * @package LifterLMS/Functions
 *
 * @since 4.11.0
 * @version 4.11.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'llms_template_instructors' ) ) {

	/**
	 * Get single post instructors template
	 *
	 * Used by courses and membership.
	 *
	 * @since 4.11.0
	 *
	 * @return void
	 */
	function llms_template_instructors() {

		$llms_post = llms_get_post( get_the_ID() );
		if ( ! $llms_post || ! $llms_post instanceof LLMS_Post_Model || ! $llms_post instanceof LLMS_Interface_Post_Instructors ) {
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
