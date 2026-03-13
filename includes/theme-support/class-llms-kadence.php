<?php
/**
 * Theme Support: Kadence
 *
 * @package LifterLMS/ThemeSupport/Classes
 *
 * @since [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Kadence class.
 *
 * @since [version]
 */
class LLMS_Kadence {

	/**
	 * Static "constructor"
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public static function init() {

		add_filter( 'llms_focus_mode_content_classes', array( __CLASS__, 'add_focus_mode_content_classes' ) );
	}

	/**
	 * Add kadence specific class for styling and background.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public static function add_focus_mode_content_classes( $classes ) {
		$classes[] = 'content-bg';

		return $classes;
	}
}

return LLMS_Kadence::init();
