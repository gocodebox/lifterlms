<?php
/**
 * LifterLMS Caching Helper
 *
 * @package LifterLMS/Classes
 *
 * @since 3.15.0
 * @version 3.15.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Cache_Helper
 *
 * @since 3.15.0]
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
	 * Define nocache constants and set nocache headers on specified pages
	 *
	 * This prevents caching for the Checkout & Student Dashboard pages.
	 *
	 * @since 3.15.0
	 *
	 * @return   void
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
