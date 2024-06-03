<?php
/**
 * Test Course data background processor
 *
 * @package LifterLMS/Tests
 *
 * @group processors
 * @group processor_course_data
 *
 * @since 4.12.0
 */
class LLMS_Test_Processor_Course_Data extends LLMS_UnitTestCase {

	/**
	 * Setup before class
	 *
	 * Forces processor debugging on so that we can make assertions against logged data.
	 *
	 * @since 4.12.0
	 * @since 5.3.3 Renamed from `setUpBeforeClass()` for compat with WP core changes.
	 *
	 * @return void
	 */
	public static function set_up_before_class() {

		parent::set_up_before_class();
		llms_maybe_define_constant( 'LLMS_PROCESSORS_DEBUG', true );

	}

	/**
	 * Setup the test case
	 *
	 * @since 4.12.0
	 * @since 5.3.3 Renamed from `setUp()` for compat with WP core changes.
	 *
	 * @return void
	 */
	public function set_up() {

		parent::set_up();

		$this->main          = llms()->processors()->get( 'course_data' );
		$this->schedule_hook = LLMS_Unit_Test_Util::get_private_property_value( $this->main, 'cron_hook_identifier' );

	}

	/**
	 * Teardown the test case
	 *
	 * @since 4.12.0
	 * @since 5.3.3 Renamed from `tearDown()` for compat with WP core changes.
	 *
	 * @return void
	 */
	public function tear_down() {

		$this->main->cancel_process();
		LLMS_Unit_Test_Util::set_private_property( $this->main, 'data', array() );
		parent::tear_down();

	}

	/**
	 * Test dispatch_calc() when throttled by number of students
	 *
	 * @since 4.12.0
	 * @since 4.21.0 Assert student enrolled count early.
	 * @since 5.2.1 Added 5 second delta on date comparison assertion.
	 * @since 5.3.3 Use `assestEqualsWithDelta()`.
	 *
	 * @return void
	 */
	public function test_dispatch_calc_throttled_by_students() {

		$course_id = $this->factory->post->create( array( 'post_type' => 'course' ) );
		$this->factory->student->create_and_enroll_many( 2, $course_id );

		// Clear things so scheduling works right.
		wp_unschedule_event( wp_next_scheduled( 'llms_calculate_course_data', array( $course_id ) ), 'llms_calculate_course_data',  array( $course_id ) );
		$this->logs->clear( 'processors' );

		// Fake throttling data.
		LLMS_Unit_Test_Util::set_private_property( $this->main, 'throttle_max_students', 1 );
		$last_run = time() - HOUR_IN_SECONDS;
		update_post_meta( $course_id, '_llms_last_data_calc_run', $last_run );

		// Dispatch.
		$this->main->dispatch_calc( $course_id );

		/**
		 * Even if a course is throttled the student count should be updated right away since it's not only used for reporting
		 *
		 * @link https://github.com/gocodebox/lifterlms/issues/1564
		 */
		$this->assertEquals( 2, get_post_meta( $course_id, '_llms_enrolled_students', true ) );

		// Expected logs.
		$logs = array(
			"Course data calculation dispatched for course {$course_id}.",
			"Course data calculation triggered for course {$course_id}.",
			"Course data calculation scheduled for course {$course_id}.",
			"Course data calculation throttled for course {$course_id}.",
		);
		$this->assertEquals( $logs, $this->logs->get( 'processors' ) );

		// Event scheduled.
		$this->assertEqualsWithDelta( $last_run + ( HOUR_IN_SECONDS * 4 ), wp_next_scheduled( 'llms_calculate_course_data', array( $course_id ) ), 5 );

		LLMS_Unit_Test_Util::set_private_property( $this->main, 'throttle_max_students', 500 );

	}

