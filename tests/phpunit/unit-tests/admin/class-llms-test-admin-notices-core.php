<?php
/**
 * Tests for LLMS_Admin_Notices_Core class
 *
 * @package LifterLMS/Tests/Admin
 *
 * @group admin
 * @group admin_notices
 *
 * @since [version]
 */
class LLMS_Test_Admin_Notices_Core extends LLMS_UnitTestCase {

	/**
	 * Setup before class.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public static function set_up_before_class() {
		parent::set_up_before_class();
		require_once LLMS_PLUGIN_DIR . 'includes/admin/class.llms.admin.notices.php';
		require_once LLMS_PLUGIN_DIR . 'includes/admin/class.llms.admin.notices.core.php';
	}

	/**
	 * Tear down.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function tear_down() {
		remove_all_filters( 'llms_admin_notice_is_wpengine' );
		remove_all_filters( 'llms_media_serve_method' );
		LLMS_Admin_Notices::delete_notice( 'wpengine-media-protection' );
		parent::tear_down();
	}

	/**
	 * Test the WP Engine media protection notice is only shown on WP Engine once media is protected,
	 * and removed once the site is configured to serve files via NGINX.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_wpengine_media_protection() {

		$id = 'wpengine-media-protection';

		$attachment_id = $this->factory->attachment->create();

		// On WP Engine but nothing is protected yet.
		add_filter( 'llms_admin_notice_is_wpengine', '__return_true' );
		LLMS_Admin_Notices_Core::wpengine_media_protection();
		$this->assertFalse( LLMS_Admin_Notices::has_notice( $id ) );

		// Not on WP Engine with protected media.
		update_post_meta( $attachment_id, LLMS_Media_Protector::AUTHORIZATION_FILTER_KEY, 'llms_attachment_is_access_allowed' );
		remove_all_filters( 'llms_admin_notice_is_wpengine' );
		LLMS_Admin_Notices_Core::wpengine_media_protection();
		$this->assertFalse( LLMS_Admin_Notices::has_notice( $id ) );

		// On WP Engine with protected media.
		add_filter( 'llms_admin_notice_is_wpengine', '__return_true' );
		LLMS_Admin_Notices_Core::wpengine_media_protection();
		$this->assertTrue( LLMS_Admin_Notices::has_notice( $id ) );
		$this->assertStringContainsString( 'https://lifterlms.com/docs/how-protected-media-files-work/', LLMS_Admin_Notices::get_notice( $id )['html'] );
		$this->assertStringContainsString( '#server-requirements', LLMS_Admin_Notices::get_notice( $id )['html'] );

		// Efficient serving configured via the documented snippet: the notice is removed.
		add_filter(
			'llms_media_serve_method',
			function () {
				return LLMS_Media_Protector::SERVE_SEND_FILE;
			}
		);
		LLMS_Admin_Notices_Core::wpengine_media_protection();
		$this->assertFalse( LLMS_Admin_Notices::has_notice( $id ) );
	}
}
