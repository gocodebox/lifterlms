<?php
/**
 * LLMS_Admin_Help_Beacon class file
 *
 * @package LifterLMS/Admin/Classes
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * Displays the LifterLMS HelpScout help beacon on LifterLMS admin screens.
 *
 * The beacon is loaded on the core LifterLMS management screens (Dashboard,
 * Settings, Reporting, etc.) and on the screens that live under the Courses,
 * Memberships, Engagements, and Orders admin menus. This includes the post types
 * nested under those menus (e.g. Achievements, Certificates, and Emails under
 * Engagements; Coupons and Vouchers under Orders) and their taxonomy screens.
 *
 * For the Courses and Memberships menus the beacon is intentionally not loaded on
 * the block editor (add or edit), where it would clutter the editing UI. For the
 * Engagements and Orders menus it is loaded on all pages, including the editors. It
 * is never loaded on the Course Builder.
 *
 * Two separate beacons are used: a support beacon shown when the site has a valid
 * add-on license and a pre-sales beacon shown otherwise. The LifterLMS system
 * report is attached to both beacons as agent-only session data.
 *
 * To respect the user's privacy the beacon script (which may set cookies) is not
 * loaded until the user clicks the launcher and confirms a consent prompt.
 *
 * @since [version]
 */
class LLMS_Admin_Help_Beacon {

	/**
	 * HelpScout beacon ID shown to sites without a valid license (pre-sales).
	 *
	 * @var string
	 */
	const PRESALES_BEACON_ID = '52c19826-01aa-491c-9a95-10ab704fc0df';

	/**
	 * HelpScout beacon ID shown to sites with a valid license (support).
	 *
	 * @var string
	 */
	const SUPPORT_BEACON_ID = '3a1a4de4-d043-4b9c-8321-4b49596f7476';

	/**
	 * Constructor.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function __construct() {

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
	}

	/**
	 * Enqueue the beacon launcher and styles on eligible screens.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function enqueue() {

		if ( ! $this->is_beacon_screen() ) {
			return;
		}

		$is_licensed = $this->is_licensed();
		$beacon_id   = $this->get_beacon_id( $is_licensed );
		if ( empty( $beacon_id ) ) {
			return;
		}

		llms()->assets->enqueue_inline( 'llms-help-beacon-styles', $this->get_inline_styles(), 'style' );
		llms()->assets->enqueue_inline( 'llms-help-beacon', $this->get_inline_script( $beacon_id ), 'footer' );
	}

	/**
	 * Determine whether the beacon should be shown on the current screen.
	 *
	 * @since [version]
	 *
	 * @return boolean
	 */
	public function is_beacon_screen() {

		$screen = get_current_screen();
		if ( ! $screen ) {
			return false;
		}

		$screens = array(
			'toplevel_page_lifterlms',
			'lifterlms_page_llms-dashboard',
			'lifterlms_page_llms-settings',
			'lifterlms_page_llms-reporting',
			'lifterlms_page_llms-import',
			'lifterlms_page_llms-status',
			'lifterlms_page_llms-resources',
			'lifterlms_page_llms-add-ons',
		);

		/**
		 * Filters the list of admin screen IDs where the LifterLMS help beacon is displayed.
		 *
		 * @since [version]
		 *
		 * @param string[] $screens Array of `WP_Screen` IDs.
		 */
		$screens = apply_filters( 'llms_help_beacon_screens', $screens );

		if ( in_array( $screen->id, $screens, true ) ) {
			return true;
		}

		if ( empty( $screen->post_type ) ) {
			return false;
		}

		/*
		 * Post types whose admin screens display the beacon, each mapped to the allowed
		 * `WP_Screen` bases: `edit` is the list table, `edit-tags` a taxonomy screen, and
		 * `post` the add/edit block editor.
		 *
		 * These are grouped by the admin menu they live under. The Courses and Memberships
		 * menus omit the `post` base so the beacon does not clutter the block editor. The
		 * Engagements menu (engagements, achievements, certificates, emails) and the Orders
		 * menu (orders, coupons, vouchers) include the `post` base so the beacon appears on
		 * all of their pages, including the editors.
		 */
		$post_types = array(
			// Courses menu.
			'course'              => array( 'edit', 'edit-tags' ),
			// Memberships menu.
			'llms_membership'     => array( 'edit', 'edit-tags' ),
			// Engagements menu.
			'llms_engagement'     => array( 'edit', 'edit-tags', 'post' ),
			'llms_achievement'    => array( 'edit', 'edit-tags', 'post' ),
			'llms_my_achievement' => array( 'edit', 'edit-tags', 'post' ),
			'llms_certificate'    => array( 'edit', 'edit-tags', 'post' ),
			'llms_my_certificate' => array( 'edit', 'edit-tags', 'post' ),
			'llms_email'          => array( 'edit', 'edit-tags', 'post' ),
			// Orders menu.
			'llms_order'          => array( 'edit', 'edit-tags', 'post' ),
			'llms_coupon'         => array( 'edit', 'edit-tags', 'post' ),
			'llms_voucher'        => array( 'edit', 'edit-tags', 'post' ),
		);

		/**
		 * Filters the post type admin screens where the LifterLMS help beacon is displayed.
		 *
		 * Each key is a post type name and each value is an array of allowed `WP_Screen`
		 * base values: `edit` (list table), `edit-tags` (taxonomy screens), and `post`
		 * (the add/edit editor).
		 *
		 * @since [version]
		 *
		 * @param array $post_types Map of post type name to an array of allowed screen bases.
		 */
		$post_types = apply_filters( 'llms_help_beacon_post_types', $post_types );

		return isset( $post_types[ $screen->post_type ] )
			&& in_array( $screen->base, (array) $post_types[ $screen->post_type ], true );
	}

