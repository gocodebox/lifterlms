<?php
/**
 * Test User Permissions and capabilities
 *
 * @package  LifterLMS_Tests/Tests
 *
 * @group user_permissions
 *
 * @since 3.34.0
 * @version 3.34.0
 */
class LLMS_Test_User_Permissions extends LLMS_UnitTestCase {

	public function setUp() {
		parent::setUp();
		$this->obj = new LLMS_User_Permissions();
	}

	/**
	 * Create mock users of different roles for testing permissions.
	 *
	 * @since 3.34.0
	 *
	 * @return int[]
	 */
	private function create_mock_users() {

		return array(
			'student' => $this->factory->student->create(),
			'admin' => $this->factory->user->create( array( 'role' => 'administrator' ) ),
			'editor' => $this->factory->user->create( array( 'role' => 'editor' ) ),
			'subscriber' => $this->factory->user->create( array( 'role' => 'subscriber' ) ),
			'lms_manager' => $this->factory->user->create( array( 'role' => 'lms_manager' ) ),
			'instructor' => $this->factory->user->create( array( 'role' => 'instructor' ) ),
			'assistant' => $this->factory->user->create( array( 'role' => 'instructors_assistant' ) ),
		);

	}

	/**
	 * Test the get_editable_roles method.
	 *
	 * @since 3.34.0
	 *
	 * @return [type]
	 */
	public function test_get_editable_roles() {

		$roles = LLMS_User_Permissions::get_editable_roles();
		$this->assertEquals( array( 'instructor', 'instructors_assistant', 'lms_manager', 'student' ), $roles['lms_manager'] );
		$this->assertEquals( array( 'instructors_assistant' ), $roles['instructor'] );

	}

	/**
	 * Test the is_current_user_instructor() method.
	 *
	 * @since 3.34.0
	 *
	 * @return [type]
	 */
	public function test_is_current_user_instructor() {

		$users = $this->create_mock_users();

		// Obviously not golfers.
		foreach ( array( 'admin', 'student', 'editor', 'subscriber', 'lms_manager', 'assistant' ) as $role ) {
			wp_set_current_user( $users[ $role ] );
			$this->assertFalse( LLMS_User_Permissions::is_current_user_instructor() );
		}

		// Winner.
		wp_set_current_user( $users['instructor'] );
		$this->assertTrue( LLMS_User_Permissions::is_current_user_instructor() );

		// Logged out.
		wp_set_current_user( null );
		$this->assertFalse( LLMS_User_Permissions::is_current_user_instructor() );

	}

	/**
	 * Test the user_can_manage_user method.
	 *
	 * @since 3.34.0
	 *
	 * @return [type]
	 */
	public function test_user_can_manage_user() {

		$users = extract( $this->create_mock_users() );

		// WP Core roles are skipped.
		$this->assertNull( LLMS_Unit_Test_Util::call_method( $this->obj, 'user_can_manage_user', array( $admin, $student ) ) );
		$this->assertNull( LLMS_Unit_Test_Util::call_method( $this->obj, 'user_can_manage_user', array( $editor, $student ) ) );
		$this->assertNull( LLMS_Unit_Test_Util::call_method( $this->obj, 'user_can_manage_user', array( $subscriber, $student ) ) );

		// LMS Managers can't manage WP core roles.
		$this->assertFalse( LLMS_Unit_Test_Util::call_method( $this->obj, 'user_can_manage_user', array( $lms_manager, $admin ) ) );
		$this->assertFalse( LLMS_Unit_Test_Util::call_method( $this->obj, 'user_can_manage_user', array( $lms_manager, $editor ) ) );
		$this->assertFalse( LLMS_Unit_Test_Util::call_method( $this->obj, 'user_can_manage_user', array( $lms_manager, $subscriber ) ) );

		// LMS Managers can manage all LMS Roles (including other LMS Managers).
		$this->assertTrue( LLMS_Unit_Test_Util::call_method( $this->obj, 'user_can_manage_user', array( $lms_manager, $student ) ) );
		$this->assertTrue( LLMS_Unit_Test_Util::call_method( $this->obj, 'user_can_manage_user', array( $lms_manager, $instructor ) ) );
		$this->assertTrue( LLMS_Unit_Test_Util::call_method( $this->obj, 'user_can_manage_user', array( $lms_manager, $assistant ) ) );
		$this->assertTrue( LLMS_Unit_Test_Util::call_method( $this->obj, 'user_can_manage_user', array( $lms_manager, $this->factory->user->create( array( 'role' => 'lms_manager' ) ) ) ) );

		// Instructor's cannot manage WP core roles.
		$this->assertFalse( LLMS_Unit_Test_Util::call_method( $this->obj, 'user_can_manage_user', array( $instructor, $admin ) ) );
		$this->assertFalse( LLMS_Unit_Test_Util::call_method( $this->obj, 'user_can_manage_user', array( $instructor, $editor ) ) );
		$this->assertFalse( LLMS_Unit_Test_Util::call_method( $this->obj, 'user_can_manage_user', array( $instructor, $subscriber ) ) );

		// Instructor's cannot manage LMS Managers or students
		$this->assertFalse( LLMS_Unit_Test_Util::call_method( $this->obj, 'user_can_manage_user', array( $instructor, $lms_manager ) ) );
		$this->assertFalse( LLMS_Unit_Test_Util::call_method( $this->obj, 'user_can_manage_user', array( $instructor, $student ) ) );

		// Instructors can only manage assistants who they "own".
		$this->assertFalse( LLMS_Unit_Test_Util::call_method( $this->obj, 'user_can_manage_user', array( $instructor, $assistant ) ) );

		$ass_obj = llms_get_instructor( $assistant );
		$ass_obj->add_parent( $instructor );
		$this->assertTrue( LLMS_Unit_Test_Util::call_method( $this->obj, 'user_can_manage_user', array( $instructor, $assistant ) ) );

		// Assistant's cannot manage anything.
		$this->assertFalse( LLMS_Unit_Test_Util::call_method( $this->obj, 'user_can_manage_user', array( $assistant, $admin ) ) );
		$this->assertFalse( LLMS_Unit_Test_Util::call_method( $this->obj, 'user_can_manage_user', array( $assistant, $editor ) ) );
		$this->assertFalse( LLMS_Unit_Test_Util::call_method( $this->obj, 'user_can_manage_user', array( $assistant, $subscriber ) ) );
		$this->assertFalse( LLMS_Unit_Test_Util::call_method( $this->obj, 'user_can_manage_user', array( $assistant, $student ) ) );
		$this->assertFalse( LLMS_Unit_Test_Util::call_method( $this->obj, 'user_can_manage_user', array( $assistant, $instructor ) ) );

		// All LMS Roles can manage themselves.
		foreach( array( $lms_manager, $instructor, $assistant ) as $uid ) {
			$this->assertTrue( LLMS_Unit_Test_Util::call_method( $this->obj, 'user_can_manage_user', array( $uid, $uid ) ) );
		}

	}

