<?php
/**
 * Membership template functions
 *
 * @package LifterLMS/Functions
 *
 * @since 4.11.0
 * @version 4.11.0
 */

defined( 'ABSPATH' ) || exit;


if ( ! function_exists( 'llms_template_membership_instructors' ) ) {
	/**
	 * Get single membership instructors template
	 *
	 * @since 4.11.0
	 *
	 * @return void
	 */
	function llms_template_membership_instructors() {
		llms_get_template( 'membership/instructors.php' );
	}
}
