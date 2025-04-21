<?php
/**
 * LifterLMS Membership Instructors Shortcode
 *
 * Output an anchor link for a membership.
 *
 * [lifterlms_membership_instructors]
 *
 * @package LifterLMS/Shortcodes/Classes
 *
 * @since 8.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Shortcode_Membership_Link
 *
 * @since 3.0.0
 * @since 3.4.3 Unknown.
 */
class LLMS_Shortcode_Membership_Instructors extends LLMS_Shortcode {

	/**
	 * Shortcode tag
	 *
	 * @var  string
	 */
	public $tag = 'lifterlms_membership_instructors';

	/**
	 * Retrieve the actual content of the shortcode
	 *
	 * $atts & $content are both filtered before being passed to get_output()
	 * output is filtered so the return of get_output() doesn't need its own filter
	 *
	 * @return   string
	 * @since    8.0.0
	 */
	protected function get_output() {
		if ( 'llms_membership' !== get_post_type( get_the_ID() ) ) {
			return '';
		}

		ob_start();

		llms_template_instructors();

		return ob_get_clean();
	}

	/**
	 * Retrieves an array of default attributes which are automatically merged
	 * with the user submitted attributes and passed to $this->get_output()
	 *
	 * @return   array
	 * @since    8.0.0
	 */
	protected function get_default_attributes() {
		return array();
	}
}

return LLMS_Shortcode_Membership_Instructors::instance();
