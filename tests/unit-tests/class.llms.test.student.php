<?php
/**
 * Tests for LifterLMS Student Functions
 * @since    3.5.0
 * @version  3.5.1
 */
class LLMS_Test_Student extends LLMS_UnitTestCase {

	/**
	 * Test mark_complete() and mark_incomplete() on a lesson, section, course, and track
	 *
	 * This test creates a course with two sections.  The first section has two lessons and
	 * the second section has one lesson.  mark_complete() is called on all three lessons
	 * in order to test lesson, section, and course completion.
	 *
	 * When the whole course is complete, mark_incomplete() is called on the three lessons
	 * in the opposite order to test 'incompletion' for a three post types
	 *
	 * @return   void
	 * @since    3.5.0
	 * @version  3.5.0
	 */
	public function test_completion() {

		// Create new user
		$user = $this->factory->user->create( array( 'role' => 'subscriber' ) );

		// Create new course
		$course = $this->factory->post->create( array( 'post_type' => 'course' ) );

		// add it to a track
		$term = wp_insert_term( 'test track', 'course_track' );
		wp_set_object_terms( $course, array( $term['term_id'] ), 'course_track', false );

		// Create two sections assigned to course
		$section1 = LLMS_POST_Handler::create_section( $course, 'test-section' );
		$section2 = LLMS_POST_Handler::create_section( $course, 'test-section2' );

		// Create two lessons assigned to section 1 so we can test if each type is complete
		$lesson1_section1 = LLMS_POST_Handler::create_lesson( $course, $section1, 'test-lesson' );
		$lesson2_section1 = LLMS_POST_Handler::create_lesson( $course, $section1, 'test-lesson' );

		// Create one lesson for section 2
		$lesson1_section2 = LLMS_POST_Handler::create_lesson( $course, $section2, 'test-lesson' );

		// Course, Sections, and Lessons should all be incomplete
		$this->assertFalse( llms_is_complete( $user, $lesson1_section1, 'lesson' ) );
		$this->assertFalse( llms_is_complete( $user, $lesson2_section1, 'lesson' ) );
		$this->assertFalse( llms_is_complete( $user, $section1, 'section' ) );
		$this->assertFalse( llms_is_complete( $user, $lesson1_section2, 'lesson' ) );
		$this->assertFalse( llms_is_complete( $user, $section2, 'section' ) );
		$this->assertFalse( llms_is_complete( $user, $course, 'course' ) );

		// Mark lesson 1 section 1 complete
		llms_mark_complete( $user, $lesson1_section1, 'lesson', 'test-mark-complete' );

		// Only first lesson should be complete
		$this->assertTrue(  llms_is_complete( $user, $lesson1_section1, 'lesson' ) );
		$this->assertFalse( llms_is_complete( $user, $lesson2_section1, 'lesson' ) );
		$this->assertFalse( llms_is_complete( $user, $section1, 'section' ) );
		$this->assertFalse( llms_is_complete( $user, $lesson1_section2, 'lesson' ) );
		$this->assertFalse( llms_is_complete( $user, $section2, 'section' ) );
		$this->assertFalse( llms_is_complete( $user, $course, 'course' ) );

		// Mark lesson 2 section 1 complete
		llms_mark_complete( $user, $lesson2_section1, 'lesson', 'test-mark-complete' );

		// Section 1 now complete
		$this->assertTrue(  llms_is_complete( $user, $lesson1_section1, 'lesson' ) );
		$this->assertTrue(  llms_is_complete( $user, $lesson2_section1, 'lesson' ) );
		$this->assertTrue(  llms_is_complete( $user, $section1, 'section' ) );
		$this->assertFalse( llms_is_complete( $user, $lesson1_section2, 'lesson' ) );
		$this->assertFalse( llms_is_complete( $user, $section2, 'section' ) );
		$this->assertFalse( llms_is_complete( $user, $course, 'course' ) );

		// Mark lesson 1 section 2 complete
		llms_mark_complete( $user, $lesson1_section2, 'lesson', 'test-mark-complete' );

		// Everthing should be complete now
		$this->assertTrue( llms_is_complete( $user, $lesson1_section1, 'lesson' ) );
		$this->assertTrue( llms_is_complete( $user, $lesson2_section1, 'lesson' ) );
		$this->assertTrue( llms_is_complete( $user, $section1, 'section' ) );
		$this->assertTrue( llms_is_complete( $user, $lesson1_section2, 'lesson' ) );
		$this->assertTrue( llms_is_complete( $user, $section2, 'section' ) );
		$this->assertTrue( llms_is_complete( $user, $course, 'course' ) );

		// check the track
		$this->assertTrue( llms_is_complete( $user, $term['term_id'], 'course_track' ) );

		// Mark lesson 1 section 2 INcomplete
		llms_mark_incomplete( $user, $lesson1_section2, 'lesson', 'test-mark-incomplete' );

		// Only section 1 now complete
		$this->assertTrue(  llms_is_complete( $user, $lesson1_section1, 'lesson' ) );
		$this->assertTrue(  llms_is_complete( $user, $lesson2_section1, 'lesson' ) );
		$this->assertTrue(  llms_is_complete( $user, $section1, 'section' ) );
		$this->assertFalse( llms_is_complete( $user, $lesson1_section2, 'lesson' ) );
		$this->assertFalse( llms_is_complete( $user, $section2, 'section' ) );
		$this->assertFalse( llms_is_complete( $user, $course, 'course' ) );

		// Mark lesson 2 section 1 INcomplete
		llms_mark_incomplete( $user, $lesson2_section1, 'lesson', 'test-mark-incomplete' );

		// Only first lesson should be complete
		$this->assertTrue(  llms_is_complete( $user, $lesson1_section1, 'lesson' ) );
		$this->assertFalse( llms_is_complete( $user, $lesson2_section1, 'lesson' ) );
		$this->assertFalse( llms_is_complete( $user, $section1, 'section' ) );
		$this->assertFalse( llms_is_complete( $user, $lesson1_section2, 'lesson' ) );
		$this->assertFalse( llms_is_complete( $user, $section2, 'section' ) );
		$this->assertFalse( llms_is_complete( $user, $course, 'course' ) );

		// Mark lesson 1 section 1 INcomplete
		llms_mark_incomplete( $user, $lesson1_section1, 'lesson', 'test-mark-incomplete' );

		// Course, Sections, and Lessons should all be incomplete
		$this->assertFalse( llms_is_complete( $user, $lesson1_section1, 'lesson' ) );
		$this->assertFalse( llms_is_complete( $user, $lesson2_section1, 'lesson' ) );
		$this->assertFalse( llms_is_complete( $user, $section1, 'section' ) );
		$this->assertFalse( llms_is_complete( $user, $lesson1_section2, 'lesson' ) );
		$this->assertFalse( llms_is_complete( $user, $section2, 'section' ) );
		$this->assertFalse( llms_is_complete( $user, $course, 'course' ) );

	}

