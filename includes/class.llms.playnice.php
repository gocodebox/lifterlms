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
 * @version 6.8.0
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
 * @since 4.0.0 Removed previously deprecated method `LLMS_PlayNice::wc_is_account_page()`.
 *              Remove Divi Frontend Builder WC conflict code.
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
	 * @since 6.8.0 Account for BuddyBoss compatibility issue.
	 *
	 * @return void
	 */
	public function __construct() {

		// Optimize press live editor initialization.
		add_action( 'op_liveeditor_init', array( $this, 'wp_optimizepress_live_editor' ) );

		// WPEngine heartbeat fix.
		add_filter( 'wpe_heartbeat_allowed_pages', array( $this, 'wpe_heartbeat_allowed_pages' ) );

		// BuddyBoss profile nav compatibility issue fix (the nav is set up at priority 6).
		add_action( 'bp_init', array( $this, 'buddyboss_compatibility' ), 5 );

		// Load other playnice things based on the presence of other plugins.
		add_action( 'init', array( $this, 'plugins_loaded' ), 11 );

	}

	/**
	 * Compatibility for BuddyBoss.
	 *
	 * @since 6.8.0
	 *
	 * @link https://github.com/gocodebox/lifterlms/issues/2142#issuecomment-1157924080.
	 *
	 * @return void
	 */
	public function buddyboss_compatibility() {

		if ( ! function_exists( 'is_plugin_active' ) || ! function_exists( 'bp_is_my_profile' ) || bp_is_my_profile() ) {
			return;
		}

		if (
			is_plugin_active( 'buddyboss-platform/bp-loader.php' ) ||
			( is_multisite() && is_plugin_active_for_network( 'buddyboss-platform/bp-loader.php' ) )
		) {
			$plugin_data    = get_plugin_data( trailingslashit( WP_PLUGIN_DIR ) . 'buddyboss-platform/bp-loader.php' );
			$plugin_version = ! empty( $plugin_data['Version'] ) ? $plugin_data['Version'] : 0;
			if ( $plugin_version && version_compare( $plugin_version, '2.0.3', '>=' ) ) {
				// Nothing to do.
				return;
			}
		}

		// Do not add our profile nav items when not in front-end (and not in "my profile"), to avoid a fatal error.
		$bp_integration = llms()->integrations()->get_integration( 'buddypress' );
		remove_action( 'bp_setup_nav', array( $bp_integration, 'add_profile_nav_items' ) );

	}

	/**
	 * Conditionally add hooks after the other plugin is loaded.
	 *
	 * @since 3.31.0
	 * @since 3.37.17 Changed the way we handle endpoints conflict, using a different WC filter hook.
	 * @since 3.37.18 Add fix for Divi Frontend-Builder WC conflict.
	 * @since 4.0.0 Remove Divi Frontend Builder WC conflict code.
	 *
	 * @return void
	 */
	public function plugins_loaded() {

		$wc_exists = function_exists( 'WC' );

		if ( $wc_exists ) {
			add_filter( 'woocommerce_account_endpoint_page_not_found', array( $this, 'wc_account_endpoint_page_not_found' ) );
		}

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
	 * OptimizePress LiveEditor fix.
	 *
	 * The live editor for OptimizePress does not work because it is trying to load a frontend environment
	 * in the admin area and needs access to LifterLMS frontend files.
	 *
	 * This function loads all frontend files when the OptimizePress live editor is initialized.
	 *
	 * @since 3.2.2
	 * @since 3.19.6 Unknown.
	 * @since 4.0.0 Removed inclusion of removed 'class.llms.person.php' file.
	 * @since 5.0.0 Remove inclusion of removed files:
	 *                    + forms/frontend/class.llms.frontend.forms.php
	 *                    + forms/frontend/class.llms.frontend.password.php
	 * @since 6.0.0 Removed loading of class files that don't instantiate their class in favor of autoloading.
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
