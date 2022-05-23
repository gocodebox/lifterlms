<?php
/**
 * LifterLMS Caching Helper
 *
 * @package LifterLMS/Classes
 *
 * @since 3.15.0
 * @version 6.6.0
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
	 * Sets a browser cookie that tells WP Engine to exclude a page from server caching.
	 *
	 * @see https://wpengine.com/support/cache/#Default_Cache_Exclusions
	 * @see https://wpengine.com/support/determining-wp-engine-environment/
	 *
	 * @since 6.6.0
	 *
	 * @param int|WP_Post $post Optional. Post ID or post object. Default is the global `$post`.
	 *
	 * @return void
	 */
	private function exclude_page_from_wpe_server_cache( $post = null ) {

		if ( function_exists( 'is_wpe' ) && is_wpe() ) {
			/*
			 * If "Settings -> Permalinks" is "Plain", i.e. the `permalink_structure` option is '',
			 * allow the entire site to be cached by WP Engine.
			 * Note: This will prevent users from being able to successfully use the "Lost your password?" feature.
			 */
			if ( isset( $GLOBALS['wp_rewrite'] ) && ! $GLOBALS['wp_rewrite']->using_permalinks() ) {
				return;
			}

			$path = wp_parse_url( get_permalink( $post ), PHP_URL_PATH );
			llms_setcookie( 'wordpress_wpe_no_cache', '1', 0, $path, COOKIE_DOMAIN, is_ssl(), true );
		}
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
	 * @since 6.4.0 Force no caching on quiz pages.
	 *              Added 'no-store' to the default WordPress nocache headers.
	 * @since 6.6.0 Added WP Engine server-side cache exclusions.
	 *
	 * @return void
	 */
	public function maybe_no_cache() {

		if ( ! is_blog_installed() ) {
			return;
		}

		/**
		 * Filter the list of pages that LifterLMS will send nocache headers for.
		 *
		 * @since 3.15.0
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

		/**
		 * Filter whether or not LifterLMS will send nocache headers.
		 *
		 * @since 6.4.0
		 *
		 * @param bool $no_cache Whether or not LifterLMS will send nocache headers.
		 */
		$do_not_cache = apply_filters( 'llms_no_cache', is_page( $ids ) || is_quiz() );

		if ( $do_not_cache ) {

			add_filter( 'nocache_headers', array( __CLASS__, 'additional_nocache_headers' ), 99 );

			llms_maybe_define_constant( 'DONOTCACHEPAGE', true );
			llms_maybe_define_constant( 'DONOTCACHEOBJECT', true );
			llms_maybe_define_constant( 'DONOTCACHEDB', true );
			nocache_headers();
			$this->exclude_page_from_wpe_server_cache();

			remove_filter( 'nocache_headers', array( __CLASS__, 'additional_nocache_headers' ), 99 );

		}

	}

	/**
	 * Set additional nocache headers.
	 *
	 * @since 6.4.0
	 *
	 * @see wp_get_nocache_headers()
	 *
	 * @param array $headers {
	 *     Header names and field values.
	 *
	 *     @type string $Expires       Expires header.
	 *     @type string $Cache-Control Cache-Control header.
	 * }
	 * @return array
	 */
	public static function additional_nocache_headers( $headers ) {

		// First tree are the default ones.
		$nocache_headers_cache_control = array(
			'no-cache',
			'must-revalidate',
			'max-age=0',
			'no-store',
		);

		if ( ! empty( $headers['Cache-Control'] ) ) {
			$original_headers_cache_control = array_map( 'trim', explode( ',', $headers['Cache-Control'] ) );
			// Merge original headers with our nocache headers.
			$nocache_headers_cache_control = array_merge( $nocache_headers_cache_control, $original_headers_cache_control );
			// Avoid duplicates.
			$nocache_headers_cache_control = array_unique( $nocache_headers_cache_control );
		}

		$headers['Cache-Control'] = implode( ', ', $nocache_headers_cache_control );

		return $headers;

	}

}

return new LLMS_Cache_Helper();
