<?php
/**
 * Manage Theme Support classes
 *
 * @package LifterLMS/ThemeSupport/Classes
 *
 * @since 3.37.0
 * @version 5.8.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Twenty_Twenty class.
 *
 * @since 3.37.0
 */
class LLMS_Theme_Support {

	/**
	 * Constructor
	 *
	 * @since 3.37.0
	 * @since 4.3.0 Load includes during `after_setup_theme` instead of `plugins_loaded`.
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'after_setup_theme', array( $this, 'includes' ) );
	}

	/**
	 * Retrieve formatted inline CSS for a given list of selectors and rules
	 *
	 * @since 4.10.0
	 *
	 * @param string[] $selectors       Array of CSS selectors.
	 * @param string[] $rules           Associative array of CSS rules and properties. For example: `array( 'color' => '#fff' )`.
	 * @param string   $selector_prefix A CSS selector to prefix each item in $selectors with.
	 * @return string
	 */
	public static function get_css( $selectors, $rules, $selector_prefix = '' ) {

		// Convert the $rules array to a list of CSS strings.
		$rules_list = array();
		foreach ( $rules as $prop => $val ) {
			$val = is_array( $val ) ? $val : array( $val );
			foreach ( $val as $value ) {
				$rules_list[] = sprintf( '%1$s: %2$s;', $prop, $value );
			}
		}

		// When supplied, prefix each selector.
		if ( $selector_prefix ) {
			foreach ( $selectors as &$selector ) {
				$selector = $selector_prefix . ' ' . $selector;
			}
		}

		// Return the formatted CSS.
		return implode( ', ', $selectors ) . ' { ' . implode( ' ', $rules_list ) . ' }';

	}

	/**
	 * Retrieve a list of CSS selectors for elements where the primary color is used as the background
	 *
	 * The primary color is a bright blue (#2295ff).
	 *
	 * @since 4.10.0
	 *
	 * @return string[] A list of CSS selectors.
	 */
	public static function get_selectors_primary_color_background() {

		/**
		 * Filter the list of CSS selectors for elements where the primary color is used as the background
		 *
		 * @since 4.10.0
		 *
		 * @param string[] $selectors A list of CSS selectors.
		 */
		return apply_filters(
			'llms_theme_support_get_selectors_primary_color_background',
			array(

				// Buttons.
				'.llms-button-primary',
				'.llms-button-primary:hover',
				'.llms-button-primary.clicked',
				'.llms-button-primary:focus',
				'.llms-button-primary:active',
				'.llms-button-action',
				'.llms-button-action:hover',
				'.llms-button-action.clicked',
				'.llms-button-action:focus',
				'.llms-button-action:active',

				// Pricing Tables.
				'.llms-access-plan-title',
				'.llms-access-plan .stamp',
				'.llms-access-plan.featured .llms-access-plan-featured',

				// Checkout.
				'.llms-checkout-wrapper .llms-form-heading',

				// Notices.
				'.llms-notice:not(.llms-debug)',

				// Progress Bar.
				'.llms-progress .progress-bar-complete',

				// My Grades.
				'.llms-sd-widgets .llms-sd-widget .llms-sd-widget-title',

				// Instructor.
				'.llms-instructor-info .llms-instructors .llms-author .avatar',

				// Quizzes.
				'.llms-question-wrapper ol.llms-question-choices li.llms-choice input:checked + .llms-marker',

			)
		);

	}

	/**
	 * Retrieve a list of CSS selectors for elements where the primary color is used as the border color
	 *
	 * The primary color is a bright blue (#2295ff).
	 *
	 * @since 4.10.0
	 *
	 * @return string[] A list of CSS selectors.
	 */
	public static function get_selectors_primary_color_border() {

		/**
		 * Filter the list of CSS selectors for elements where the primary color is used as the border
		 *
		 * @since 4.10.0
		 *
		 * @param string[] $selectors A list of CSS selectors.
		 */
		return apply_filters(
			'llms_theme_support_get_selectors_primary_color_background',
			array(

				// Notifications.
				'.llms-notification',

				// Featured access plan.
				'.llms-access-plan.featured .llms-access-plan-content',
				'.llms-access-plan.featured .llms-access-plan-footer',

				// Checkout.
				'.llms-checkout-section',
				'.llms-checkout-wrapper form.llms-login',

				// Notices.
				'.llms-notice:not(.llms-debug)',

				// Instructor.
				'.llms-instructor-info .llms-instructors .llms-author',
				'.llms-instructor-info .llms-instructors .llms-author .avatar',

			)
		);

	}


	/**
	 * Retrieve a list of CSS selectors for elements where the primary color is used as the text color
	 *
	 * The primary color is a bright blue (#2295ff).
	 *
	 * @since 4.10.0
	 *
	 * @return string[] A list of CSS selectors.
	 */
	public static function get_selectors_primary_color_text() {

		/**
		 * Filter the list of CSS selectors for elements where the primary color is used as the text color
		 *
		 * @since 4.10.0
		 *
		 * @param string[] $selectors A list of CSS selectors.
		 */
		return apply_filters(
			'llms_theme_support_get_selectors_primary_color_background',
			array(

				// Pricing Tables.
				'.llms-access-plan-restrictions a',
				'.llms-access-plan-restrictions a:hover',

				// Loop.
				'.llms-loop-item-content .llms-loop-title:hover',

				// Donuts.
				'.llms-donut',

				// Checks on Syllabus.
				'.llms-lesson-preview.is-free .llms-lesson-complete',
				'.llms-lesson-preview.is-complete .llms-lesson-complete',
			)
		);

	}

	/**
	 * Conditionally require additional theme support classes.
	 *
	 * @since 3.37.0
	 * @since 4.3.0 Method access changed to `public`.
	 * @since 5.8.0 Added twenty-twenty-two compatibility.
	 *
	 * @return void
	 */
	public function includes() {

		switch ( get_template() ) {

			case 'twentynineteen':
				require_once 'class-llms-twenty-nineteen.php';
				break;

			case 'twentytwenty':
				require_once 'class-llms-twenty-twenty.php';
				break;

			case 'twentytwentyone':
				require_once 'class-llms-twenty-twenty-one.php';
				break;

			case 'twentytwentytwo':
				require_once 'class-llms-twenty-twenty-two.php';
				break;
		}

	}

}

return new LLMS_Theme_Support();
