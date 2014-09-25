<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
* Frontend scripts class
*
* Initializes front end scripts
*
* @version 1.0
* @author codeBOX
* @project lifterLMS
*/
class LLMS_Frontend_Assets {

	public static $min = ''; //'.min';

	/**
	* Constructor
	*
	* loads scripts and styles on the wp_enqueue+scripts action.
	*/
	public function __construct () {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'wp_footer', array( $this, 'wp_footer' ) );
	}

	/**
	 * get style sheets
	 *
	 * @return string
	 */
	public static function enqueue_styles() {

		wp_enqueue_style( 'admin-styles', plugins_url( '/assets/css/lifterlms' . LLMS_Frontend_Assets::$min . '.css', LLMS_PLUGIN_FILE ) );

	}

	/**
	 * enqueues scripts and styles
	 *
	 * @return string
	 */
	public function enqueue_scripts() {
		//no scripts yet. This will work exactly like the admin side.
		global $post, $wp;
	}

	/**
	 * add inline script to the footer
	 * @return void
	 */
	public function wp_footer() {
		echo '<script type="text/javascript">window.llms = window.llms || {};window.llms.ajaxurl = "'.admin_url( 'admin-ajax.php' ).'";</script>';
	}
}

new LLMS_Frontend_Assets();
