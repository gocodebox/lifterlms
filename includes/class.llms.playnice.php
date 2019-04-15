<?php
/**
 * Make LifterLMS play nicely with other plugins, themes, & webhosts
 *
 * @package  LifterLMS/Classes
 * @since    3.1.3
 * @version  3.29.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_PlayNice class.
 */
class LLMS_PlayNice {

	/**
	 * Constructor
	 *
	 * @since    3.1.3
	 * @version  3.29.0
	 */
	public function __construct() {

		// optimize press live editor initialization
		add_action( 'op_liveeditor_init' , array( $this, 'wp_optimizepress_live_editor' ) );

		// wpe heartbeat fix
		add_filter( 'wpe_heartbeat_allowed_pages', array( $this, 'wpe_heartbeat_allowed_pages' ) );

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

		// These files are necessary to get optimizepress ajax to play nicely in the liveeditor
		include_once( 'class.llms.ajax.php' );
		include_once( 'class.llms.ajax.handler.php' );

		// These files are all necessary to get the liveeditor to open
		include_once( 'llms.template.functions.php' );
		include_once 'class.llms.https.php';

		include_once( 'class.llms.template.loader.php' );
		include_once( 'class.llms.frontend.assets.php' );
		include_once( 'forms/frontend/class.llms.frontend.forms.php' );
		include_once( 'forms/frontend/class.llms.frontend.password.php' );
		include_once( 'class.llms.person.php' );
		include_once( 'shortcodes/class.llms.shortcodes.php' );

		include_once( 'shortcodes/class.llms.shortcode.my.account.php' );
		include_once( 'shortcodes/class.llms.shortcode.checkout.php' );

	}

	/**
	 * WPE blocks the WordPress Heartbeat script from being loaded
	 * Event when it's explicitly defined as a dependency
	 * @param    array    $pages    list of pages that the heartbeat is allowed to load on
	 * @return   array
	 * @since    3.16.4
	 * @version  3.16.4
	 */
	public function wpe_heartbeat_allowed_pages( $pages ) {

		if ( is_admin() && isset( $_GET['page'] ) && 'llms-course-builder' === $_GET['page'] ) {

			$pages[] = 'admin.php';

		}

		return $pages;

	}

}

return new LLMS_PlayNice();
