<?php
/**
 * Manage Theme Support classes
 *
 * @package LifterLMS/ThemeSupport/Classes
 *
 * @since 3.37.0
 * @version 4.3.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Twenty_Twenty class.
 *
 * @since 3.37.0
 */
class LLMS_Theme_Support {

	/**
	 * Constructor
	 *
	 * @since 3.37.0
	 * @since 4.3.0 Load includes during `after_setup_theme` instead of `plugins_loaded`.
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'after_setup_theme', array( $this, 'includes' ) );
	}

	/**
	 * Conditionally require additional theme support classes.
	 *
	 * @since 3.37.0
	 * @since 4.3.0 Method access changed to `public`.
	 *
	 * @return void
	 */
	public function includes() {

		switch ( get_template() ) {

			case 'twentynineteen':
				require_once 'class-llms-twenty-nineteen.php';
				break;

			case 'twentytwenty':
				require_once 'class-llms-twenty-twenty.php';
				break;

		}

	}

}

return new LLMS_Theme_Support();