	/**
	 * Test whether a user is_enrolled() in a course or membership
	 * @return   void
	 * @since    3.5.0
	 * @version  3.5.0
	 */
	public function test_enrollment() {

		// Create new user
		$user_id = $this->factory->user->create( array( 'role' => 'subscriber' ) );

		// Create new course
		$course_id = $this->factory->post->create( array( 'post_type' => 'course' ) );

		// Create new membership
		$memb_id = $this->factory->post->create( array( 'post_type' => 'llms_membership' ) );

		// Student shouldn't be enrolled in newly created course/membership
		$this->assertFalse( llms_is_user_enrolled( $user_id, $course_id ) );
		$this->assertFalse( llms_is_user_enrolled( $user_id, $memb_id ) );

		// Enroll Student in newly created course/membership
		llms_enroll_student( $user_id, $course_id, 'test_is_enrolled' );
		llms_enroll_student( $user_id, $memb_id, 'test_is_enrolled' );

		// Student should be enrolled in course/membership
		$this->assertTrue( llms_is_user_enrolled( $user_id, $course_id ) );
		$this->assertTrue( llms_is_user_enrolled( $user_id, $memb_id ) );

		// Wait 1 second before unenrolling Student
		// otherwise, enrollment and unenrollment postmeta will have identical timestamps
		sleep( 1 );

		// Unenroll Student in newly created course/membership
		llms_unenroll_student( $user_id, $course_id, 'cancelled', 'test_is_enrolled');
		llms_unenroll_student( $user_id, $memb_id, 'cancelled', 'test_is_enrolled' );

		// Student should be not enrolled in newly created course/membership
		$this->assertFalse( llms_is_user_enrolled( $user_id, $course_id ) );
		$this->assertFalse( llms_is_user_enrolled( $user_id, $memb_id ) );

	}

	/**
	 * Test Student Getters and Setters
	 * @return   void
	 * @since    3.5.1
	 * @version  3.5.1
	 */
	public function test_getters_setters() {

		$uid = $this->factory->user->create( array( 'role' => 'student' ) );
		$user = new WP_User( $uid );
		$student =  new LLMS_Student( $uid );

		// test some core prefixed stuff from the usermeta table
		$student->set( 'first_name', 'Student' );
		$student->set( 'last_name', 'McStudentFace' );
		$this->assertEquals( get_user_meta( $uid, 'first_name', true ), $student->get( 'first_name' ) );
		$this->assertEquals( get_user_meta( $uid, 'last_name', true ), $student->get( 'last_name' ) );

		// stuff from the user table
		$this->assertEquals( $user->user_email, $student->get( 'user_email' ) );

		// llms custom user meta
		$student->set( 'billing_address', '123 Student Place' );
		$this->assertEquals( get_user_meta( $uid, 'llms_billing_address', true ), $student->get( 'billing_address' ) );

	}

	/**
	 * Test get_name() function
	 * @return   void
	 * @since    3.5.1
	 * @version  3.5.1
	 */
	public function test_get_name() {

		$uid = $this->factory->user->create( array(
			'role' => 'student'
		) );
		$user = new WP_User( $uid );
		$student =  new LLMS_Student( $uid );

		// no first/last name set, should return display name
		$this->assertEquals( $user->display_name, $student->get_name() );

		// set a first & last name
		$uid = $this->factory->user->create( array(
			'first_name' => 'Student',
			'last_name' => 'McStudentFace',
			'role' => 'student'
		) );
		$student =  new LLMS_Student( $uid );
		$this->assertEquals( 'Student McStudentFace', $student->get_name() );


	}

}
