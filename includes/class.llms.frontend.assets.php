<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
* Frontend scripts class
*
* Initializes front end scripts
*
* @author codeBOX
*/
class LLMS_Frontend_Assets {

	public static $min = '.min'; //'.min';

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

	//http://local.wordpress-trunk.dev/wp-content/plugins/lifterlms/assets/css/lifterlms-temp.css
	public static function enqueue_styles() {
		global $post_type;

		wp_enqueue_style( 'chosen-styles', plugins_url( '/assets/chosen/chosen' . LLMS_Frontend_Assets::$min . '.css', LLMS_PLUGIN_FILE ) );
		wp_enqueue_style( 'admin-styles', plugins_url( '/assets/css/lifterlms' . LLMS_Frontend_Assets::$min . '.css', LLMS_PLUGIN_FILE ) );

		$filename = ABSPATH . 'wp-content/plugins/lifterlms/assets/css/lifterlms-temp' . LLMS_Frontend_Assets::$min . '.css';

		if (file_exists( $filename )) {
			wp_enqueue_style( 'temp-styles', plugins_url( '/assets/css/lifterlms-temp' . LLMS_Frontend_Assets::$min . '.css', LLMS_PLUGIN_FILE ) );
		}

		if ( 'llms_my_certificate' == $post_type || 'llms_certificate' == $post_type ) {
			wp_enqueue_style( 'certificates', plugins_url( '/assets/css/certificates' . LLMS_Frontend_Assets::$min . '.css', LLMS_PLUGIN_FILE ) );
		}
	}

	/**
	 * enqueues scripts and styles
	 *
	 * @return string
	 */
	public function enqueue_scripts() {
		global $post, $wp;
		wp_enqueue_script( 'jquery-ui-tooltip' );
		wp_enqueue_script( 'jquery-ui-datepicker' );
		wp_enqueue_script( 'jquery-ui-slider' );
		wp_enqueue_script( 'chosen-jquery', plugins_url( 'assets/chosen/chosen.jquery' . LLMS_Frontend_Assets::$min . '.js', LLMS_PLUGIN_FILE ), array( 'jquery' ), '', true );
		wp_enqueue_script( 'collapse', plugins_url( 'assets/js/vendor/collapse.js', LLMS_PLUGIN_FILE ) );
		wp_enqueue_script( 'transition', plugins_url( 'assets/js/vendor/transition.js', LLMS_PLUGIN_FILE ) );

		wp_register_script( 'llms', plugins_url( '/assets/js/llms' . LLMS_Frontend_Assets::$min . '.js', LLMS_PLUGIN_FILE ), array( 'jquery' ), '', true );
		wp_enqueue_script( 'llms' );

		wp_enqueue_script( 'llms-ajax', plugins_url( '/assets/js/llms-ajax' . LLMS_Frontend_Assets::$min . '.js', LLMS_PLUGIN_FILE ), array( 'jquery' ), '', true );
		//wp_enqueue_script( 'llms-quiz', plugins_url(  '/assets/js/llms-quiz' . LLMS_Frontend_Assets::$min . '.js', LLMS_PLUGIN_FILE ), array('jquery'), '', TRUE);
		wp_enqueue_script( 'llms-form-checkout', plugins_url( '/assets/js/llms-form-checkout' . LLMS_Frontend_Assets::$min . '.js', LLMS_PLUGIN_FILE ), array( 'jquery' ), '', true );
		if (is_course()) {
			wp_enqueue_script( 'llms-lesson-locked', plugins_url( 'assets/js/llms-lesson-locked' . LLMS_Frontend_Assets::$min . '.js', LLMS_PLUGIN_FILE ), array(), false, true );
		}

	}

	/**
	 * add inline script to the footer
	 * @return void
	 */
	public function wp_footer() {

		//register ajax
		echo '<script type="text/javascript">window.llms = window.llms || {};window.llms.ajaxurl = "'.admin_url( 'admin-ajax.php' ).'";</script>';
		echo '<script type="text/javascript">window.LLMS = window.LLMS || {};</script>';
		echo '<script type="text/javascript">window.LLMS.l10n = window.LLMS.l10n || {}; window.LLMS.l10n.strings = ' . LLMS_l10n::get_js_strings( true ) . ';</script>';
	}
}

new LLMS_Frontend_Assets();

