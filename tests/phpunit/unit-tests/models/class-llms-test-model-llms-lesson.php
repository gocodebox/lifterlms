<?php
/**
 * Tests for LifterLMS Lesson Model
 *
 * @group post_models
 * @group lessons
 *
 * @since 3.14.8
 * @since 3.29.0 Unknown.
 * @since 3.36.2 Added tests on lesson's availability with drip method set as 3 days after
 *               the course start date and empty course start date.
 *               Also added `$date_delta` property to be used to test dates against current time.
 * @since 4.4.0 Added tests on next/previous lessons retrieval.
 * @since 4.4.2 Added additional navigation testing scenarios.
 * @since 4.11.0 Addeed additional tests when retrieving next/prev lesson with empty sibling sections.
 * @since 6.3.0 Added tests on comment_status reflecting default settings on lesson creation.
 */
class LLMS_Test_LLMS_Lesson extends LLMS_PostModelUnitTestCase {

	/**
	 * Class name for the model being tested by the class
	 *
	 * @var string
	 */
	protected $class_name = 'LLMS_Lesson';

	/**
	 * Db post type of the model being tested
	 *
	 * @var string
	 */
	protected $post_type = 'lesson';

	/**
	 * Consider dates equal for +/- 1 min
	 *
	 * @var integer
	 */
	private $date_delta = 60;

	/**
	 * Get properties, used by test_getters_setters
	 *
	 * This should match, exactly, the object's $properties array.
	 *
	 * @since 3.14.8
	 * @since 3.16.11 Unknown.
	 * @return array
	 */
	protected function get_properties() {
		return array(

			'order'                 => 'absint',

			// Drippable.
			'days_before_available' => 'absint',
			'date_available'        => 'text',
			'drip_method'           => 'text',
			'time_available'        => 'text',

			// Parent element.
			'parent_course'         => 'absint',
			'parent_section'        => 'absint',

			'audio_embed'           => 'text',
			'free_lesson'           => 'yesno',
			'has_prerequisite'      => 'yesno',
			'prerequisite'          => 'absint',
			'require_passing_grade' => 'yesno',
			'video_embed'           => 'text',

			// Quizzes.
			'quiz'                  => 'absint',
			'quiz_enabled'          => 'yesno',

		);
	}

