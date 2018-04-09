<?php
/**
 * Tests for LifterLMS Course Model
 * @group    LLMS_Course
 * @group    LLMS_Post_Model
 * @since    3.4.0
 * @version  [version]
 */
class LLMS_Test_LLMS_Course extends LLMS_PostModelUnitTestCase {

	/**
	 * class name for the model being tested by the class
	 * @var  string
	 */
	protected $class_name = 'LLMS_Course';

	/**
	 * db post type of the model being tested
	 * @var  string
	 */
	protected $post_type = 'course';

	/**
	 * Get properties, used by test_getters_setters
	 * This should match, exactly, the object's $properties array
	 * @return   array
	 * @since    3.4.0
	 * @version  3.4.0
	 */
	protected function get_properties() {
		return array(
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
	}

	/**
	 * Get data to fill a create post with
	 * This is used by test_getters_setters
	 * @return   array
	 * @since    3.4.0
	 * @version  3.4.0
	 */
	protected function get_data() {
		return array(
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
	}

	/**
	 * Test Audio and Video Embeds
	 * @return   void
	 * @since    3.4.0
	 * @version  3.4.0
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

	/**
	 * Test get percent complete from course
	 * @return   void
	 * @since    [version]
	 * @version  [version]
	 */
	public function test_get_percent_complete() {

		$course = llms_get_post( $this->generate_mock_courses( 1, 4, 4, 0, 0 )[0] );
		$student = $this->get_mock_student();

		$student->enroll( $course->get( 'id' ) );

		// get student by ID
		$this->assertEquals( 0, $course->get_percent_complete( $student->get( 'id' ) ) );

		// get from current user
		$this->assertEquals( 0, $course->get_percent_complete() );

		// complete some courses
		$this->complete_courses_for_student( $student->get_id(), $course->get( 'id' ), 75 );

		// get by id
		$this->assertEquals( 75, $course->get_percent_complete( $student->get( 'id' ) ) );

		// get from current user
		$this->assertEquals( 0, $course->get_percent_complete() );

		// log the user in
		wp_set_current_user( $student->get_id() );

		// get from current user
		$this->assertEquals( 75, $course->get_percent_complete() );


	}

	/**
	 * Test perequisite functions related to courses
	 * @return   void
	 * @since    3.4.0
	 * @version  3.7.3
	 */
	public function test_get_prerequisites() {

		$course = new LLMS_Course( 'new', 'Course Name' );
		$prereq_course = new LLMS_Course( 'new', 'Course Prereq' );
		$prereq_track = wp_create_term( 'test track', 'course_track' );

		// no prereqs
		$this->assertFalse( $course->has_prerequisite( 'any' ) );
		$this->assertFalse( $course->has_prerequisite( 'course' ) );
		$this->assertFalse( $course->has_prerequisite( 'course_track' ) );
		$this->assertFalse( $course->get_prerequisite_id( 'course' ) );
		$this->assertFalse( $course->get_prerequisite_id( 'course_track' ) );

		$course->set( 'prerequisite', $prereq_course->get( 'id' ) );
		$course->set( 'prerequisite_track', $prereq_track['term_id'] );

		// still no prereqs
		$this->assertFalse( $course->has_prerequisite( 'any' ) );
		$this->assertFalse( $course->has_prerequisite( 'course' ) );
		$this->assertFalse( $course->has_prerequisite( 'course_track' ) );
		$this->assertFalse( $course->get_prerequisite_id( 'course' ) );
		$this->assertFalse( $course->get_prerequisite_id( 'course_track' ) );

		$course->set( 'has_prerequisite', 'yes' );

		// have prereqs
		$this->assertTrue( $course->has_prerequisite( 'any' ) );
		$this->assertTrue( $course->has_prerequisite( 'course' ) );
		$this->assertTrue( $course->has_prerequisite( 'course_track' ) );
		$this->assertEquals( $prereq_course->get( 'id' ), $course->get_prerequisite_id( 'course' ) );
		$this->assertEquals( $prereq_track['term_id'], $course->get_prerequisite_id( 'course_track' ) );

		$course->set( 'prerequisite', 0 );

		$this->assertTrue( $course->has_prerequisite( 'any' ) );
		$this->assertFalse( $course->has_prerequisite( 'course' ) );
		$this->assertTrue( $course->has_prerequisite( 'course_track' ) );
		$this->assertEquals( 0, $course->get_prerequisite_id( 'course' ) );

		$course->set( 'prerequisite', 'string' );
		$this->assertFalse( $course->has_prerequisite( 'course' ) );
		$this->assertEquals( 0, $course->get_prerequisite_id( 'course' ) );

	}

	/**
	 * Test the get lessons function
	 * @return   void
	 * @since    3.12.0
	 * @version  3.12.0
	 */
	public function test_get_lessons() {

		$course = llms_get_post( $this->generate_mock_courses( 1, 2, 2, 0, 0 )[0] );

		// get just ids
		$lessons = $course->get_lessons( 'ids' );
		$this->assertEquals( 4, count( $lessons ) );
		array_map( function( $id ) {
			$this->assertTrue( is_numeric( $id ) );
		}, $lessons );

		// wp post objects
		$lessons = $course->get_lessons( 'posts' );
		$this->assertEquals( 4, count( $lessons ) );
		array_map( function( $post ) {
			$this->assertTrue( is_a( $post, 'WP_Post' ) );
		}, $lessons );

		// lesson objects
		$lessons = $course->get_lessons( 'lessons' );
		$this->assertEquals( 4, count( $lessons ) );
		array_map( function( $lesson ) {
			$this->assertTrue( is_a( $lesson, 'LLMS_Lesson' ) );
		}, $lessons );

	}

	/**
	 * Test the get quizzes function
	 * @return   void
	 * @since    3.12.0
	 * @version  3.12.0
	 */
	public function test_get_quizzes() {

		$course = llms_get_post( $this->generate_mock_courses( 1, 1, 5, 3, 1 )[0] );

		$quizzes = $course->get_quizzes();
		$this->assertEquals( 3, count( $quizzes ) );
		array_map( function( $id ) {
			$this->assertTrue( is_numeric( $id ) );
		}, $quizzes );

	}

	/**
	 * Test the get sections function
	 * @return   void
	 * @since    3.12.0
	 * @version  3.12.0
	 */
	public function test_get_sections() {

		$course = llms_get_post( $this->generate_mock_courses( 1, 4, 0, 0, 0 )[0] );

		// get just ids
		$sections = $course->get_sections( 'ids' );
		$this->assertEquals( 4, count( $sections ) );
		array_map( function( $id ) {
			$this->assertTrue( is_numeric( $id ) );
		}, $sections );

		// wp post objects
		$sections = $course->get_sections( 'posts' );
		$this->assertEquals( 4, count( $sections ) );
		array_map( function( $post ) {
			$this->assertTrue( is_a( $post, 'WP_Post' ) );
		}, $sections );

		// section objects
		$sections = $course->get_sections( 'sections' );
		$this->assertEquals( 4, count( $sections ) );
		array_map( function( $section ) {
			$this->assertTrue( is_a( $section, 'LLMS_Section' ) );
		}, $sections );

	}

	/**
	 * Test the get students function
	 * @return   void
	 * @since    3.12.0
	 * @version  3.12.0
	 */
	public function test_get_students() {

		$this->create();

		$students = $this->factory->user->create_many( 10, array( 'role' => 'student' ) );
		foreach ( $students as $sid ) {
			llms_enroll_student( $sid, $this->obj->get( 'id' ), 'testing' );
		}

		$this->assertEquals( 5, count( $this->obj->get_students( array( 'enrolled' ), 5 ) ) );
		$this->assertEquals( 10, count( $this->obj->get_students() ) );

	}

	/**
	 * Test the has_capacity function
	 * @return   void
	 * @since    3.12.0
	 * @version  3.12.0
	 */
	public function test_has_capacity() {

		$this->create();
		// has capacity when nothing set
		$this->assertTrue( $this->obj->has_capacity() );

		$students = $this->factory->user->create_many( 10, array( 'role' => 'student' ) );
		foreach ( $students as $sid ) {
			llms_enroll_student( $sid, $this->obj->get( 'id' ), 'testing' );
		}

		// has capacity when students enrolled and nothing set
		$this->assertTrue( $this->obj->has_capacity() );

		// enabled capacity
		$this->obj->set( 'enable_capacity', 'yes' );
		$this->obj->set( 'capacity', 25 );

		// still open
		$this->assertTrue( $this->obj->has_capacity() );

		// over capacity
		$this->obj->set( 'capacity', 5 );
		$this->assertFalse( $this->obj->has_capacity() );

		// disable capacity
		$this->obj->set( 'enable_capacity', 'no' );
		$this->assertTrue( $this->obj->has_capacity() );

	}

}
