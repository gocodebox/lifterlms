<?php
/**
 * LifterLMS Caching Helper
 *
 * @package LifterLMS/Classes
 *
 * @since 3.15.0
 * @version 4.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Cache_Helper
 *
 * @since 3.15.0
 * @since 4.0.0 Add WP_Object_Cache API helper.
 */
class LLMS_Cache_Helper {

	/**
	 * Constructor
	 *
	 * @since 3.15.0
	 *
	 * @return void
	 */
	public function __construct() {

		add_action( 'wp', array( $this, 'maybe_no_cache' ) );

	}

	/**
	 * Retrieve a cache prefix that can be used with WP_Object_Cache methods
	 *
	 * Using a cache prefix allows simple invalidation of all items with the same
	 * prefix simply by updating the prefix.
	 *
	 * The "prefix" is microtime(), if we wish to invalidate all items in the prefix group
	 * we call the method again with `$invalidate=true` which updates the prefix to the current
	 * microtime(), thereby invalidating the entire cache group.
	 *
	 * @since 4.0.0
	 *
	 * @link https://core.trac.wordpress.org/ticket/4476#comment:10
	 *
	 * @param string $group Cache group name.
	 * @return string
	 */
	public static function get_prefix( $group ) {

		$key    = sprintf( 'llms_%s_cache_prefix', $group );
		$prefix = wp_cache_get( $key, $group );

		if ( false === $prefix ) {
			$prefix = microtime();
			wp_cache_set( $key, $prefix, $group );
		}

		return sprintf( 'llms_cache_%s_', $prefix );

	}

	/**
	 * Invalidate a cache group prefix.
	 *
	 * @since 4.0.0
	 *
	 * @link https://core.trac.wordpress.org/ticket/4476#comment:10
	 *
	 * @param string $group Cache group name.
	 * @return void
	 */
	public static function invalidate_group( $group ) {
		wp_cache_set( sprintf( 'llms_%s_cache_prefix', $group ), microtime(), $group );
	}

	/**
	 * Define nocache constants and set nocache headers on specified pages
	 *
	 * This prevents caching for the Checkout & Student Dashboard pages.
	 *
	 * @since 3.15.0
	 *
	 * @return void
	 */
	public function maybe_no_cache() {

		if ( ! is_blog_installed() ) {
			return;
		}

		/**
		 * Filter the list of pages that LifterLMS will send nocache headers for
		 *
		 * @since 3.15..0
		 *
		 * @param int[] $ids List of WP_Post IDs.
		 */
		$ids = apply_filters(
			'llms_no_cache_page_ids',
			array(
				llms_get_page_id( 'checkout' ),
				llms_get_page_id( 'myaccount' ),
			)
		);

		if ( is_page( $ids ) ) {

			llms_maybe_define_constant( 'DONOTCACHEPAGE', true );
			llms_maybe_define_constant( 'DONOTCACHEOBJECT', true );
			llms_maybe_define_constant( 'DONOTCACHEDB', true );
			nocache_headers();

		}

	}

}

return new LLMS_Cache_Helper();