	public function test_student_crud_caps() {

		$users = $this->create_mock_users();

		// These users have all student permissions regardless of the user role.
		foreach ( array( 'admin', 'lms_manager' ) as $role ) {

			wp_set_current_user( $users[ $role ] );
			$this->assertTrue( current_user_can( 'create_students' ) );
			foreach ( $users as $user ) {
				// General Capability.
				$this->assertTrue( current_user_can( 'view_students' ) );
				$this->assertTrue( current_user_can( 'edit_students' ) );
				$this->assertTrue( current_user_can( 'delete_students' ) );
				// Specific User.
				$this->assertTrue( current_user_can( 'view_students', $user ) );
				$this->assertTrue( current_user_can( 'edit_students', $user ) );
				$this->assertTrue( current_user_can( 'delete_students', $user ) );
			}

		}

		// These users can't do anything.
		foreach ( array( 'student', 'editor', 'subscriber' ) as $role ) {

			wp_set_current_user( $users[ $role ] );
			$this->assertFalse( current_user_can( 'create_students' ) );

			foreach ( $users as $user ) {
				// General Capability.
				$this->assertFalse( current_user_can( 'view_students' ) );
				$this->assertFalse( current_user_can( 'edit_students' ) );
				$this->assertFalse( current_user_can( 'delete_students' ) );
				// Specific User.
				$this->assertFalse( current_user_can( 'view_students', $user ) );
				$this->assertFalse( current_user_can( 'edit_students', $user ) );
				$this->assertFalse( current_user_can( 'delete_students', $user ) );
			}

		}

		$course_1 = $this->factory->course->create_and_get( array( 'sections' => 0 ) );
		$course_2 = $this->factory->course->create_and_get( array( 'sections' => 0 ) );

		// These users can view their own and that's it.
		foreach ( array( 'assistant', 'instructor' ) as $role ) {

			wp_set_current_user( $users[ $role ] );
			$this->assertFalse( current_user_can( 'create_students' ) );

			foreach ( $users as $user ) {
				// General Capability.
				$this->assertTrue( current_user_can( 'view_students' ) );
				$this->assertFalse( current_user_can( 'edit_students' ) );
				$this->assertFalse( current_user_can( 'delete_students' ) );
				// Specific User.
				$this->assertFalse( current_user_can( 'view_students', $user ) );
				$this->assertFalse( current_user_can( 'edit_students', $user ) );
				$this->assertFalse( current_user_can( 'delete_students', $user ) );
			}

			$course_1->instructors()->set_instructors( array( array( 'id' => $users[ $role ] ) ) );
			$course_2->instructors()->set_instructors( array( array( 'id' => $users[ $role ] ) ) );

			foreach ( $users as $user ) {

				llms_enroll_student( $user, $course_1->get( 'id' ) );
				$this->assertTrue( current_user_can( 'view_students', $user ) );

			}

		}



	}

}
