<?php
/**
 * LLMS_Admin_Review class file
 *
 * @package LifterLMS/Admin/Classes
 *
 * @since 3.24.0
 * @version 4.14.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Admin review request
 *
 * Handles UI updates to the admin panel which request users to rate & review the
 * LifterLMS plugin on WordPress.org.
 *
 * Please say nice things about us.
 *
 * @since 3.24.0
 */
class LLMS_Admin_Review {

	/**
	 * Constructor
	 *
	 * @since 3.24.0
	 *
	 * @return void
	 */
	public function __construct() {

		add_filter( 'admin_footer_text', array( $this, 'admin_footer' ), 1 );
		add_action( 'admin_notices', array( $this, 'maybe_show_notice' ) );

		add_action( 'wp_ajax_llms_review_dismiss', array( $this, 'dismiss' ) );

	}

	/**
	 * On LifterLMS admin screens replace the default footer text with a review request
	 *
	 * @since 3.24.0
	 *
	 * @param string $text Default footer text.
	 * @return string
	 */
	public function admin_footer( $text ) {

		global $current_screen;

		if ( ! empty( $current_screen->id ) && false !== strpos( $current_screen->id, 'lifterlms' ) ) {

			$url  = 'https://wordpress.org/support/plugin/lifterlms/reviews/?filter=5#new-post';
			$text = sprintf(
				wp_kses(
					/* Translators: %1$s = LifterLMS plugin name; %2$s = WP.org review link; %3$s = WP.org review link. */
					__( 'Please rate %1$s <a href="%2$s" target="_blank" rel="noopener noreferrer">&#9733;&#9733;&#9733;&#9733;&#9733;</a> on <a href="%3$s" target="_blank" rel="noopener">WordPress.org</a> to help us spread the word. Thank you from the LifterLMS team!', 'lifterlms' ),
					array(
						'a' => array(
							'href'   => array(),
							'target' => array(),
							'rel'    => array(),
						),
					)
				),
				'<strong>LifterLMS</strong>',
				$url,
				$url
			);

		}

		return $text;

	}

	/**
	 * AJAX callback for dismissing the notice
	 *
	 * @since 3.24.0
	 * @since 4.14.0 Only users with `manager_lifterlms` caps can dismiss and added nonce verification.
	 *               Use `llms_filter_input()` in favor of `filter_input()`.
	 *
	 * @return void
	 */
	public function dismiss() {

		if ( ! current_user_can( 'manage_lifterlms' ) || ! llms_verify_nonce( 'nonce', 'llms-admin-review-request-dismiss' ) ) {
			wp_die();
		}

		$success = llms_parse_bool( llms_filter_input( INPUT_POST, 'success', FILTER_SANITIZE_STRING ) );

		update_option(
			'llms_review',
			array(
				'time'      => time(),
				'dismissed' => true,
				'success'   => $success ? 'yes' : 'no',
			)
		);

		wp_die();

	}

	/**
	 * Determine if the notice should be displayed and display it
	 *
	 * @since 3.24.0
	 * @since 4.14.0 Only show to users with `manage_lifterlms` instead of only admins.
	 *
	 * @return null|false|void Returns `null` when there are permission issues, `false` when the notification is not set to be
	 *                         displayed, and has no return when the notice is successfully displayed.
	 */
	public function maybe_show_notice() {

		// Only show review request to admins.
		if ( ! current_user_can( 'manage_lifterlms' ) ) {
			return null;
		}

		// Verify that we can do a check for reviews.
		$review      = get_option( 'llms_review' );
		$time        = time();
		$enrollments = 0;

		// No review info stored, create a stub.
		if ( ! $review ) {

			update_option(
				'llms_review',
				array(
					'time'      => $time,
					'dismissed' => false,
				)
			);
			return false;

		}

		// Review has not been dismissed and LifterLMS has been installed at least a week.
		if ( ( isset( $review['dismissed'] ) && ! $review['dismissed'] ) && isset( $review['time'] ) && ( $review['time'] + WEEK_IN_SECONDS <= $time ) ) {

			// Show if the enrollments threshold is reached.
			global $wpdb;
			$enrollments = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}lifterlms_user_postmeta WHERE meta_key = '_status' AND meta_value = 'enrolled'" ); // no-cache ok.

		}

		// Only load if we have 30 or more enrollments.
		if ( $enrollments < 30 ) {
			return false;
		}

		$enrollments = self::round_down( $enrollments );

		include 'views/notices/review-request.php';

	}

	/**
	 * Round a number down to a big-ish round number
	 *
	 * @since 3.24.0
	 * @since 4.14.0 Numbers less than 10 are not rounded & numbers less than 100 are rounded to the nearest 10.
	 *
	 * @param int $number Input number.
	 * @return int
	 */
	public static function round_down( $number ) {

		if ( $number < 10 ) {
			return $number;
		}

		if ( $number < 100 ) {
			$number = floor( $number / 10 ) * 10;
		} elseif ( $number < 1000 ) {
			$number = floor( $number / 100 ) * 100;
		} elseif ( $number < 10000 ) {
			$number = floor( $number / 1000 ) * 1000;
		} else {
			$number = 10000;
		}

		return $number;

	}

}

return new LLMS_Admin_Review();
