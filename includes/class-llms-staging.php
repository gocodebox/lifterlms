<?php
/**
 * Handle warnings and notices when staging is enabled.
 *
 * @package  LifterLMS/Classes
 *
 * @since 3.32.0
 * @version 3.35.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Staging class.
 *
 * @since 3.32.0
 * @since 3.35.0 Sanitize input data.
 */
class LLMS_Staging {

	/**
	 * Static Constructor.
	 *
	 * @since 3.32.0
	 *
	 * @return void
	 */
	public static function init() {

		add_action( 'admin_menu', array( __CLASS__, 'menu_warning' ) );
		add_action( 'admin_init', array( __CLASS__, 'handle_staging_notice_actions' ) );

	}

	/**
	 * Handle the action buttons present in the recurring payments staging notice.
	 *
	 * @since 3.32.0
	 * @since 3.35.0 Sanitize input data.
	 *
	 * @return void
	 */
	public static function handle_staging_notice_actions() {

		if ( ! isset( $_GET['llms-staging-status'] ) || ! isset( $_GET['_llms_staging_nonce'] ) ) {
			return;
		}

		if ( ! llms_verify_nonce( '_llms_staging_nonce', 'llms_staging_status', 'GET' ) ) {
			wp_die( __( 'Action failed. Please refresh the page and retry.', 'lifterlms' ) );
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'Cheatin&#8217; huh?', 'lifterlms' ) );
		}

		if ( 'enable' === $_GET['llms-staging-status'] ) {
			LLMS_Site::set_lock_url();
			LLMS_Site::update_feature( 'recurring_payments', true );
		} elseif ( 'disable' === $_GET['llms-staging-status'] ) {
			LLMS_Site::clear_lock_url();
			LLMS_Site::update_feature( 'recurring_payments', false );
			update_option( 'llms_site_url_ignore', 'yes' );
		}

		LLMS_Admin_Notices::delete_notice( 'maybe-staging' );

		if ( ! empty( $_SERVER['HTTP_REFERER'] ) ) {
			llms_redirect_and_exit( sanitize_text_field( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) );
		}

	}

	/**
	 * Adds a "bubble" to the "Orders" menu item when recurring payments are disabled.
	 *
	 * @since 3.32.0
	 *
	 * @return void
	 */
	public static function menu_warning() {

		if ( LLMS_Site::get_feature( 'recurring_payments' ) ) {
			return;
		}

		global $menu;
		foreach ( $menu as $index => $item ) {

			if ( 'edit.php?post_type=llms_order' === $item[2] ) {
				$menu[ $index ][0] .= ' <span class="update-plugins">' . esc_html__( 'Staging', 'lifterlms' ) . '</span>';
			}
		}

	}

}

return LLMS_Staging::init();
