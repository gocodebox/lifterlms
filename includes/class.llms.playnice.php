<?php
/**
 * Make LifterLMS play nicely with other plugins, themes, & webhosts
 *
 * @package LifterLMS/Classes
 *
 * @since 3.1.3
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_PlayNice class
 *
 * @since 3.1.3
 * @since 3.31.0 Resolve dashboard endpoint 404s resulting from changes in WC 3.6.
 * @since [version] Changed the way we handle the dashboard endpoints conflict, using a different wc filter hook.
 *                 Deprecated `LLMS_PlayNice::wc_is_account_page()`.
 */
class LLMS_PlayNice {

	/**
	 * Constructor
	 *
	 * @since 3.1.3
	 * @since 3.31.0 Add `plugins_loaded` hook.
	 *
	 * @return void
	 */
	public function __construct() {

		// optimize press live editor initialization.
		add_action( 'op_liveeditor_init', array( $this, 'wp_optimizepress_live_editor' ) );

		// wpe heartbeat fix.
		add_filter( 'wpe_heartbeat_allowed_pages', array( $this, 'wpe_heartbeat_allowed_pages' ) );

		add_action( 'init', array( $this, 'plugins_loaded' ), 11 );

	}

	/**
	 * Conditionally add hooks after the other plugin is loaded.
	 *
	 * @since 3.31.0
	 * @since [version] Changed the way we handle endpoints conflict, using a different wc filter hook.
	 *
	 * @return void
	 */
	public function plugins_loaded() {
		if ( function_exists( 'WC' ) ) {
			add_filter( 'woocommerce_account_endpoint_page_not_found', array( $this, 'wc_account_endpoint_page_not_found' ) );
		}
	}

	/**
	 * Allow our dashboard endpoints sharing a query var with WC to function
	 *
	 * Lie to WC and tell it we're on a WC account page when accessing endpoints which
	 * share a query var with WC. See https://github.com/gocodebox/lifterlms/issues/849.
	 *
	 * @since 3.31.0
	 * @deprecated [version]
	 *
	 * @param bool $is_acct_page False from `woocommerce_is_account_page` filter.
	 * @return bool
	 */
	public function wc_is_account_page( $is_acct_page ) {

		if ( ! $is_acct_page && is_llms_account_page() && is_wc_endpoint_url() ) {
			$is_acct_page = true;
		}

		return $is_acct_page;

	}

	/**
	 * Allow our dashboard endpoints sharing a query var with WC to function
	 *
	 * Inform WC that it should not force a 404 because we're on a valid endpoint.
	 * See https://github.com/gocodebox/lifterlms/issues/849.
	 *
	 * @since [version]
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
	 * @return   void
	 * @since    3.2.2
	 * @version  3.19.6
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
