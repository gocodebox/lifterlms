<?php
/**
 * Tests for course stream helpers and related student/lesson behavior.
 *
 * @group functions
 * @group streams
 *
 * @since [version]
 */
class LLMS_Test_Functions_Streams extends LLMS_UnitTestCase {

	/**
	 * Create a course with two streams and three lessons.
	 *
	 * Lesson 1 belongs to morning, lesson 2 to evening, lesson 3 to every stream.
	 *
	 * @since [version]
	 *
	 * @return array {
	 *     @type LLMS_Course  $course  Course object.
	 *     @type LLMS_Lesson  $morning Morning-only lesson.
	 *     @type LLMS_Lesson  $evening Evening-only lesson.
	 *     @type LLMS_Lesson  $shared  Lesson in every stream.
	 *     @type LLMS_Student $student Enrolled student.
	 * }
	 */
	private function create_stream_course() {

		$course_id = $this->generate_mock_courses( 1, 1, 3, 0 )[0];
		$course    = llms_get_post( $course_id );
		$lessons   = $course->get_lessons();

		$course->set( 'streams_enabled', 'yes' );
		$course->set(
			'streams',
			array(
				array(
					'id'   => 'morning',
					'name' => 'Morning',
				),
				array(
					'id'   => 'evening',
					'name' => 'Evening',
				),
			)
		);
		$course->set( 'streams_default', 'morning' );

		$lessons[0]->set( 'streams', array( 'morning' ) );
		$lessons[1]->set( 'streams', array( 'evening' ) );
		$lessons[2]->set( 'streams', array() );

		$student = $this->get_mock_student();
		$student->enroll( $course_id );

		return array(
			'course'  => $course,
			'morning' => $lessons[0],
			'evening' => $lessons[1],
			'shared'  => $lessons[2],
			'student' => $student,
		);
	}

