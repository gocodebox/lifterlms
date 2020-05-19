<?php
/**
 * Singleton class trait.
 *
 * @package  LifterLMS_REST/Classes
 *
 * @since 1.0.0-beta.1
 * @version 1.0.0-beta.1
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_REST_Trait_Singleton class..
 *
 * @since 1.0.0-beta.1
 */
trait LLMS_REST_Trait_Singleton {

	/**
	 * Singleton instance of the class.
	 *
	 * @var obj
	 */
	private static $instance = null;

	/**
	 * Private Constructor.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	private function __construct() {}

	/**
	 * Singleton Instance of the LifterLMS_REST_API class.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return obj instance of the LifterLMS_REST_API class.
	 */
	public static function instance() {

		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;

	}

}
