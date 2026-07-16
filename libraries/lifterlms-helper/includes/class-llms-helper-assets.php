<?php
/**
 * Enqueue Scripts & Styles
 *
 * @package LifterLMS_Helper/Classes
 *
 * @since 3.0.0
 * @version 3.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Helper_Assets
 *
 * @since 3.0.0
 */
class LLMS_Helper_Assets {

	/**
	 * Constructor
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function __construct() {

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
	}

	/**
	 * Register, enqueue, & localize
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function enqueue() {

		$load   = false;
		$screen = get_current_screen();
		if ( 'lifterlms_page_llms-status' === $screen->id && isset( $_GET['tab'] ) && 'betas' === $_GET['tab'] ) {
			$load = true;
		} elseif ( 'lifterlms_page_llms-add-ons' === $screen->id ) {
			$load = true;
		}

		if ( ! $load ) {
			return;
		}

		wp_register_style( 'llms-helper', LLMS_HELPER_PLUGIN_URL . 'assets/css/llms-helper' . LLMS_ASSETS_SUFFIX . '.css', array(), LLMS_HELPER_VERSION );
		wp_enqueue_style( 'llms-helper' );

		wp_style_add_data( 'llms-sl', 'rtl', 'replace' );
		wp_style_add_data( 'llms-sl', 'suffix', LLMS_ASSETS_SUFFIX );
	}
}
return new LLMS_Helper_Assets();