	/**
	 * Test dispatch_calc() when throttled because it's already processing for the course.
	 *
	 * @since 4.12.0
	 *
	 * @return void
	 */
	public function test_dispatch_calc_throttled_by_course() {

		$course_id = $this->factory->post->create( array( 'post_type' => 'course' ) );
		$this->factory->student->create_and_enroll_many( 1, $course_id );

		update_post_meta( $course_id, '_llms_temp_calc_data_lock', 'yes' );

		$this->logs->clear( 'processors' );

		// Dispatch.
		$this->main->dispatch_calc( $course_id );

		// Expected logs.
		$logs = array(
			"Course data calculation dispatched for course {$course_id}.",
			"Course data calculation triggered for course {$course_id}.",
			"Course data calculation throttled for course {$course_id}.",
		);
		$this->assertEquals( $logs, $this->logs->get( 'processors' ) );

	}

	/**
	 * Test dispatch_calc() when there's no students in the course
	 *
	 * @since 4.21.0
 	 * @since 5.2.1 Added 5 second delta on date comparison assertion.
	 *
	 * @link https://github.com/gocodebox/lifterlms/issues/1596#issuecomment-821585937
	 *
	 * @return void
	 */
	public function test_dispatch_calc_no_students() {

		$course_id = $this->factory->post->create( array( 'post_type' => 'course' ) );
		$course    = llms_get_post( $course_id );

		// Mock meta data that may exist on the course (from a previous run, for example).
		$metas = array(
			'average_grade' => array( 95, 0 ),
			'average_progress' => array( 22, 0 ),
			'enrolled_students' => array( 204, 0 ),
			'last_data_calc_run' => array( time() - HOUR_IN_SECONDS, time() ),
			'temp_calc_data' => array( array( 123 ), array() ),
		);
		foreach ( $metas as $key => $vals ) {
			$course->set( $key, $vals[0] );
		}

		$this->main->dispatch_calc( $course_id );

		foreach ( $metas as $key => $vals ) {
			$delta = 'last_data_calc_run' === $key ? 5 : 0;
			$this->assertEqualsWithDelta( $vals[1], $course->get( $key ), $delta, $key );
		}

	}

	/**
	 * Test dispatch_calc()
	 *
	 * @since 4.12.0
	 * @since 4.21.0 Assert student enrolled count early.
	 *
	 * @return void
	 */
	public function test_dispatch_calc_success() {

		$course_id = $this->factory->post->create( array( 'post_type' => 'course' ) );
		$this->factory->student->create_and_enroll_many( 5, $course_id );
		$this->logs->clear( 'processors' );

		$handler = function( $args ) {
			$args['per_page'] = 2;
			return $args;
		};
		add_filter( 'llms_data_processor_course_data_student_query_args', $handler );

		$this->main->dispatch_calc( $course_id );

		/**
		 * Even if a course is throttled the student count should be updated right away since it's not only used for reporting
		 *
		 * @link https://github.com/gocodebox/lifterlms/issues/1564
		 */
		$this->assertEquals( 5, get_post_meta( $course_id, '_llms_enrolled_students', true ) );

		// Logged properly.
		$this->assertEquals( array( "Course data calculation dispatched for course {$course_id}." ), $this->logs->get( 'processors' ) );

		// Test data is loaded into the queue properly.
		foreach ( LLMS_Unit_Test_Util::call_method( $this->main, 'get_batch' )->data as $i => $args ) {

			$this->assertEquals( $course_id, $args['post_id'] );
			$this->assertEquals( 2, $args['per_page'] );
			$this->assertEquals( array( 'enrolled' ), $args['statuses'] );
			$this->assertEquals( ++$i, $args['page'] );

		}

		// Event scheduled.
		$this->assertTrue( ! empty( wp_next_scheduled( $this->schedule_hook ) ) );

		remove_filter( 'llms_data_processor_course_data_student_query_args', $handler );

	}

