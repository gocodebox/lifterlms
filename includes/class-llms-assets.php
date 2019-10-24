<?php
/**
 * Handle static LifterLMS Assets.
 *
 * @package  LifterLMS/Classes
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Assets class..
 *
 * @since [version]
 */
class LLMS_Assets {

	/**
	 * Static Constructor.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public static function init() {

		add_action( 'init', array( __CLASS__, 'register' ) );

	}

	/**
	 * Registers scripts on both the frontend and backend.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public static function register() {

		wp_register_style( 'llms-select2-styles', LLMS_PLUGIN_URL . 'assets/vendor/select2/css/select2' . LLMS_ASSETS_SUFFIX . '.css', array(), '4.0.3' );
		wp_register_script( 'llms-select2', LLMS_PLUGIN_URL . 'assets/vendor/select2/js/select2' . LLMS_ASSETS_SUFFIX . '.js', array( 'jquery' ), '4.0.3', true );

	}

}

return LLMS_Assets::init();
