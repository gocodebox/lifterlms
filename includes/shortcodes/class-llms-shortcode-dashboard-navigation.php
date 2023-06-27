<?php
/**
 * LLMS_Shortcode_Dashboard_Navigation class.
 *
 * @package LifterLMS/Shortcodes/Classes
 *
 * @since   [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * LifterLMS Dashboard Navigation Shortcode.
 *
 * Shortcode: [lifterlms_dashboard_navigation]
 *
 * @since [version]
 */
class LLMS_Shortcode_Dashboard_Navigation extends LLMS_Shortcode {

	/**
	 * Shortcode tag.
	 *
	 * @var string
	 */
	public $tag = 'lifterlms_dashboard_navigation';

	/**
	 * Retrieve the actual content of the shortcode.
	 *
	 * @since [version]
	 *
	 * @return string
	 */
	protected function get_output(): string {
		ob_start();

		llms_get_template( 'myaccount/navigation.php' );

		return ob_get_clean();
	}

}

return LLMS_Shortcode_Dashboard_Navigation::instance();
