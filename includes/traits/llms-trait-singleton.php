<?php
/**
 * LifterLMS singleton trait
 *
 * @package LifterLMS/Traits
 *
 * @since 5.3.0
 * @version [version]
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
	 * @since 5.3.0 Introduced.
	 * @since [version] Removed backward compatible use of the removed `$_instance` property.
	 *
	 * @return self
	 */
	public static function instance() {

		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}