	/**
	 * Test get_last_run()
	 *
	 * @since 4.12.0
	 *
	 * @return void
	 */
	public function test_get_last_run() {

		$course_id = $this->factory->post->create( array( 'post_type' => 'course' ) );
		$this->assertEquals( 0, LLMS_Unit_Test_Util::call_method( $this->main, 'get_last_run', array( $course_id ) ) );

		$now = time();
		update_post_meta( $course_id, '_llms_last_data_calc_run', $now );
		$this->assertEquals( $now, LLMS_Unit_Test_Util::call_method( $this->main, 'get_last_run', array( $course_id ) ) );

	}

	/**
	 * Test get_task_data()
	 *
	 * @since 4.21.0
	 *
	 * @return void
	 */
	public function test_get_task_data() {

		$data = array();

		// Default data only.
		$res = LLMS_Unit_Test_Util::call_method( $this->main, 'get_task_data' );
		$this->assertEquals( array(
			'students' => 0,
			'progress' => 0,
			'quizzes'  => 0,
			'grade'    => 0,
		), $res );


		// Merge in some data
		$merge = array(
			'progress' => 25,
			'students' => 203,
			'custom' => 'abc',
		);
		$res = LLMS_Unit_Test_Util::call_method( $this->main, 'get_task_data', array( $merge ) );
		$this->assertEquals( array(
			'students' => 203,
			'progress' => 25,
			'quizzes'  => 0,
			'grade'    => 0,
			'custom'   => 'abc',
		), $res );

	}

	/**
	 * Test is_already_processing_course() when it's not processing.
	 *
	 * @since 4.12.0
	 *
	 * @return void
	 */
	public function test_is_already_processing_course() {

		$course_id = $this->factory->post->create( array( 'post_type' => 'course' ) );
		$course    = llms_get_post( $course_id );

		// No meta data.
		$this->assertFalse( LLMS_Unit_Test_Util::call_method( $this->main, 'is_already_processing_course', array( $course_id ) ) );

		// Unexpected / invalid meta values.
		$course->set( 'temp_calc_data_lock', '' );
		$this->assertFalse( LLMS_Unit_Test_Util::call_method( $this->main, 'is_already_processing_course', array( $course_id ) ) );

		$course->set( 'temp_calc_data_lock', 'no' );
		$this->assertFalse( LLMS_Unit_Test_Util::call_method( $this->main, 'is_already_processing_course', array( $course_id ) ) );

		// Is running.
		$course->set( 'temp_calc_data_lock', 'yes' );
		$this->assertTrue( LLMS_Unit_Test_Util::call_method( $this->main, 'is_already_processing_course', array( $course_id ) ) );


	}

	/**
	 * Test maybe_throttle()
	 *
	 * @since 4.12.0
	 *
	 * @return void
	 */
	public function test_maybe_throttle() {

		$course_id = $this->factory->post->create( array( 'post_type' => 'course' ) );
		$this->assertFalse( LLMS_Unit_Test_Util::call_method( $this->main, 'maybe_throttle', array( 25, $course_id ) ) );

		// Hasn't run recently.
		$this->assertFalse( LLMS_Unit_Test_Util::call_method( $this->main, 'maybe_throttle', array( 500, $course_id ) ) );
		$this->assertFalse( LLMS_Unit_Test_Util::call_method( $this->main, 'maybe_throttle', array( 2500, $course_id ) ) );

		// Should be throttled because of a recent run.
		update_post_meta( $course_id, '_llms_last_data_calc_run', time() - HOUR_IN_SECONDS );
		$this->assertTrue( LLMS_Unit_Test_Util::call_method( $this->main, 'maybe_throttle', array( 500, $course_id ) ) );
		$this->assertTrue( LLMS_Unit_Test_Util::call_method( $this->main, 'maybe_throttle', array( 2500, $course_id ) ) );

	}

