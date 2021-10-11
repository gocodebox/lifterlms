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

}
