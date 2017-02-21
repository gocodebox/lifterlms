<?php
/**
 * Localization Functions
 * Currently only used to translate strings output by Javascript functions
 * More robust features will be added in the future
 *
 * @since   2.7.3
 * @version 3.4.4
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_l10n {

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
	 * @version 3.4.0
	 */
	public static function get_js_strings( $json = true ) {

		// add translatable strings to this array
		// alphabatize the array so we can quickly find strings
		// include references to the JS file where the string is used so we can cleanup if needed in the future
		$strings = array(

			/**
			 * file: _private/js/llms-ajax.js
			 */
			'Loading Question...' => __( 'Loading Question...', 'lifterlms' ),
			'Loading Quiz Results...' => __( 'Loading Quiz Results...', 'lifterlms' ),

			/**
			 * file: _private/js/llms-metabox-product.js
			 * @since    3.0.0
			 * @version  3.0.0
			 */
			'There was an error loading the necessary resources. Please try again.' => __( 'There was an error loading the necessary resources. Please try again.', 'lifterlms' ),

			/**
			 * file: _private/js/llms-metaboxes.js
			 * @since    3.0.0
			 * @version  3.4.0
			 */
			'Cancel' => __( 'Cancel', 'lifterlms' ),
			'Copy this code and paste it into the desired area' => __( 'Copy this code and paste it into the desired area', 'lifterlms' ),
			'membership_bulk_enrollment_warning' => __( 'Click okay to enroll all active members into the selected course. Enrollment will take place in the background and you may leave your site after confirmation. This action cannot be undone!', 'lifterlms' ),
			'Record a Manual Payment' => __( 'Record a Manual Payment', 'lifterlms' ),
			'Refund' => __( 'Refund', 'lifterlms' ),

			/**
			 * file: _private/js/app/llms-password-strength.js
			 * @since    3.0.0
			 * @version  3.0.0
			 */
			'Medium' => _x( 'Medium', 'password strength meter', 'lifterlms' ),
			'Mismatch' => _x( 'Mismatch', 'password strength meter', 'lifterlms' ),
			'Strong' => _x( 'Strong', 'password strength meter', 'lifterlms' ),
			'There is an issue with your chosen password.' => __( 'There is an issue with your chosen password.', 'lifterlms' ),
			'Too Short' => _x( 'Too Short', 'password length validation', 'lifterlms' ),
			'Very Weak' => _x( 'Very Weak', 'password strength meter', 'lifterlms' ),
			'Weak' => _x( 'Weak', 'password strength meter', 'lifterlms' ),

			/**
			 * file: _private/js/app/llms-pricing-tables.js
			 */
			'This plan is for members only. Click the links above to learn more.' => __( 'This plan is for members only. Click the links above to learn more.', 'lifterlms' ),

			/**
			 * file: _private/js/app/llms-syllabus.js
			 * @since   3.2.4
			 * @version 3.2.4
			 */
			'You do not have permission to access to this content' => __( 'You do not have permission to access to this content', 'lifterlms' ),

			/**
			 * file: _private/js/app/llms-quiz.js
			 * @since   2.7.3
			 * @version 2.7.4
			 */
			'Hide Summary' => __( 'Hide Summary', 'lifterlms' ),
			'View Summary' => __( 'View Summary', 'lifterlms' ),
			'You must enter an answer to continue.' => __( 'You must enter an answer to continue.', 'lifterlms' ),
		);

		// add strings that should only be translated on the admin panel
		if ( is_admin() ) {

			$admin_strings = array(

				/**
				 * file: _private/js/llms-admin.js
				 * @since   3.4.4
				 * @version 3.4.4
				 */
				'An unknown error occurred, please try again.' => __( 'An unknown error occurred, please try again.', 'lifterlms' ),
				'delete_quiz_attempt' => __( 'Are you sure you want to delete this quiz attempt? This action cannot be undone!', 'lifterlms' ),

				/**
				 * file: _private/js/llms-analytics.js
				 * @since   3.0.0
				 * @version 3.0.0
				 */
				'Error' => __( 'Error', 'lifterlms' ),
				'Filter by Student(s)' => __( 'Filter by Student(s)', 'lifterlms' ),
				'Request timed out' => __( 'Request timed out', 'lifterlms' ),
				'Retry' => __( 'Retry', 'lifterlms' ),

			);

			$strings = array_merge( $strings, apply_filters( 'lifterlms_js_l10n_admin', $admin_strings ) );

		}

		// allow filtering so extensions don't have to implement their own l10n functions
		$strings = apply_filters( 'lifterlms_js_l10n', $strings );

		if ( true === $json ) {

			return json_encode( $strings );

		} else {

			return $strings;

		}

	}

}
