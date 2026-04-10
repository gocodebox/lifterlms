<?php
/**
 * Tests for LLMS_Query_Quiz_Attempt found_results / count_only behavior.
 *
 * @package LifterLMS/Tests
 *
 * @group quizzes
 * @group query
 * @group dbquery
 *
 * @since [version]
 */
class LLMS_Test_Quiz_Attempt_Query extends LLMS_UnitTestCase {

	/**
	 * Teardown.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function tear_down() {
		parent::tear_down();
		global $wpdb;
		$wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}lifterlms_quiz_attempts" );
	}

	/**
	 * Create mock quiz attempts for a student.
	 *
	 * @since [version]
	 *
	 * @param int $count Number of attempts.
	 * @return array { quiz_id, lesson_id, student_id }
	 */
	private function create_attempts( $count = 1 ) {

		$uid     = $this->factory->user->create();
		$courses = $this->generate_mock_courses( 1, 1, 1, 1, 1 );
		$course  = llms_get_post( $courses[0] );
		$lesson  = $course->get_lessons()[0];
		$lid     = $lesson->get( 'id' );
		$qid     = $lesson->get( 'quiz' );

		for ( $i = 0; $i < $count; $i++ ) {
			$attempt = LLMS_Quiz_Attempt::init( $qid, $lid, $uid );
			$attempt->save();
		}

		return array(
			'quiz_id'    => $qid,
			'lesson_id'  => $lid,
			'student_id' => $uid,
		);
	}

	/**
	 * Test found_results and max_pages with pagination.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_found_results_with_pagination() {

		$data = $this->create_attempts( 7 );

		$query = new LLMS_Query_Quiz_Attempt(
			array(
				'student_id' => $data['student_id'],
				'quiz_id'    => $data['quiz_id'],
				'per_page'   => 3,
			)
		);

		$this->assertSame( 7, $query->get_found_results() );
		$this->assertSame( 3, $query->get_max_pages() );
		$this->assertSame( 3, $query->get_number_results() );
	}

	/**
	 * Test no_found_rows skips counting.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_no_found_rows_skips_count() {

		$data = $this->create_attempts( 3 );

		$query = new LLMS_Query_Quiz_Attempt(
			array(
				'student_id'    => $data['student_id'],
				'quiz_id'       => $data['quiz_id'],
				'no_found_rows' => true,
			)
		);

		$this->assertTrue( $query->has_results() );
		$this->assertSame( 0, $query->get_found_results() );
		$this->assertSame( 0, $query->get_max_pages() );
	}

	/**
	 * Test count_only returns accurate count.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_count_only() {

		$data = $this->create_attempts( 5 );

		$query = new LLMS_Query_Quiz_Attempt(
			array(
				'student_id' => $data['student_id'],
				'quiz_id'    => $data['quiz_id'],
				'count_only' => true,
			)
		);

		$this->assertSame( 5, $query->get_count_only_result() );
	}
}
