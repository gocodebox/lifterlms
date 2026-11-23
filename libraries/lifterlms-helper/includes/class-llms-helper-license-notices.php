<?php
/**
 * Admin notices for sites running paid add-ons without a license
 *
 * @package LifterLMS_Helper/Classes
 *
 * @since [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Helper_License_Notices
 *
 * Shows one dismissible notice while the site runs add-ons that require a license and are not covered
 * by a license key. The notice removes itself as soon as every active add-on is licensed.
 *
 * @since [version]
 */
class LLMS_Helper_License_Notices {

	/**
	 * ID of the notice shown when the site's license keys lapsed.
	 *
	 * @var string
	 */
	const NOTICE_LAPSED = 'license-lapsed';

	/**
	 * ID of the notice shown when add-ons are unlicensed and no license key lapsed.
	 *
	 * @var string
	 */
	const NOTICE_MISSING = 'license-missing';

	/**
	 * Constructor
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function __construct() {

		add_action( 'current_screen', array( $this, 'sync_notices' ), 77 );
		add_filter( 'llms_admin_notice_is_visible', array( $this, 'limit_notice_screens' ), 10, 2 );

		// Lets the banner notifications feed target sites by license status, e.g. `"show_if": { "llms_license": "none" }`.
		add_filter( 'lifterlms_notification_test_llms_license', array( $this, 'banner_test_license' ), 10, 2 );
	}

	/**
	 * Banner notifications feed test: whether the site's license status is one of the requested statuses.
	 *
	 * @since [version]
	 *
	 * @param bool            $value    The current test value.
	 * @param string|string[] $statuses License status or statuses to match, see {@see llms_helper_get_license_status()}.
	 * @return bool
	 */
	public function banner_test_license( $value, $statuses ) {
		return in_array( llms_helper_get_license_status(), (array) $statuses, true );
	}

	/**
	 * Retrieve the URL of the LifterLMS.com pricing page.
	 *
	 * @since [version]
	 *
	 * @param string $content Value of the `utm_content` query arg.
	 * @return string
	 */
	private function get_pricing_url( $content ) {

		return add_query_arg(
			array(
				'utm_source'   => rawurlencode( 'LifterLMS Plugin' ),
				'utm_medium'   => rawurlencode( 'Banner' ),
				'utm_campaign' => rawurlencode( 'Plugin to Sale' ),
				'utm_content'  => rawurlencode( $content ),
			),
			'https://lifterlms.com/pricing'
		);
	}

	/**
	 * Retrieve the HTML of a notice.
	 *
	 * @since [version]
	 *
	 * @param string               $notice_id ID of the notice, either `self::NOTICE_LAPSED` or `self::NOTICE_MISSING`.
	 * @param LLMS_Helper_Add_On[] $add_ons   The unlicensed add-ons.
	 * @return string
	 */
	private function get_notice_html( $notice_id, $add_ons ) {

		$add_ons_url = admin_url( 'admin.php?page=llms-add-ons' );

		if ( self::NOTICE_LAPSED === $notice_id ) {
			$title   = __( 'Your LifterLMS License Has Expired…', 'lifterlms' );
			$message = __( 'Your paid add-ons will continue to work as is, but this site no longer receives security updates, new releases, or priority support. Renew your license or get a new one to keep everything current and protected. And regain access to the talented LifterLMS support team when you need it.', 'lifterlms' );
			$buttons = array(
				array( __( 'Activate My License', 'lifterlms' ), $this->get_pricing_url( 'Expired License Detected' ), true ),
				array( __( 'Enter a License Key', 'lifterlms' ), $add_ons_url, false ),
			);
		} else {
			$title   = __( 'Activate Your LifterLMS License Key', 'lifterlms' );
			$message = __( 'This site runs paid LifterLMS add-ons that are not covered by an active license key. They will continue to work as is, but they do not receive security updates or new releases, and priority support is not available. Enter your license key or get a license to keep everything current and protected.', 'lifterlms' );
			$buttons = array(
				array( __( 'Enter a License Key', 'lifterlms' ), $add_ons_url, false ),
				array( __( 'Compare LifterLMS Plans', 'lifterlms' ), $this->get_pricing_url( 'Unlicensed Add-Ons Detected' ), true ),
			);
		}

		$titles = array();
		foreach ( $add_ons as $add_on ) {
			$titles[] = $add_on->get( 'title' );
		}

		$html  = '<p><strong>' . esc_html( $title ) . '</strong></p>';
		$html .= '<p>' . esc_html( $message ) . '</p>';
		// Translators: %s = Comma-separated list of add-on names.
		$html .= '<p>' . esc_html( sprintf( __( 'Add-ons without a license: %s.', 'lifterlms' ), implode( ', ', $titles ) ) ) . '</p>';

		$html .= '<p>';
		foreach ( $buttons as $index => $button ) {
			list( $text, $url, $external ) = $button;
			$html                         .= sprintf(
				'<a class="%1$s" href="%2$s"%3$s>%4$s</a> ',
				0 === $index ? 'button button-primary' : 'button',
				esc_url( $url ),
				$external ? ' target="_blank" rel="noopener noreferrer"' : '',
				esc_html( $text )
			);
		}
		$html .= '</p>';

		return $html;
	}

