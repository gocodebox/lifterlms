<?php
/**
 * Tests for LifterLMS Award Certificate Sync Meta Box.
 *
 * @package LifterLMS/Tests
 *
 * @group metabox_certificate_template_sync
 * @group admin
 * @group metaboxes
 * @group metaboxes_post_type
 *
 * @since [version]
 * @version [version]
 */
class LLMS_Test_Meta_Box_Certificate_Template_Sync extends LLMS_PostTypeMetaboxTestCase {

	/**
	 * Setup test.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function set_up() {

		parent::set_up();
		$this->metabox = new LLMS_Meta_Box_Certificate_Template_Sync();

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
			array( 'llms_certificate' ),
			LLMS_Unit_Test_Util::call_method( $this->metabox, 'get_screens' )
		);

	}

	/**
	 * Test sync awarded certificates action.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_sync_action() {

		$action = 'action=sync_awarded_certificates';

		$post                = $this->factory->post->create_and_get();
		$this->metabox->post = $post;

		LLMS_Unit_Test_Util::call_method(
			$this->metabox,
			'sync_action'
		);

		// Not llms_certificate post type.
		$this->assertStringNotContainsString(
			$action,
			LLMS_Unit_Test_Util::call_method(
				$this->metabox,
				'sync_action'
			)
		);
		wp_delete_post( $post->ID, true );

		$post                = $this->factory->post->create_and_get( array( 'post_type' => 'llms_certificate' ) );
		$this->metabox->post = $post;

		// llms_certificate post type but no awarded certificates.
		$this->assertStringNotContainsString(
			$action,
			LLMS_Unit_Test_Util::call_method(
				$this->metabox,
				'sync_action'
			)
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
			LLMS_Unit_Test_Util::call_method(
				$this->metabox,
				'sync_action'
			)
		);

		// Create various awarded certificates: only 2 of them have the required post_status (publish and future).
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
			LLMS_Unit_Test_Util::call_method(
				$this->metabox,
				'sync_action'
			)
		);

		$this->assertStringContainsString(
			'2 awarded certificates',
			LLMS_Unit_Test_Util::call_method(
				$this->metabox,
				'sync_action'
			)
		);

		// Delete created posts.
		foreach( array_merge( $awarded_certificates, array( $post->ID ) ) as $to_delete ) {
			wp_delete_post( $to_delete );
		}

	}

}
