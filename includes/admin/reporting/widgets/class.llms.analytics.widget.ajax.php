<?php
/**
 * Register WordPress AJAX methods for Analytics Widgets
 *
 * @package LifterLMS/Admin/Reporting/Widgets/Classes
 *
 * @since 3.0.0
 * @version 7.3.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Analytics_Widget_Ajax class
 *
 * @since 3.0.0
 * @since 3.35.0 Sanitize `$_REQUEST` data.
 */
class LLMS_Analytics_Widget_Ajax {

	/**
	 * Constructor.
	 *
	 * @since 3.0.0
	 * @since 3.16.8 Unknown.
	 * @since 3.35.0 Sanitize `$_REQUEST` data.
	 * @since 6.0.0 Removed loading of class files that don't instantiate their class in favor of autoloading.
	 * @since 7.3.0 Ajax calls are now handled by `LLMS_Analytics_Widget_Ajax::handle()` method.
	 *
	 * @return void
	 */
	public function __construct() {

		// Only proceed if we're doing ajax.
		if ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX || ! isset( $_REQUEST['action'] ) ) {
			return;
		}

		$methods = array(

			// Sales.
			'coupons',
			'discounts',
			'refunded',
			'refunds',
			'revenue',
			'sales',
			'sold',

			// Enrollments.
			'enrollments',
			'registrations',
			'lessoncompletions',
			'coursecompletions',
		);

		$method = str_replace( 'llms_widget_', '', sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) );

		$file = LLMS_PLUGIN_DIR . 'includes/admin/reporting/widgets/class.llms.analytics.widget.' . $method . '.php';

		if ( file_exists( $file ) ) {
			add_action( 'wp_ajax_llms_widget_' . $method, array( __CLASS__, 'handle' ) );
		}
	}

	/**
	 * Handles the AJAX request.
	 *
	 * @since 7.3.0
	 *
	 * @return void
	 */
	public static function handle() {

		// Make sure we are getting a valid AJAX request.
		check_ajax_referer( LLMS_Ajax::NONCE );

		$method = str_replace(
			'llms_widget_',
			'',
			sanitize_text_field( wp_unslash( $_REQUEST['action'] ?? '' ) )
		);
		$class  = 'LLMS_Analytics_' . ucwords( $method ) . '_Widget';

		if ( ! class_exists( $class ) ) {
			return;
		}

		$widget           = new $class();
		$can_be_processed = $widget->can_be_processed();

		if ( is_wp_error( $can_be_processed ) ) {
			wp_send_json_error( $can_be_processed );
			wp_die();
		}

		$widget->output();

	}

}

return new LLMS_Analytics_Widget_Ajax();
