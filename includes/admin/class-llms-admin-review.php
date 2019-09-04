<?php
defined( 'ABSPATH' ) || exit;

/**
 * Please say nice things about us
 *
 * @since    3.24.0
 * @version  3.24.0
 */
class LLMS_Admin_Review {

	/**
	 * Constructor
	 *
	 * @since    3.24.0
	 * @version  3.24.0
	 */
	public function __construct() {

		add_filter( 'admin_footer_text', array( $this, 'admin_footer' ), 1 );
		add_action( 'admin_notices', array( $this, 'maybe_show_notice' ) );

		add_action( 'wp_ajax_llms_review_dismiss', array( $this, 'dismiss' ) );

	}

	/**
	 * On LifterLMS admin screens replace the default footer text with a review request
	 *
	 * @param   string $text default footer text
	 * @return  string
	 * @since   3.24.0
	 * @version 3.24.0
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
	 * @return  void
	 * @since   3.24.0
	 * @version 3.24.0
	 */
	public function dismiss() {

		$success = llms_parse_bool( filter_input( INPUT_POST, 'success', FILTER_SANITIZE_STRING ) );

		update_option(
			'llms_review',
			array(
				'time'      => time(),
				'dismissed' => true,
				'success'   => $success ? 'yes' : 'no',
			)
		);

		die;

	}

	/**
	 * Determine if the notice should be displayed and display it
	 *
	 * @return  void
	 * @since   3.24.0
	 * @version 3.24.0
	 */
	public function maybe_show_notice() {

		// Only show review request to admins.
		if ( ! is_super_admin() ) {
			return;
		}

		// Verify that we can do a check for reviews.
		$review      = get_option( 'llms_review' );
		$time        = time();
		$enrollments = 0;

		// No review info stored, create a stub.
		if ( ! $review ) {

			$review = array(
				'time'      => $time,
				'dismissed' => false,
			);
			update_option( 'llms_review', $review );

		} else {

			// Review has not been dismissed and LifterLMS has been installed at least a week.
			if ( ( isset( $review['dismissed'] ) && ! $review['dismissed'] ) && isset( $review['time'] ) && ( $review['time'] + WEEK_IN_SECONDS <= $time ) ) {

				// Show if there are more than 50 enrollments in the db.
				global $wpdb;
				$enrollments = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}lifterlms_user_postmeta WHERE meta_key = '_status' AND meta_value = 'enrolled'" );

			}
		}

		// Only load if we have 50 or more enrollments
		if ( $enrollments < 50 ) {
			return;
		}

		$enrollments = self::round_down( $enrollments );

		include 'views/notices/review-request.php';

	}

	/**
	 * Round a number down to a big-ish round number
	 *
	 * @param   int $number input number
	 * @return  int
	 * @since   3.24.0
	 * @version 3.24.0
	 */
	public static function round_down( $number ) {

		if ( $number < 50 ) {
			return $number;
		}

		if ( $number < 100 ) {
			$number = 50;
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
