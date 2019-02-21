<?php
/**
 * Tests for LifterLMS Lesson Model
 * @group     post_models
 * @group     lessons
 * @since     3.14.8
 * @version   [version]
 */
class LLMS_Test_LLMS_Lesson extends LLMS_PostModelUnitTestCase {

	/**
	 * class name for the model being tested by the class
	 * @var  string
	 */
	protected $class_name = 'LLMS_Lesson';

	/**
	 * db post type of the model being tested
	 * @var  string
	 */
	protected $post_type = 'lesson';

	/**
	 * Get properties, used by test_getters_setters
	 * This should match, exactly, the object's $properties array
	 * @return   array
	 * @since    3.14.8
	 * @version  3.16.11
	 */
	protected function get_properties() {
		return array(

			'order' => 'absint',

			// drippable
			'days_before_available' => 'absint',
			'date_available' => 'text',
			'drip_method' => 'text',
			'time_available' => 'text',

			// parent element
			'parent_course' => 'absint',
			'parent_section' => 'absint',

			'audio_embed' => 'text',
			'free_lesson' => 'yesno',
			'has_prerequisite' => 'yesno',
			'prerequisite' => 'absint',
			'require_passing_grade' => 'yesno',
			'video_embed' => 'text',

			// quizzes
			'quiz' => 'absint',
			'quiz_enabled' => 'yesno',

		);
	}

	/**
	 * Get data to fill a create post with
	 * This is used by test_getters_setters
	 * @return   array
	 * @since    3.14.8
	 * @version  3.16.11
	 */
	protected function get_data() {
		return array(
			'audio_embed' => 'http://example.tld/audio_embed',
			'date_available' => '11/21/2018',
			'days_before_available' => '24',
			'drip_method' => 'date',
			'free_lesson' => 'no',
			'has_prerequisite' => 'yes',
			'order' => 1,
			'parent_course' => 85,
			'parent_section' => 32,
			'prerequisite' => 344,
			'quiz' => 123,
			'quiz_enabled' => 'yes',
			'require_passing_grade' => 'yes',
			'time_available' => '12:34 PM',
			'video_embed' => 'http://example.tld/video_embed',
		);
	}


	/*
		   /$$                           /$$
		  | $$                          | $$
		 /$$$$$$    /$$$$$$   /$$$$$$$ /$$$$$$   /$$$$$$$
		|_  $$_/   /$$__  $$ /$$_____/|_  $$_/  /$$_____/
		  | $$    | $$$$$$$$|  $$$$$$   | $$   |  $$$$$$
		  | $$ /$$| $$_____/ \____  $$  | $$ /$$\____  $$
		  |  $$$$/|  $$$$$$$ /$$$$$$$/  |  $$$$//$$$$$$$/
		   \___/   \_______/|_______/    \___/ |_______/
	*/