	/**
	 * Get data to fill a create post with
	 *
	 * This is used by test_getters_setters.
	 *
	 * @since 3.14.8
	 * @since 3.16.11 Unknown.
	 *
	 * @return array
	 */
	protected function get_data() {
		return array(
			'audio_embed'           => 'http://example.tld/audio_embed',
			'date_available'        => '11/21/2018',
			'days_before_available' => '24',
			'drip_method'           => 'date',
			'free_lesson'           => 'no',
			'has_prerequisite'      => 'yes',
			'order'                 => 1,
			'parent_course'         => 85,
			'parent_section'        => 32,
			'prerequisite'          => 344,
			'quiz'                  => 123,
			'quiz_enabled'          => 'yes',
			'require_passing_grade' => 'yes',
			'time_available'        => '12:34 PM',
			'video_embed'           => 'http://example.tld/video_embed',
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

	/**
	 * Test get available date.
	 *
	 * @since Unknown.
	 * @since 3.36.2 Added tests on lesson's availability with drip method set as 3 days after
	 *               the course start date and empty course start date.
	 * @since 5.3.3 Use `assertEqualsWithDelta()`.
	 *
	 * @return void
	 */
	public function test_get_available_date() {

		$format = 'Y-m-d';

		$course_id = $this->generate_mock_courses( 1, 1, 2, 0 )[0];
		$course    = llms_get_post( $course_id );
		$lesson    = $course->get_lessons()[0];
		$lesson_id = $lesson->get( 'id' );
		$student   = $this->get_mock_student();
		wp_set_current_user( $student->get_id() );
		$student->enroll( $course_id );

		// No drip settings, lesson is currently available.
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

		// Check lesson immediately available if set to be available after 3 days ofter a course start date which is empty.
		$lesson->set( 'drip_method', 'start' );
		$lesson->set( 'days_before_available', '3' );
		$course->set( 'start_date', '' );
		$this->assertEqualsWithDelta( current_time( 'timestamp' ), $lesson->get_available_date( 'U' ), $this->date_delta );

	}

	/**
	 * Test get available date when the course has "After course starts" delay in days set.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_available_date_with_course_drip_settings() {

		$format = 'Y-m-d';

		$course_id = $this->generate_mock_courses( 1, 3, 2, 0 )[0];

		$course = llms_get_post( $course_id );
		$course->set( 'lesson_drip', 'yes' );
		$course->set( 'drip_method', 'start' );
		$course->set( 'days_before_available', '7' );
		$course->set( 'ignore_lessons', '1' );

		$student = $this->get_mock_student();
		wp_set_current_user( $student->get_id() );
		$student->enroll( $course_id );

		$now = new DateTimeImmutable();

		$this->assertEquals( $now->format( $format ), $course->get_lessons()[0]->get_available_date( $format ) );
		$this->assertEquals( $now->add( DateInterval::createFromDateString( '7 days') )->format( $format ), $course->get_lessons()[1]->get_available_date( $format ) );
		$this->assertEquals( $now->add( DateInterval::createFromDateString( '14 days') )->format( $format ), $course->get_lessons()[2]->get_available_date( $format ) );
		$this->assertEquals( $now->add( DateInterval::createFromDateString( '21 days') )->format( $format ), $course->get_lessons()[3]->get_available_date( $format ) );

	}

	/**
	 * Test get available date when the course has "After course starts" delay in days set and
	 * the course has a fixed start date.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_available_date_with_course_drip_settings_with_course_start_date() {

		$format = 'Y-m-d';

		$course_id = $this->generate_mock_courses( 1, 3, 2, 0 )[0];

		$course = llms_get_post( $course_id );
		$course->set( 'lesson_drip', 'yes' );
		$course->set( 'drip_method', 'start' );
		$course->set( 'days_before_available', '7' );
		$course->set( 'ignore_lessons', '1' );
		$course_start = new DateTimeImmutable( '-1 week' );
		$course->set( 'start_date', $course_start->format( 'm/d/Y' ) );

		$student = $this->get_mock_student();
		wp_set_current_user( $student->get_id() );
		$student->enroll( $course_id );

		$now = new DateTimeImmutable();

		$this->assertEquals( $now->format( $format ), $course->get_lessons()[0]->get_available_date( $format ) );
		$this->assertEquals( $course_start->add( DateInterval::createFromDateString( '7 days') )->format( $format ), $course->get_lessons()[1]->get_available_date( $format ) );
		$this->assertEquals( $course_start->add( DateInterval::createFromDateString( '14 days') )->format( $format ), $course->get_lessons()[2]->get_available_date( $format ) );
		$this->assertEquals( $course_start->add( DateInterval::createFromDateString( '21 days') )->format( $format ), $course->get_lessons()[3]->get_available_date( $format ) );

	}

	/**
	 * Test get course
	 *
	 * @since unknown
	 *
	 * @return void
	 */
	public function test_get_course() {

		$course = llms_get_post( $this->generate_mock_courses( 1, 1, 1, 0, 0 )[0] );
		$lesson = llms_get_post( $course->get_lessons( 'ids' )[0] );

		// Returns a course when everything's okay.
		$this->assertTrue( is_a( $lesson->get_course(), 'LLMS_Course' ) );

		// Course trashed / doesn't exist, returns null.
		wp_delete_post( $course->get( 'id' ), true );
		$this->assertNull( $lesson->get_course() );

	}

	/**
	 * Test Audio and Video Embeds
	 *
	 * @since 3.14.8
	 * @since 4.10.0 Fix faulty tests, use assertSame in favor of assertEquals.
	 * @since 6.0.0 Mock oembed results to prevent rate limiting issues causing tests to fail.
	 *
	 * @return void
	 */
	public function test_get_embeds() {

		$iframe = '<iframe src="%s"></iframe>';

		$handler = function( $html, $url ) use ( $iframe ) {
			return sprintf( $iframe, $url );
		};

		add_filter( 'pre_oembed_result', $handler, 10, 2 );

		$lesson = new LLMS_Lesson( 'new', 'Lesson With Embeds' );

		$audio_url = 'http://example.tld/audio_embed';
		$video_url = 'http://example.tld/video_embed';

		// Empty string when none set.
		$this->assertEmpty( $lesson->get_audio() );
		$this->assertEmpty( $lesson->get_video() );

		$lesson->set( 'audio_embed', $audio_url );
		$lesson->set( 'video_embed', $video_url );

		$audio_embed = $lesson->get_audio();
		$video_embed = $lesson->get_video();

		// Should be an iframe for valid embeds.
		$this->assertEquals( sprintf( $iframe, $audio_url ),$audio_embed );
		$this->assertEquals( sprintf( $iframe, $video_url ),$video_embed );

		remove_filter( 'pre_oembed_result', $handler, 10, 2 );

		// Fallbacks should be a link to the URL.
		$not_embeddable_url = 'http://lifterlms.com/not/embeddable';

		$lesson->set( 'audio_embed', $not_embeddable_url );
		$lesson->set( 'video_embed', $not_embeddable_url );
		$audio_embed = $lesson->get_audio();
		$video_embed = $lesson->get_video();

		$this->assertSame( 0, strpos( $audio_embed, '<a' ) );
		$this->assertSame( 0, strpos( $video_embed, '<a' ) );

		$this->assertStringContains( sprintf( 'href="%s"', $not_embeddable_url ), $audio_embed );
		$this->assertStringContains( sprintf( 'href="%s"', $not_embeddable_url ), $video_embed );

	}

	/**
	 * Test getting parent section
	 *
	 * @since unknown
	 *
	 * @return void
	 */
	public function test_get_section() {

		$course = llms_get_post( $this->generate_mock_courses( 1, 1, 1, 0, 0 )[0] );
		$lesson = llms_get_post( $course->get_lessons( 'ids' )[0] );

		// Returns a course when everything's okay.
		$this->assertTrue( is_a( $lesson->get_section(), 'LLMS_Section' ) );

		// Section trashed / doesn't exist, returns null.
		wp_delete_post( $lesson->get( 'parent_section' ), true );
		$this->assertNull( $lesson->get_section() );

	}

	/**
	 * Test has_modified_slug function
	 *
	 * @since 3.14.8
	 *
	 * @return void
	 */
	public function test_has_modified_slug() {

		$lesson = new LLMS_Lesson( 'new', 'New Lesson' );

		// Default unmodified slug.
		$this->assertFalse( $lesson->has_modified_slug() );

		// Default unmodified slug with a unique int at the end.
		$lesson->set( 'name', 'new-lesson-123' );

		$this->assertFalse( $lesson->has_modified_slug() );

		// Renamed slug.
		$lesson->set( 'name', 'modified-slug' );

		$this->assertTrue( $lesson->has_modified_slug() );

	}

	/**
	 * Test the has_quiz() method
	 *
	 * @since 3.29.0
	 *
	 * @return void
	 */
	public function test_has_quiz() {

		$lesson = new LLMS_Lesson( 'new', 'New Lesson' );

		$this->assertFalse( $lesson->has_quiz() );
		$lesson->set( 'quiz', 123 );
		$this->assertTrue( $lesson->has_quiz() );

	}

	/**
	 * Test the is_available() method
	 *
	 * @since unknown
	 * @since 6.0.0 Replaced use of deprecated items.
	 *              - `llms_reset_current_time()` with `llms_tests_reset_current_time()` from the `lifterlms-tests` project
	 *              - `llms_mock_current_time()` with `llms_tests_mock_current_time()` from the `lifterlms-tests` project
	 *
	 * @return void
	 */
	public function test_is_available() {

		$course_id = $this->generate_mock_courses( 1, 1, 2, 0 )[0];
		$course    = llms_get_post( $course_id );
		$lesson    = $course->get_lessons()[0];
		$lesson_id = $lesson->get( 'id' );
		$student   = $this->get_mock_student();
		wp_set_current_user( $student->get_id() );
		$student->enroll( $course_id );

		// No drip settings, lesson is currently available.
		$this->assertTrue( $lesson->is_available() );

		// Date in past so the lesson is available.
		$lesson = llms_get_post( $lesson_id );
		$lesson->set( 'drip_method', 'date' );
		$lesson->set( 'date_available', '12/12/2012' );
		$lesson->set( 'time_available', '12:12 AM' );
		$this->assertTrue( $lesson->is_available() );

		// Date in future so lesson not available.
		$lesson->set( 'date_available', date( 'm/d/Y', current_time( 'timestamp' ) + DAY_IN_SECONDS ) );
		$this->assertFalse( $lesson->is_available() );

		// Available 3 days after enrollment.
		$lesson->set( 'drip_method', 'enrollment' );
		$lesson->set( 'days_before_available', '3' );
		$this->assertFalse( $lesson->is_available() );

		// Now available.
		llms_tests_mock_current_time( '+4 days' );
		$this->assertTrue( $lesson->is_available() );

		llms_tests_reset_current_time();
		$lesson->set( 'drip_method', 'start' );
		$course->set( 'start_date', date( 'm/d/Y', current_time( 'timestamp' ) + DAY_IN_SECONDS ) );

		// Not available until 3 days after course start date.
		$this->assertFalse( $lesson->is_available() );

		// Now available.
		llms_tests_mock_current_time( '+4 days' );
		$this->assertTrue( $lesson->is_available() );
		llms_tests_reset_current_time();

		$prereq_id = $lesson_id;
		$student->mark_complete( $lesson_id, 'lesson' );

		// Second lesson not available until 3 days after lesson 1 is complete.
		$lesson = $course->get_lessons()[1];

		$lesson->set( 'has_prerequisite', 'yes' );
		$lesson->set( 'prerequisite', $lesson_id );

		$lesson->set( 'drip_method', 'prerequisite' );
		$lesson->set( 'days_before_available', '3' );

		$this->assertFalse( $lesson->is_available() );

		llms_tests_mock_current_time( '+4 days' );
		$this->assertTrue( $lesson->is_available() );

	}

	/**
	 * Test the is_orphan() method
	 *
	 * @since 3.14.8
	 *
	 * @return void
	 */
	public function test_is_orphan() {

		$course  = llms_get_post( $this->generate_mock_courses( 1, 1, 1, 0, 0 )[0] );
		$section = llms_get_post( $course->get_sections( 'ids' )[0] );
		$lesson  = llms_get_post( $course->get_lessons( 'ids' )[0] );

		// Not an orphan.
		$this->assertFalse( $lesson->is_orphan() );

		$test_statuses = get_post_stati( array( '_builtin' => true ) );
		foreach ( array_keys( $test_statuses ) as $status ) {

			$assert = in_array( $status, array( 'publish', 'future', 'draft', 'pending', 'private', 'auto-draft' ), true ) ? 'assertFalse' : 'assertTrue';

			// Check parent course.
			wp_update_post(
				array(
					'ID'          => $course->get( 'id' ),
					'post_status' => $status,
				)
			);
			$this->$assert( $lesson->is_orphan() );
			wp_update_post(
				array(
					'ID'          => $course->get( 'id' ),
					'post_status' => 'publish',
				)
			);

			// Check parent section.
			wp_update_post(
				array(
					'ID'          => $section->get( 'id' ),
					'post_status' => $status,
				)
			);
			$this->$assert( $lesson->is_orphan() );
			wp_update_post(
				array(
					'ID'          => $section->get( 'id' ),
					'post_status' => 'publish',
				)
			);

		}

		// Parent course doesn't exist.
		$lesson->set( 'parent_course', 123456789 );
		$this->assertTrue( $lesson->is_orphan() );
		$lesson->set( 'parent_course', $course->get( 'id' ) );

		// Parent section doesn't exist.
		$lesson->set( 'parent_section', 123456789 );
		$this->assertTrue( $lesson->is_orphan() );
		$lesson->set( 'parent_section', $section->get( 'id' ) );

		// Parent course isn't set.
		$lesson->set( 'parent_course', '' );
		$this->assertTrue( $lesson->is_orphan() );
		$lesson->set( 'parent_course', $course->get( 'id' ) );

		// Parent section isn't set.
		$lesson->set( 'parent_section', '' );
		$this->assertTrue( $lesson->is_orphan() );
		$lesson->set( 'parent_section', $section->get( 'id' ) );

		// Metakey for parent course doesn't exist.
		delete_post_meta( $lesson->get( 'id' ), '_llms_parent_course' );
		$this->assertTrue( $lesson->is_orphan() );
		$lesson->set( 'parent_course', $course->get( 'id' ) );

		// Metakey for parent section doesn't exist.
		delete_post_meta( $lesson->get( 'id' ), '_llms_parent_section' );
		$this->assertTrue( $lesson->is_orphan() );
		$lesson->set( 'parent_section', $section->get( 'id' ) );

		// Not an orphan.
		$this->assertFalse( $lesson->is_orphan() );

	}

	/**
	 * Test next lesson
	 *
	 * @since 4.4.0
	 */
	public function test_get_next_lesson() {

		// Generate a course with 2 sections and 3 lessons for each of them.
		$course_id   = $this->generate_mock_courses( 1, 2, 3, 0 )[0];
		$section_ids = llms_get_post( $course_id )->get_sections( 'ids' );
		$section_one = llms_get_post( $section_ids[0] );
		$section_two = llms_get_post( $section_ids[1] );

		$sec_lessons = array(
			$section_one->get_lessons( 'ids' ),
			$section_two->get_lessons( 'ids' ),
		);

		// Test next lesson of s1 l2 is s1 l3.
		$this->assertEquals( $sec_lessons[0][2], llms_get_post( $sec_lessons[0][1] )->get_next_lesson() );

		// Test next lesson of s1 l3 is s2 l1.
		$this->assertEquals( $sec_lessons[1][0], llms_get_post( $sec_lessons[0][2] )->get_next_lesson() );

		// Swap s1 l2 and s1 l3 orders.
		llms_get_post( $sec_lessons[0][1] )->set( 'order', 3 );
		llms_get_post( $sec_lessons[0][2] )->set( 'order', 2 );

		// Test next lesson of s1 l1 is the original s1 l3.
		$this->assertEquals( $sec_lessons[0][2], llms_get_post( $sec_lessons[0][0] )->get_next_lesson() );
		// "Persist" the new order in our sec_lessons array.
		list( $sec_lessons[0][2], $sec_lessons[0][1] ) = array( $sec_lessons[0][1], $sec_lessons[0][2] );

		// Test s2 l3 has no next lesson.
		$this->assertFalse( llms_get_post( $sec_lessons[1][2] )->get_next_lesson() );

		// Unpublish s1 l2, test next lesson of s1 l1 is s1 l3.
		llms_get_post( $sec_lessons[0][1] )->set( 'status', 'draft' );
		$this->assertEquals( $sec_lessons[0][2], llms_get_post( $sec_lessons[0][0] )->get_next_lesson() );

		// Unpublish s2 l3, test next lesson of s2 l2 is false.
		llms_get_post( $sec_lessons[1][2] )->set( 'status', 'draft' );
		$this->assertFalse( llms_get_post( $sec_lessons[1][1] )->get_next_lesson() );
	}


	/**
	 * Test previous lesson
	 *
	 * @since 4.4.0
	 */
	public function test_get_previous_lesson() {

		// Generate a course with 2 sections and 3 lessons for each of them.
		$course_id   = $this->generate_mock_courses( 1, 2, 3, 0 )[0];
		$section_ids = llms_get_post( $course_id )->get_sections( 'ids' );
		$section_one = llms_get_post( $section_ids[0] );
		$section_two = llms_get_post( $section_ids[1] );

		$sec_lessons = array(
			$section_one->get_lessons( 'ids' ),
			$section_two->get_lessons( 'ids' ),
		);

		// Test previous lesson of s1 l3 is s1 l2.
		$this->assertEquals( $sec_lessons[0][1], llms_get_post( $sec_lessons[0][2] )->get_previous_lesson() );

		// Test previous lesson of s2 l1 is s1 l3.
		$this->assertEquals( $sec_lessons[0][2], llms_get_post( $sec_lessons[1][0] )->get_previous_lesson() );

		// Swap s1 l1 and s1 l2 orders.
		llms_get_post( $sec_lessons[0][0] )->set( 'order', 2 );
		llms_get_post( $sec_lessons[0][1] )->set( 'order', 1 );

		// Test previous lesson of s1 l3 is the original s1 l1.
		$this->assertEquals( $sec_lessons[0][0], llms_get_post( $sec_lessons[0][2] )->get_previous_lesson() );
		// "Persist" the new order in our sec_lessons array.
		list( $sec_lessons[0][0], $sec_lessons[0][1] ) = array( $sec_lessons[0][1], $sec_lessons[0][0] );

		// Test s1 l1 has no previous lesson.
		$this->assertFalse( llms_get_post( $sec_lessons[0][0] )->get_previous_lesson() );

		// Unpublish s2 l2, test previous lesson of s2 l3 is s2 l1.
		llms_get_post( $sec_lessons[1][1] )->set( 'status', 'draft' );
		$this->assertEquals( $sec_lessons[1][0], llms_get_post( $sec_lessons[1][2] )->get_previous_lesson() );

		// Unpublish s2 l1, test previous lesson of s2 l2 is s1 l3.
		llms_get_post( $sec_lessons[1][0] )->set( 'status', 'draft' );
		$this->assertEquals( $sec_lessons[0][2], llms_get_post( $sec_lessons[1][1] )->get_previous_lesson() );

		// Unpublish s1 l1, test previous lesson of s1 l2 is false.
		llms_get_post( $sec_lessons[0][0] )->set( 'status', 'draft' );
		$this->assertFalse( llms_get_post( $sec_lessons[0][1] )->get_previous_lesson() );

	}

	/**
	 * Test navigation with sections that have more than 10 lessons
	 *
	 * This scenario exposes an issue that causes string comparisons to fail, the lesson order will be returned
	 * incorrectly.
	 *
	 * @since 4.4.2
	 *
	 * @link https://github.com/gocodebox/lifterlms/issues/1316
	 *
	 * @return void
	 */
	public function test_navigation_large_sections() {

		$course  = $this->factory->course->create_and_get( array( 'sections' => 2, 'lessons' => 10, 'quizzes' => 0 ) );
		$lessons = $course->get_lessons();

		$i = 0;
		while ( $i < count( $lessons ) ) {

			$lesson = $lessons[ $i ];

			$next = 19 === $i ? false : $lessons[ $i + 1 ]->get( 'id' );
			$prev = 0 === $i ? false : $lessons[ $i - 1 ]->get( 'id' );

			$this->assertEquals( $next, $lesson->get_next_lesson(), $i );
			$this->assertEquals( $prev, $lesson->get_previous_lesson(), $i );

			++$i;

		}

	}

	/**
	 * Test comment status on creation.
	 *
	 * @since 6.3.0
	 *
	 * @return void
	 */
	public function test_comment_status_on_creation() {

		$original_comments_status = get_default_comment_status( $this->post_type );
		update_option( 'default_comment_status', 'open' );
		$lesson = new LLMS_Lesson( 'new', 'Lesson with open comments' );
		$this->assertEquals(
			'open',
			$lesson->get( 'comment_status' )
		);
		update_option( 'default_comment_status', 'closed' );
		$lesson = new LLMS_Lesson( 'new', 'Lesson with closed comments' );
		$this->assertEquals(
			'closed',
			$lesson->get( 'comment_status' )
		);
		update_option( 'default_comment_status', $original_comments_status );

	}

	/**
	 * Test next/prev lesson with empty sibling sections
	 *
	 * @since 4.11.0
	 *
	 * @return void
	 */
	public function test_navigation_empty_sibling_section() {

		$course = $this->factory->course->create_and_get(
			array(
				'sections' => 2,
				'lessons' => 1,
				'quizzes' => 0
			)
		);

		$lessons = $course->get_lessons();

		// Detach the second lesson from the second section.
		$second_section = $lessons[1]->get_parent_section();
		$lessons[1]->set_parent_section('');
		// Check the next lesson of the first one is false.
		$this->assertEquals( false, $lessons[0]->get_next_lesson() );

		// Re-attach the second lesson to the second section.
		$lessons[1]->set_parent_section( $second_section );

		// Detach the first lesson from the first section.
		$lessons[0]->set_parent_section('');
		// Check the previous lesson of the second one is false.
		$this->assertEquals( false, $lessons[1]->get_previous_lesson() );

	}
}
