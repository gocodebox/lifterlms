<?php
/**
 * Test Abstract Course Element Post methods
 * @since    [version]
 * @version  [version]
 * @group    course_element_post
 */
class LLMS_Test_Course_Element_Post extends LLMS_UnitTestCase {

	// public function test_get_available_date() {}

	public function get_available_date() {

	}

	public function test_get_course() {

		$course = llms_get_post( $this->generate_mock_courses( 1, 1, 1, 0, 0 )[0] );
		$lesson = llms_get_post( $course->get_lessons( 'ids' )[0] );

		// returns a course when everything's okay
		$this->assertTrue( is_a( $lesson->get_course(), 'LLMS_Course' ) );

		// course trashed / doesn't exist, returns null
		wp_delete_post( $course->get( 'id' ), true );
		$this->assertNull( $lesson->get_course() );

	}

	public function test_get_section() {

		$course = llms_get_post( $this->generate_mock_courses( 1, 1, 1, 0, 0 )[0] );
		$lesson = llms_get_post( $course->get_lessons( 'ids' )[0] );

		// returns a course when everything's okay
		$this->assertTrue( is_a( $lesson->get_section(), 'LLMS_Section' ) );

		// section trashed / doesn't exist, returns null
		wp_delete_post( $lesson->get( 'parent_section' ), true );
		$this->assertNull( $lesson->get_section() );

	}

	public function test_is_available() {

		$course_id = $this->generate_mock_courses( 1, 1, 2, 0 )[0];
		$course = llms_get_post( $course_id );
		$lesson = $course->get_lessons()[0];
		$lesson_id = $lesson->get( 'id' );
		$student = $this->get_mock_student();
		wp_set_current_user( $student->get_id() );
		$student->enroll( $course_id );

		// no drip settings, lesson is currently available
		$this->assertTrue( $lesson->is_available() );

		// date in past so the lesson is available
		$lesson = llms_get_post( $lesson_id );
		$lesson->set( 'drip_method', 'date' );
		$lesson->set( 'date_available', '12/12/2012' );
		$lesson->set( 'time_available', '12:12 AM' );
		$this->assertTrue( $lesson->is_available() );

		// date in future so lesson not available
		$lesson->set( 'date_available', date( 'm/d/Y', current_time( 'timestamp' ) + DAY_IN_SECONDS ) );
		$this->assertFalse( $lesson->is_available() );

		// available 3 days after enrollment
		$lesson->set( 'drip_method', 'enrollment' );
		$lesson->set( 'days_before_available', '3' );
		$this->assertFalse( $lesson->is_available() );

		// now available
		llms_mock_current_time( '+4 days' );
		$this->assertTrue( $lesson->is_available() );

		llms_reset_current_time();
		$lesson->set( 'drip_method', 'start' );
		$course->set( 'start_date', date( 'm/d/Y', current_time( 'timestamp' ) + DAY_IN_SECONDS ) );

		// not available until 3 days after course start date
		$this->assertFalse( $lesson->is_available() );

		// now available
		llms_mock_current_time( '+4 days' );
		$this->assertTrue( $lesson->is_available() );


		// second lesson not available until 3 days after lesson 1 is complete
		$lesson = $course->get_lessons()[1];
		$lesson_id = $lesson->get( 'id' );
		$lesson->set( 'drip_method', 'complete' );
		$lesson->set( 'days_before_available', '3' );

		$this->assertFalse( $lesson->is_available() );


	}

	public function test_is_first() {

		$course = llms_get_post( $this->generate_mock_courses( 1, 2, 2, 0, 0 )[0] );
		foreach ( $course->get_lessons() as $i => $lesson ) {

			// first lesson is the first in course
			if ( 0 === $i ) {
				$this->assertTrue( $lesson->is_first( 'course' ) );
			// all the rest are not
			} else {
				$this->assertFalse( $lesson->is_first( 'course' ) );
			}

			// first & third lessons are first in section
			if ( 0 === $i % 2 ) {
				$this->assertTrue( $lesson->is_first( 'section' ) );
			// otherse are not
			} else {
				$this->assertFalse( $lesson->is_first( 'section' ) );
			}
		}

	}


}
