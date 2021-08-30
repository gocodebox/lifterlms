<?php
/**
 * LifterLMS singleton trait
 *
 * @package LifterLMS/Traits
 *
 * @since 5.3.0
 * @version 5.3.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LifterLMS singleton trait.
 *
 * @since 5.3.0
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
	 * @since 5.3.0
	 *
	 * @return self
	 */
	public static function instance() {

		if ( is_null( self::$instance ) ) {

			if ( property_exists( __CLASS__, '_instance' ) ) {

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
