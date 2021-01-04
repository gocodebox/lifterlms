<?php
/**
 * Membership template functions
 *
 * @package LifterLMS/Functions
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;


if ( ! function_exists( 'llms_template_membership_instructors' ) ) {
	/**
	 * Get single membership instructors template
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	function llms_template_membership_instructors() {
		llms_get_template( 'membership/instructors.php' );
	}
}