	/**
	 * Test schedule_calculation()
	 *
	 * @since 4.21.0
 	 * @since 5.2.1 Added 5 second delta on date comparison assertions.
	 * @since 5.3.3 Use `assestEqualsWithDelta()`.
	 *
	 * @return void
	 */
	public function test_schedule_calculation() {

		$course_id = $this->factory->post->create( array( 'post_type' => 'course' ) );

		$expected_time = time() + HOUR_IN_SECONDS;
		$logs = array (
			"Course data calculation triggered for course {$course_id}.",
			"Course data calculation scheduled for course {$course_id}.",
		);

		// Schedule an event.
		$this->main->schedule_calculation( $course_id, $expected_time );
		$this->assertEqualsWithDelta( $expected_time, wp_next_scheduled( 'llms_calculate_course_data', array( $course_id ) ), 5 );
		$this->assertEquals( $logs, $this->logs->get( 'processors' ) );

		$this->logs->clear( 'processors' );

		// No duplicate scheduled.
		$this->main->schedule_calculation( $course_id );
		$this->assertEqualsWithDelta( $expected_time, wp_next_scheduled( 'llms_calculate_course_data', array( $course_id ) ), 5 );
		$this->assertEquals( array( $logs[0] ), $this->logs->get( 'processors' ) );

	}

	/**
	 * Test schedule_calculation() to ensure duplicate events aren't scheduled regardless of ID variable type
	 *
	 * @since 4.21.0
 	 * @since 5.2.1 Added 5 second delta on date comparison assertions.
	 * @since 5.3.3 Use `assestEqualsWithDelta()`.
	 *
	 * @link https://github.com/gocodebox/lifterlms/issues/1600
	 *
	 * @return void
	 */
	public function test_schedule_calculation_string_or_int() {

		$course_id = $this->factory->post->create( array( 'post_type' => 'course' ) );

		$expected_time = time() + HOUR_IN_SECONDS;
		$logs = array (
			"Course data calculation triggered for course {$course_id}.",
			"Course data calculation scheduled for course {$course_id}.",
		);

		// Schedule with an int.
		$this->main->schedule_calculation( $course_id, $expected_time );
		$this->assertEqualsWithDelta( $expected_time, wp_next_scheduled( 'llms_calculate_course_data', array( $course_id ) ), 5 );
		$this->assertEquals( $logs, $this->logs->get( 'processors' ) );

		$this->logs->clear( 'processors' );

		// No duplicate should be scheduled if using a string later.
		$this->main->schedule_calculation( (string) $course_id );
		$this->assertEqualsWithDelta( $expected_time, wp_next_scheduled( 'llms_calculate_course_data', array( $course_id ) ), 5 );
		$this->assertEquals( array( $logs[0] ), $this->logs->get( 'processors' ) );

	}

	/**
	 * Test schedule_from_course()
	 *
	 * @since 4.12.0
	 * @since 5.3.3 Use `assestEqualsWithDelta()`.
	 *
	 * @return void
	 */
	public function test_schedule_from_course() {

		$course_id = $this->factory->post->create( array( 'post_type' => 'course' ) );

		$this->main->schedule_from_course( 123, $course_id );

		// Logs.
		$logs = array (
			"Course data calculation triggered for course {$course_id}.",
			"Course data calculation scheduled for course {$course_id}.",
		);
		$this->assertEquals( $logs, $this->logs->get( 'processors' ) );

		// Event.
		$this->assertEqualsWithDelta( time(), wp_next_scheduled( 'llms_calculate_course_data', array( $course_id ) ), 5 );

	}

	/**
	 * Test schedule_from_lesson()
	 *
	 * @since 4.12.0
	 * @since 5.3.3 Use `assestEqualsWithDelta()`.
	 *
	 * @return void
	 */
	public function test_schedule_from_lesson() {

		$course_id = $this->factory->course->create( array( 'sections' => 1, 'lessons' => 1 ) );
		$lesson_id = llms_get_post( $course_id )->get_lessons( 'ids' )[0];

		$this->main->schedule_from_lesson( 123, $lesson_id );

		// Logs.
		$logs = array (
			"Course data calculation triggered for course {$course_id}.",
			"Course data calculation scheduled for course {$course_id}.",
		);
		$this->assertEquals( $logs, $this->logs->get( 'processors' ) );

		// Event.
		$this->assertEqualsWithDelta( time(), wp_next_scheduled( 'llms_calculate_course_data', array( $course_id ) ), 5 );

	}

