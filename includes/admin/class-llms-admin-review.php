<?php
/**
 * LLMS_Admin_Review class file
 *
 * @package LifterLMS/Admin/Classes
 *
 * @since 3.24.0
 * @version 7.1.3
 */

defined( 'ABSPATH' ) || exit;

/**
 * Admin review request.
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
	 * On LifterLMS admin screens replace the default footer text with a review request.
	 *
	 * @since 3.24.0
	 * @since 7.1.0 Show footer on our custom post types in admin, but not on the block editor.
	 * @since 7.1.3 Using strpos instead of str_starts_with for compatibility.
	 *
	 * @param string $text Default footer text.
	 * @return string
	 */
	public function admin_footer( $text ) {

		global $current_screen;

		// Show footer on our custom post types in admin, but not on the block editor.
		if (
			isset( $current_screen->post_type ) &&
			in_array( $current_screen->post_type, array( 'course', 'lesson', 'llms_review', 'llms_membership', 'llms_engagement', 'llms_order', 'llms_coupon', 'llms_voucher', 'llms_form', 'llms_achievement', 'llms_my_achievement', 'llms_certificate', 'llms_my_certificate', 'llms_email' ), true ) &&
			false === $current_screen->is_block_editor
		) {
			$show_footer = true;
		}

		// Show footer on our settings pages.
		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- No nonce verification needed here
		// phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- No sanitization needed here, we're not gonna use this value other than for checks
		// phpcs:disable WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- No unslash needed here, we're not gonna use this value other than for checks
		if (
			( ! empty( $_GET['page'] ) && strpos( $_GET['page'], 'llms-' ) === 0 ) ||
			( ! empty( $current_screen->id ) && strpos( $current_screen->id, 'lifterlms' ) === 0 )
		) {
			$show_footer = true;
		}
		// phpcs:enable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		// phpcs:enable WordPress.Security.ValidatedSanitizedInput.MissingUnslash

		// Exclude the wizard.
		if ( ! empty( $_GET['page'] ) && 'llms-setup' === $_GET['page'] ) {
			$show_footer = false;
		}
		// phpcs:enable WordPress.Security.NonceVerification.Recommended

		// Don't show footer on the Course Builder.
		if ( isset( $current_screen->base ) && 'admin_page_llms-course-builder' === $current_screen->base ) {
			$show_footer = false;
		}

		// Conditionally filter footer text with our content.
		if ( ! empty( $show_footer ) ) {

			$url  = 'https://wordpress.org/support/plugin/lifterlms/reviews/?filter=5#new-post';
			$text = sprintf(
				wp_kses(
					/* Translators: %1$s = LifterLMS plugin name; %2$s = WP.org review link; %3$s = WP.org review link. */
					__( 'Please rate %1$s <a class="llms-rating-stars" href="%2$s" target="_blank" rel="noopener noreferrer">&#9733;&#9733;&#9733;&#9733;&#9733;</a> on <a href="%3$s" target="_blank" rel="noopener">WordPress.org</a> to help us spread the word. Thank you from the LifterLMS team!', 'lifterlms' ),
					array(
						'a' => array(
							'class'  => array(),
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
	 * @since 5.9.0 Stop using deprecated `FILTER_SANITIZE_STRING`.
	 *
	 * @return void
	 */
	public function dismiss() {

		if ( ! current_user_can( 'manage_lifterlms' ) || ! llms_verify_nonce( 'nonce', 'llms-admin-review-request-dismiss' ) ) {
			wp_die();
		}

		$success = llms_parse_bool( llms_filter_input( INPUT_POST, 'success' ) );

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
		// This will be 0 if the review time/dismissed check above fails.
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
