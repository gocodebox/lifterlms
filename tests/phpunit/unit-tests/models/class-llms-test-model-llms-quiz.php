<?php
/**
 * Tests for LifterLMS Quiz Model
 *
 * @package  LifterLMS_Tests/Models
 *
 * @group post_models
 * @group quizzes
 * @group quiz
 *
 * @since 3.16.0
 * @since 3.37.2 Added test coverage for many untested methods.
 * @since 4.2.0 Added test coverage for `is_orphan()` method with `$deep` param set to true.
 */
class LLMS_Test_LLMS_Quiz extends LLMS_PostModelUnitTestCase {

	/**
	 * class name for the model being tested by the class
	 *
	 * @var  string
	 */
	protected $class_name = 'LLMS_Quiz';

	/**
	 * db post type of the model being tested
	 *
	 * @var  string
	 */
	protected $post_type = 'llms_quiz';

	/**
	 * Get properties, used by test_getters_setters
	 *
	 * This should match, exactly, the object's $properties array.
	 *
	 * @since 3.16.0
	 *
	 * @return string[]
	 */
	protected function get_properties() {
		return array(
			'lesson_id' => 'absint',
		);
	}

	/**
	 * Get data to fill a create post with
	 *
	 * This is used by test_getters_setters.
	 *
	 * @since 3.16.0
	 *
	 * @return array
	 */
	protected function get_data() {
		return array(
			'lesson_id' => 123,
		);
	}


	/**
	 * Test the questions()->create_question() method.
	 *
	 * @since 3.16.0
	 *
	 * @return void
	 */
	public function test_create_question() {

		$this->create( 'test title' );
		$this->assertTrue( is_numeric( $this->obj->questions()->create_question() ) );

	}

	/**
	 * Test the questions()->delete_question() method.
	 *
	 * @since 3.16.0
	 *
	 * @return void
	 */
	public function test_delete_question() {

		$this->create( 'test title' );
		$qid = $this->obj->questions()->create_question();
		$this->assertTrue( $this->obj->questions()->delete_question( $qid ) );

		// belongs to another quiz, can't delete
		$this->create( 'second question' );
		$this->assertFalse( $this->obj->questions()->delete_question( $qid ) );

		// doesn't exist
		$this->assertFalse( $this->obj->questions()->delete_question( 999999999 ) );

	}

	/**
	 * Test get_course() on a quiz with no parent lesson.
	 *
	 * @since 3.37.2
	 *
	 * @return void
	 */
	public function test_get_course_no_lesson() {

		$this->create( 'test title' );
		$this->assertFalse( $this->obj->get_course() );

	}

	/**
	 * Test get_course() on a quiz with a parent lesson which has no parent course.
	 *
	 * @since 3.37.2
	 *
	 * @return void
	 */
	public function test_get_course_lesson_no_course() {

		$this->create( 'test title' );
		$lesson = llms_get_post( $this->factory->post->create( array( 'post_type' => 'lesson' ) ) );
		$lesson->set( 'quiz', $this->obj->get( 'id' ) );

		$this->assertFalse( $this->obj->get_course() );

	}

	/**
	 * Test get_course() success.
	 *
	 * @since 3.37.2
	 *
	 * @return void
	 */
	public function test_get_course() {

		$course = $this->factory->course->create_and_get( array( 'sections' => 1, 'lessons' => 1 ) );
		$lesson = $course->get_lessons()[0];
		$quiz = $lesson->get_quiz();
		$this->assertEquals( $course, $quiz->get_course() );

	}

	/**
	 * Test get_lesson() when no value is set.
	 *
	 * @since 3.37.2
	 *
	 * @return void
	 */
	public function test_get_lesson_no_value() {

		$this->create( 'test title' );
		$this->obj->set( 'lesson_id', '' );
		$this->assertFalse( $this->obj->get_lesson() );

	}

	/**
	 * Test get_lesson() when the value is an invalid post.
	 *
	 * @since 3.37.2
	 *
	 * @return void
	 */
	public function test_get_lesson_invalid() {

		$this->create( 'test title' );
		$post = $this->factory->post->create();
		$this->obj->set( 'lesson_id', ++$post );
		$this->assertNull( $this->obj->get_lesson() );

	}

