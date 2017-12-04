<?php
/**
 * Tests for LifterLMS Student Functions
 * @group    LLMS_Student
 * @since    3.5.0
 * @version  3.15.0
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
	 * @version  3.7.0
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
	 * @version  3.12.2
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


		// these were tests against now deprectaed has_access
		sleep( 1 );

		$student = $this->get_mock_student();

		$course_id = $this->generate_mock_courses()[0];

		// no access
		$this->assertFalse( $student->is_enrolled( $course_id ) );

		// has access
		llms_enroll_student( $student->get_id(), $course_id );
		$this->assertTrue( $student->is_enrolled( $course_id ) );

		// check access after an access plan has expired access
		$gateway = LLMS()->payment_gateways()->get_gateway_by_id( 'manual' );
		update_option( $gateway->get_option_name( 'enabled' ), 'yes' );

		// new student
		$student = $this->get_mock_student();

		// create an access plan
		$plan = new LLMS_Access_Plan( 'new', 'Test Access Plan' );
		$plan_data = array(
			'access_expiration' => 'limited-period',
			'access_length' => '1',
			'access_period' => 'month',
			'frequency' => 25,
			'is_free' => 'no',
			'length' => 0,
			'on_sale' => 'no',
			'period' => 'day',
			'price' => 25.00,
			'product_id' => $course_id,
			'sku' => 'accessplansku',
			'trial_offer' => 'no',
		);
		foreach ( $plan_data as $key => $val ) {
			$plan->set( $key, $val );
		}

		$order = new LLMS_Order( 'new' );
		$order->init( $student, $plan, $gateway );

		$order->set( 'status', 'llms-completed' );
		update_option( $gateway->get_option_name( 'enabled' ), 'no' ); // prevent potential issues elsewhere

		// should be enrolled with no issues
		$this->assertTrue( $student->is_enrolled( $course_id ) );

		// fast forward
		llms_mock_current_time( date( 'Y-m-d', current_time( 'timestamp' ) + YEAR_IN_SECONDS ) );

		sleep( 1 ); // so the expiration status is later than the enrollment

		// trigger expiration
		do_action( 'llms_access_plan_expiration', $order->get( 'id' ) );

		$this->assertFalse( $student->is_enrolled( $course_id ) );

		sleep( 1 );

		// manually re-enroll the student, admin enrollment should take precendence here even though they no longer have access
		llms_enroll_student( $student->get_id(), $course_id );
		$this->assertTrue( $student->is_enrolled( $course_id ) );


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

	/**
	 * Test get_progress()
	 * @return   void
	 * @since    3.15.0
	 * @version  3.15.0
	 */
	public function test_get_progress() {

		$student = $this->get_mock_student();

		$courses = $this->generate_mock_courses( 3, 2, 5, 0 );


		// create a track and add all 3 courses to it
		$track_id = wp_insert_term( 'Test Course Track', 'course_track' )['term_id'];
		foreach ( $courses as $cid ) {
			wp_set_post_terms( $cid, array( $track_id ), 'course_track' );
		}

		// course for most of our tests
		$course_id = $courses[0];
		$course = llms_get_post( $course_id );

		// check progress through course
		$i = 0;
		while ( $i <= 100 ) {

			$this->complete_courses_for_student( $student->get( 'id' ), array( $course_id ), $i );
			$this->assertEquals( $i, $student->get_progress( $course_id, 'course' ) );

			$i += 10;

		}

		// check track progress
		$this->assertEquals( 33.33, $student->get_progress( $track_id, 'course_track' ), '', 0.01 );
		$this->complete_courses_for_student( $student->get( 'id' ), array( $courses[1], $courses[2] ), 100 );
		$this->assertEquals( 100, $student->get_progress( $track_id, 'course_track' ), '', 0.01 );

		// test the progress through a section
		$student = $this->get_mock_student();
		foreach ( $course->get_sections( 'ids' ) as $i => $section_id ) {

			$this->assertEquals( 0, $student->get_progress( $section_id, 'section' ) );

			if ( 0 === $i ) {
				$this->complete_courses_for_student( $student->get( 'id' ), array( $course_id ), 50 );
				$this->assertEquals( 100, $student->get_progress( $section_id, 'section' ) );
			} else {
				$this->complete_courses_for_student( $student->get( 'id' ), array( $course_id ), 80 );
				$this->assertEquals( 60, $student->get_progress( $section_id, 'section' ) );
			}

		}

	}

}
