<?php
/**
 * LifterLMS singleton trait
 *
 * @package LifterLMS/Traits
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * LifterLMS singleton trait.
 *
 * @since [version]
 */
trait LLMS_Trait_Singleton {

	/**
	 * Singleton instance of the class.
	 *
	 * @var object
	 */
	private static $instance = null;

	/**
	 * Returns a singleton instance of the class that uses this trait.
	 *
	 * @since [version]
	 *
	 * @return object
	 */
	public static function instance() {

		if ( is_null( self::$instance ) ) {

			if ( property_exists( __CLASS__, '_instance') ) {

				if ( is_null( self::$_instance ) ) {

					self::$instance = new self();
					self::$_instance = self::$instance;
				} else {
					self::$instance = self::$_instance;
				}
			} else {
				self::$instance = new self();
			}
		}

		return self::$instance;
	}
}
