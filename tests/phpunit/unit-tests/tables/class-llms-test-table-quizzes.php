<?php
/**
 * Test the quizzes reporting table.
 *
 * @package LifterLMS/Tests/Tables
 *
 * @group reporting_tables
 *
 * @since 3.36.1
 */
class LLMS_Test_Table_Quizzes extends LLMS_UnitTestCase {

	/**
	 * Setup test.
	 *
	 * @since 3.36.1
	 * @since 5.3.3 Renamed from `setUp()` for compat with WP core changes.
	 *
	 * @return void
	 */
	public function set_up() {

		parent::set_up();
		require_once LLMS_PLUGIN_DIR . 'includes/admin/reporting/tables/llms.table.quizzes.php';
		$this->table = new LLMS_Table_Quizzes();

	}

	/**
	 * test quizzes table is empty for instructors with no courses or courses with no lessons
	 *
	 * @since 3.36.1
	 *
	 * @return void
	 */
	public function test_no_quizzes_for_instructor_with_no_course_lesson() {

		// Setup a course with lessons and quizzes.
		$course = $this->factory->course->create_and_get( array(
			'sections' => 1,
			'lessons' => 3,
			'quizzes' => 2,
		) );

		// Setup an instructor.
		$instructor_id = $this->factory->instructor->create();

		wp_set_current_user( $instructor_id );

		// The instructor has no courses, we expect no data.
		$table = new LLMS_Table_Quizzes();
		$table->get_results();
		$this->assertEquals( 0, count( $table->get_tbody_data() ) );

		// Setup a course with no lessons and assign the instructor to the course.
		$inst_course = $this->factory->course->create_and_get( array(
			'sections' => 1,
			'lessons'  => 0,
		) );
		$inst_course->instructors()->set_instructors(array(
			array(
				'id' => $instructor_id,
			),
		));

		// The instructor has a course, but the course has no lessons, we expect no data.
		$table = new LLMS_Table_Quizzes();
		$table->get_results();
		$this->assertEquals( 0, count( $table->get_tbody_data() ) );

	}

	/**
	 * Test filtering quizzes by attempts awaiting review.
	 *
	 * @return void
	 */
	public function test_filter_by_awaiting_review() {

		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );

		$course   = $this->factory->course->create_and_get( array(
			'sections' => 1,
			'lessons'  => 3,
			'quizzes'  => 3,
		) );
		$quiz_ids = array_map( function( $lesson ) {
			return $lesson->get( 'quiz' );
		}, $course->get_lessons() );

		$student_id = $this->factory->student->create();

		// No quiz has a pending attempt yet.
		$this->table->get_results( array( 'filter' => 'pending', 'filterby' => 'pending' ) );
		$this->assertEquals( array(), $this->table->get_tbody_data() );

		// One graded attempt and one pending attempt on different quizzes.
		$this->take_quiz( $quiz_ids[0], $student_id );
		$this->take_quiz( $quiz_ids[1], $student_id )->set( 'status', 'pending', true );

		$table = new LLMS_Table_Quizzes();
		$table->get_results( array( 'filter' => 'pending', 'filterby' => 'pending' ) );
		$this->assertEquals( array( $quiz_ids[1] ), wp_list_pluck( $table->get_tbody_data(), 'ID' ) );
		$this->assertEquals( 1, LLMS_Unit_Test_Util::call_method( $table, 'get_data', array( 'pending', $quiz_ids[1] ) ) );

		// The "any" option is the same as no filter.
		$unfiltered = new LLMS_Table_Quizzes();
		$unfiltered->get_results();

		$table = new LLMS_Table_Quizzes();
		$table->get_results( array( 'filter' => 'any', 'filterby' => 'pending' ) );
		$this->assertEquals( wp_list_pluck( $unfiltered->get_tbody_data(), 'ID' ), wp_list_pluck( $table->get_tbody_data(), 'ID' ) );
		$this->assertContains( $quiz_ids[0], wp_list_pluck( $table->get_tbody_data(), 'ID' ) );

	}

}
