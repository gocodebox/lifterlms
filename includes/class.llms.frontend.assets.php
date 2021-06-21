<?php
/**
 * Frontend scripts class
 *
 * @package LifterLMS/Classes
 *
 * @since 1.0.0
 * @version 5.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Frontend_Assets
 *
 * @since 1.0.0
 * @since 3.35.0 Explicitly define asset versions.
 * @since 3.36.0 Localize tracking with client-side settings.
 * @since 4.0.0 Remove JS dependencies "collapse" and "transition".
 * @since 4.4.0 Method `enqueue_inline_script()` is deprecated in favor of `LLMS_Assets::enqueue_inline()`.
 *              Method `is_inline_script_enqueued()` is deprecated in favor of `LLMS_Frontend_Assets::is_inline_enqueued()`.
 *              Private properties `$enqueued_inline_scripts` and `$inline_scripts` have been removed.
 *              Removed private methods `get_inline_scripts()` and `output_inline_scripts()`.
 */
class LLMS_Frontend_Assets {

	/**
	 * Inline script ids that have been enqueued.
	 *
	 * @var  array
	 */
	private static $enqueued_inline_scripts = array();

	/**
	 * Array of inline scripts to be output in the header / footer respectively.
	 *
	 * @var  array
	 */
	private static $inline_scripts = array(
		'header' => array(),
		'footer' => array(),
	);

	/**
	 * Initializer
	 *
	 * Replaces non-static __construct() from 3.4.0 & lower.
	 *
	 * @since 3.4.1
	 * @since 3.17.5 Unknown.
	 *
	 * @return void
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
	 * @version 3.4.1
	 * @deprecated 4.4.0 Use `LLMS_Assets::enqueue_inline()` instead.
	 *
	 * @param string $id       Unique id for the script, used to prevent duplicates.
	 * @param string $script   JS to enqueue, do not add <script> tags!.
	 * @param string $location Where to enqueue, accepts "header" or "footer".
	 * @param float  $priority Enqueue priority.
	 * @return boolean
	 */
	public static function enqueue_inline_script( $id, $script, $location = 'footer', $priority = 10 ) {
		llms_deprecated_function( 'LLMS_Frontend_Assets::enqueue_inline_script()', '4.4.0', 'LLMS_Assets::enqueue_inline()' );
		return llms()->assets->enqueue_inline( $id, $script, $location, $priority ) ? true : false;
	}