	/**
	 * Test stream enablement, definitions, and default fallback.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_course_stream_definitions() {

		$course = llms_get_post( $this->factory->course->create( array( 'sections' => 0 ) ) );

		$this->assertFalse( llms_course_streams_enabled( $course ) );
		$this->assertSame( array(), llms_get_course_streams( $course ) );
		$this->assertSame( '', llms_get_course_default_stream( $course ) );

		$course->set( 'streams_enabled', 'yes' );
		$this->assertFalse( llms_course_streams_enabled( $course ) );

		$course->set(
			'streams',
			array(
				array(
					'name' => 'Morning Cohort',
				),
				array(
					'id'   => 'evening',
					'name' => 'Evening',
				),
			)
		);

		$this->assertTrue( llms_course_streams_enabled( $course ) );

		$streams = llms_get_course_streams( $course );
		$this->assertSame( 'morning-cohort', $streams[0]['id'] );
		$this->assertSame( 'Morning Cohort', $streams[0]['name'] );
		$this->assertSame( 'evening', $streams[1]['id'] );

		$this->assertSame( 'morning-cohort', llms_get_course_default_stream( $course ) );

		$course->set( 'streams_default', 'evening' );
		$this->assertSame( 'evening', llms_get_course_default_stream( $course ) );

		$course->set( 'streams_default', 'does-not-exist' );
		$this->assertSame( 'morning-cohort', llms_get_course_default_stream( $course ) );
	}

	/**
	 * Test student stream selection, validation, and fallback.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_and_set_student_stream() {

		$fixture = $this->create_stream_course();
		$course  = $fixture['course'];
		$student = $fixture['student'];

		$this->assertSame( 'morning', llms_get_student_stream( $student, $course ) );
		$this->assertFalse( llms_set_student_stream( $student, $course, 'does-not-exist' ) );
		$this->assertTrue( llms_set_student_stream( $student, $course, 'evening' ) );
		$this->assertSame( 'evening', llms_get_student_stream( $student, $course ) );
		$this->assertSame( 'evening', sanitize_title( llms_get_user_postmeta( $student->get_id(), $course->get( 'id' ), '_stream' ) ) );

		llms_update_user_postmeta( $student->get_id(), $course->get( 'id' ), '_stream', 'retired' );
		$this->assertSame( 'morning', llms_get_student_stream( $student, $course ) );

		$course->set( 'streams_enabled', 'no' );
		$this->assertSame( '', llms_get_student_stream( $student, $course ) );
		$this->assertFalse( llms_set_student_stream( $student, $course, 'evening' ) );
	}

	/**
	 * Test lesson membership in a stream.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_lesson_in_stream_and_filtering() {

		$fixture = $this->create_stream_course();
		$course  = $fixture['course'];
		$student = $fixture['student'];

		$this->assertTrue( llms_lesson_in_stream( $fixture['morning'], 'morning' ) );
		$this->assertFalse( llms_lesson_in_stream( $fixture['morning'], 'evening' ) );
		$this->assertTrue( llms_lesson_in_stream( $fixture['shared'], 'morning' ) );
		$this->assertTrue( llms_lesson_in_stream( $fixture['shared'], 'evening' ) );
		$this->assertTrue( llms_lesson_in_stream( $fixture['morning'], '' ) );

		$ids = array(
			$fixture['morning']->get( 'id' ),
			$fixture['evening']->get( 'id' ),
			$fixture['shared']->get( 'id' ),
		);

		$filtered = llms_filter_lessons_by_stream( $ids, $course, $student );
		$this->assertSame(
			array(
				$fixture['morning']->get( 'id' ),
				$fixture['shared']->get( 'id' ),
			),
			$filtered
		);

		llms_set_student_stream( $student, $course, 'evening' );
		$filtered = llms_filter_lessons_by_stream( $ids, $course, $student );
		$this->assertSame(
			array(
				$fixture['evening']->get( 'id' ),
				$fixture['shared']->get( 'id' ),
			),
			$filtered
		);
	}

	/**
	 * Test sanitization of course and lesson stream lists.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_sanitize_streams() {

		$this->assertSame( array(), llms_sanitize_course_streams( 'nope' ) );
		$this->assertSame(
			array(
				array(
					'id'   => 'alpha',
					'name' => 'Alpha',
				),
				array(
					'id'   => 'alpha-2',
					'name' => 'Alpha',
				),
			),
			llms_sanitize_course_streams(
				array(
					'Alpha',
					array(
						'id'   => 'alpha',
						'name' => 'Alpha',
					),
					array(
						'name' => '',
					),
				)
			)
		);

		$fixture = $this->create_stream_course();
		$this->assertSame( array( 'morning' ), llms_sanitize_lesson_streams( array( 'morning', 'morning', 'nope' ), $fixture['course'] ) );
		$this->assertSame( array(), llms_sanitize_lesson_streams( array() ) );
	}

	/**
	 * Test stream-filtered progress, next lesson, and completion after a stream switch.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_progress_and_next_lesson_are_stream_filtered() {

		$fixture = $this->create_stream_course();
		$course  = $fixture['course'];
		$student = $fixture['student'];

		$this->assertEquals( 0, $student->get_progress( $course->get( 'id' ), 'course', false ) );
		$this->assertSame( $fixture['morning']->get( 'id' ), $student->get_next_lesson( $course->get( 'id' ) ) );

		$student->mark_complete( $fixture['morning']->get( 'id' ), 'lesson' );
		$student->mark_complete( $fixture['shared']->get( 'id' ), 'lesson' );

		$this->assertEquals( 100, $student->get_progress( $course->get( 'id' ), 'course', false ) );
		$this->assertFalse( $student->get_next_lesson( $course->get( 'id' ) ) );

		$this->assertTrue( llms_set_student_stream( $student, $course, 'evening' ) );
		$this->assertEquals( 50, $student->get_progress( $course->get( 'id' ), 'course', false ) );
		$this->assertSame( $fixture['evening']->get( 'id' ), $student->get_next_lesson( $course->get( 'id' ) ) );
		$this->assertFalse( $student->is_complete( $course->get( 'id' ), 'course' ) );

		$student->mark_complete( $fixture['evening']->get( 'id' ), 'lesson' );
		$this->assertTrue( llms_set_student_stream( $student, $course, 'morning' ) );
		$this->assertTrue( $student->is_complete( $course->get( 'id' ), 'course' ) );
	}

	/**
	 * Test lesson prev/next navigation follows the student's stream.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_lesson_navigation_is_stream_aware() {

		$fixture = $this->create_stream_course();
		$course  = $fixture['course'];
		$student = $fixture['student'];

		wp_set_current_user( $student->get_id() );

		$this->assertSame( $fixture['shared']->get( 'id' ), $fixture['morning']->get_next_lesson() );
		$this->assertFalse( $fixture['morning']->get_previous_lesson() );
		$this->assertSame( $fixture['morning']->get( 'id' ), $fixture['shared']->get_previous_lesson() );
		$this->assertFalse( $fixture['shared']->get_next_lesson() );

		llms_set_student_stream( $student, $course, 'evening' );

		$this->assertSame( $fixture['shared']->get( 'id' ), $fixture['evening']->get_next_lesson() );
		$this->assertFalse( $fixture['evening']->get_previous_lesson() );
		$this->assertSame( $fixture['evening']->get( 'id' ), $fixture['shared']->get_previous_lesson() );
		$this->assertFalse( $fixture['shared']->get_next_lesson() );
	}

	/**
	 * Test out-of-stream lessons are restricted and cannot be marked complete.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_stream_access_restriction() {

		$fixture = $this->create_stream_course();
		$course  = $fixture['course'];
		$student = $fixture['student'];

		wp_set_current_user( $student->get_id() );

		$this->assertFalse( llms_is_post_restricted_by_stream( $fixture['morning']->get( 'id' ), $student->get_id() ) );
		$this->assertSame( $fixture['evening']->get( 'id' ), llms_is_post_restricted_by_stream( $fixture['evening']->get( 'id' ), $student->get_id() ) );
		$this->assertFalse( llms_is_post_restricted_by_stream( $fixture['shared']->get( 'id' ), $student->get_id() ) );
		$this->assertFalse( llms_is_post_restricted_by_stream( $course->get( 'id' ), $student->get_id() ) );

		$this->assertTrue( llms_can_user_complete_lesson( $student->get_id(), $fixture['morning'] ) );
		$this->assertFalse( llms_can_user_complete_lesson( $student->get_id(), $fixture['evening'] ) );

		$restriction = array(
			'reason'         => 'lesson_stream',
			'restriction_id' => $fixture['evening']->get( 'id' ),
			'content_id'     => $fixture['evening']->get( 'id' ),
		);
		$this->assertStringContainsString( $fixture['evening']->get( 'title' ), llms_get_restriction_message( $restriction ) );
	}

	/**
	 * Test the stream selector form controller.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_controller_stream_form() {

		$fixture    = $this->create_stream_course();
		$course     = $fixture['course'];
		$student    = $fixture['student'];
		$controller = new LLMS_Controller_Course_Streams();

		$controller->handle_stream_form();
		$this->assertSame( 'morning', llms_get_student_stream( $student, $course ) );

		wp_set_current_user( $student->get_id() );
		$this->mockPostRequest(
			array(
				'llms_change_stream'         => '1',
				'llms_change_stream_nonce'  => wp_create_nonce( 'llms_change_stream' ),
				'llms_stream_course_id'     => $course->get( 'id' ),
				'llms_stream_id'           => 'evening',
			)
		);

		try {
			$controller->handle_stream_form();
			$this->fail( 'Expected a redirect after a successful stream change.' );
		} catch ( LLMS_Unit_Test_Exception_Redirect $exception ) {
			$this->assertSame(
				get_permalink( $course->get( 'id' ) ) . ' [302] YES',
				$exception->getMessage()
			);
		}

		$this->assertSame( 'evening', llms_get_student_stream( $student, $course ) );
	}

	/**
	 * Test REST schema, request mapping, enrollment field, and ability configs.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_rest_streams_integration() {

		$rest    = new LLMS_REST_Streams();
		$fixture = $this->create_stream_course();
		$course  = $fixture['course'];
		$student = $fixture['student'];

		$schema = $rest->add_course_schema_properties( array( 'properties' => array() ) );
		$this->assertArrayHasKey( 'streams_enabled', $schema['properties'] );
		$this->assertArrayHasKey( 'streams', $schema['properties'] );
		$this->assertArrayHasKey( 'streams_default', $schema['properties'] );

		$lesson_schema = $rest->add_lesson_schema_properties( array( 'properties' => array() ) );
		$this->assertArrayHasKey( 'streams', $lesson_schema['properties'] );

		$data = $rest->prepare_course_response( array(), $course );
		$this->assertTrue( $data['streams_enabled'] );
		$this->assertSame( 'morning', $data['streams_default'] );
		$this->assertCount( 2, $data['streams'] );

		$request = new WP_REST_Request();
		$request->set_param( 'streams_enabled', false );
		$request->set_param(
			'streams',
			array(
				array(
					'name' => 'Weekend',
				),
			)
		);
		$request->set_param( 'streams_default', 'weekend' );
		$prepared = $rest->pre_insert_course( array(), $request );
		$this->assertSame( 'no', $prepared['streams_enabled'] );
		$this->assertSame( 'weekend', $prepared['streams'][0]['id'] );
		$this->assertSame( 'weekend', $prepared['streams_default'] );

		$request->set_param( 'streams_enabled', true );
		$prepared = $rest->pre_insert_course( array(), $request );
		$this->assertSame( 'yes', $prepared['streams_enabled'] );

		$lesson_request = new WP_REST_Request();
		$lesson_request->set_param( 'streams', array( 'morning', 'nope' ) );
		$lesson_prepared = $rest->pre_insert_lesson(
			array(
				'parent_course' => $course->get( 'id' ),
			),
			$lesson_request
		);
		$this->assertSame( array( 'morning' ), $lesson_prepared['streams'] );

		$enrollment = array(
			'student_id' => $student->get_id(),
			'post_id'    => $course->get( 'id' ),
		);
		$this->assertSame( 'morning', $rest->get_enrollment_stream( $enrollment ) );

		$object = (object) $enrollment;
		$this->assertTrue( $rest->update_enrollment_stream( 'evening', $object ) );
		$this->assertSame( 'evening', llms_get_student_stream( $student, $course ) );
		$this->assertWPError( $rest->update_enrollment_stream( 'nope', $object ) );

		$configs = $rest->add_ability_configs( array() );
		$names   = wp_list_pluck( $configs, 'name' );
		$this->assertContains( 'get-student-stream', $names );
		$this->assertContains( 'set-student-stream', $names );

		$set_config = null;
		foreach ( $configs as $config ) {
			if ( 'set-student-stream' === $config['name'] ) {
				$set_config = $config;
				break;
			}
		}
		$this->assertIsArray( $set_config );
		$this->assertArrayHasKey( 'stream', $set_config['args'] );
		$this->assertTrue( $set_config['args']['stream']['required'] );
	}
}
