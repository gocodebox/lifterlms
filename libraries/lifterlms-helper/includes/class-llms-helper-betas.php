<?php
/**
 * Handle status beta tab
 *
 * @package LifterLMS_Helper/Classes
 *
 * @since 3.0.0
 * @version 3.4.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Helper_Betas
 *
 * @since 3.0.0
 */
class LLMS_Helper_Betas {

	/**
	 * Constructor
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function __construct() {

		add_filter( 'llms_admin_page_status_tabs', array( $this, 'add_tab' ) );

		add_action( 'llms_before_admin_page_status', array( $this, 'output_tab' ) );

		add_action( 'admin_init', array( $this, 'handle_form_submit' ) );
	}

	/**
	 * Add the tab to the nav
	 *
	 * @since 3.0.0
	 * @since 3.4.0 Use core textdomain.
	 *
	 * @param array $tabs Existing tabs.
	 * @return array
	 */
	public function add_tab( $tabs ) {
		return llms_assoc_array_insert( $tabs, 'tools', 'betas', __( 'Beta Testing', 'lifterlms' ) );
	}

	/**
	 * Handle channel subscription saves
	 *
	 * @since 3.0.0
	 * @since 3.2.0 Don't access `$_POST` directly.
	 * @since 3.2.1 Flush transient caches when a subscription changes.
	 *
	 * @return null|string Returns null when nonce errors or invalid data are submitted, otherwise returns an array of addon subscription data.
	 */
	public function handle_form_submit() {

		if ( ! llms_verify_nonce( '_llms_beta_sub_nonce', 'llms_save_channel_subscriptions' ) ) {
			return;
		}

		$subs = llms_filter_input( INPUT_POST, 'llms_channel_subscriptions', FILTER_UNSAFE_RAW, FILTER_REQUIRE_ARRAY );
		if ( ! $subs || ! is_array( $subs ) ) {
			return;
		}

		$new_subscription = false;

		foreach ( $subs as $id => $channel ) {

			$addon = llms_get_add_on( $id );
			if ( 'channel' !== $addon->get_channel_subscription() ) {
				$addon->subscribe_to_channel( sanitize_text_field( $channel ) );
				$new_subscription = true;
			}
		}

		// When a channel subscription changes also flush caches so we'll get the most recent add-on data immediately and allow upgrading immediately from wp core update screens.
		if ( $new_subscription ) {
			llms_helper_flush_cache();
		}

		return $subs;
	}

	/**
	 * Output content for the beta testing screen
	 *
	 * @since 3.0.0
	 *
	 * @param string $curr_tab Current status screen tab.
	 * @return void
	 */
	public function output_tab( $curr_tab ) {

		if ( 'betas' !== $curr_tab ) {
			return;
		}

		$addons = llms_helper_get_available_add_ons();
		array_unshift( $addons, 'lifterlms-com-lifterlms', 'lifterlms-com-lifterlms-helper' );
		include 'views/beta-testing.php';
	}
}
return new LLMS_Helper_Betas();
