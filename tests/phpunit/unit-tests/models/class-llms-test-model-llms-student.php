<?php
/**
 * Tests for LifterLMS Student Model
 * @group LLMS_Student
 * @group LLMS_Student_Model
 *
 * @since 3.33.0
 * @since 3.36.2 Added tests on membership enrollment with related courses enrollments deletion.
 * @version 3.36.2
 */
class LLMS_Test_LLMS_Student extends LLMS_UnitTestCase {

	/**
	 * Setup test
	 *
	 * @since 3.33.0
	 * @since 5.3.3 Renamed from `setUp()` for compat with WP core changes.
	 *
	 * @return void
	 */
	public function set_up() {

		parent::set_up();
		$this->student   = $this->get_mock_student();
		// Create new course
		$this->course_id = $this->factory->post->create( array(
			'post_type' => 'course',
		));
		// Create new membership
		$this->memb_id   = $this->factory->post->create( array(
			'post_type' => 'llms_membership',
		));

	}

	/**
	* Functional test for the enroll() method.
	*
	* @since 3.33.0
	* @see user/class-llms-test-student.php for integration tests.
	*
	* @return void
	*/
	public function test_enroll() {

		// check against both courses and memberships

		// enroll in a non existent course/membership
		$this->assertFalse( $this->student->enroll( $this->course_id + 100, 'test_is_enrolled' ) );
		$this->assertEquals( 0, did_action( 'llms_user_enrolled_in_course' ) );
		$this->assertEquals( 0, did_action( 'llms_user_added_to_membership_level' ) );
		$this->assertFalse( $this->student->enroll( $this->memb_id + 100, 'test_is_enrolled' ) );
		$this->assertEquals( 0, did_action( 'llms_user_enrolled_in_course' ) );
		$this->assertEquals( 0, did_action( 'llms_user_added_to_membership_level' ) );

		// enroll a student
		$this->assertTrue( $this->student->enroll( $this->course_id, 'test_is_enrolled' ) );
		$this->assertEquals( 1, did_action( 'llms_user_enrolled_in_course' ) );
		$this->assertEquals( 0, did_action( 'llms_user_added_to_membership_level' ) );
		$this->assertTrue( $this->student->enroll( $this->memb_id, 'test_is_enrolled' ) );
		$this->assertEquals( 1, did_action( 'llms_user_enrolled_in_course' ) );
		$this->assertEquals( 1, did_action( 'llms_user_added_to_membership_level' ) );

		// enroll a student twice
		$this->assertFalse( $this->student->enroll( $this->course_id, 'test_is_enrolled' ) );
		$this->assertEquals( 1, did_action( 'llms_user_enrolled_in_course' ) );
		$this->assertEquals( 1, did_action( 'llms_user_added_to_membership_level' ) );

		// check re-enroll
		$this->student->unenroll( $this->course_id, 'test_is_enrolled', 'expired' );
		$this->assertTrue( $this->student->enroll( $this->course_id, 'test_is_enrolled' ) );
		$this->assertEquals( 2, did_action( 'llms_user_enrolled_in_course' ) );
		$this->assertEquals( 1, did_action( 'llms_user_added_to_membership_level' ) );

	}

	/**
	 * Functional test for the unenroll() method.
	 *
	 * @since 3.33.0
	 * @since 6.0.0 Changed use of the deprecated `llms_user_removed_from_membership_level` action hook to `llms_user_removed_from_membership`.
	 *
	 * @see user/class-llms-test-student.php for integration tests.
	 *
	 * @return void
	 */
	public function test_unenroll() {

		// unenroll a non enrolled student
		$this->assertFalse( $this->student->unenroll( $this->course_id ) );
		$this->assertEquals( 0, did_action( 'llms_user_removed_from_course' ) );
		$this->assertEquals( 0, did_action( 'llms_user_removed_from_membership' ) );
		$this->assertFalse( $this->student->unenroll( $this->memb_id ) );
		$this->assertEquals( 0, did_action( 'llms_user_removed_from_course' ) );
		$this->assertEquals( 0, did_action( 'llms_user_removed_from_membership' ) );

		// unenroll a student in a course
		$this->student->enroll( $this->course_id );
		$this->assertTrue( $this->student->unenroll( $this->course_id ) );
		$this->assertEquals( 1, did_action( 'llms_user_removed_from_course' ) );
		$this->assertEquals( 0, did_action( 'llms_user_removed_from_membership' ) );

		// unenroll a student in a membership
		$this->student->enroll( $this->memb_id );
		$this->assertTrue( $this->student->unenroll( $this->memb_id ) );
		$this->assertEquals( 1, did_action( 'llms_user_removed_from_course' ) );
		$this->assertEquals( 1, did_action( 'llms_user_removed_from_membership' ) );

		// try to unenroll a student with a different trigger
		$this->student->enroll( $this->memb_id );
		$res = $this->student->unenroll( $this->memb_id, $this->student->get_enrollment_trigger( $this->memb_id ) . '_test' );
		$this->assertFalse( $res );
		$this->assertEquals( 1, did_action( 'llms_user_removed_from_course' ) );
		$this->assertEquals( 1, did_action( 'llms_user_removed_from_membership' ) );

	}

	/**
	 * Functional test for the delete_enrollment() method.
	 *
	 * @since 3.33.0
	 * @since 3.36.2 Added tests on membership enrollment with related courses enrollments deletion.
	 * @see user/class-llms-test-student.php for integration tests.
	 *
	 * @return void
	 */
	public function test_delete_enrollment() {

		// delete a non existent enrollment: user not enrolled at all.
		$this->assertFalse( $this->student->delete_enrollment( $this->course_id ) );
		$this->assertEquals( 0, did_action( 'llms_user_enrollment_deleted' ) );

		// enroll a student.
		$this->student->enroll( $this->course_id );

		// delete a non existent enrollment: user enrolled with a different trigger.
		$res = $this->student->delete_enrollment( $this->course_id, $this->student->get_enrollment_trigger( $this->course_id ) . '_test' );
		$this->assertFalse( $res );
		$this->assertEquals( 0, did_action( 'llms_user_enrollment_deleted' ) );

		// delete an existent enrollment.
		$this->assertTrue( $this->student->delete_enrollment( $this->course_id , $this->student->get_enrollment_trigger( $this->course_id ) ) );
		$this->assertEquals( 1, did_action( 'llms_user_enrollment_deleted' ) );

		$this->student->enroll( $this->course_id );

		// delete an existent enrollment: any trigger.
		$this->assertTrue( $this->student->delete_enrollment( $this->course_id ) );
		$this->assertEquals( 2, did_action( 'llms_user_enrollment_deleted' ) );

		// Test auto-enrollments deletion.

		// create a membership.
		$membership    = new LLMS_Membership( 'new', 'Membership Title' );
		$membership_id = $membership->get('id');
		// create two courses and set them as membership auto-enrollments.
		$courses = $this->factory->course->create_many( 2, array( 0, 0, 0, 0 ) );
		$membership->set( 'auto_enroll', $courses );

		$actions = did_action( 'llms_user_enrollment_deleted' );

		// enroll a student to the membership.
		$this->student->enroll( $membership_id );

		$res = $this->student->delete_enrollment( $membership_id, $this->student->get_enrollment_trigger( $membership_id  ) );
		$this->assertTrue( $res );
		// test we had 3 deletion: the membership, and the related courses.
		$this->assertEquals( $actions + 3, did_action( 'llms_user_enrollment_deleted' ) );
	}

}
