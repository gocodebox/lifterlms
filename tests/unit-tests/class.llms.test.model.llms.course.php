<?php
/**
 * Tests for LifterLMS Core Functions
 * @since    3.3.2
 * @version  3.3.2
 */
class LLMS_Test_LLMS_Course extends LLMS_UnitTestCase {

	/**
	 * Test creation of a new course
	 * @return   void
	 * @since    3.3.2
	 * @version  3.3.2
	 */
	public function test_create() {

		$course = new LLMS_Course( 'new', 'Course Name' );
		$id = $course->get( 'id' );

		$test = llms_get_post( $id );

		$this->assertEquals( $id, $test->get( 'id' ) );
		$this->assertEquals( 'course', $test->get( 'type' ) );
		$this->assertEquals( 'Course Name', $test->get( 'title' ) );

	}

	/**
	 * Test the property getters and setters for LifterLMS Course Properties
	 * @return   void
	 * @since    3.3.2
	 * @version  3.3.2
	 */
	public function test_course_getters_and_setters() {

		// this should match the $properties attribute of the LLMS_Course class
		$props = array(
			'audio_embed' => 'text',
			'capacity' => 'absint',
			'capacity_message' => 'text',
			'course_closed_message' => 'text',
			'course_opens_message' => 'text',
			'content_restricted_message' => 'text',
			'enable_capacity' => 'yesno',
			'end_date' => 'text',
			'enrollment_closed_message' => 'text',
			'enrollment_end_date' => 'text',
			'enrollment_opens_message' => 'text',
			'enrollment_period' => 'yesno',
			'enrollment_start_date' => 'text',
			'has_prerequisite' => 'yesno',
			'length' => 'text',
			'prerequisite' => 'absint',
			'prerequisite_track' => 'absint',
			'tile_featured_video' => 'yesno',
			'time_period' => 'yesno',
			'start_date' => 'text',
			'video_embed' => 'text',
		);

		$data = array(
			'audio_embed' => 'http://example.tld/audio_embed',
			'capacity' => 25,
			'capacity_message' => 'Capacity Reached',
			'course_closed_message' => 'Course has closed',
			'course_opens_message' => 'Course is not yet open',
			'content_restricted_message' => 'You cannot access this content',
			'enable_capacity' => 'yes',
			'end_date' => '2017-05-05',
			'enrollment_closed_message' => 'Enrollment is closed',
			'enrollment_end_date' => '2017-05-05',
			'enrollment_opens_message' => 'Enrollment opens later',
			'enrollment_period' => 'yes',
			'enrollment_start_date' => '2017-05-01',
			'has_prerequisite' => 'no',
			'length' => '1 year',
			'prerequisite' => 0,
			'prerequisite_track' => 0,
			'tile_featured_video' => 'yes',
			'time_period' => 'yes',
			'start_date' => '2017-05-01',
			'video_embed' => 'http://example.tld/video_embed',
		);

		$course = new LLMS_Course( 'new', 'Course Name' );

		foreach ( $props as $prop => $type ) {

			// set should return true
			$this->assertTrue( $course->set( $prop, $data[ $prop ] ) );

			// make sure gotten value equals set val
			$this->assertEquals( $data[ $prop ], $course->get( $prop ) );

			// check type
			switch ( $type ) {
				case 'absint':
					$this->assertTrue( is_numeric( $course->get( $prop ) ) );
					$course->set( $prop, 'string' );
					$this->assertEquals( 0, $course->get( $prop ) );
				break;

				case 'yesno':
					$course->set( $prop, 'yes' );
					$this->assertEquals( 'yes', $course->get( $prop ) );
					$course->set( $prop, 'no' );
					$this->assertEquals( 'no', $course->get( $prop ) );
					$course->set( $prop, 'string' );
					$this->assertEquals( 'no', $course->get( $prop ) );
				break;

				case 'text':
					$this->assertTrue( is_string( $course->get( $prop ) ) );
				break;

			}

		}

	}

