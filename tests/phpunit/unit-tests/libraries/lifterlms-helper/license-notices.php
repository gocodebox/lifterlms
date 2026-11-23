<?php
/**
 * Test license status tracking and the license notices
 *
 * @package LifterLMS_Helper/Tests
 *
 * @group license_notices
 *
 * @since [version]
 */
class LLMS_Helper_Test_License_Notices extends LLMS_Helper_Unit_Test_Case {

	/**
	 * License key used by the tests.
	 *
	 * @var string
	 */
	private $key = 'aaaaaaa-mock-license-key-zzzzzzz';

	/**
	 * Set up the test.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function set_up() {

		parent::set_up();

		require_once LLMS_PLUGIN_DIR . 'includes/admin/class.llms.admin.notices.php';
		LLMS_Admin_Notices::init();

		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );

	}

	/**
	 * Tear down the test.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function tear_down() {

		foreach ( LLMS_Admin_Notices::get_notices() as $id ) {
			LLMS_Admin_Notices::delete_notice( $id );
		}
		set_current_screen( 'front' );

		parent::tear_down();

	}

	/**
	 * Make WordPress report plugins as installed and active.
	 *
	 * @since [version]
	 *
	 * @param string[] $active    Plugin files, relative to the plugins directory, to report as installed and active.
	 * @param string[] $installed Plugin files to report as installed and inactive.
	 * @return void
	 */
	private function mock_plugins( $active, $installed = array() ) {

		$plugins = array_fill_keys( array_merge( $active, $installed ), array( 'Name' => 'Mock', 'Version' => '1.0.0' ) );
		wp_cache_set( 'plugins', array( '' => $plugins ), 'plugins' );
		update_option( 'active_plugins', $active );

	}

	/**
	 * Save a license key covering the Stripe add-on.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	private function add_stripe_license() {

		LLMS_Helper_Keys::add_license_key(
			array(
				'id'          => 'lifterlms-com-stripe-extension',
				'license_key' => $this->key,
				'update_key'  => 'mock-update-key',
				'addons'      => array( 'lifterlms-com-stripe-extension' ),
			)
		);

	}

	/**
	 * Run the weekly license check with LifterLMS.com reporting the saved key as inactive.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	private function lapse_stripe_license() {

		$this->mock_http_request(
			'https://lifterlms.com/wp-json/llms/v3/license/status',
			array(
				'body'     => wp_json_encode(
					array(
						'code'    => 'success',
						'message' => 'Success',
						'data'    => array(
							'status' => 200,
							'keys'   => array(
								$this->key => array( 'status' => 0 ),
							),
						),
					)
				),
				'response' => array(
					'code'    => 200,
					'message' => 'OK',
				),
			)
		);

		LLMS_Helper_Keys::check_keys( true );

	}

	/**
	 * Move the recorded lapse of the license keys into the past.
	 *
	 * @since [version]
	 *
	 * @param int $seconds Number of seconds.
	 * @return void
	 */
	private function age_lapse( $seconds ) {

		$lapsed = llms_helper_options()->get_lapsed_license_keys();
		foreach ( $lapsed as &$record ) {
			$record['time'] -= $seconds;
		}
		llms_helper_options()->set_lapsed_license_keys( $lapsed );

	}