	/**
	 * Test get_lesson() success.
	 *
	 * @since 3.37.2
	 *
	 * @return void
	 */
	public function test_get_lesson() {

		$course = $this->factory->course->create_and_get( array( 'sections' => 1, 'lessons' => 1 ) );
		$lesson = $course->get_lessons()[0];
		$quiz = $lesson->get_quiz();
		$this->assertEquals( $lesson, $quiz->get_lesson() );

	}

	/**
	 * Test the questions()->get_question() method.
	 *
	 * @since 3.16.0
	 *
	 * @return void
	 */
	public function test_get_question() {

		$this->create( 'test title' );
		$qid = $this->obj->questions()->create_question();
		$this->assertTrue( is_a( $this->obj->questions()->get_question( $qid ), 'LLMS_Question' ) );

		// question doesn't belong to quiz so it should return false
		$this->create( 'second question' );
		$this->assertFalse( $this->obj->questions()->get_question( $qid ) );

		// question doesn't exist
		$this->assertFalse( $this->obj->questions()->get_question( 9999999 ) );

	}

	/**
	 * Test the questions() method.
	 *
	 * @since 3.16.0
	 *
	 * @return void
	 */
	public function test_get_questions() {

		$this->create( 'test title' );
		$i = 1;
		while( $i <= 3 ) {
			$this->obj->questions()->create_question();
			$i++;
		}

		// check default 'questions'
		$questions = $this->obj->get_questions();
		$this->assertEquals( 3, count( $questions ) );
		foreach ( $questions as $question ) {
			$this->assertInstanceOf( 'LLMS_Question', $question );
		}

		// check posts return
		$questions = $this->obj->get_questions( 'posts' );
		$this->assertEquals( 3, count( $questions ) );
		foreach ( $questions as $question ) {
			$this->assertInstanceOf( 'WP_Post', $question );
		}

		// check id return
		$questions = $this->obj->get_questions( 'ids' );
		$this->assertEquals( 3, count( $questions ) );
		foreach ( $questions as $question ) {
			$this->assertTrue( is_numeric( $question ) );
		}

	}

	/**
	 * Test the has_attempt_limit() method.
	 *
	 * @since 3.37.2
	 *
	 * @return void
	 */
	public function test_has_attempt_limit() {

		$this->create();

		// No value set.
		$this->obj->set( 'limit_attempts', '' );
		$this->assertFalse( $this->obj->has_attempt_limit() );

		// Explicit no.
		$this->obj->set( 'limit_attempts', 'no' );
		$this->assertFalse( $this->obj->has_attempt_limit() );

		// Something unexpected (still no).
		$this->obj->set( 'limit_attempts', 'fake' );
		$this->assertFalse( $this->obj->has_attempt_limit() );

		// Yes..
		$this->obj->set( 'limit_attempts', 'yes' );
		$this->assertTrue( $this->obj->has_attempt_limit() );

	}

	/**
	 * Test the has_time_limit() method.
	 *
	 * @since 3.37.2
	 *
	 * @return void
	 */
	public function test_has_time_limit() {

		$this->create();

		// No value set.
		$this->obj->set( 'limit_time', '' );
		$this->assertFalse( $this->obj->has_time_limit() );

		// Explicit no.
		$this->obj->set( 'limit_time', 'no' );
		$this->assertFalse( $this->obj->has_time_limit() );

		// Something unexpected (still no).
		$this->obj->set( 'limit_time', 'fake' );
		$this->assertFalse( $this->obj->has_time_limit() );

		// Yes..
		$this->obj->set( 'limit_time', 'yes' );
		$this->assertTrue( $this->obj->has_time_limit() );

	}

	/**
	 * Test is_open() with no student.
	 *
	 * @since 3.37.2
	 *
	 * @return void
	 */
	public function test_is_open_no_student() {

		$this->create();
		$this->assertFalse( $this->obj->is_open() );

	}