	/**
	 * Determine which notice applies to the site.
	 *
	 * @since [version]
	 *
	 * @param LLMS_Helper_Add_On[] $add_ons The unlicensed add-ons.
	 * @return string ID of the notice or an empty string when no notice applies.
	 */
	private function get_notice_id( $add_ons ) {

		$notice_id = '';

		if ( $add_ons ) {

			$notice_id = self::NOTICE_MISSING;

			if ( 'lapsed' === llms_helper_get_license_status() ) {

				/**
				 * Filters how long after the site's last license key lapsed the lapsed license notice is first shown.
				 *
				 * The notice that the key was deactivated is shown as soon as the key lapses.
				 *
				 * @since [version]
				 *
				 * @param int $delay Delay in seconds.
				 */
				$delay     = apply_filters( 'llms_helper_lapsed_license_notice_delay', WEEK_IN_SECONDS );
				$lapsed_at = max( wp_list_pluck( llms_helper_options()->get_lapsed_license_keys(), 'time' ) );
				$notice_id = time() >= $lapsed_at + $delay ? self::NOTICE_LAPSED : '';

			}
		}

		/**
		 * Filters which license notice is shown on the site.
		 *
		 * @since [version]
		 *
		 * @param string               $notice_id ID of the notice: `license-lapsed`, `license-missing`, or an empty string to show no notice.
		 * @param LLMS_Helper_Add_On[] $add_ons   The active add-ons that require a license and are not covered by a license key.
		 */
		return apply_filters( 'llms_helper_license_notice', $notice_id, $add_ons );
	}

	/**
	 * Show the license notices on the screens where licenses and updates are managed and on LifterLMS screens only.
	 *
	 * @since [version]
	 *
	 * @param bool   $visible   Whether the notice is output on the current screen.
	 * @param string $notice_id ID of the notice.
	 * @return bool
	 */
	public function limit_notice_screens( $visible, $notice_id ) {

		if ( ! in_array( $notice_id, array( self::NOTICE_LAPSED, self::NOTICE_MISSING ), true ) ) {
			return $visible;
		}

		$screen = get_current_screen();
		if ( ! $screen ) {
			return false;
		}

		if ( in_array( $screen->id, array( 'dashboard', 'plugins', 'themes', 'update-core' ), true ) ) {
			return $visible;
		}

		foreach ( array( $screen->id, (string) $screen->post_type ) as $name ) {
			if ( false !== strpos( $name, 'llms' ) || false !== strpos( $name, 'lifterlms' ) || in_array( $name, array( 'course', 'lesson' ), true ) ) {
				return $visible;
			}
		}

		return false;
	}

	/**
	 * Add the notice that applies to the site and remove the ones that do not.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function sync_notices() {

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$add_ons   = llms_helper_get_unlicensed_add_ons();
		$notice_id = $this->get_notice_id( $add_ons );

		foreach ( array( self::NOTICE_LAPSED, self::NOTICE_MISSING ) as $id ) {

			if ( $id === $notice_id ) {
				LLMS_Admin_Notices::add_notice(
					$id,
					$this->get_notice_html( $id, $add_ons ),
					array(
						'type'             => 'warning',
						'dismiss_for_days' => 30,
					)
				);
			} elseif ( LLMS_Admin_Notices::has_notice( $id ) ) {
				LLMS_Admin_Notices::delete_notice( $id );
			}
		}
	}
}

return new LLMS_Helper_License_Notices();
