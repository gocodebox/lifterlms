<?php
/**
 * Test LLMS_Admin_User_Custom_Fields
 *
 * @package LifterLMS/Tests/Admin
 *
 * @group admin
 * @group admin_user_custom_fields
 *
 * @since 11.1.0
 */
class LLMS_Test_Admin_User_Custom_Fields extends LLMS_Unit_Test_Case {

	/**
	 * Saves the profile of `$user_id` as `$current_user_id` submitting `$parent_ids` as parent instructors.
	 *
	 * @since 11.1.0
	 *
	 * @param int   $current_user_id WP_User ID of the user saving the profile.
	 * @param int   $user_id         WP_User ID of the instructor's assistant being saved.
	 * @param int[] $parent_ids      Submitted parent instructor IDs.
	 * @param bool  $create          Whether the assistant is being created (`true`) or updated (`false`).
	 * @return int[] The assistant's parent instructors after saving.
	 */
	private function save_parents( $current_user_id, $user_id, $parent_ids, $create = false ) {

		wp_set_current_user( $current_user_id );
		$this->mockPostRequest( array( 'llms_parent_instructors' => $parent_ids ) );

		$fields = new LLMS_Admin_User_Custom_Fields();
		$fields->save( $create ? $user_id : get_userdata( $user_id ) );

		$parents = llms_get_instructor( $user_id )->get( 'parent_instructors' );
		return array_map( 'absint', $parents ? $parents : array() );
	}

	/**
	 * Test who can set an instructor's assistant's parent instructors.
	 *
	 * @since 11.1.0
	 *
	 * @return void
	 */
	public function test_save_parent_instructors() {

		$admin      = $this->factory->user->create( array( 'role' => 'administrator' ) );
		$instructor = $this->factory->user->create( array( 'role' => 'instructor' ) );
		$other      = $this->factory->user->create( array( 'role' => 'instructor' ) );
		$assistant  = $this->factory->user->create( array( 'role' => 'instructors_assistant' ) );

		// An instructor creating an assistant becomes its parent, and can't add anyone else.
		$this->assertSame( array( $instructor ), $this->save_parents( $instructor, $assistant, array( $instructor, $other ), true ) );

		// The assistant can't add a parent by submitting the field on their own profile.
		$this->assertSame( array( $instructor ), $this->save_parents( $assistant, $assistant, array( $other ) ) );

		// Nor can the parent instructor when editing the assistant.
		$this->assertSame( array( $instructor ), $this->save_parents( $instructor, $assistant, array( $other ) ) );

		// Admins choose the parents.
		$this->assertSame( array( $instructor, $other ), $this->save_parents( $admin, $assistant, array( $other ) ) );
	}
}
