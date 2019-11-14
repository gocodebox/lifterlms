<?php
/**
 * Manage Theme Support classes
 *
 * @package  LifterLMS/Classes/ThemeSupport
 *
 * @since 3.37.0
 * @version 3.37.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Twenty_Twenty class..
 *
 * @since 3.37.0
 */
class LLMS_Theme_Support {

	/**
	 * Constructor
	 *
	 * @since 3.37.0
	 *
	 * @return void
	 */
	public function __construct() {

		$this->includes();

	}

	/**
	 * Conditionally require additional theme support classes.
	 *
	 * @since 3.37.0
	 *
	 * @return void
	 */
	protected function includes() {

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
