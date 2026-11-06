<?php
/**
 * Tests for LifterLMS Membership Metabox
 *
 * @package LifterLMS/Tests
 *
 * @group metabox_membership
 * @group admin
 * @group metaboxes
 * @group metaboxes_post_type
 *
 * @since [version]
 */
class LLMS_Test_Meta_Box_Membership extends LLMS_PostTypeMetaboxTestCase {

	/**
	 * Setup test
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function set_up() {

		parent::set_up();
		$this->metabox = new LLMS_Meta_Box_Membership();

	}

	/**
	 * Test screens property.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_screens() {

		$this->assertEquals( array( 'llms_membership' ), $this->metabox->screens );

	}

	/**
	 * Test get_fields() includes Auto Enrollment tab for users with manage_lifterlms capability.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_fields_includes_auto_enrollment_for_managers() {

		$membership_id = $this->factory->post->create( array( 'post_type' => 'llms_membership' ) );
		$this->metabox->post = get_post( $membership_id );

		foreach ( array( 'administrator', 'lms_manager' ) as $role ) {
			wp_set_current_user( $this->factory->user->create( array( 'role' => $role ) ) );

			$fields = $this->metabox->get_fields();
			$titles = wp_list_pluck( $fields, 'title' );

			$this->assertContains( 'Auto Enrollment', $titles, sprintf( 'Role %s should have access to Auto Enrollment tab.', $role ) );
		}

	}

	/**
	 * Test get_fields() omits Auto Enrollment tab for users without manage_lifterlms capability.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_fields_omits_auto_enrollment_for_non_managers() {

		$membership_id = $this->factory->post->create( array( 'post_type' => 'llms_membership' ) );
		$this->metabox->post = get_post( $membership_id );

		foreach ( array( 'instructor', 'instructors_assistant', 'subscriber' ) as $role ) {
			wp_set_current_user( $this->factory->user->create( array( 'role' => $role ) ) );

			$fields = $this->metabox->get_fields();
			$titles = wp_list_pluck( $fields, 'title' );

			$this->assertNotContains( 'Auto Enrollment', $titles, sprintf( 'Role %s should not have access to Auto Enrollment tab.', $role ) );
		}

	}

}
