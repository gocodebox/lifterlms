<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * LifterLMS Caching Helper
 * @since    [version]
 * @version  [version]
 */
class LLMS_Cache_Helper {

	/**
	 * Constructor
	 * @since    [version]
	 * @version  [version]
	 */
	public function __construct() {

		add_action( 'wp', array( $this, 'maybe_no_cache' ) );

	}

	/**
	 * Define nocache constants and set nocache headers on specified pages
	 * Checkout & Student Dashboard
	 * @return   void
	 * @since    [version]
	 * @version  [version]
	 */
	public function maybe_no_cache() {

		if ( ! is_blog_installed() ) {
			return;
		}

		$ids = apply_filters( 'llms_no_cache_page_ids', array(
			llms_get_page_id( 'checkout' ),
			llms_get_page_id( 'myaccount' )
		) );

		if ( is_page( $ids ) ) {

			llms_maybe_define_constant( 'DONOTCACHEPAGE', true );
			llms_maybe_define_constant( 'DONOTCACHEOBJECT', true );
			llms_maybe_define_constant( 'DONOTCACHEDB', true );
			nocache_headers();

		}

	}

}

return new LLMS_Cache_Helper;
