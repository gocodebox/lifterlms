<?php
/**
 * LLMS_Shortcode_Dashboard_Section class.
 *
 * @package LifterLMS/Shortcodes/Classes
 *
 * @since   [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * LifterLMS Dashboard Section Shortcode.
 *
 * Shortcode: [lifterlms_dashboard_section]
 *
 * @since [version]
 */
class LLMS_Shortcode_Dashboard_Section extends LLMS_Shortcode {

	/**
	 * Shortcode tag.
	 *
	 * @var string
	 */
	public $tag = 'lifterlms_dashboard_section';

	/**
	 * Retrieves an array of default attributes.
	 *
	 * @since [version]
	 *
	 * @return array
	 */
	protected function get_default_attributes() {
		return [
			'section' => 'courses',
		];
	}

	/**
	 * Retrieve the actual content of the shortcode.
	 *
	 * @since [version]
	 *
	 * @return string
	 */
	protected function get_output(): string {

		$current = LLMS_Student_Dashboard::get_current_tab( 'slug' );

		if ( 'dashboard' !== $current ) {
			return '';
		}

		$section = $this->get_attribute( 'section' );

		$callbacks = [
			'courses'      => [
				'render' => 'lifterlms_template_student_dashboard_my_courses',
				'args'   => [ true ]
			],
			'achievements' => [
				'render' => 'lifterlms_template_student_dashboard_my_achievements',
				'args'   => [ true ]
			],
			'certificates' => [
				'render' => 'lifterlms_template_student_dashboard_my_certificates',
				'args'   => [ true ]
			],
			'memberships'  => [
				'render' => 'lifterlms_template_student_dashboard_my_memberships',
				'args'   => [ true ]
			],
		];

		/**
		 * Dashboard section callbacks.
		 *
		 * @since [version]
		 *
		 * @param array $callbacks Array of callbacks.
		 */
		$callbacks = apply_filters( 'lifterlms_dashboard_section_callbacks', $callbacks );

		if ( ! is_callable( $callbacks[ $section ]['render'] ?? '' ) ) {
			return '';
		}

		ob_start();

		call_user_func(
			$callbacks[ $section ]['render'],
			...( $callbacks[ $section ]['args'] ?? [] )
		);

		return ob_get_clean();

	}

}

return LLMS_Shortcode_Dashboard_Section::instance();
