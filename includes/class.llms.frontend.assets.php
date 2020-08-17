<?php
/**
 * Frontend scripts class
 *
 * @package LifterLMS/Classes
 *
 * @since 1.0.0
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Frontend_Assets
 *
 * @since 1.0.0
 * @since 3.35.0 Explicitly define asset versions.
 * @since 3.36.0 Localize tracking with client-side settings.
 * @since 4.0.0 Remove JS dependencies "collapse" and "transition".
 */
class LLMS_Frontend_Assets {

	/**
	 * Inline script ids that have been enqueued
	 *
	 * @var  array
	 */
	private static $enqueued_inline_scripts = array();

	/**
	 * Array of inline scripts to be output in the header / footer respectively
	 *
	 * @var  array
	 */
	private static $inline_scripts = array(
		'header' => array(),
		'footer' => array(),
	);

	/**
	 * Initializer
	 * Replaces non-static __construct() from 3.4.0 & lower
	 *
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
	 *
	 * @param    string $id        unique id for the script, used to prevent duplicates
	 * @param    string $script    JS to enqueue, do not add <script> tags!
	 * @param    string $where     where to enqueue, in the header or footer
	 * @param    float  $priority  enqueue priority
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
	 *
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
	 *
	 * @since 1.0.0
	 * @since 3.18.0 Unknown.
	 * @since 3.35.0 Explicitly define asset versions.
	 * @since [version] Use `LLMS_Assets` methods for enqueueing and registration.
	 *
	 * @return void
	 */
	public static function enqueue_styles() {

		global $post_type;

		LLMS_Assets::register_style( 'llms-iziModal' );

		LLMS_Assets::enqueue_style( 'webui-popover' );
		LLMS_Assets::enqueue_style( 'lifterlms-styles' );

		if ( in_array( $post_type, array( 'llms_my_certificate', 'llms_certificate' ), true ) ) {
			LLMS_Assets::enqueue_style( 'certificates' );
		} elseif ( is_llms_account_page() ) {
			LLMS_Assets::enqueue_style( 'llms-iziModal' );
		} elseif ( is_singular( 'llms_quiz' ) ) {
			wp_enqueue_style( 'wp-mediaelement' );
		}

	}

	/**
	 * Enqueue Scripts
	 *
	 * @since 1.0.0
	 * @since 3.22.0 Unknown.
	 * @since 3.35.0 Explicitly define asset versions.
	 * @since 3.36.0 Localize tracking with client-side settings.
	 * @since 4.0.0 Remove dependencies "collapse" and "transition".
	 * @since [version] Enqueue & register scripts using `LLMS_Assets` methods.
	 *              Add Add `window.llms.ajax_nonce` data to replace `wp_ajax_data.nonce`.
	 *
	 * @return void
	 */
	public static function enqueue_scripts() {

		// I don't think we need these next 3 scripts.
		wp_enqueue_script( 'jquery-ui-tooltip' );
		wp_enqueue_script( 'jquery-ui-datepicker' );
		wp_enqueue_script( 'jquery-ui-slider' );

		LLMS_Assets::enqueue_script( 'webui-popover' );

		LLMS_Assets::register_script( 'llms-jquery-matchheight' );
		if ( is_llms_account_page() || is_course() || is_membership() || is_lesson() || is_memberships() || is_courses() || is_tax( array( 'course_cat', 'course_tag', 'course_difficulty', 'course_track', 'membership_tag', 'membership_cat' ) ) ) {
			LLMS_Assets::enqueue_script( 'llms-jquery-matchheight' );
		}

		LLMS_Assets::enqueue_script( 'llms' );

		LLMS_Assets::register_script( 'llms-notifications' );
		if ( get_current_user_id() ) {
			LLMS_Assets::enqueue_script( 'llms-notifications' );
		}

		// Doesn't seem like there's any reason to enqueue this script on the frontend.
		// wp_enqueue_script( 'llms-ajax', LLMS_PLUGIN_URL . 'assets/js/llms-ajax' . LLMS_ASSETS_SUFFIX . '.js', array( 'jquery' ), LLMS()->version, true );

		// I think we only need this on account and checkout pages.
		LLMS_Assets::enqueue_script( 'llms-form-checkout' );

		if ( ( is_llms_account_page() || is_llms_checkout() ) && 'yes' === get_option( 'lifterlms_registration_password_strength' ) ) {
			wp_enqueue_script( 'password-strength-meter' );
			self::enqueue_inline_pw_script();
		}

		if ( is_singular( 'llms_quiz' ) ) {
			LLMS_Assets::enqueue_script( 'llms-quiz' );
		}

		LLMS_Assets::register_script( 'llms-iziModal' );
		if ( is_llms_account_page() ) {
			LLMS_Assets::enqueue_script( 'llms-iziModal' );
		}

		$ssl = is_ssl() ? 'https' : 'http';
		self::enqueue_inline_script(
			'llms-ajaxurl',
			'window.llms = window.llms || {};window.llms.ajaxurl = "' . admin_url( 'admin-ajax.php', $ssl ) . '";'
		);
		self::enqueue_inline_script(
			'llms-ajax-nonce',
			'window.llms = window.llms || {};window.llms.ajax_nonce = "' . wp_create_nonce( LLMS_AJAX::NONCE ) . '";'
		);
		self::enqueue_inline_script(
			'llms-tracking-settings',
			"window.llms.tracking = '" . wp_json_encode( LLMS()->events()->get_client_settings() ) . "';"
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
	 *
	 * @param    string $where  header or footer, if none provided both will be returned
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
	 *
	 * @param    string $id  id of the inline script
	 * @return   boolean
	 * @since    3.4.1
	 * @version  3.4.1
	 */
	public static function is_inline_script_enqueued( $id ) {
		return in_array( $id, self::$enqueued_inline_scripts );
	}

	/**
	 * Output inline scripts
	 *
	 * @param    string $where  which set of scripts to output [header|footer]
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
	 *
	 * @return   void
	 * @since    3.4.1
	 * @version  3.4.1
	 */
	public static function output_footer_scripts() {
		self::output_inline_scripts( 'footer' );
	}

	/**
	 * Output inline scripts to the header
	 *
	 * @return   void
	 * @since    3.4.1
	 * @version  3.4.1
	 */
	public static function output_header_scripts() {
		self::output_inline_scripts( 'header' );
	}

}

return LLMS_Frontend_Assets::init();
