<?php
/**
 * Tests for LifterLMS Certificate Sync Meta Box.
 *
 * @package LifterLMS/Tests
 *
 * @group metabox_certificate_sync
 * @group admin
 * @group metaboxes
 * @group metaboxes_post_type
 *
 * @since [version]
 * @version [version]
 */
class LLMS_Test_Meta_Box_Certificate_Sync extends LLMS_PostTypeMetaboxTestCase {

	/**
	 * @var LLMS_Meta_Box_Certificate_Sync
	 */
	private $metabox;

	/**
	 * Setup test.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function set_up() {

		parent::set_up();
		$this->metabox = new LLMS_Meta_Box_Certificate_Sync();
	}

	/**
	 * Tear down test.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function tear_down() {

		// Reset current screen.
		llms_tests_reset_current_screen();
	}


	/**
	 * Test the get_screens() method.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_screens() {

		$this->assertEquals(
			array( 'llms_certificate', 'llms_my_certificate' ),
			LLMS_Unit_Test_Util::call_method( $this->metabox, 'get_screens' )
		);
	}

	/**
	 * Test sync awarded certificate action.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_sync_action_certificate() {

		$action = 'action=sync_awarded_certificate';

		$post                = $this->factory->post->create_and_get();
		$this->metabox->post = $post;
		$this->metabox->configure();

		// Not llms_my_certificate post type.
		$this->assertStringNotContainsString(
			$action,
			LLMS_Unit_Test_Util::call_method( $this->metabox, 'sync_action' )
		);

		$my_certificate      = $this->factory->post->create_and_get( array( 'post_type' => 'llms_my_certificate' ) );
		$this->metabox->post = $my_certificate;
		$this->metabox->configure();

		// llms_my_certificate post type but no certificate template parent.
		$this->assertStringNotContainsString(
			$action,
			LLMS_Unit_Test_Util::call_method( $this->metabox, 'sync_action' )
		);

		// Set a template which is not an `llms_certificate`.
		$template = $this->factory->post->create_and_get();
		wp_update_post(
			array(
				'ID'          => $my_certificate->ID,
				'post_parent' => $template->ID,
			)
		);
		$this->assertStringNotContainsString(
			$action,
			LLMS_Unit_Test_Util::call_method( $this->metabox, 'sync_action' )
		);

		// Set a template which is a `llms_certificate`.
		wp_update_post(
			array(
				'ID'        => $template->ID,
				'post_type' => 'llms_certificate',
			)
		);
		$this->assertStringContainsString(
			$action,
			LLMS_Unit_Test_Util::call_method( $this->metabox, 'sync_action' )
		);

		// Delete created posts.
		foreach ( array( $post, $my_certificate, $template ) as $to_delete ) {
			wp_delete_post( $to_delete->ID );
		}
	}

	/**
	 * Test sync awarded certificates action.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_sync_action_certificates() {

		$action = 'action=sync_awarded_certificates';

		$post                = $this->factory->post->create_and_get();
		$this->metabox->post = $post;
		$this->metabox->configure();

		// Not llms_certificate post type.
		$this->assertStringNotContainsString(
			$action,
			LLMS_Unit_Test_Util::call_method( $this->metabox, 'sync_action' )
		);
		wp_delete_post( $post->ID, true );

		$post                = $this->factory->post->create_and_get( array( 'post_type' => 'llms_certificate' ) );
		$this->metabox->post = $post;
		$this->metabox->configure();

		// llms_certificate post type but no awarded certificates.
		$this->assertStringNotContainsString(
			$action,
			LLMS_Unit_Test_Util::call_method( $this->metabox, 'sync_action' )
		);

		$awarded_certificates = array();

		// Create various awarded certificates but with a different template.
		foreach ( get_available_post_statuses( 'llms_my_certificate' ) as $status ) {
			$awarded_certificates[] = $this->factory->post->create(
				array(
					'post_type'   => 'llms_my_certificate',
					'post_parent' => 999,
					'post_status' => $status,
				)
			);
		}
		$this->assertStringNotContainsString(
			$action,
			LLMS_Unit_Test_Util::call_method( $this->metabox, 'sync_action' )
		);

		// Create various awarded certificates: only 3 of them have the required post_status (draft, future and publish).
		foreach ( get_available_post_statuses( 'llms_my_certificate' ) as $status ) {
			$awarded_certificates[] = $this->factory->post->create(
				array(
					'post_type'   => 'llms_my_certificate',
					'post_parent' => $post->ID,
					'post_status' => $status,
				)
			);
		}

		$this->assertStringContainsString(
			$action,
			LLMS_Unit_Test_Util::call_method( $this->metabox, 'sync_action' )
		);

		$this->assertStringContainsString(
			'3 awarded certificates',
			LLMS_Unit_Test_Util::call_method( $this->metabox, 'sync_action' )
		);

		// Delete created posts.
		foreach ( array_merge( $awarded_certificates, array( $post->ID ) ) as $to_delete ) {
			wp_delete_post( $to_delete );
		}
	}
}