	/**
	 * Test is_open() with a student when there's no attempt limits.
	 *
	 * @since 3.37.2
	 *
	 * @return void
	 */
	public function test_is_open_with_student_no_limits() {

		$this->create();

		$user = $this->factory->student->create();

		// Pass in a user id.
		$this->assertTrue( $this->obj->is_open( $user ) );

		// Use the current session's user.
		wp_set_current_user( $user );
		$this->assertTrue( $this->obj->is_open() );

	}

	/**
	 * Test is_open() with a student when there are attempt limits.
	 *
	 * @since 3.37.2
	 *
	 * @return void
	 */
	public function test_is_open_with_student_with_limits() {

		$course = $this->factory->course->create_and_get( array( 'sections' => 1, 'lessons' => 1 ) );
		$lesson = $course->get_lessons()[0];
		$quiz = $lesson->get_quiz();

		$quiz->set( 'limit_attempts', 'yes' );
		$quiz->set( 'allowed_attempts', 1 );

		$user = $this->factory->student->create();

		// Use the current session's user.
		wp_set_current_user( $user );
		$this->assertTrue( $quiz->is_open() );

		// Pass in a user id.
		$this->assertTrue( $quiz->is_open( $user ) );

		// Take the quiz.
		$this->take_quiz( $quiz->get( 'id' ), $user );

		// Use the current session's user.
		$this->assertFalse( $quiz->is_open() );

		// Pass in a user id.
		$this->assertFalse( $quiz->is_open( $user ) );

	}

	/**
	 * Test the is_orphan() method.
	 *
	 * @since 3.37.2
	 *
	 * @return void
	 */
	public function test_is_orphan() {

		$this->create();

		$this->obj->set( 'lesson_id', '' );
		$this->assertTrue( $this->obj->is_orphan() );

		$this->obj->set( 'lesson_id', 123 );
		$this->assertFalse( $this->obj->is_orphan() );

	}

	/**
	 * Test the is_orphan() method with the $deep param set to true
	 *
	 * @since 4.2.0
	 *
	 * @return void
	 */
	public function test_is_orphan_deep() {

		$this->create();
		$lesson = llms_get_post( $this->factory->post->create( array( 'post_type' => 'lesson' ) ) );
		$lesson->set( 'quiz', $this->obj->get( 'id' ) );

		// Quiz `lesson_id` meta unset, we expect both `is_orpan()` and `is_orphan( $deep = true )` to return true.
		$this->obj->set( 'lesson_id', '' );
		$this->assertTrue( $this->obj->is_orphan() );
		$this->assertTrue( $this->obj->is_orphan( true ) );

		// Quiz `lesson_id` set as `$lesson`'s id, we expect both `is_orpan()` and `is_orphan( $deep = true )` to return false.
		$this->obj->set( 'lesson_id', $lesson->get( 'id' ) );
		$this->assertFalse( $this->obj->is_orphan() );
		$this->assertFalse( $this->obj->is_orphan( true ) );

		// quiz `lesson_id` and the lesson's quiz id differ: we expect `is_orpan()` to return false but `is_orphan( $deep = true )` to return true.
		$lesson->set( 'quiz', 123 );
		$this->assertFalse( $this->obj->is_orphan() );
		$this->assertTrue( $this->obj->is_orphan( true ) );

		// quiz `lesson_id` and lesson's quiz id are equal but 123 is not a real lesson's id: we expect `is_orpan()` to return false but `is_orphan( $deep = true )` to return true.
		$this->obj->set( 'lesson_id', 123 );
		$this->assertFalse( $this->obj->is_orphan() );
		$this->assertTrue( $this->obj->is_orphan( true ) );

	}

	/**
	 * Test the questions()->update_question() method.
	 *
	 * @since 3.16.0
	 *
	 * @return void
	 */
	public function test_update_question() {

		$this->create( 'test title' );

		// create when no id supplied
		$id = $this->obj->questions()->update_question();
		$this->assertTrue( is_numeric( $id ) );

		// update should return it's own id
		$this->assertEquals( $id, $this->obj->questions()->update_question( array( 'id' => $id ) ) );

		// can't update from another quiz
		$this->create( 'second question' );
		$this->assertFalse( $this->obj->questions()->update_question( array( 'id' => $id ) ) );

	}


