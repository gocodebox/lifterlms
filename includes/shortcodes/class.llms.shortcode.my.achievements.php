<?php
/**
 * LifterLMS My Achievements
 *
 * [lifterlms_my_achievements]
 *
 * @package LifterLMS/Shortcodes/Classes
 *
 * @since 3.14.1
 * @version 3.14.1
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Shortcode_My_Achievements
 *
 * @since 3.14.1
 */
class LLMS_Shortcode_My_Achievements extends LLMS_Shortcode {

	/**
	 * Shortcode tag
	 *
	 * @var string
	 */
	public $tag = 'lifterlms_my_achievements';

	/**
	 * Retrieves an array of default attributes which are automatically merged
	 * with the user submitted attributes and passed to $this->get_output()
	 *
	 * @since 3.14.1
	 * @return array
	 */
	protected function get_default_attributes() {
		return array(
			'count'   => null,
			'columns' => 5,
			'user_id' => get_current_user_id(),
		);
	}

	/**
	 * Retrieve the actual content of the shortcode
	 *
	 * $atts & $content are both filtered before being passed to get_output()
	 * output is filtered so the return of get_output() doesn't need its own filter
	 *
	 * @since 3.14.1
	 *
	 * @return string
	 */
	protected function get_output() {

		if ( ! $this->get_attribute( 'user_id' ) ) {
			return '';
		}

		$student = llms_get_student( $this->get_attribute( 'user_id' ) );
		if ( ! $student ) {
			return '';
		}

		$course = new LLMS_Course( $this->get_attribute( 'course_id' ) );

		ob_start();
		lifterlms_template_achievements_loop( $student, $this->get_attribute( 'count' ), $this->get_attribute( 'columns' ) );
		return ob_get_clean();

	}

}

return LLMS_Shortcode_My_Achievements::instance();
