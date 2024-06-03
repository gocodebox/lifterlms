<?php
/**
 * Test User Permissions and capabilities
 *
 * @package LifterLMS_Tests/Tests
 *
 * @group user_permissions
 *
 * @since 3.34.0
 */
class LLMS_Test_User_Permissions extends LLMS_UnitTestCase {

	/**
	 * Setup the test case
	 *
	 * @since 3.34.0
	 * @since 5.3.3 Renamed from `setUp()` for compat with WP core changes.
	 *
	 * @return void
	 */
	public function set_up() {
		parent::set_up();
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
			'student'     => $this->factory->student->create(),
			'admin'       => $this->factory->user->create( array( 'role' => 'administrator' ) ),
			'admin2'      => $this->factory->user->create( array( 'role' => 'administrator' ) ),
			'editor'      => $this->factory->user->create( array( 'role' => 'editor' ) ),
			'subscriber'  => $this->factory->user->create( array( 'role' => 'subscriber' ) ),
			'lms_manager' => $this->factory->user->create( array( 'role' => 'lms_manager' ) ),
			'instructor'  => $this->factory->user->create( array( 'role' => 'instructor' ) ),
			'assistant'   => $this->factory->user->create( array( 'role' => 'instructors_assistant' ) ),
		);

	}

	/**
	 * Test the get_editable_roles method.
	 *
	 * @since 3.34.0
	 *
	 * @return void
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
	 * @return void
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
	 * @since 3.41.0 Add tests to ensure admins can still manage other admins.
	 *
	 * @return void
	 */
	public function test_user_can_manage_user() {

		extract( $this->create_mock_users() );

		// WP Core roles are skipped.
		$this->assertNull( LLMS_Unit_Test_Util::call_method( $this->obj, 'user_can_manage_user', array( $admin, $student ) ) );
		$this->assertNull( LLMS_Unit_Test_Util::call_method( $this->obj, 'user_can_manage_user', array( $admin, $admin2 ) ) );
		$this->assertNull( LLMS_Unit_Test_Util::call_method( $this->obj, 'user_can_manage_user', array( $admin, $editor ) ) );
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

	/**
	 * Test the user_can_manage_user() for users with multiple roles.
	 *
	 * @since 3.41.0
	 *
	 * @return void
	 */
	public function test_user_can_manage_user_multiple_roles() {

		extract( $this->create_mock_users() );

		$admin = new WP_User( $admin );
		$admin->add_role( 'student' );

		// Admin with student role.
		$this->assertNull( LLMS_Unit_Test_Util::call_method( $this->obj, 'user_can_manage_user', array( $admin->ID, $student ) ) );

		$lms_manager = new WP_User( $lms_manager );
		$lms_manager->add_role( 'student' );

		$this->assertTrue( LLMS_Unit_Test_Util::call_method( $this->obj, 'user_can_manage_user', array( $lms_manager->ID, $student ) ) );

	}

	/**
	 * Test the editable_roles() filter for users with single roles.
	 *
	 * @since 4.10.0
	 *
	 * @return void
	 */
	public function test_editable_roles_single_role() {

		$users = $this->create_mock_users();

		$all_roles = wp_roles()->roles;

		$editable_roles = LLMS_Unit_Test_Util::call_method( $this->obj, 'get_editable_roles');

		wp_set_current_user( $users['lms_manager'] );
		$lms_manager_editable_roles = array_keys ( LLMS_Unit_Test_Util::call_method( $this->obj, 'editable_roles', array( $all_roles ) ) );

		// Assert that lms_managers can edit mapped roles.
		foreach ( $editable_roles['lms_manager'] as $editable_role ) {
			$this->assertContains( $editable_role, $lms_manager_editable_roles );
		}

		wp_set_current_user( $users['instructor'] );
		$instructor_editable_roles = array_keys ( LLMS_Unit_Test_Util::call_method( $this->obj, 'editable_roles', array( $all_roles ) ) );

		// Assert that instructor can edit mapped roles.
		foreach ( $editable_roles['instructor'] as $editable_role ) {
			$this->assertContains( $editable_role, $instructor_editable_roles );
		}

		wp_set_current_user( $users['assistant'] );
		$assistant_editable_roles = array_keys ( LLMS_Unit_Test_Util::call_method( $this->obj, 'editable_roles', array( $all_roles ) ) );

		// Assert that assistants can edit all roles.
		foreach ( array_keys( $all_roles ) as $role ) {
			$this->assertContains( $role, $assistant_editable_roles );
		}

		wp_set_current_user( $users['admin'] );
		$administrator_editable_roles = array_keys ( LLMS_Unit_Test_Util::call_method( $this->obj, 'editable_roles', array( $all_roles ) ) );

		// Assert that administrator can edit all roles.
		foreach ( array_keys( $all_roles ) as $role ) {
			$this->assertContains( $role, $administrator_editable_roles );
		}
	}

	/**
	 * Test the editable_roles() filter for users with multiple roles.
	 *
	 * @since 4.10.0
	 *
	 * @return void
	 */
	public function test_editable_roles_multiple_roles() {

		$users = $this->create_mock_users();

		$all_roles = wp_roles()->roles;

		wp_set_current_user( $users['lms_manager'] );
		$lms_manager_editable_roles = array_keys ( LLMS_Unit_Test_Util::call_method( $this->obj, 'editable_roles', array( $all_roles ) ) );

		wp_set_current_user( $users['instructor'] );
		$instructor_editable_roles = array_keys ( LLMS_Unit_Test_Util::call_method( $this->obj, 'editable_roles', array( $all_roles ) ) );

		wp_set_current_user( $users['lms_manager'] );
		$user = wp_get_current_user();
		$user->add_role( 'instructor' );
		$lms_manager_instructor_editable_roles = array_keys ( LLMS_Unit_Test_Util::call_method( $this->obj, 'editable_roles', array( $all_roles ) ) );

		// Assert that lms_manager with instructor role has editable roles from both roles.
		foreach ( $lms_manager_editable_roles as $lms_manager_editable_role ) {
			$this->assertContains( $lms_manager_editable_role, $lms_manager_instructor_editable_roles );
		}
		foreach ( $instructor_editable_roles as $instructor_editable_role ) {
			$this->assertContains( $instructor_editable_role, $lms_manager_instructor_editable_roles );
		}

		wp_set_current_user( $users['admin'] );
		$user = wp_get_current_user();
		$user->add_role( 'instructor' );
		$administrator_instructor_editable_roles = array_keys ( LLMS_Unit_Test_Util::call_method( $this->obj, 'editable_roles', array( $all_roles ) ) );

		// Assert that administrator with instructor role can edit all roles.
		foreach ( array_keys( $all_roles ) as $role ) {
			$this->assertContains( $role, $administrator_instructor_editable_roles );
		}
	}

	/**
	 * Test student CRUD capabilities
	 *
	 * @since Unknown
	 *
	 * @return void
	 */
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

	/**
	 * Test view_grades capability errors
	 *
	 * @since 4.21.2
	 *
	 * @return void
	 */
	public function test_view_grades_cap_errs() {

		// Logged out user.
		$this->assertFalse( current_user_can( 'view_grades' ) );

		wp_set_current_user( $this->factory->user->create() );

		// Missing required args.
		$this->assertFalse( current_user_can( 'view_grades' ) );

	}

	/**
	 * Test view_grades cap in various scenarios and different user types
	 *
	 * @since 4.21.2
	 *
	 * @return void
	 */
	public function test_view_grades_cap() {

		$users   = $this->create_mock_users();
		$course  = $this->factory->course->create_and_get( array( 'sections' => 1, 'lessons' => 1 ) );
		$quiz    = $course->get_lessons()[0]->get_quiz();
		$quiz_id = $quiz->get( 'id' );

		$users['student2'] = $this->factory->user->create( array( 'role' => 'student' ) );
		$users['student3'] = $this->factory->user->create( array( 'role' => 'student' ) );

		llms_enroll_student( $users['student'], $course->get( 'id' ) );
		llms_enroll_student( $users['student2'], $course->get( 'id' ) );

		// Can view anyone's grades.
		foreach ( array( 'admin', 'lms_manager' ) as $current_role ) {
			wp_set_current_user( $users[ $current_role ] );
			foreach ( $users as $uid ) {
				$this->assertTrue( current_user_can( 'view_grades', $uid, $quiz_id ) );
				$this->assertTrue( current_user_can( 'view_grades', $uid ) );
			}
		}

		// Can't view other people's grades.
		foreach ( array( 'editor', 'subscriber', 'instructor', 'assistant', 'student2' ) as $role ) {
			wp_set_current_user( $users[ $role ] );

			// No for others.
			$this->assertFalse( current_user_can( 'view_grades', $users['student'], $quiz_id ), $role );
			$this->assertFalse( current_user_can( 'view_grades', $users['student'] ), $role );

			// Yes for their own.
			$this->assertTrue( current_user_can( 'view_grades', $users[ $role ], $quiz_id ), $role );
			$this->assertTrue( current_user_can( 'view_grades', $users[ $role ] ), $role );

		}

		// Instructors can view their own students.
		$assistant = llms_get_instructor( $users['assistant'] );
		$assistant->add_parent( $users['instructor'] );

		$course->instructors()->set_instructors( array(
			array( 'id' => $users['instructor'] ),
			array( 'id' => $users['assistant'] ),
		) );

		// Can view grades for their students.
		foreach ( array( 'instructor', 'assistant' ) as $role ) {

			wp_set_current_user( $users[ $role ] );

			$this->assertTrue( current_user_can( 'view_grades', $users['student'], $quiz_id ), $role );
			$this->assertTrue( current_user_can( 'view_grades', $users['student2'], $quiz_id ), $role );

			$this->assertFalse( current_user_can( 'view_grades', $users['student3'], $quiz_id ), $role );

			$this->assertTrue( current_user_can( 'view_grades', $users['student'] ), $role );

		}
	}


}
