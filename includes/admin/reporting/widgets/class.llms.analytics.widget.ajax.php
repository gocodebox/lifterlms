<?php
/**
 * Register WordPress AJAX methods for Analytics Widgets
 *
 * @since  3.0.0
 * @version 3.35.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Analytics_Widget_Ajax
 *
 * @since  3.0.0
 * @since 3.35.0 Sanitize `$_REQUEST` data.
 */
class LLMS_Analytics_Widget_Ajax {

	/**
	 * Constructor
	 *
	 * @since 3.0.0
	 * @since 3.16.8 Unknown.
	 * @since 3.35.0 Sanitize `$_REQUEST` data.
	 *
	 * @return void
	 */
	public function __construct() {

		// only proceed if we're doing ajax
		if ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX || ! isset( $_REQUEST['action'] ) ) {
			return;
		}

		$methods = array(

			// sales
			'coupons',
			'discounts',
			'refunded',
			'refunds',
			'revenue',
			'sales',
			'sold',

			// enrollments
			'enrollments',
			'registrations',
			'lessoncompletions',
			'coursecompletions',

		);

		// include the abstract
		include LLMS_PLUGIN_DIR . 'includes/abstracts/abstract.llms.analytics.widget.php';

		$method = str_replace( 'llms_widget_', '', sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) );

		$file = LLMS_PLUGIN_DIR . 'includes/admin/reporting/widgets/class.llms.analytics.widget.' . $method . '.php';

		if ( file_exists( $file ) ) {

			include $file;
			$class = 'LLMS_Analytics_' . ucwords( $method ) . '_Widget';
			add_action( 'wp_ajax_llms_widget_' . $method, array( new $class(), 'output' ) );

		}

	}

}

return new LLMS_Analytics_Widget_Ajax();