	/**
	 * Determine whether the site has a valid LifterLMS add-on license.
	 *
	 * @since [version]
	 *
	 * @return boolean
	 */
	public function is_licensed() {

		$licensed = false;

		if ( function_exists( 'llms_helper_options' ) ) {
			foreach ( llms_helper_options()->get_license_keys() as $key ) {
				if ( isset( $key['status'] ) && 1 == $key['status'] ) { // phpcs:ignore Universal.Operators.StrictComparisons.LooseEqual -- Status is stored as a numeric string.
					$licensed = true;
					break;
				}
			}
		}

		/**
		 * Filters whether the site is considered licensed when determining which help beacon to display.
		 *
		 * @since [version]
		 *
		 * @param boolean $licensed Whether a valid license key was found.
		 */
		return (bool) apply_filters( 'llms_help_beacon_is_licensed', $licensed );
	}

	/**
	 * Retrieve the HelpScout beacon ID for the current license state.
	 *
	 * @since [version]
	 *
	 * @param boolean|null $is_licensed Whether the site is licensed. When `null` the license state is determined automatically.
	 * @return string
	 */
	public function get_beacon_id( $is_licensed = null ) {

		$is_licensed = is_null( $is_licensed ) ? $this->is_licensed() : (bool) $is_licensed;
		$beacon_id   = $is_licensed ? self::SUPPORT_BEACON_ID : self::PRESALES_BEACON_ID;

		/**
		 * Filters the HelpScout beacon ID to display.
		 *
		 * @since [version]
		 *
		 * @param string  $beacon_id   The HelpScout beacon ID.
		 * @param boolean $is_licensed Whether the site has a valid license.
		 */
		return apply_filters( 'llms_help_beacon_id', $beacon_id, $is_licensed );
	}

	/**
	 * Build the beacon session data from the LifterLMS system report.
	 *
	 * The data is attached to the beacon as agent-only session data. It is visible in
	 * the HelpScout conversation activity but is not added to the customer's message.
	 * Each top-level system report section becomes a single session data attribute.
	 *
	 * @since [version]
	 *
	 * @return array Associative array of attribute label => string value.
	 */
	protected function get_session_data() {

		if ( ! class_exists( 'LLMS_Data' ) ) {
			return array();
		}

		$report = LLMS_Data::get_data( 'system_report' );
		$data   = array();

		foreach ( $report as $section => $values ) {
			$label = 'LifterLMS: ' . ucwords( str_replace( '_', ' ', $section ) );
			$value = is_array( $values ) ? $this->stringify_report_section( $values ) : (string) $values;
			// HelpScout limits session data values to 10,000 characters.
			$data[ $label ] = strlen( $value ) > 10000 ? substr( $value, 0, 9997 ) . '...' : $value;
		}

		// HelpScout limits session data to 20 attribute/value pairs.
		$data = array_slice( $data, 0, 20, true );

		/**
		 * Filters the session data attached to the help beacon.
		 *
		 * Return an empty array to prevent the system report from being attached to beacon conversations.
		 *
		 * @since [version]
		 *
		 * @param array $data Associative array of session data attribute labels and string values.
		 */
		return apply_filters( 'llms_help_beacon_session_data', $data );
	}

