<?php
/**
 * Localization Functions
 * Currently only used to translate strings output by Javascript functions
 * More robust features will be added in the future
 *
 * @since   2.7.3
 * @version 3.14.8
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_L10n {

	/**
	 * Create an object of translatable strings
	 *
	 * This object is added to the LLMS.l10n JS object
	 * the text used in JS *MUST* exactly match the string found in this object
	 * which is redundant but is the best and lightest weight solution
	 * I could dream up quickly
	 *
	 * @param  boolean $json if true, convert to JSON, otherwise return the array
	 * @return string|array
	 *
	 * @since   2.7.3
	 * @version 3.14.8
	 */
	public static function get_js_strings( $json = true ) {

		// add translatable strings to this array
		// alphabatize the array so we can quickly find strings
		// include references to the JS file where the string is used so we can cleanup if needed in the future
		$strings = array(

			/**
			 * file: _private/js/llms-metabox-product.js
			 * @since    3.0.0
			 * @version  3.0.0
			 */
			'There was an error loading the necessary resources. Please try again.' => esc_html__( 'There was an error loading the necessary resources. Please try again.', 'lifterlms' ),

			/**
			 * file: _private/js/llms-metaboxes.js
			 * @since    3.0.0
			 * @version  3.4.0
			 */
			'Cancel' => esc_html__( 'Cancel', 'lifterlms' ),
			'Copy this code and paste it into the desired area' => esc_html__( 'Copy this code and paste it into the desired area', 'lifterlms' ),
			'membership_bulk_enrollment_warning' => esc_html__( 'Click okay to enroll all active members into the selected course. Enrollment will take place in the background and you may leave your site after confirmation. This action cannot be undone!', 'lifterlms' ),
			'Record a Manual Payment' => esc_html__( 'Record a Manual Payment', 'lifterlms' ),
			'Refund' => esc_html__( 'Refund', 'lifterlms' ),

			/**
			 * file: _private/js/app/llms-password-strength.js
			 * @since    3.0.0
			 * @version  3.0.0
			 */
			'Medium' => _x( 'Medium', 'password strength meter', 'lifterlms' ),
			'Mismatch' => _x( 'Mismatch', 'password strength meter', 'lifterlms' ),
			'Strong' => _x( 'Strong', 'password strength meter', 'lifterlms' ),
			'There is an issue with your chosen password.' => esc_html__( 'There is an issue with your chosen password.', 'lifterlms' ),
			'Too Short' => _x( 'Too Short', 'password length validation', 'lifterlms' ),
			'Very Weak' => _x( 'Very Weak', 'password strength meter', 'lifterlms' ),
			'Weak' => _x( 'Weak', 'password strength meter', 'lifterlms' ),

			/**
			 * file: _private/js/app/llms-pricing-tables.js
			 * @since    3.0.0
			 * @version  3.9.1
			 */
			'Members Only Pricing' => esc_html__( 'Members Only Pricing', 'lifterlms' ),

			/**
			 * file: _private/js/app/llms-student-dashboard.js
			 * @since    3.10.0
			 * @version  3.10.0
			 */
			'Are you sure you want to cancel your subscription?' => esc_html__( 'Are you sure you want to cancel your subscription?', 'lifterlms' ),

			/**
			 * file: _private/js/app/llms-syllabus.js
			 * @since   3.2.4
			 * @version 3.2.4
			 */
			'You do not have permission to access to this content' => esc_html__( 'You do not have permission to access to this content', 'lifterlms' ),

			/**
			 * file: _private/js/app/llms-quiz.js
			 * @since   2.7.3
			 * @version 3.9.0
			 */
			'An unknown error occurred. Please try again.' => esc_html__( 'An unknown error occurred. Please try again.', 'lifterlms' ),
			'Hide Summary' => esc_html__( 'Hide Summary', 'lifterlms' ),
			'Loading...' => esc_html__( 'Loading...', 'lifterlms' ),
			'Loading Question...' => esc_html__( 'Loading Question...', 'lifterlms' ),
			'Loading Quiz...' => esc_html__( 'Loading Quiz...', 'lifterlms' ),
			'Loading Quiz Results...' => esc_html__( 'Loading Quiz Results...', 'lifterlms' ),
			'View Summary' => esc_html__( 'View Summary', 'lifterlms' ),
			'You must enter an answer to continue.' => esc_html__( 'You must enter an answer to continue.', 'lifterlms' ),

		);

		// add strings that should only be translated on the admin panel
		if ( is_admin() ) {

			$admin_strings = array(
				/**
				 * file: _private/js/llms-admin.js
				 * @since   3.4.4
				 * @version 3.4.4
				 */
				'An unknown error occurred, please try again.' => esc_html__( 'An unknown error occurred, please try again.', 'lifterlms' ),
				'delete_quiz_attempt' => esc_html__( 'Are you sure you want to delete this quiz attempt? This action cannot be undone!', 'lifterlms' ),

				/**
				 * file: _private/js/llms-builder.js
				 * @since   3.14.8
				 * @version 3.14.8
				 */
				'Add an Existing Lesson' => esc_html__( 'Add an Existing Lesson', 'lifterlms' ),
				'Are you sure you want to permanently delete this lesson?' => esc_html__( 'Are you sure you want to permanently delete this lesson?' , 'lifterlms' ),
				'If you leave now your changes may not be saved!' => esc_html__( 'If you leave now your changes may not be saved!', 'lifterlms' ),
				'New Lesson' => esc_html__( 'New Lesson', 'lifterlms' ),
				'Search for existing lessons...' => esc_html__( 'Search for existing lessons...' , 'lifterlms' ),
				'You must remove all lessons before deleting a section.' => esc_html__( 'You must remove all lessons before deleting a section.', 'lifterlms' ),

				/**
				 * file: _private/js/llms-analytics.js
				 * @since   3.0.0
				 * @version 3.0.0
				 */
				'Error' => esc_html__( 'Error', 'lifterlms' ),
				'Filter by Student(s)' => esc_html__( 'Filter by Student(s)', 'lifterlms' ),
				'Request timed out' => esc_html__( 'Request timed out', 'lifterlms' ),
				'Retry' => esc_html__( 'Retry', 'lifterlms' ),

				/**
				 * file: _private/js/llms-builder.js
				 * @since   3.13.0
				 * @version 3.13.0
				 */
				'Are you sure you want to permanently delete this lesson?' => esc_html__( 'Are you sure you want to permanently delete this lesson?', 'lifterlms' ),
				'If you leave now your changes may not be saved!' => esc_html__( 'If you leave now your changes may not be saved!', 'lifterlms' ),
				'You must remove all lessons before deleting a section.' => esc_html__( 'You must remove all lessons before deleting a section.', 'lifterlms' ),

				/**
				 * file: _private/js/partials/_metabox-field-repeater.js
				 * @since    3.11.0
				 * @version  3.13.0
				 */
				'Are you sure you want to delete this row? This cannot be undone.' => esc_html__( 'Are you sure you want to delete this template? This cannot be undone.', 'lifterlms' ),

			);

			$strings = array_merge( $strings, apply_filters( 'lifterlms_js_l10n_admin', $admin_strings ) );

		}// End if().

		// allow filtering so extensions don't have to implement their own l10n functions
		$strings = apply_filters( 'lifterlms_js_l10n', $strings );

		if ( true === $json ) {

			return json_encode( $strings );

		} else {

			return $strings;

		}

	}

}