	/**
	 * Test get_short_date_format(): keeps the site's date order and drops only the year.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_short_date_format() {

		$this_year = strtotime( current_time( 'Y' ) . '-11-20 23:59:59' );
		$next_year = strtotime( ( current_time( 'Y' ) + 1 ) . '-01-05 10:00:00' );

		$tests = array(
			// Site format       => array( this year, another year ).
			'F j, Y'          => array( 'M j', 'M j, Y' ),
			'j F Y'           => array( 'j M', 'j M Y' ),
			'j \\d\\e F \\d\\e Y' => array( 'j \\d\\e M', 'j \\d\\e M \\d\\e Y' ),
			'd/m/Y'           => array( 'd/m', 'd/m/Y' ),
			'Y-m-d'           => array( 'm-d', 'Y-m-d' ),
			'l, F jS, Y'      => array( 'D, M jS', 'D, M jS, Y' ),
			// Japanese and Chinese, Korean, Hungarian: the text after the year goes with the year.
			'Y年n月j日'          => array( 'n月j日', 'Y年n月j日' ),
			'Y년 n월 j일'        => array( 'n월 j일', 'Y년 n월 j일' ),
			'Y. F j.'         => array( 'M j.', 'Y. M j.' ),
			'n月j日 Y年'         => array( 'n月j日', 'n月j日 Y年' ),
			// Only a year: nothing shorter to show.
			'Y'               => array( 'Y', 'Y' ),
		);

		foreach ( $tests as $site_format => $expected ) {
			update_option( 'date_format', $site_format );
			$this->assertSame( $expected[0], LLMS_Quiz::get_short_date_format( $this_year ), $site_format );
			$this->assertSame( $expected[1], LLMS_Quiz::get_short_date_format( $next_year ), $site_format );
		}

		// Escaped format characters are left alone.
		update_option( 'date_format', '\\F\\l j F Y' );
		$this->assertSame( '\\F\\l j M', LLMS_Quiz::get_short_date_format( $this_year ) );
	}

	/**
	 * Test due timestamps: real (UTC) Unix timestamps, with day-based extensions keeping the time of day
	 * across daylight saving time.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_due_timestamp_utc() {

		update_option( 'timezone_string', 'America/New_York' );
		$student_id = $this->factory->student->create();
		$this->create( 'due date' );
		$this->obj->set( 'due_method', 'date' );
		$this->obj->set( 'due_time', '23:59' );

		// 11:59 PM EDT (UTC-4) and EST (UTC-5).
		$this->obj->set( 'due_date', '2030-07-01' );
		$this->assertSame( gmmktime( 3, 59, 0, 7, 2, 2030 ), $this->obj->get_due_timestamp( $student_id ) );
		$this->assertStringContainsString( '<time datetime="2030-07-02T03:59:00+00:00">', $this->obj->get_due_date_html( $student_id ) );
		$this->obj->set( 'due_date', '2030-01-15' );
		$this->assertSame( gmmktime( 4, 59, 0, 1, 16, 2030 ), $this->obj->get_due_timestamp( $student_id ) );

		// A 7 day accommodation across the end of daylight saving time (Nov 3, 2030) is still due at 11:59 PM.
		$this->obj->set( 'due_date', '2030-10-30' );
		update_user_option( $student_id, 'llms_due_date_accommodation', 'extend' );
		update_user_option( $student_id, 'llms_due_date_extension_days', 7 );
		$this->assertSame( gmmktime( 4, 59, 0, 11, 7, 2030 ), $this->obj->get_due_timestamp( $student_id ) );

		// Per-student due dates are stored in UTC and take precedence.
		$this->obj->set_due_date_override( $student_id, gmmktime( 17, 0, 0, 11, 10, 2030 ) );
		$this->assertSame( '2030-11-10 17:00:00', $this->obj->get_due_date_override( $student_id )['due'] );
		$this->assertSame( gmmktime( 17, 0, 0, 11, 10, 2030 ), $this->obj->get_due_timestamp( $student_id ) );

		// Submission dates are stored in the site's timezone: 12:30 PM EST is 5:30 PM UTC, after the due date.
		$this->assertFalse( $this->obj->is_submission_late( $student_id, '2030-11-10 11:30:00' ) );
		$this->assertTrue( $this->obj->is_submission_late( $student_id, '2030-11-10 12:30:00' ) );

		update_option( 'timezone_string', '' );
	}

	/**
	 * Test the number of days of a quiz that has never had due date settings saved.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_due_days_default() {

		$this->create( 'due days' );

		// Matches the course builder's default for a new quiz, so choosing "days after enrollment" doesn't start at 0.
		$this->assertSame( 7, $this->obj->get( 'due_days' ) );
		$this->assertSame( 7, $this->obj->toArray()['due_days'] );

		// A saved 0 (due at the end of the enrollment day) is kept.
		$this->obj->set( 'due_days', 0 );
		$this->assertSame( 0, $this->obj->get( 'due_days' ) );
	}

	/**
	 * Test that due dates and times are read only in their stored `Y-m-d` and 24-hour `H:i` formats.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_due_timestamp_storage_format() {

		$student_id = $this->factory->student->create();
		$this->create( 'due date format' );
		$this->obj->set( 'due_method', 'date' );
		$this->obj->set( 'due_date', '2030-07-01' );

		$this->obj->set( 'due_time', '17:00' );
		$this->assertSame( gmmktime( 17, 0, 0, 7, 1, 2030 ), $this->obj->get_due_timestamp( $student_id ) );

		// A time in any other format, such as 12-hour, is the end of the day.
		$this->obj->set( 'due_time', '05:00 PM' );
		$this->assertSame( gmmktime( 23, 59, 59, 7, 1, 2030 ), $this->obj->get_due_timestamp( $student_id ) );

		// A date in any other format, or one that doesn't exist, is no due date.
		$this->obj->set( 'due_time', '' );
		foreach ( array( '07/01/2030', '2030-02-30', 'July 1, 2030' ) as $date ) {
			$this->obj->set( 'due_date', $date );
			$this->assertFalse( $this->obj->get_due_timestamp( $student_id ), $date );
		}
	}

	/**
	 * Test due dates that are a number of days after the student enrolls or the course starts.
	 *
	 * Days are calendar days in the site's timezone: daylight saving time starts on March 10, 2030, between the
	 * enrollment or start day and the due date, and the due time stays at 5:00 PM.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_due_timestamp_relative_methods() {

		global $wpdb;

		$course     = $this->factory->course->create_and_get( array( 'sections' => 1, 'lessons' => 1 ) );
		$quiz       = $course->get_lessons()[0]->get_quiz();
		$student_id = $this->factory->student->create();

		// Set after the course is created: generating a course commits the test's database transaction.
		update_option( 'timezone_string', 'America/New_York' );

		$quiz->set( 'due_method', 'enrollment' );
		$quiz->set( 'due_days', 3 );
		$quiz->set( 'due_time', '17:00' );

		// Not enrolled, or no student: there's no enrollment day to count from.
		$this->assertFalse( $quiz->get_due_timestamp( $student_id ) );
		$this->assertFalse( $quiz->get_due_timestamp() );

		// Enrolled late in the evening of March 8: due on March 11 at 5:00 PM EDT (UTC-4).
		llms_enroll_student( $student_id, $course->get( 'id' ) );
		$wpdb->update( $wpdb->prefix . 'lifterlms_user_postmeta', array( 'updated_date' => '2030-03-08 22:30:00' ), array( 'user_id' => $student_id, 'post_id' => $course->get( 'id' ) ) );
		wp_cache_flush();
		$this->assertSame( gmmktime( 21, 0, 0, 3, 11, 2030 ), $quiz->get_due_timestamp( $student_id ) );

		// Days after the course start date don't depend on the student.
		$quiz->set( 'due_method', 'start' );
		$this->assertFalse( $quiz->get_due_timestamp( $student_id ) );
		$course->set( 'start_date', '03/09/2030' );
		$this->assertSame( gmmktime( 21, 0, 0, 3, 12, 2030 ), $quiz->get_due_timestamp( $student_id ) );
		$this->assertSame( gmmktime( 21, 0, 0, 3, 12, 2030 ), $quiz->get_due_timestamp() );

		update_option( 'timezone_string', '' );
	}

	/**
	 * Test which due date applies to a student: their own due date, then their accommodation, then the default.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_due_timestamp_precedence() {

		$student_id = $this->factory->student->create();
		$other_id   = $this->factory->student->create();
		$this->create( 'due date precedence' );
		$this->obj->set( 'due_method', 'date' );
		$this->obj->set( 'due_date', '2030-07-01' );
		$this->obj->set( 'due_time', '17:00' );
		$default = gmmktime( 17, 0, 0, 7, 1, 2030 );

		// An extension without a number of days isn't an accommodation.
		update_user_option( $student_id, 'llms_due_date_accommodation', 'extend' );
		$this->assertSame( $default, $this->obj->get_due_timestamp( $student_id ) );

		// "No due dates" removes the due date, for this student only.
		update_user_option( $student_id, 'llms_due_date_accommodation', 'none' );
		$this->assertFalse( $this->obj->get_due_timestamp( $student_id ) );
		$this->assertSame( 'none', $this->obj->get_student_due_status( $student_id ) );
		$this->assertSame( $default, $this->obj->get_due_timestamp( $other_id ) );

		// The student's own due date on this quiz wins over the accommodation.
		$this->obj->set_due_date_override( $student_id, gmmktime( 9, 30, 0, 7, 4, 2030 ) );
		$this->assertSame( gmmktime( 9, 30, 0, 7, 4, 2030 ), $this->obj->get_due_timestamp( $student_id ) );

		// So does removing the due date for a student whose accommodation only extends it.
		update_user_option( $student_id, 'llms_due_date_accommodation', 'extend' );
		update_user_option( $student_id, 'llms_due_date_extension_days', 2 );
		$this->obj->set_due_date_override( $student_id, 'none' );
		$this->assertFalse( $this->obj->get_due_timestamp( $student_id ) );

		// Resetting the student's own due date goes back to the default plus the accommodation.
		$this->obj->set_due_date_override( $student_id, false );
		$this->assertFalse( $this->obj->get_due_date_override( $student_id ) );
		$this->assertSame( gmmktime( 17, 0, 0, 7, 3, 2030 ), $this->obj->get_due_timestamp( $student_id ) );
	}

	/**
	 * Test that a student's due status follows their first completed attempt.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_student_due_status_first_attempt() {

		$student_id = $this->factory->student->create();
		$this->create( 'due status' );
		$this->obj->set( 'due_method', 'date' );
		$this->obj->set( 'due_date', '2020-07-01' );
		$this->obj->set( 'due_time', '17:00' );

		$attempt = function ( $status, $end_date ) use ( $student_id ) {
			$attempt = new LLMS_Quiz_Attempt();
			$attempt->setup( array( 'quiz_id' => $this->obj->get( 'id' ), 'student_id' => $student_id, 'status' => $status, 'end_date' => $end_date ) )->save();
			return $attempt;
		};

		$this->assertFalse( $this->obj->get_first_submission_time( $student_id ) );
		$this->assertSame( 'overdue', $this->obj->get_student_due_status( $student_id ) );

		// An attempt the student hasn't finished isn't a submission.
		$attempt( 'incomplete', null );
		$this->assertSame( 'overdue', $this->obj->get_student_due_status( $student_id ) );

		// A retake after the due date is late itself, but the student submitted by the due date.
		$retake = $attempt( 'pass', '2020-07-05 10:00:00' );
		$this->assertSame( 'late', $this->obj->get_student_due_status( $student_id ) );
		$first = $attempt( 'fail', '2020-07-01 16:59:00' );
		$this->assertSame( gmmktime( 16, 59, 0, 7, 1, 2020 ), $this->obj->get_first_submission_time( $student_id ) );
		$this->assertSame( 'on-time', $this->obj->get_student_due_status( $student_id ) );
		$this->assertTrue( $retake->is_late() );
		$this->assertFalse( $first->is_late() );
	}
}