	/**
	 * Test schedule_from_quiz()
	 *
	 * @since 4.12.0
	 * @since 5.3.3 Use `assestEqualsWithDelta()`.
	 *
	 * @return void
	 */
	public function test_schedule_from_quiz() {

		$course_id  = $this->factory->course->create( array( 'sections' => 1, 'lessons' => 1 ) );
		$quiz_id    = llms_get_post( $course_id )->get_lessons()[0]->get( 'quiz' );
		$student_id = $this->factory->student->create();
		$attempt    = $this->take_quiz( $quiz_id, $student_id );

		$this->main->schedule_from_quiz( $student_id, $quiz_id, $attempt );

		// Logs.
		// In this particular test the process is already running because of the lesson completion triggered by the quiz.
		// This does not render the trigger entirely useless though as the quiz itself could trigger without lessons
		// when using add-ons that implement restrictions on lesson progression.
		$logs = array (
			"Course data calculation triggered for course {$course_id}.",
			"Course data calculation scheduled for course {$course_id}.",
			"Course data calculation triggered for course {$course_id}.",
			"Course data calculation triggered for course {$course_id}.",
		);
		$this->assertEquals( $logs, $this->logs->get( 'processors' ) );

		// Event.
		$this->assertEqualsWithDelta( time(), wp_next_scheduled( 'llms_calculate_course_data', array( $course_id ) ), 5 );

	}

	/**
	 * Test task() method
	 *
	 * @since 4.12.0
	 * @since 5.3.3 Use `assestEqualsWithDelta()`.
	 *
	 * @return void
	 */
	public function test_task() {

		$course_id = $this->factory->course->create( array( 'sections' => 1, 'lessons' => 2, 'quizzes' => 1 ) );
		$course    = llms_get_post( $course_id );
		$students  = $this->factory->student->create_and_enroll_many( 5, $course_id );

		foreach ( $students as $i => $student ) {
			$perc = array( 0, 50, 50, 100, 100 );
			$this->complete_courses_for_student( $student, $course_id, $perc[ $i ] );
		}

		// Clear any data that may exist as a result of mock data creation above.
		delete_post_meta( $course_id, '_llms_temp_calc_data' );

		// Perform task for page 1, not completed, save the data.
		$this->assertFalse( $this->main->task( array(
			'post_id' => $course_id,
			'statuses' => array( 'enrolled' ),
			'page'     => 1,
			'per_page' => 2,
			'sort'     => array(
				'id' => 'ASC',
			),
		) ) );

		$expect = array(
			'students' => 2,
			'progress' => floatval( 50 ),
			'quizzes'  => 0,
			'grade'    => 0,
		);
		$this->assertEquals( $expect, $course->get( 'temp_calc_data' ) );
		$this->assertTrue( LLMS_Unit_Test_Util::call_method( $this->main, 'is_already_processing_course', array( $course_id ) ) );


		// Perform task for page 2, not completed, save the data.
		$this->assertFalse( $this->main->task( array(
			'post_id' => $course_id,
			'statuses' => array( 'enrolled' ),
			'page'     => 2,
			'per_page' => 2,
			'sort'     => array(
				'id' => 'ASC',
			),
		) ) );

		$expect = array(
			'students' => 4,
			'progress' => floatval( 200 ),
			'quizzes'  => 1,
			'grade'    => floatval( 100 ),
		);
		$this->assertEquals( $expect, $course->get( 'temp_calc_data' ) );
		$this->assertTrue( LLMS_Unit_Test_Util::call_method( $this->main, 'is_already_processing_course', array( $course_id ) ) );

		// Perform task for page 3, completed.
		$this->assertFalse( $this->main->task( array(
			'post_id' => $course_id,
			'statuses' => array( 'enrolled' ),
			'page'     => 3,
			'per_page' => 2,
			'sort'     => array(
				'id' => 'ASC',
			),
		) ) );
		$this->assertFalse( LLMS_Unit_Test_Util::call_method( $this->main, 'is_already_processing_course', array( $course_id ) ) );
		$this->assertEmpty( $course->get( 'temp_calc_data' ) );
		$this->assertEmpty( $course->get( 'temp_calc_data_lock' ) );
		$this->assertEquals( 100, $course->get( 'average_grade' ) );
		$this->assertEquals( 60, $course->get( 'average_progress' ) );
		$this->assertEquals( 5, $course->get( 'enrolled_students' ) );
		$this->assertEqualsWithDelta( time(), $course->get( 'last_data_calc_run' ), 5 );

	}