	/**
	 * Test perequisite functions related to courses
	 * @return   void
	 * @since    3.3.2
	 * @version  3.3.2
	 */
	public function test_get_prerequisites() {

		$course = new LLMS_Course( 'new', 'Course Name' );
		$prereq_course = new LLMS_Course( 'new', 'Course Prereq' );
		$prereq_track = wp_create_term( 'test track', 'course_track' );

		// no prereqs
		$this->assertFalse( $course->has_prerequisite( 'any' ) );
		$this->assertFalse( $course->has_prerequisite( 'course' ) );
		$this->assertFalse( $course->has_prerequisite( 'track' ) );
		$this->assertFalse( $course->get_prerequisite_id( 'course' ) );
		$this->assertFalse( $course->get_prerequisite_id( 'track' ) );

		$course->set( 'prerequisite', $prereq_course->get( 'id' ) );
		$course->set( 'prerequisite_track', $prereq_track['term_id'] );

		// still no prereqs
		$this->assertFalse( $course->has_prerequisite( 'any' ) );
		$this->assertFalse( $course->has_prerequisite( 'course' ) );
		$this->assertFalse( $course->has_prerequisite( 'track' ) );
		$this->assertFalse( $course->get_prerequisite_id( 'course' ) );
		$this->assertFalse( $course->get_prerequisite_id( 'track' ) );

		$course->set( 'has_prerequisite', 'yes' );

		// have prereqs
		$this->assertTrue( $course->has_prerequisite( 'any' ) );
		$this->assertTrue( $course->has_prerequisite( 'course' ) );
		$this->assertTrue( $course->has_prerequisite( 'track' ) );
		$this->assertEquals( $prereq_course->get( 'id' ), $course->get_prerequisite_id( 'course' ) );
		$this->assertEquals( $prereq_track['term_id'], $course->get_prerequisite_id( 'track' ) );

		$course->set( 'prerequisite', 0 );

		$this->assertTrue( $course->has_prerequisite( 'any' ) );
		$this->assertFalse( $course->has_prerequisite( 'course' ) );
		$this->assertTrue( $course->has_prerequisite( 'track' ) );
		$this->assertEquals( 0, $course->get_prerequisite_id( 'course' ) );

		$course->set( 'prerequisite', 'string' );
		$this->assertFalse( $course->has_prerequisite( 'course' ) );
		$this->assertEquals( 0, $course->get_prerequisite_id( 'course' ) );

	}

	/**
	 * Test Audio and Video Embeds
	 * @return   void
	 * @since    3.3.2
	 * @version  3.3.2
	 */
	public function test_get_embeds() {

		$audio_url = 'https://open.spotify.com/track/1rNUOtuCWv1qswqsMFvzvz';
		$video_url = 'https://www.youtube.com/watch?v=MhQlNwxn5oo';

		$course = new LLMS_Course( 'new', 'Course With Embeds' );

		// empty string when none set
		$this->assertEmpty( $course->get_audio() );
		$this->assertEmpty( $course->get_video() );

		$course->set( 'audio_embed', $audio_url );
		$course->set( 'video_embed', $video_url );

		$audio_embed = $course->get_audio();
		$video_embed = $course->get_video();

		// string
		$this->assertTrue( is_string( $audio_embed ) );
		$this->assertTrue( is_string( $video_embed ) );

		// should be an iframe for valid embeds
		$this->assertEquals( 0, strpos( $audio_embed, '<iframe' ) );
		$this->assertEquals( 0, strpos( $video_embed, '<iframe' ) );

		// fallbacks should be a link to the URL
		$course->set( 'audio_embed', 'http://lifterlms.com/not/embeddable' );
		$course->set( 'video_embed', 'http://lifterlms.com/not/embeddable' );
		$this->assertEquals( 0, strpos( $audio_embed, '<a' ) );
		$this->assertEquals( 0, strpos( $video_embed, '<a' ) );

	}

}