	/**
	 * Flatten a system report section into a human-readable multi-line string.
	 *
	 * @since [version]
	 *
	 * @param array $section Associative array of report values.
	 * @return string
	 */
	protected function stringify_report_section( $section ) {

		$lines = array();

		foreach ( $section as $key => $value ) {
			if ( is_bool( $value ) ) {
				$value = $value ? 'true' : 'false';
			} elseif ( is_array( $value ) ) {
				$value = wp_json_encode( $value );
			}
			$lines[] = $key . ': ' . $value;
		}

		return implode( "\n", $lines );
	}

	/**
	 * Build the inline styles for the beacon launcher button.
	 *
	 * @since [version]
	 *
	 * @return string
	 */
	protected function get_inline_styles() {

		// Colors mirror the `.llms-button-primary` styles for visual consistency with the admin UI.
		return '#llms-help-beacon-launcher{position:fixed;bottom:20px;right:20px;z-index:100000;display:flex;align-items:center;justify-content:center;width:56px;height:56px;padding:0;border:0;border-radius:50%;background:#466dd8;color:#fefefe;font-size:24px;font-weight:700;line-height:1;cursor:pointer;box-shadow:0 4px 12px rgba(0,0,0,.25);transition:background .2s ease;}#llms-help-beacon-launcher:hover{background:#2b55cb;}#llms-help-beacon-launcher:focus{background:#6888df;}';
	}

	/**
	 * Build the inline script that renders the launcher and lazily loads the beacon.
	 *
	 * The HelpScout beacon script is only injected after the user clicks the launcher
	 * and confirms the consent prompt, so no third-party script or cookie is loaded
	 * until the user opts in.
	 *
	 * @since [version]
	 *
	 * @param string $beacon_id The HelpScout beacon ID to initialize.
	 * @return string
	 */
	protected function get_inline_script( $beacon_id ) {

		$user = wp_get_current_user();

		$settings = array(
			'beaconId' => $beacon_id,
			'label'    => __( 'LifterLMS Help', 'lifterlms' ),
			'consent'  => __( 'When you click OK we will open our HelpScout beacon where you can find answers to your questions. This beacon will load our support data and also potentially set cookies.', 'lifterlms' ),
			'prefill'  => array(
				'name'  => $user->exists() ? $user->display_name : '',
				'email' => $user->exists() ? $user->user_email : '',
			),
		);

		// Attach the LifterLMS system report as agent-only session data on both the support and pre-sales beacons.
		$session_data = $this->get_session_data();
		if ( ! empty( $session_data ) ) {
			$settings['sessionData'] = $session_data;
		}

		// Encode with tag/amp hex escaping so the data is safe to embed within the inline <script> tag.
		$json = wp_json_encode( $settings, JSON_HEX_TAG | JSON_HEX_AMP );

		return "( function() {
	var settings = {$json};
	var loaded   = false;

	function loadBeacon() {
		// Official HelpScout Beacon embed loader.
		!function(e,t,n){function a(){var e=t.getElementsByTagName('script')[0],n=t.createElement('script');n.type='text/javascript',n.async=!0,n.src='https://beacon-v2.helpscout.net',e.parentNode.insertBefore(n,e)}if(e.Beacon=n=function(t,n,a){e.Beacon.readyQueue.push({method:t,options:n,data:a})},n.readyQueue=[],'complete'===t.readyState)return a();e.attachEvent?e.attachEvent('onload',a):e.addEventListener('load',a,!1)}(window,document,window.Beacon||function(){});
		window.Beacon( 'init', settings.beaconId );
		if ( settings.prefill && settings.prefill.email ) {
			window.Beacon( 'prefill', settings.prefill );
		}
		if ( settings.sessionData ) {
			window.Beacon( 'session-data', settings.sessionData );
		}
		loaded = true;
	}

	function createLauncher() {
		if ( document.getElementById( 'llms-help-beacon-launcher' ) ) {
			return;
		}
		var btn = document.createElement( 'button' );
		btn.type = 'button';
		btn.id = 'llms-help-beacon-launcher';
		btn.setAttribute( 'aria-label', settings.label );
		btn.appendChild( document.createTextNode( '?' ) );
		btn.addEventListener( 'click', function() {
			if ( ! loaded ) {
				if ( ! window.confirm( settings.consent ) ) {
					return;
				}
				loadBeacon();
				btn.style.display = 'none';
			}
			if ( window.Beacon ) {
				window.Beacon( 'open' );
			}
		} );
		document.body.appendChild( btn );
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', createLauncher );
	} else {
		createLauncher();
	}
} )();";
	}
}

return new LLMS_Admin_Help_Beacon();
