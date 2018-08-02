<?php
defined( 'ABSPATH' ) || exit;

/**
* Frontend scripts class
* @since    1.0.0
* @version  3.22.0
*/
class LLMS_Frontend_Assets {

	/**
	 * Inline script ids that have been enqueued
	 * @var  array
	 */
	private static $enqueued_inline_scripts = array();

	/**
	 * Array of inline scripts to be output in the header / footer respectively
	 * @var  array
	 */
	private static $inline_scripts = array(
		'header' => array(),
		'footer' => array(),
	);

	/**
	 * Initializer
	 * Replaces non-static __construct() from 3.4.0 & lower
	 * @return   void
	 * @since    3.4.1
	 * @version  3.17.5
	 */
	public static function init() {

		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_styles' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ) );
		add_action( 'wp_head', array( __CLASS__, 'output_header_scripts' ) );
		add_action( 'wp_print_footer_scripts', array( __CLASS__, 'output_footer_scripts' ), 1 );
	}

	/**
	 * Enqueue an inline script
	 * @param    string     $id        unique id for the script, used to prevent duplicates
	 * @param    string     $script    JS to enqueue, do not add <script> tags!
	 * @param    string     $where     where to enqueue, in the header or footer
	 * @param    float      $priority  enqueue priority
	 * @return   boolean
	 * @since    3.4.1
	 * @version  3.4.1
	 */
	public static function enqueue_inline_script( $id, $script, $where = 'footer', $priority = 10 ) {

		// dupcheck
		if ( self::is_inline_script_enqueued( $id ) ) {
			return false;
		}

		// retrieve the current array of scripts
		$scripts = self::get_inline_scripts( $where );

		$priority = (string) $priority;

		// if something already exist at the priority, increment until we can save it
		while ( isset( $scripts[ $priority ] ) ) {

			$priority = (float) $priority;
			$priority = $priority + 0.01;
			$priority = (string) $priority;

		}

		// add the script to the array
		$scripts[ $priority ] = $script;

		// add it to the array of enqueued scripts
		self::$enqueued_inline_scripts[] = $id;

		ksort( $scripts );

		// save updated array
		self::$inline_scripts[ $where ] = $scripts;

		return true;

	}

	/**
	 * Output the inline PW Strength meter script
	 * @return   void
	 * @since    3.4.1
	 * @version  3.4.1
	 */
	public static function enqueue_inline_pw_script() {
		self::enqueue_inline_script(
			'llms-pw-strength',
			'window.LLMS.PasswordStrength = window.LLMS.PasswordStrength || {}; window.LLMS.PasswordStrength.get_minimum_strength = function() { return "' . llms_get_minimum_password_strength() . '"; }',
			'footer',
			15
		);
	}

	/**
	 * Enqueue Styles
	 * @since   1.0.0
	 * @version 3.18.0
	 */
	public static function enqueue_styles() {

		global $post_type;

		wp_register_style( 'llms-iziModal', LLMS_PLUGIN_URL . 'assets/vendor/izimodal/iziModal' . LLMS_ASSETS_SUFFIX . '.css' );

		wp_enqueue_style( 'webui-popover', LLMS_PLUGIN_URL . 'assets/vendor/webui-popover/jquery.webui-popover' . LLMS_ASSETS_SUFFIX . '.css' );

		wp_enqueue_style( 'lifterlms-styles', LLMS_PLUGIN_URL . 'assets/css/lifterlms' . LLMS_ASSETS_SUFFIX . '.css' );
		wp_style_add_data( 'lifterlms-styles', 'rtl', 'replace' );
		wp_style_add_data( 'lifterlms-styles', 'suffix', LLMS_ASSETS_SUFFIX );

		if ( 'llms_my_certificate' == $post_type || 'llms_certificate' == $post_type ) {
			wp_enqueue_style( 'certificates', LLMS_PLUGIN_URL . 'assets/css/certificates' . LLMS_ASSETS_SUFFIX . '.css' );
			wp_style_add_data( 'certificates', 'rtl', 'replace' );
			wp_style_add_data( 'certificates', 'suffix', LLMS_ASSETS_SUFFIX );
		}

		if ( is_llms_account_page() ) {
			wp_enqueue_style( 'llms-iziModal' );
		}

	}