	/**
	 * Test deleted / nonexistant courses/posts.
	 *
	 * @since 4.21.0
	 *
	 * @return void
	 */
	public function test_task_nonexistent_course() {

		$tests = array(
			// Deleted course.
			$this->factory->post->create( array( 'post_type' => 'course' ) ),

			// Not a course.
			$this->factory->post->create(),
		);

		wp_delete_post( $tests[0], true );

		// Not a real post at all.
		$tests[] = $tests[1] + 1;

		foreach ( $tests as $post_id ) {

			$args = compact( 'post_id' );
			$this->assertFalse( $this->main->task( $args ) );

			$json = wp_json_encode( $args );

			$logs = array (
				"Course data calculation task called for course {$post_id} with args: {$json}",
				"Course data calculation task skipped for course {$post_id}.",
			);

			$this->assertEquals( $logs, $this->logs->get( 'processors' ) );

			$this->logs->clear( 'processors' );

		}

	}


	/**
	 * Test dispatch_calc() with multiple courses to make sure that tasks are not duplicated in other batches.
	 *
	 * @since 4.21.0
	 *
	 * @link https://github.com/gocodebox/lifterlms/issues/1602
	 *
	 * @return void
	 */
	public function test_duplicate_batch_tasks() {

		$course_ids[] = $this->factory->post->create( array( 'post_type' => 'course' ) );
		$course_ids[] = $this->factory->post->create( array( 'post_type' => 'course' ) );
		foreach ( $course_ids as $course_id ) {
			$this->factory->student->create_and_enroll_many( 5, $course_id );
		}
		$this->logs->clear( 'processors' );

		$handler = function ( $args ) {
			$args['per_page'] = 2;

			return $args;
		};
		add_filter( 'llms_data_processor_course_data_student_query_args', $handler );

		$expected_logs = array();
		foreach ( $course_ids as $course_id ) {
			$this->main->dispatch_calc( $course_id );
			$expected_logs[] = "Course data calculation dispatched for course {$course_id}.";
		}
		// Logged properly.
		$this->assertEquals( $expected_logs, $this->logs->get( 'processors' ) );

		foreach ( $course_ids as $course_id ) {
			$batch = LLMS_Unit_Test_Util::call_method( $this->main, 'get_batch' );

			// Test data is loaded into the queue properly.
			foreach ( $batch->data as $i => $student_query_args ) {
				$this->assertEquals( $course_id, $student_query_args['post_id'], $course_id );
				$this->assertEquals( 2, $student_query_args['per_page'], 'per_page' );
				$this->assertEquals( array( 'enrolled' ), $student_query_args['statuses'], 'statuses' );
				$this->assertEquals( ++ $i, $student_query_args['page'], 'page' );
			}

			// Simulate handling of queued batched tasks.
			LLMS_Unit_Test_Util::call_method( $this->main, 'delete', array( $batch->key ) );
		}

		remove_filter( 'llms_data_processor_course_data_student_query_args', $handler );

	}

}
