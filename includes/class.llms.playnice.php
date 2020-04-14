<?php
/**
 * Make LifterLMS play nicely with other plugins, themes, & webhosts
 *
 * * * * * * * * * * * * * * * * * *
 * True, there is no joy           *
 * in software conflicts (or war)  *
 * Here we are, trying             *
 * * * * * * * * * * * * * * * * * *
 *
 * @package LifterLMS/Classes
 *
 * @since 3.1.3
 * @version 3.37.18
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_PlayNice class
 *
 * @since 3.1.3
 * @since 3.31.0 Resolve dashboard endpoint 404s resulting from changes in WC 3.6.
 * @since 3.37.17 Changed the way we handle the dashboard endpoints conflict, using a different wc filter hook.
 *                Deprecated `LLMS_PlayNice::wc_is_account_page()`.
 * @since 3.37.18 Resolve Divi/WC conflict encountered using the frontend pagebuilder on courses and memberships.
 */
class LLMS_PlayNice {

	/**
	 * Hold temporary variables used by methods in this class.
	 *
	 * @var array
	 */
	private $temp_vars = array();

	/**
	 * Constructor
	 *
	 * @since 3.1.3
	 * @since 3.31.0 Add `plugins_loaded` hook.
	 *
	 * @return void
	 */
	public function __construct() {

		// Optimize press live editor initialization.
		add_action( 'op_liveeditor_init', array( $this, 'wp_optimizepress_live_editor' ) );

		// WPEngine heartbeat fix.
		add_filter( 'wpe_heartbeat_allowed_pages', array( $this, 'wpe_heartbeat_allowed_pages' ) );

		// Load other playnice things based on the presence of other plugins.
		add_action( 'init', array( $this, 'plugins_loaded' ), 11 );

	}

	/**
	 * Conditionally add hooks after the other plugin is loaded.
	 *
	 * @since 3.31.0
	 * @since 3.37.17 Changed the way we handle endpoints conflict, using a different WC filter hook.
	 * @since 3.37.18 Add fix for Divi Frontend-Builder WC conflict.
	 *
	 * @return void
	 */
	public function plugins_loaded() {

		$wc_exists = function_exists( 'WC' );

		if ( $wc_exists ) {
			add_filter( 'woocommerce_account_endpoint_page_not_found', array( $this, 'wc_account_endpoint_page_not_found' ) );
		}

		if ( $wc_exists && 'divi' === strtolower( get_template() ) ) {
			add_action( 'et_fb_enqueue_assets', array( $this, 'divi_fb_wc_product_tabs_before' ), 1 );
		}

	}

	/**
	 * After Divi processes WC metabox tabs restore our global variables (just in case).
	 *
	 * @since 3.37.18
	 *
	 * @link https://github.com/gocodebox/lifterlms/issues/1079
	 *
	 * @param array[] $tabs Array of WC product metabox tabs.
	 * @return array[]
	 */
	public function divi_fb_wc_product_tabs_after( $tabs ) {

		if ( ! empty( $this->temp_vars['product'] ) ) {
			$GLOBALS['product'] = $this->temp_vars['product'];
		}

		return $tabs;

	}

	/**
	 * Temporarily remove global LLMS_Product data when the Divi Frontend Page builder is loading.
	 *
	 * Resolves an issue encountered when running Divi, WooCommerce, and LifterLMS which
	 * prevents the frontend builder from loading on courses and memberships because LifterLMS
	 * (stupidly?) and WC both use the global `$product` variable to store data about our respective
	 * products and Divi assumes (understandably?) that `$product` is always a `WC_Product` causing
	 * fatal errors.
	 *
	 * @since 3.37.18
	 *
	 * @link https://github.com/gocodebox/lifterlms/issues/1079
	 *
	 * @return void
	 */
	public function divi_fb_wc_product_tabs_before() {

		global $product;
		if ( isset( $_GET['et_fb'] ) && isset( $product ) && is_a( $product, 'LLMS_Product' ) ) {

			// Store the product temporarily.
			$this->temp_vars['product'] = $product;

			// Unset it.
			unset( $GLOBALS['product'] );

			// Restore it when Divi's done with the var.
			add_filter( 'woocommerce_product_tabs', array( $this, 'divi_fb_wc_product_tabs_after' ), 999 );

		}

	}

	/**
	 * Allow our dashboard endpoints sharing a query var with WC to function
	 *
	 * Lie to WC and tell it we're on a WC account page when accessing endpoints which
	 * share a query var with WC.
	 *
	 * @since 3.31.0
	 * @deprecated 3.37.17 No longer required based on the usage of `wc_account_endpoint_page_not_found()`.
	 *
	 * @link https://github.com/gocodebox/lifterlms/issues/849
	 *
	 * @param bool $is_acct_page False from `woocommerce_is_account_page` filter.
	 * @return bool
	 */
	public function wc_is_account_page( $is_acct_page ) {

		llms_deprecated_function( 'LLMS_PlayNice::wc_is_account_page()', '3.37.17' );

		if ( ! $is_acct_page && is_llms_account_page() && is_wc_endpoint_url() ) {
			$is_acct_page = true;
		}

		return $is_acct_page;

	}

	/**
	 * Allow our dashboard endpoints sharing a query var with WC to function
	 *
	 * Inform WC that it should not force a 404 because we're on a valid endpoint.
	 *
	 * @since 3.37.17
	 *
	 * @link https://github.com/gocodebox/lifterlms/issues/849
	 *
	 * @param bool $is_page_not_found True from `woocommerce_account_endpoint_page_not_found` filter.
	 * @return bool
	 */
	public function wc_account_endpoint_page_not_found( $is_page_not_found ) {

		if ( is_llms_account_page() && is_wc_endpoint_url() ) {
			$is_page_not_found = false;
		}

		return $is_page_not_found;

	}

	/**
	 * OptimizePress LiveEditor fix
	 *
	 * The live editor for OptimizePress does not work because it is trying to load a frontend environment
	 * in the admin area and needs access lifterlms frontend files.
	 *
	 * This function loads all frontend files when the optimizepress live editor is initialized.
	 *
	 * @since 3.2.2
	 * @since 3.19.6 Unknown.
	 *
	 * @return void
	 */
	public function wp_optimizepress_live_editor() {

		// These files are necessary to get optimizepress ajax to play nicely in the liveeditor.
		include_once 'class.llms.ajax.php';
		include_once 'class.llms.ajax.handler.php';

		// These files are all necessary to get the liveeditor to open.
		include_once 'llms.template.functions.php';
		include_once 'class.llms.https.php';

		include_once 'class.llms.template.loader.php';
		include_once 'class.llms.frontend.assets.php';
		include_once 'forms/frontend/class.llms.frontend.forms.php';
		include_once 'forms/frontend/class.llms.frontend.password.php';
		include_once 'class.llms.person.php';
		include_once 'shortcodes/class.llms.shortcodes.php';

		include_once 'shortcodes/class.llms.shortcode.my.account.php';
		include_once 'shortcodes/class.llms.shortcode.checkout.php';

	}

	/**
	 * WPE blocks the WordPress Heartbeat script from being loaded
	 *
	 * Event when it's explicitly defined as a dependency.
	 *
	 * @since 3.16.4
	 *
	 * @param array $pages List of pages that the heartbeat is allowed to load on.
	 * @return array
	 */
	public function wpe_heartbeat_allowed_pages( $pages ) {

		if ( is_admin() && isset( $_GET['page'] ) && 'llms-course-builder' === $_GET['page'] ) {

			$pages[] = 'admin.php';

		}

		return $pages;

	}

}

return new LLMS_PlayNice();