	/**
	 * Enqueue Scripts
	 * @since   1.0.0
	 * @version 3.22.0
	 */
	public static function enqueue_scripts() {

		wp_enqueue_script( 'jquery-ui-tooltip' );
		wp_enqueue_script( 'jquery-ui-datepicker' );
		wp_enqueue_script( 'jquery-ui-slider' );
		wp_enqueue_script( 'collapse', LLMS_PLUGIN_URL . 'assets/js/vendor/collapse.js' );
		wp_enqueue_script( 'transition', LLMS_PLUGIN_URL . 'assets/js/vendor/transition.js' );
		wp_enqueue_script( 'webui-popover', LLMS_PLUGIN_URL . 'assets/vendor/webui-popover/jquery.webui-popover' . LLMS_ASSETS_SUFFIX . '.js', array( 'jquery' ), '', true );

		wp_register_script( 'llms-jquery-matchheight', LLMS_PLUGIN_URL . 'assets/js/vendor/jquery.matchHeight.js', array( 'jquery' ), '', true );
		if ( is_llms_account_page() || is_course() || is_membership() || is_lesson() || is_memberships() || is_courses() || is_tax( array( 'course_cat', 'course_tag', 'course_difficulty', 'course_track', 'membership_tag', 'membership_cat' ) ) ) {
			wp_enqueue_script( 'llms-jquery-matchheight' );
		}

		/**
		 * @todo  this is currently being double registered and enqueued by LLMS_Ajax
		 *        fix it ffs...
		 */
		wp_register_script( 'llms', LLMS_PLUGIN_URL . 'assets/js/llms' . LLMS_ASSETS_SUFFIX . '.js', array( 'jquery' ), '', true );
		wp_enqueue_script( 'llms' );

		wp_register_script( 'llms-notifications', LLMS_PLUGIN_URL . 'assets/js/llms-notifications' . LLMS_ASSETS_SUFFIX . '.js', array( 'jquery' ), '', true );
		if ( get_current_user_id() ) {
			wp_enqueue_script( 'llms-notifications' );
		}

		wp_enqueue_script( 'llms-ajax', LLMS_PLUGIN_URL . 'assets/js/llms-ajax' . LLMS_ASSETS_SUFFIX . '.js', array( 'jquery' ), '', true );
		wp_enqueue_script( 'llms-form-checkout', LLMS_PLUGIN_URL . 'assets/js/llms-form-checkout' . LLMS_ASSETS_SUFFIX . '.js', array( 'jquery' ), '', true );

		if ( ( is_llms_account_page() || is_llms_checkout() ) && 'yes' === get_option( 'lifterlms_registration_password_strength' ) ) {
			wp_enqueue_script( 'password-strength-meter' );
			self::enqueue_inline_pw_script();
		}

		if ( is_singular( 'llms_quiz' ) ) {
			wp_enqueue_style( 'wp-mediaelement' );
			wp_enqueue_script( 'llms-quiz', LLMS_PLUGIN_URL . 'assets/js/llms-quiz' . LLMS_ASSETS_SUFFIX . '.js', array( 'jquery', 'llms', 'wp-mediaelement' ), LLMS()->version, true );
		}

		wp_register_script( 'llms-iziModal', LLMS_PLUGIN_URL . 'assets/vendor/izimodal/iziModal' . LLMS_ASSETS_SUFFIX . '.js', array( 'jquery' ), '1.5.1', true );
		if ( is_llms_account_page() ) {
			wp_enqueue_script( 'llms-iziModal' );
		}

		$ssl = is_ssl() ? 'https' : 'http';
		self::enqueue_inline_script(
			'llms-ajaxurl',
			'window.llms = window.llms || {};window.llms.ajaxurl = "' . admin_url( 'admin-ajax.php', $ssl ) . '";'
		);
		self::enqueue_inline_script(
			'llms-LLMS-obj',
			'window.LLMS = window.LLMS || {};'
		);
		self::enqueue_inline_script(
			'llms-l10n',
			'window.LLMS.l10n = window.LLMS.l10n || {}; window.LLMS.l10n.strings = ' . LLMS_L10n::get_js_strings( true ) . ';'
		);

	}

	/**
	 * Retrieve inline scripts
	 * @param    string     $where  header or footer, if none provided both will be returned
	 * @return   array
	 * @since    3.4.1
	 * @version  3.4.1
	 */
	private static function get_inline_scripts( $where = null ) {

		$scripts = self::$inline_scripts;

		if ( isset( $scripts[ $where ] ) ) {
			$scripts = $scripts[ $where ];
		}

		return apply_filters( 'llms_frontend_assets_inline_scripts', $scripts );

	}

	/**
	 * Determine if an inline script has already been enqueued
	 * @param    string     $id  id of the inline script
	 * @return   boolean
	 * @since    3.4.1
	 * @version  3.4.1
	 */
	public static function is_inline_script_enqueued( $id ) {
		return in_array( $id, self::$enqueued_inline_scripts );
	}

	/**
	 * Output inline scripts
	 * @param    string     $where  which set of scripts to output [header|footer]
	 * @return   void
	 * @since    3.4.1
	 * @version  3.4.1
	 */
	private static function output_inline_scripts( $where ) {

		$scripts = self::get_inline_scripts( $where );

		// bail if no scripts
		if ( ! $scripts ) {
			return;
		}

		echo '<script id="llms-inline-' . $where . 'scripts" type="text/javascript">';
		foreach ( $scripts as $script ) {
			echo $script;
		}
		echo '</script>';

	}

	/**
	 * Output inline scripts to the footer
	 * @return   void
	 * @since    3.4.1
	 * @version  3.4.1
	 */
	public static function output_footer_scripts() {
		self::output_inline_scripts( 'footer' );
	}

	/**
	 * Output inline scripts to the header
	 * @return   void
	 * @since    3.4.1
	 * @version  3.4.1
	 */
	public static function output_header_scripts() {
		self::output_inline_scripts( 'header' );
	}

}

return LLMS_Frontend_Assets::init();