	/**
	 * A key reported as inactive by LifterLMS.com is removed and remembered, a manually removed key is not.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_license_status() {

		$this->assertSame( 'none', llms_helper_get_license_status() );

		$this->add_stripe_license();
		$this->assertSame( 'active', llms_helper_get_license_status() );

		// Removed by the site's admin.
		LLMS_Helper_Keys::remove_license_key( $this->key );
		$this->assertSame( 'none', llms_helper_get_license_status() );

		// Reported as inactive by LifterLMS.com.
		$this->add_stripe_license();
		$time = time();
		$this->lapse_stripe_license();

		$this->assertSame( array(), llms_helper_options()->get_license_keys() );
		$this->assertSame( 'lapsed', llms_helper_get_license_status() );

		$lapsed = llms_helper_options()->get_lapsed_license_keys();
		$this->assertSame( array( 'aaaaaaa******************zzzzzzz' ), array_keys( $lapsed ) );
		$this->assertSame( 'lifterlms-com-stripe-extension', $lapsed['aaaaaaa******************zzzzzzz']['product_id'] );
		$this->assertEqualsWithDelta( $time, $lapsed['aaaaaaa******************zzzzzzz']['time'], 5 );

		// The full key is not kept.
		$this->assertStringNotContainsString( $this->key, wp_json_encode( get_option( 'llms_helper_options' ) ) );

		// Licensed again.
		$this->add_stripe_license();
		$this->assertSame( 'active', llms_helper_get_license_status() );
		$this->assertSame( array(), llms_helper_options()->get_lapsed_license_keys() );

	}

	/**
	 * Only active add-ons that require a license and are not covered by a saved key are unlicensed.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_unlicensed_add_ons() {

		$this->mock_plugins(
			array(
				'lifterlms-stripe/lifterlms-stripe.php', // Requires a license, licensed below.
				'lifterlms-pdfs/lifterlms-pdfs.php',     // Requires a license, not licensed.
				'lifterlms/lifterlms.php',               // Does not require a license.
			),
			array(
				'lifterlms-groups/lifterlms-groups.php', // Requires a license, inactive.
			)
		);
		$this->add_stripe_license();

		$ids = array();
		foreach ( llms_helper_get_unlicensed_add_ons() as $add_on ) {
			$ids[] = $add_on->get( 'id' );
		}

		$this->assertSame( array( 'lifterlms-com-lifterlms-pdfs' ), $ids );

		// Including the installed add-ons that are not active.
		$ids = array();
		foreach ( llms_helper_get_unlicensed_add_ons( false ) as $add_on ) {
			$ids[] = $add_on->get( 'id' );
		}
		sort( $ids );

		$this->assertSame( array( 'lifterlms-com-groups', 'lifterlms-com-lifterlms-pdfs' ), $ids );

	}

	/**
	 * A theme add-on is in use when a child theme of it is the active theme.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_unlicensed_add_ons_child_theme() {

		$this->mock_plugins( array( 'lifterlms/lifterlms.php' ) );
		$this->assertSame( array(), llms_helper_get_unlicensed_add_ons() );

		$child_of_sky_pilot = function () {
			return 'sky-pilot';
		};
		add_filter( 'template', $child_of_sky_pilot );
		$add_ons = llms_helper_get_unlicensed_add_ons();
		remove_filter( 'template', $child_of_sky_pilot );

		$this->assertCount( 1, $add_ons );
		$this->assertSame( 'lifterlms-com-sky-pilot', $add_ons[0]->get( 'id' ) );

	}

	/**
	 * No notice is added on a site without paid add-ons, whatever its license status.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_no_notice_without_paid_add_ons() {

		$this->mock_plugins( array( 'lifterlms/lifterlms.php' ) );
		$notices = new LLMS_Helper_License_Notices();

		$notices->sync_notices();
		$this->assertSame( array(), LLMS_Admin_Notices::get_notices() );

		$this->add_stripe_license();
		$this->lapse_stripe_license();
		$this->age_lapse( YEAR_IN_SECONDS );
		LLMS_Admin_Notices::delete_notice( 'key_check_aaaaaaa******************zzzzzzz' );

		$notices->sync_notices();
		$this->assertSame( array(), LLMS_Admin_Notices::get_notices() );

	}

	/**
	 * A site running a paid add-on that was never licensed is asked for a license key right away.
	 *
	 * The notice is removed once the add-on is licensed.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_missing_license_notice() {

		$this->mock_plugins( array( 'lifterlms-stripe/lifterlms-stripe.php' ) );
		$notices = new LLMS_Helper_License_Notices();

		$notices->sync_notices();
		$this->assertTrue( LLMS_Admin_Notices::has_notice( 'license-missing' ) );
		$this->assertFalse( LLMS_Admin_Notices::has_notice( 'license-lapsed' ) );

		$html = LLMS_Admin_Notices::get_notice( 'license-missing' )['html'];
		$this->assertStringContainsString( 'Add-ons without a license: LifterLMS Stripe.', $html );
		$this->assertStringContainsString( esc_url( admin_url( 'admin.php?page=llms-add-ons' ) ), $html );

		$this->add_stripe_license();
		$notices->sync_notices();
		$this->assertFalse( LLMS_Admin_Notices::has_notice( 'license-missing' ) );

	}

	/**
	 * A site whose license lapsed sees the lapsed license notice once the license has been lapsed for a week.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_lapsed_license_notice() {

		$this->mock_plugins( array( 'lifterlms-stripe/lifterlms-stripe.php' ) );
		$notices = new LLMS_Helper_License_Notices();

		$this->add_stripe_license();
		$this->lapse_stripe_license();

		// Six days after the lapse: only the notice that the key was deactivated.
		$this->age_lapse( 6 * DAY_IN_SECONDS );
		$notices->sync_notices();
		$this->assertFalse( LLMS_Admin_Notices::has_notice( 'license-lapsed' ) );
		$this->assertFalse( LLMS_Admin_Notices::has_notice( 'license-missing' ) );
		$this->assertTrue( LLMS_Admin_Notices::has_notice( 'key_check_aaaaaaa******************zzzzzzz' ) );

		// Eight days after the lapse: the lapsed license notice replaces it.
		$this->age_lapse( 2 * DAY_IN_SECONDS );
		$notices->sync_notices();
		$this->assertTrue( LLMS_Admin_Notices::has_notice( 'license-lapsed' ) );
		$this->assertFalse( LLMS_Admin_Notices::has_notice( 'license-missing' ) );
		$this->assertFalse( LLMS_Admin_Notices::has_notice( 'key_check_aaaaaaa******************zzzzzzz' ) );

		$notice = LLMS_Admin_Notices::get_notice( 'license-lapsed' );
		$this->assertTrue( $notice['dismissible'] );
		$this->assertStringContainsString( 'Your LifterLMS License Has Expired…', $notice['html'] );
		$this->assertStringContainsString(
			esc_url( 'https://lifterlms.com/pricing?utm_source=LifterLMS%20Plugin&utm_medium=Banner&utm_campaign=Plugin%20to%20Sale&utm_content=Expired%20License%20Detected' ),
			$notice['html']
		);

		// Dismissed notices stay hidden.
		LLMS_Admin_Notices::delete_notice( 'license-lapsed', 'hide' );
		$notices->sync_notices();
		$this->assertFalse( LLMS_Admin_Notices::has_notice( 'license-lapsed' ) );

	}

	/**
	 * License notices are output on LifterLMS screens and the screens where plugins are managed only.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_notice_screens() {

		$this->mock_plugins( array( 'lifterlms-stripe/lifterlms-stripe.php' ) );
		( new LLMS_Helper_License_Notices() )->sync_notices();
		LLMS_Admin_Notices::add_notice( 'mock-other-notice', 'Mock other notice.' );

		$screens = array(
			'dashboard'                     => true,
			'plugins'                       => true,
			'lifterlms_page_llms-reporting' => true,
			'edit-course'                   => true,
			'edit-post'                     => false,
			'options-general'               => false,
		);

		foreach ( $screens as $screen => $visible ) {

			set_current_screen( $screen );

			$license = $this->get_output( array( 'LLMS_Admin_Notices', 'output_notice' ), array( 'license-missing' ) );
			$this->assertSame( $visible, false !== strpos( $license, 'llms-noticelicense-missing' ), $screen );

			// Other notices are not affected.
			$other = $this->get_output( array( 'LLMS_Admin_Notices', 'output_notice' ), array( 'mock-other-notice' ) );
			$this->assertStringContainsString( 'Mock other notice.', $other, $screen );

		}

		// Hidden notices stay saved.
		$this->assertTrue( LLMS_Admin_Notices::has_notice( 'license-missing' ) );

	}

	/**
	 * Each installed add-on without a license gets a message under its row on the Plugins screen.
	 *
	 * The message replaces the one the upgrader adds to the "update available" row of the add-on.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_plugin_row_notices() {

		$stripe = 'lifterlms-stripe/lifterlms-stripe.php'; // Active, licensed.
		$pdfs   = 'lifterlms-pdfs/lifterlms-pdfs.php';     // Active, not licensed.
		$groups = 'lifterlms-groups/lifterlms-groups.php'; // Inactive, not licensed.
		$core   = 'lifterlms/lifterlms.php';               // Does not require a license.

		$this->mock_plugins( array( $stripe, $pdfs, $core ), array( $groups ) );
		$this->add_stripe_license();

		$upgrader = LLMS_Helper_Upgrader::instance();
		foreach ( array( $stripe, $pdfs, $groups ) as $file ) {
			add_action( "in_plugin_update_message-{$file}", array( $upgrader, 'in_plugin_update_message' ), 10, 2 );
		}

		$get_row     = function ( $file ) {
			return $this->get_output( 'do_action', array( "after_plugin_row_{$file}", $file, array(), 'all' ) );
		};
		$get_message = function ( $file ) {
			return $this->get_output( 'do_action', array( "in_plugin_update_message-{$file}", array( 'package' => '' ), new stdClass() ) );
		};

		// Not on other screens.
		set_current_screen( 'dashboard' );
		$this->assertSame( '', $get_row( $pdfs ) );

		// Not for users who cannot manage the site's license keys.
		$admin = get_current_user_id();
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'editor' ) ) );
		set_current_screen( 'plugins' );
		$this->assertSame( '', $get_row( $pdfs ) );

		wp_set_current_user( $admin );
		set_current_screen( 'plugins' );

		$row = $get_row( $pdfs );
		$this->assertSame( 1, substr_count( $row, '<tr' ) );
		$this->assertStringContainsString( '<strong>LifterLMS PDFs</strong> is not covered by an active license key', $row );
		$this->assertStringContainsString( 'plugin-update-tr llms-license-row-notice active', $row );
		$this->assertStringContainsString( 'href="' . esc_url( admin_url( 'admin.php?page=llms-add-ons' ) ) . '"', $row );
		$this->assertStringContainsString( 'href="https://lifterlms.com/product/lifterlms-pdfs/?utm_source=LifterLMS%20Plugin', $row );

		$row = $get_row( $groups );
		$this->assertStringContainsString( '<strong>LifterLMS Groups</strong> is not covered by an active license key', $row );
		$this->assertStringNotContainsString( 'llms-license-row-notice active', $row );
		$this->assertStringContainsString( 'href="https://lifterlms.com/product/groups/?utm_source=LifterLMS%20Plugin', $row );

		$this->assertSame( '', $get_row( $stripe ) );
		$this->assertSame( '', $get_row( $core ) );

		// The upgrader's message is left to the add-ons that have no license row.
		$this->assertSame( '', $get_message( $pdfs ) );
		$this->assertSame( '', $get_message( $groups ) );
		$this->assertStringContainsString( 'currently unlicensed and cannot be updated', $get_message( $stripe ) );

	}

	/**
	 * The banner notifications feed can keep a banner for free sites away from sites that run unlicensed paid add-ons.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_banner_notification_unlicensed_add_ons_test() {

		$notifier     = $GLOBALS['lifterlms_banner_notifications'];
		$notification = (object) array(
			'show_if' => (object) array( 'llms_license' => 'none' ),
			'hide_if' => (object) array( 'llms_unlicensed_add_ons' => true ),
		);

		// A free site.
		$this->mock_plugins( array( 'lifterlms/lifterlms.php' ) );
		$this->assertTrue( $notifier->should_show_notification( $notification ) );
		$this->assertFalse( $notifier->should_hide_notification( $notification ) );

		// A site running a paid add-on that was never licensed.
		$this->mock_plugins( array( 'lifterlms/lifterlms.php', 'lifterlms-stripe/lifterlms-stripe.php' ) );
		$this->assertTrue( $notifier->should_show_notification( $notification ) );
		$this->assertTrue( $notifier->should_hide_notification( $notification ) );

		// The opposite expectation.
		$only_free = (object) array( 'show_if' => (object) array( 'llms_unlicensed_add_ons' => false ) );
		$this->assertFalse( $notifier->should_show_notification( $only_free ) );

	}

	/**
	 * The banner notifications feed can target sites by license status.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_banner_notification_license_test() {

		$notification = (object) array(
			'show_if' => (object) array( 'llms_license' => 'none' ),
			'hide_if' => (object) array( 'llms_license' => array( 'active', 'lapsed' ) ),
		);

		$this->assertTrue( $GLOBALS['lifterlms_banner_notifications']->should_show_notification( $notification ) );
		$this->assertFalse( $GLOBALS['lifterlms_banner_notifications']->should_hide_notification( $notification ) );

		$this->add_stripe_license();
		$this->assertFalse( $GLOBALS['lifterlms_banner_notifications']->should_show_notification( $notification ) );
		$this->assertTrue( $GLOBALS['lifterlms_banner_notifications']->should_hide_notification( $notification ) );

		$this->lapse_stripe_license();
		$this->assertFalse( $GLOBALS['lifterlms_banner_notifications']->should_show_notification( $notification ) );
		$this->assertTrue( $GLOBALS['lifterlms_banner_notifications']->should_hide_notification( $notification ) );

	}

}