	public function test_get_available_date() {

		$format = 'Y-m-d';

		$course_id = $this->generate_mock_courses( 1, 1, 2, 0 )[0];
		$course = llms_get_post( $course_id );
		$lesson = $course->get_lessons()[0];
		$lesson_id = $lesson->get( 'id' );
		$student = $this->get_mock_student();
		wp_set_current_user( $student->get_id() );
		$student->enroll( $course_id );

		// no drip settings, lesson is currently available
		$this->assertEquals( current_time( $format ), $lesson->get_available_date( $format ) );

		$lesson->set( 'drip_method', 'date' );
		$lesson->set( 'date_available', '12/12/2012' );
		$lesson->set( 'time_available', '12:12 AM' );
		$this->assertEquals( date( $format, strtotime( '12/12/2012' ) ), $lesson->get_available_date( $format ) );
		$this->assertEquals( date( 'U', strtotime( '12/12/2012 12:12 AM' ) ), $lesson->get_available_date( 'U' ) );

		$lesson->set( 'drip_method', 'enrollment' );
		$lesson->set( 'days_before_available', '3' );
		$this->assertEquals( $student->get_enrollment_date( $course_id, 'enrolled', 'U' ) + ( DAY_IN_SECONDS * 3 ), $lesson->get_available_date( 'U' ) );

		$lesson->set( 'drip_method', 'start' );
		$start = current_time( 'm/d/Y' );
		$course->set( 'start_date', $start );
		$this->assertEquals( strtotime( $start ) + ( DAY_IN_SECONDS * 3 ), $lesson->get_available_date( 'U' ) );

		$prereq_id = $lesson_id;
		$student->mark_complete( $lesson_id, 'lesson' );

		$lesson = $course->get_lessons()[1];

		$lesson->set( 'has_prerequisite', 'yes' );
		$lesson->set( 'prerequisite', $lesson_id );

		$lesson->set( 'drip_method', 'prerequisite' );
		$lesson->set( 'days_before_available', '3' );
		$this->assertEquals( $student->get_completion_date( $prereq_id, 'U' ) + ( DAY_IN_SECONDS * 3 ), $lesson->get_available_date( 'U' ) );

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

	/**
	 * Test Audio and Video Embeds
	 * @return   void
	 * @since    3.14.8
	 * @version  3.14.8
	 */
	public function test_get_embeds() {

		$audio_url = 'https://open.spotify.com/track/1rNUOtuCWv1qswqsMFvzvz';
		$video_url = 'https://www.youtube.com/watch?v=MhQlNwxn5oo';

		$lesson = new LLMS_Lesson( 'new', 'Lesson With Embeds' );

		// empty string when none set
		$this->assertEmpty( $lesson->get_audio() );
		$this->assertEmpty( $lesson->get_video() );

		$lesson->set( 'audio_embed', $audio_url );
		$lesson->set( 'video_embed', $video_url );

		$audio_embed = $lesson->get_audio();
		$video_embed = $lesson->get_video();

		// string
		$this->assertTrue( is_string( $audio_embed ) );
		$this->assertTrue( is_string( $video_embed ) );

		// should be an iframe for valid embeds
		$this->assertEquals( 0, strpos( $audio_embed, '<iframe' ) );
		$this->assertEquals( 0, strpos( $video_embed, '<iframe' ) );

		// fallbacks should be a link to the URL
		$lesson->set( 'audio_embed', 'http://lifterlms.com/not/embeddable' );
		$lesson->set( 'video_embed', 'http://lifterlms.com/not/embeddable' );
		$this->assertEquals( 0, strpos( $audio_embed, '<a' ) );
		$this->assertEquals( 0, strpos( $video_embed, '<a' ) );

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

	/**
	 * Test has_modified_slug function
	 * @return   void
	 * @since    3.14.8
	 * @version  3.14.8
	 */
	public function test_has_modified_slug() {

		$lesson = new LLMS_Lesson( 'new', 'New Lesson' );

		// default unmodified slug
		$this->assertFalse( $lesson->has_modified_slug() );

		// default unmodifed slug with a unique int at the end
		$lesson->set( 'name', 'new-lesson-123' );

		$this->assertFalse( $lesson->has_modified_slug() );

		// renamed slug
		$lesson->set( 'name', 'modified-slug' );

		$this->assertTrue( $lesson->has_modified_slug() );

	}

	/**
	 * Test the has_quiz() method
	 *
	 * @return  void
	 * @since   [version]
	 * @version [version]
	 */
	public function test_has_quiz() {

		$lesson = new LLMS_Lesson( 'new', 'New Lesson' );

		$this->assertFalse( $lesson->has_quiz() );
		$lesson->set( 'quiz', 123 );
		$this->assertTrue( $lesson->has_quiz() );

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
		llms_reset_current_time();

		$prereq_id = $lesson_id;
		$student->mark_complete( $lesson_id, 'lesson' );

		// second lesson not available until 3 days after lesson 1 is complete
		$lesson = $course->get_lessons()[1];

		$lesson->set( 'has_prerequisite', 'yes' );
		$lesson->set( 'prerequisite', $lesson_id );

		$lesson->set( 'drip_method', 'prerequisite' );
		$lesson->set( 'days_before_available', '3' );

		$this->assertFalse( $lesson->is_available() );

		llms_mock_current_time( '+4 days' );
		$this->assertTrue( $lesson->is_available() );

	}

	/**
	 * test the is_orphan function
	 * @return   [type]
	 * @since    3.14.8
	 * @version  3.14.8
	 */
	public function test_is_orphan() {

		$course = llms_get_post( $this->generate_mock_courses( 1, 1, 1, 0, 0 )[0] );
		$section = llms_get_post( $course->get_sections( 'ids' )[0] );
		$lesson = llms_get_post( $course->get_lessons( 'ids' )[0] );

		// not an orphan
		$this->assertFalse( $lesson->is_orphan() );

 		$test_statuses = get_post_stati( array( '_builtin' => true ) );
		foreach ( array_keys( $test_statuses ) as $status ) {

			$assert = in_array( $status, array( 'publish', 'future', 'draft', 'pending', 'private', 'auto-draft' ) ) ? 'assertFalse' : 'assertTrue';

			// check parent course
			wp_update_post( array(
				'ID' => $course->get( 'id' ),
				'post_status' => $status,
			) );
			$this->$assert( $lesson->is_orphan() );
			wp_update_post( array(
				'ID' => $course->get( 'id' ),
				'post_status' => 'publish',
			) );

			// check parent section
			wp_update_post( array(
				'ID' => $section->get( 'id' ),
				'post_status' => $status,
			) );
			$this->$assert( $lesson->is_orphan() );
			wp_update_post( array(
				'ID' => $section->get( 'id' ),
				'post_status' => 'publish',
			) );

		}

		// parent course doesn't exist
		$lesson->set( 'parent_course', 123456789 );
		$this->assertTrue( $lesson->is_orphan() );
		$lesson->set( 'parent_course', $course->get( 'id' ) );

		// parent section doesn't exist
		$lesson->set( 'parent_section', 123456789 );
		$this->assertTrue( $lesson->is_orphan() );
		$lesson->set( 'parent_section', $section->get( 'id' ) );

		// parent course isn't set
		$lesson->set( 'parent_course', '' );
		$this->assertTrue( $lesson->is_orphan() );
		$lesson->set( 'parent_course', $course->get( 'id' ) );

		// parent section isn't set
		$lesson->set( 'parent_section', '' );
		$this->assertTrue( $lesson->is_orphan() );
		$lesson->set( 'parent_section', $section->get( 'id' ) );

		// metakey for parent course doesn't exist
		delete_post_meta( $lesson->get( 'id' ), '_llms_parent_course' );
		$this->assertTrue( $lesson->is_orphan() );
		$lesson->set( 'parent_course', $course->get( 'id' ) );

		// metakey for parent section doesn't exist
		delete_post_meta( $lesson->get( 'id' ), '_llms_parent_section' );
		$this->assertTrue( $lesson->is_orphan() );
		$lesson->set( 'parent_section', $section->get( 'id' ) );

		// not an orphan
		$this->assertFalse( $lesson->is_orphan() );

	}

}