	/**
	 * Output the inline PW Strength meter script
	 *
	 * @since 3.4.1
	 *
	 * @return void
	 */
	public static function enqueue_inline_pw_script() {
		llms()->assets->enqueue_inline(
			'llms-pw-strength',
			'window.LLMS.PasswordStrength = window.LLMS.PasswordStrength || {};window.LLMS.PasswordStrength.get_minimum_strength = function() { return "' . llms_get_minimum_password_strength() . '"; };',
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
	 * @since 4.4.0 Enqueue & register scripts using `LLMS_Assets` methods.
	 * @since 5.0.0 Enqueue select2 on account and checkout pages for searchable dropdowns for country & state.
	 *
	 * @return void
	 */
	public static function enqueue_styles() {

		global $post_type;

		llms()->assets->register_style( 'llms-iziModal' );

		llms()->assets->enqueue_style( 'webui-popover' );
		llms()->assets->enqueue_style( 'lifterlms-styles' );

		if ( in_array( $post_type, array( 'llms_my_certificate', 'llms_certificate' ), true ) ) {
			llms()->assets->enqueue_style( 'certificates' );
		} elseif ( is_llms_account_page() ) {
			llms()->assets->enqueue_style( 'llms-iziModal' );
		} elseif ( is_singular( 'llms_quiz' ) ) {
			wp_enqueue_style( 'wp-mediaelement' );
		}

		if ( is_llms_account_page() || is_llms_checkout() ) {
			llms()->assets->enqueue_style( 'llms-select2-styles' );
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
	 * @since 4.4.0 Enqueue & register scripts using `LLMS_Assets` methods.
	 *              Add Add `window.llms.ajax_nonce` data to replace `wp_ajax_data.nonce`.
	 *              Moved inline scripts to `enqueue_inline_scripts()`.
	 * @since 5.0.0 Enqueue locale data and dependencies on account and checkout pages for searchable dropdowns for country & state.
	 *               Remove password strength inline enqueue.
	 *
	 * @return void
	 */
	public static function enqueue_scripts() {

		// I don't think we need these next 3 scripts.
		wp_enqueue_script( 'jquery-ui-tooltip' );
		wp_enqueue_script( 'jquery-ui-datepicker' );
		wp_enqueue_script( 'jquery-ui-slider' );

		llms()->assets->enqueue_script( 'webui-popover' );

		llms()->assets->register_script( 'llms-jquery-matchheight' );
		if ( is_llms_account_page() || is_course() || is_membership() || is_lesson() || is_memberships() || is_courses() || is_tax( array( 'course_cat', 'course_tag', 'course_difficulty', 'course_track', 'membership_tag', 'membership_cat' ) ) ) {
			llms()->assets->enqueue_script( 'llms-jquery-matchheight' );
		}

		llms()->assets->enqueue_script( 'llms' );

		llms()->assets->register_script( 'llms-notifications' );
		if ( get_current_user_id() ) {
			llms()->assets->enqueue_script( 'llms-notifications' );
		}

		// Doesn't seem like there's any reason to enqueue this script on the frontend.
		wp_enqueue_script( 'llms-ajax', LLMS_PLUGIN_URL . 'assets/js/llms-ajax' . LLMS_ASSETS_SUFFIX . '.js', array( 'jquery' ), LLMS()->version, true );

		// I think we only need this on account and checkout pages.
		llms()->assets->enqueue_script( 'llms-form-checkout' );

		if ( is_singular( 'llms_quiz' ) ) {
			llms()->assets->enqueue_script( 'llms-quiz' );
		}

		llms()->assets->register_script( 'llms-iziModal' );
		if ( is_llms_account_page() ) {
			llms()->assets->enqueue_script( 'llms-iziModal' );
		}

		self::enqueue_inline_scripts();
		self::enqueue_locale_scripts();

	}

	/**
	 * Enqueue inline scripts.
	 *
	 * @since 4.4.0
	 *
	 * @return void
	 */
	protected static function enqueue_inline_scripts() {

		// Ensure the main llms object exists.
		llms()->assets->enqueue_inline( 'llms-obj', 'window.llms = window.llms || {};', 'footer', 5 );

		// Define inline scripts.
		$scripts = array(
			'llms-ajaxurl'           => 'window.llms.ajaxurl = "' . admin_url( 'admin-ajax.php', is_ssl() ? 'https' : 'http' ) . '";',
			'llms-ajax-nonce'        => 'window.llms.ajax_nonce = "' . wp_create_nonce( LLMS_AJAX::NONCE ) . '";',
			'llms-tracking-settings' => "window.llms.tracking = '" . wp_json_encode( LLMS()->events()->get_client_settings() ) . "';",
			'llms-LLMS-obj'          => 'window.LLMS = window.LLMS || {};',
			'llms-l10n'              => 'window.LLMS.l10n = window.LLMS.l10n || {}; window.LLMS.l10n.strings = ' . LLMS_L10n::get_js_strings( true ) . ';',
		);

		// Enqueue them.
		foreach ( $scripts as $handle => $script ) {
			llms()->assets->enqueue_inline( $handle, $script, 'footer' );
		}

	}

	/**
	 * Enqueue dependencies and inline script data for form localization
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	protected static function enqueue_locale_scripts() {

		if ( is_llms_account_page() || is_llms_checkout() ) {
			llms()->assets->enqueue_script( 'llms-select2' );
			llms()->assets->enqueue_inline(
				'llms-countries-locale',
				"window.llms.address_info = '" . wp_json_encode( llms_get_countries_address_info() ) . "';",
				'footer',
				20
			);
		}

	}

	/**
	 * Determine if an inline script has already been enqueued
	 *
	 * @since 3.4.1
	 * @deprecated 4.4.0
	 *
	 * @param string $handle Handle of the inline script.
	 * @return boolean
	 */
	public static function is_inline_script_enqueued( $handle ) {
		llms_deprecated_function( 'LLMS_Frontend_Assets::is_inline_enqueued()', '4.4.0', 'LLMS_Frontend_Assets::is_inline_enqueued()' );
		return llms()->assets->is_inline_enqueued( $handle );
	}

	/**
	 * Output inline scripts in the footer
	 *
	 * @since 3.4.1
	 * @since 4.4.0 Use `LLMS_Assets::output_inline()` to output scripts.
	 *
	 * @return void
	 */
	public static function output_footer_scripts() {
		llms()->assets->output_inline( 'footer' );
	}

	/**
	 * Output inline scripts in the header
	 *
	 * @since 3.4.1
	 * @since 4.4.0 Use `LLMS_Assets::output_inline()` to output scripts.
	 *
	 * @return void
	 */
	public static function output_header_scripts() {
		llms()->assets->output_inline( 'header' );
	}

}

return LLMS_Frontend_Assets::init();
