<?php
/**
 * Tests for the LLMS_Engagements_Scanner and LLMS_Engagements_Thresholds classes
 *
 * @package LifterLMS/Tests
 *
 * @group engagements
 * @group engagements_scanner
 *
 * @since [version]
 */
class LLMS_Test_Engagements_Scanner extends LLMS_UnitTestCase {

	/**
	 * @var LLMS_Engagements_Scanner
	 */
	private $scanner;

	/**
	 * Setup the test case.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function set_up() {
		parent::set_up();
		$this->scanner = llms()->engagements()->scanner;
		reset_phpmailer_instance();
	}

	/**
	 * Teardown the test case.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function tear_down() {
		llms_tests_reset_current_time();
		parent::tear_down();
	}

	/**
	 * Backdate all user postmeta rows for a given user & post.
	 *
	 * @since [version]
	 *
	 * @param int    $user_id WP_User ID.
	 * @param int    $post_id WP_Post ID.
	 * @param string $date    MySQL datetime string.
	 * @return void
	 */
	private function backdate_user_postmeta( $user_id, $post_id, $date ) {
		global $wpdb;
		$wpdb->query(
			$wpdb->prepare(
				"UPDATE {$wpdb->prefix}lifterlms_user_postmeta SET updated_date = %s WHERE user_id = %d AND post_id = %d",
				$date,
				$user_id,
				$post_id
			)
		);
	}

	/**
	 * Create an engagement configured for a scan-based trigger.
	 *
	 * @since [version]
	 *
	 * @param string $trigger_type Trigger type slug.
	 * @param int    $trigger_post WP_Post ID of the trigger (related) post.
	 * @param int    $period       Inactivity period in days.
	 * @return WP_Post
	 */
	private function create_scan_engagement( $trigger_type, $trigger_post, $period ) {
		$engagement = $this->create_mock_engagement( $trigger_type, 'email', 0, $trigger_post );
		update_post_meta( $engagement->ID, '_llms_engagement_trigger_period', $period );
		return $engagement;
	}

	/**
	 * Reset a metabox's run-once save guard so a subsequent `wp_update_post()`
	 * simulates a fresh admin save request.
	 *
	 * @since [version]
	 *
	 * @param LLMS_Admin_Metabox $metabox Metabox instance.
	 * @return void
	 */
	private function reset_metabox_saved_guard( $metabox ) {

		$saved = new ReflectionProperty( LLMS_Admin_Metabox::class, '_saved' );
		// PHP 8.1+ can write private properties without this. Calling it on 8.5 is a deprecation.
		if ( PHP_VERSION_ID < 80100 ) {
			$saved->setAccessible( true );
		}
		$saved->setValue( $metabox, null );
	}

	/**
	 * Test that core scannable triggers are registered and the registration filter works.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_scannable_triggers() {

		$triggers = $this->scanner->get_scannable_triggers();

		$expected = array(
			'days_since_login',
			'course_inactivity',
			'course_never_started',
			'course_completion_deadline',
			'quiz_attempt_abandoned',
		);
		foreach ( $expected as $slug ) {
			$this->assertArrayHasKey( $slug, $triggers, $slug );
			$this->assertTrue( is_callable( $triggers[ $slug ] ), $slug );
		}

		// Add-ons can register additional scannable triggers.
		$callback = function ( $triggers ) {
			$triggers['mock_scan_trigger'] = '__return_empty_array';
			return $triggers;
		};
		add_filter( 'llms_scannable_engagement_triggers', $callback );
		$this->assertArrayHasKey( 'mock_scan_trigger', $this->scanner->get_scannable_triggers() );
		remove_filter( 'llms_scannable_engagement_triggers', $callback );
	}

	/**
	 * Test the days_since_login candidate query, including the never-logged-in registration
	 * fallback and the requirement that unscoped scans only cover enrolled students.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_query_days_since_login() {

		$engagement = $this->create_scan_engagement( 'days_since_login', 0, 14 );
		update_post_meta( $engagement->ID, '_llms_engagement_trigger_post', 'any' );

		$course_id = $this->factory->course->create(
			array(
				'sections' => 1,
				'lessons'  => 1,
				'quizzes'  => 0,
			)
		);

		$inactive = $this->factory->student->create();
		$active   = $this->factory->student->create();
		$never    = $this->factory->student->create();
		foreach ( array( $inactive, $active, $never ) as $student ) {
			llms_enroll_student( $student, $course_id );
		}

		$old_login = gmdate( 'Y-m-d H:i:s', llms_current_time( 'timestamp' ) - ( 20 * DAY_IN_SECONDS ) );
		update_user_meta( $inactive, 'llms_last_login', $old_login );
		update_user_meta( $active, 'llms_last_login', llms_current_time( 'mysql' ) );

		// Not enrolled in anything: never a candidate, no matter how stale the login.
		$unenrolled = $this->factory->user->create();
		update_user_meta( $unenrolled, 'llms_last_login', $old_login );

		$result   = $this->scanner->query_days_since_login( get_post( $engagement->ID ), 0, 500 );
		$user_ids = wp_list_pluck( $result['candidates'], 'user_id' );

		$this->assertContains( $inactive, $user_ids );
		$this->assertNotContains( $active, $user_ids );
		// Never logged in: falls back to the registration date (recent, so not a candidate).
		$this->assertNotContains( $never, $user_ids );
		$this->assertNotContains( $unenrolled, $user_ids );
	}

	/**
	 * Test course_inactivity and course_never_started are mutually exclusive.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_query_course_activity_mutual_exclusivity() {

		$course_id = $this->factory->course->create(
			array(
				'sections' => 1,
				'lessons'  => 2,
				'quizzes'  => 0,
			)
		);
		$course    = llms_get_post( $course_id );
		$lesson_id = $course->get_lessons( 'ids' )[0];

		$stalled       = $this->factory->student->create();
		$never_started = $this->factory->student->create();
		$fresh         = $this->factory->student->create();

		llms_enroll_student( $stalled, $course_id );
		llms_enroll_student( $never_started, $course_id );
		llms_enroll_student( $fresh, $course_id );

		llms_mark_complete( $stalled, $lesson_id, 'lesson' );

		// Backdate everything for the stalled and never-started students by 30 days.
		$backdate = gmdate( 'Y-m-d H:i:s', llms_current_time( 'timestamp' ) - ( 30 * DAY_IN_SECONDS ) );
		foreach ( array_merge( array( $course_id ), $course->get_lessons( 'ids' ), $course->get_sections( 'ids' ) ) as $post_id ) {
			$this->backdate_user_postmeta( $stalled, $post_id, $backdate );
			$this->backdate_user_postmeta( $never_started, $post_id, $backdate );
		}

		$inactivity_engagement = $this->create_scan_engagement( 'course_inactivity', $course_id, 14 );
		$started_engagement    = $this->create_scan_engagement( 'course_never_started', $course_id, 14 );

		$inactivity_result = $this->scanner->query_course_inactivity( get_post( $inactivity_engagement->ID ), 0, 500 );
		$inactivity_ids    = wp_list_pluck( $inactivity_result['candidates'], 'user_id' );

		$never_result = $this->scanner->query_course_never_started( get_post( $started_engagement->ID ), 0, 500 );
		$never_ids    = wp_list_pluck( $never_result['candidates'], 'user_id' );

		// The stalled student started (completed a lesson) then went idle: inactivity only.
		$this->assertContains( $stalled, $inactivity_ids );
		$this->assertNotContains( $stalled, $never_ids );

		// The never-started student is exclusively a never-started candidate.
		$this->assertContains( $never_started, $never_ids );
		$this->assertNotContains( $never_started, $inactivity_ids );

		// The freshly-enrolled student matches neither.
		$this->assertNotContains( $fresh, $inactivity_ids );
		$this->assertNotContains( $fresh, $never_ids );

		// No double-fire is structurally possible.
		$this->assertEmpty( array_intersect( $inactivity_ids, $never_ids ) );
	}

	/**
	 * Test course_inactivity never fires for students who already completed the course.
	 *
	 * Completed students remain enrolled with stale activity dates, but they are
	 * done, not stalled.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_query_course_inactivity_excludes_completed_students() {

		$course_id = $this->factory->course->create(
			array(
				'sections' => 1,
				'lessons'  => 2,
				'quizzes'  => 0,
			)
		);
		$course    = llms_get_post( $course_id );
		$lessons   = $course->get_lessons( 'ids' );

		$completed = $this->factory->student->create();
		$stalled   = $this->factory->student->create();
		llms_enroll_student( $completed, $course_id );
		llms_enroll_student( $stalled, $course_id );

		llms_mark_complete( $completed, $lessons[0], 'lesson' );
		llms_mark_complete( $completed, $lessons[1], 'lesson' );
		llms_mark_complete( $stalled, $lessons[0], 'lesson' );

		$backdate = gmdate( 'Y-m-d H:i:s', llms_current_time( 'timestamp' ) - ( 30 * DAY_IN_SECONDS ) );
		foreach ( array_merge( array( $course_id ), $lessons, $course->get_sections( 'ids' ) ) as $post_id ) {
			$this->backdate_user_postmeta( $completed, $post_id, $backdate );
			$this->backdate_user_postmeta( $stalled, $post_id, $backdate );
		}

		$engagement = $this->create_scan_engagement( 'course_inactivity', $course_id, 14 );
		$result     = $this->scanner->query_course_inactivity( get_post( $engagement->ID ), 0, 500 );
		$user_ids   = wp_list_pluck( $result['candidates'], 'user_id' );

		$this->assertContains( $stalled, $user_ids );
		$this->assertNotContains( $completed, $user_ids );
	}

	/**
	 * Test that engagement send/award bookkeeping rows do not count as student activity.
	 *
	 * A successful email send writes `_email_sent` user postmeta on the related course;
	 * if that counted as activity the send itself would re-arm the engagement and it
	 * would refire every period forever.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_engagement_send_does_not_count_as_activity() {

		$course_id = $this->factory->course->create(
			array(
				'sections' => 1,
				'lessons'  => 2,
				'quizzes'  => 0,
			)
		);
		$course    = llms_get_post( $course_id );
		$lesson_id = $course->get_lessons( 'ids' )[0];

		$student = $this->factory->student->create();
		llms_enroll_student( $student, $course_id );
		llms_mark_complete( $student, $lesson_id, 'lesson' );

		$backdate = gmdate( 'Y-m-d H:i:s', llms_current_time( 'timestamp' ) - ( 30 * DAY_IN_SECONDS ) );
		foreach ( array_merge( array( $course_id ), $course->get_lessons( 'ids' ), $course->get_sections( 'ids' ) ) as $post_id ) {
			$this->backdate_user_postmeta( $student, $post_id, $backdate );
		}

		// Simulate a successful engagement email send recorded against the course.
		llms_update_user_postmeta( $student, $course_id, '_email_sent', 123, false );

		$engagement = $this->create_scan_engagement( 'course_inactivity', $course_id, 14 );
		$result     = $this->scanner->query_course_inactivity( get_post( $engagement->ID ), 0, 500 );

		$candidates = array();
		foreach ( $result['candidates'] as $candidate ) {
			$candidates[ $candidate['user_id'] ] = $candidate;
		}

		// Still a candidate, and the anchor is the real (old) activity date, not the send date.
		$this->assertArrayHasKey( $student, $candidates );
		$this->assertEquals( $backdate, $candidates[ $student ]['anchor'] );
	}

	/**
	 * Test that recent quiz attempt activity prevents a student from being considered inactive.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_query_course_inactivity_quiz_attempts_count_as_activity() {

		global $wpdb;

		$course_id = $this->factory->course->create(
			array(
				'sections' => 1,
				'lessons'  => 2,
				'quizzes'  => 1,
			)
		);
		$course    = llms_get_post( $course_id );
		$lesson_id = $course->get_lessons( 'ids' )[0];
		$quiz_id   = $course->get_quizzes()[0];

		$student = $this->factory->student->create();
		llms_enroll_student( $student, $course_id );
		llms_mark_complete( $student, $lesson_id, 'lesson' );

		$backdate = gmdate( 'Y-m-d H:i:s', llms_current_time( 'timestamp' ) - ( 30 * DAY_IN_SECONDS ) );
		foreach ( array_merge( array( $course_id ), $course->get_lessons( 'ids' ), $course->get_sections( 'ids' ) ) as $post_id ) {
			$this->backdate_user_postmeta( $student, $post_id, $backdate );
		}

		$engagement = $this->create_scan_engagement( 'course_inactivity', $course_id, 14 );

		// Without quiz activity the student is a candidate.
		$result = $this->scanner->query_course_inactivity( get_post( $engagement->ID ), 0, 500 );
		$this->assertContains( $student, wp_list_pluck( $result['candidates'], 'user_id' ) );

		// A recent (failed or incomplete) quiz attempt counts as activity.
		$wpdb->insert(
			"{$wpdb->prefix}lifterlms_quiz_attempts",
			array(
				'student_id'  => $student,
				'quiz_id'     => $quiz_id,
				'status'      => 'fail',
				'update_date' => llms_current_time( 'mysql' ),
			)
		);

		$result = $this->scanner->query_course_inactivity( get_post( $engagement->ID ), 0, 500 );
		$this->assertNotContains( $student, wp_list_pluck( $result['candidates'], 'user_id' ) );
	}

	/**
	 * Test the course_completion_deadline candidate query.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_query_course_completion_deadline() {

		$course_id = $this->factory->course->create(
			array(
				'sections' => 1,
				'lessons'  => 1,
				'quizzes'  => 0,
			)
		);
		$course    = llms_get_post( $course_id );

		$overdue   = $this->factory->student->create();
		$completed = $this->factory->student->create();

		llms_enroll_student( $overdue, $course_id );
		llms_enroll_student( $completed, $course_id );

		foreach ( $course->get_lessons( 'ids' ) as $lesson_id ) {
			llms_mark_complete( $completed, $lesson_id, 'lesson' );
		}

		$backdate = gmdate( 'Y-m-d H:i:s', llms_current_time( 'timestamp' ) - ( 30 * DAY_IN_SECONDS ) );
		$this->backdate_user_postmeta( $overdue, $course_id, $backdate );

		// Backdate the completed student's enrollment too: completion should still exclude them.
		global $wpdb;
		$wpdb->query(
			$wpdb->prepare(
				"UPDATE {$wpdb->prefix}lifterlms_user_postmeta SET updated_date = %s WHERE user_id = %d AND post_id = %d AND meta_key = '_start_date'",
				$backdate,
				$completed,
				$course_id
			)
		);

		$engagement = $this->create_scan_engagement( 'course_completion_deadline', $course_id, 14 );
		$result     = $this->scanner->query_course_completion_deadline( get_post( $engagement->ID ), 0, 500 );
		$user_ids   = wp_list_pluck( $result['candidates'], 'user_id' );

		$this->assertContains( $overdue, $user_ids );
		$this->assertNotContains( $completed, $user_ids );
	}

	/**
	 * Test the quiz_attempt_abandoned candidate query.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_query_quiz_attempt_abandoned() {

		global $wpdb;

		$quiz_id    = $this->factory->post->create( array( 'post_type' => 'llms_quiz' ) );
		$student    = $this->factory->student->create();
		$engagement = $this->create_scan_engagement( 'quiz_attempt_abandoned', $quiz_id, 7 );

		$old = gmdate( 'Y-m-d H:i:s', llms_current_time( 'timestamp' ) - ( 10 * DAY_IN_SECONDS ) );

		$insert = function ( $status, $date ) use ( $wpdb, $student, $quiz_id ) {
			$wpdb->insert(
				"{$wpdb->prefix}lifterlms_quiz_attempts",
				array(
					'student_id'  => $student,
					'quiz_id'     => $quiz_id,
					'status'      => $status,
					'update_date' => $date,
				)
			);
			return $wpdb->insert_id;
		};

		$abandoned = $insert( 'incomplete', $old );
		$insert( 'incomplete', llms_current_time( 'mysql' ) ); // Recent: not abandoned yet.
		$insert( 'fail', $old ); // Completed attempts are never candidates.

		$result  = $this->scanner->query_quiz_attempt_abandoned( get_post( $engagement->ID ), 0, 500 );
		$anchors = wp_list_pluck( $result['candidates'], 'anchor' );

		$this->assertEquals( array( $abandoned ), $anchors );
		$this->assertEquals( $student, $result['candidates'][0]['user_id'] );
		$this->assertEquals( $quiz_id, $result['candidates'][0]['related_post_id'] );
	}

	/**
	 * Test fire-once / re-arm semantics of maybe_fire().
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_maybe_fire_rearm() {

		$course_id  = $this->factory->course->create(
			array(
				'sections' => 1,
				'lessons'  => 1,
				'quizzes'  => 0,
			)
		);
		$student    = $this->factory->student->create();
		llms_enroll_student( $student, $course_id );

		$post       = $this->create_scan_engagement( 'course_inactivity', $course_id, 14 );
		$engagement = (object) array(
			'trigger_id'    => $post->ID,
			'engagement_id' => get_post_meta( $post->ID, '_llms_engagement', true ),
			'trigger_event' => 'course_inactivity',
			'event_type'    => 'email',
			'delay'         => 0,
		);

		$candidate = array(
			'user_id'         => $student,
			'related_post_id' => $course_id,
			'anchor'          => '2026-01-01 00:00:00',
		);

		// First encounter fires.
		$this->assertTrue( $this->scanner->maybe_fire( $engagement, $candidate ) );

		// Same anchor: still idle since the last fire, no re-fire.
		$this->assertFalse( $this->scanner->maybe_fire( $engagement, $candidate ) );

		// Newer anchor: the student was active again and went idle again, re-fire.
		$candidate['anchor'] = '2026-02-01 00:00:00';
		$this->assertTrue( $this->scanner->maybe_fire( $engagement, $candidate ) );

		// And the marker advanced.
		$this->assertFalse( $this->scanner->maybe_fire( $engagement, $candidate ) );
	}

	/**
	 * Test candidate queries exclude users which no longer exist.
	 *
	 * Deleting a WP user does not remove their LifterLMS user postmeta rows, so the
	 * candidate queries must join against the users table.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_queries_exclude_deleted_users() {

		global $wpdb;

		$course_id = $this->factory->course->create(
			array(
				'sections' => 1,
				'lessons'  => 2,
				'quizzes'  => 0,
			)
		);
		$course    = llms_get_post( $course_id );
		$lesson_id = $course->get_lessons( 'ids' )[0];

		$kept    = $this->factory->student->create();
		$deleted = $this->factory->student->create();

		$backdate = gmdate( 'Y-m-d H:i:s', llms_current_time( 'timestamp' ) - ( 30 * DAY_IN_SECONDS ) );
		foreach ( array( $kept, $deleted ) as $student ) {
			llms_enroll_student( $student, $course_id );
			llms_mark_complete( $student, $lesson_id, 'lesson' );
			foreach ( array_merge( array( $course_id ), $course->get_lessons( 'ids' ), $course->get_sections( 'ids' ) ) as $post_id ) {
				$this->backdate_user_postmeta( $student, $post_id, $backdate );
			}
		}

		// Simulate WP user deletion, which leaves the LifterLMS user postmeta rows behind.
		$wpdb->delete( $wpdb->users, array( 'ID' => $deleted ) );
		clean_user_cache( $deleted );

		$engagement = $this->create_scan_engagement( 'course_inactivity', $course_id, 14 );
		$result     = $this->scanner->query_course_inactivity( get_post( $engagement->ID ), 0, 500 );
		$user_ids   = wp_list_pluck( $result['candidates'], 'user_id' );

		$this->assertContains( $kept, $user_ids );
		$this->assertNotContains( $deleted, $user_ids );
	}

	/**
	 * Test scan-fired engagements run the handler's processing checks.
	 *
	 * Unlike live event triggers, scan candidates come from database rows, so the
	 * user may no longer exist by (or at) scan time. No email may ever be sent to
	 * a nonexistent user (which would produce an empty recipient).
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_maybe_fire_deleted_user_sends_no_email() {

		$course_id = $this->factory->course->create(
			array(
				'sections' => 1,
				'lessons'  => 1,
				'quizzes'  => 0,
			)
		);

		$post       = $this->create_scan_engagement( 'course_inactivity', $course_id, 14 );
		$engagement = (object) array(
			'trigger_id'    => $post->ID,
			'engagement_id' => get_post_meta( $post->ID, '_llms_engagement', true ),
			'trigger_event' => 'course_inactivity',
			'event_type'    => 'email',
			'delay'         => 0,
		);

		reset_phpmailer_instance();

		// A user which does not exist: the marker is recorded but the handler must refuse to send.
		$this->scanner->maybe_fire(
			$engagement,
			array(
				'user_id'         => 987654321,
				'related_post_id' => $course_id,
				'anchor'          => '2026-01-01 00:00:00',
			)
		);
		$this->assertEmpty( tests_retrieve_phpmailer_instance()->mock_sent );

		// Control: an existing enrolled student does receive the email.
		$student = $this->factory->student->create();
		llms_enroll_student( $student, $course_id );
		$this->scanner->maybe_fire(
			$engagement,
			array(
				'user_id'         => $student,
				'related_post_id' => $course_id,
				'anchor'          => '2026-01-01 00:00:00',
			)
		);
		$this->assertNotEmpty( tests_retrieve_phpmailer_instance()->mock_sent );
	}

	/**
	 * Test enrollment-check errors are removed only for triggers targeting unenrolled students.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_remove_enrollment_check_errors() {

		$engagements = llms()->engagements();

		$exempt     = $this->create_mock_engagement( 'course_enrollment_cancelled', 'email' );
		$non_exempt = $this->create_mock_engagement( 'course_completed', 'email' );

		$enrollment_error = new WP_Error( 'llms-engagement-check-post--enrollment', 'Not enrolled.' );
		$other_error      = new WP_Error( 'llms-engagement-check-user--not-found', 'User not found.' );

		// Exempt trigger: the enrollment error alone resolves to a pass.
		$this->assertTrue( $engagements->remove_enrollment_check_errors( array( $enrollment_error ), 1, 2, 3, $exempt->ID ) );

		// Exempt trigger: unrelated errors are kept.
		$this->assertEquals(
			array( $other_error ),
			$engagements->remove_enrollment_check_errors( array( $enrollment_error, $other_error ), 1, 2, 3, $exempt->ID )
		);

		// Non-exempt trigger: untouched.
		$this->assertEquals(
			array( $enrollment_error ),
			$engagements->remove_enrollment_check_errors( array( $enrollment_error ), 1, 2, 3, $non_exempt->ID )
		);

		// Passing results and missing engagement IDs are untouched.
		$this->assertTrue( $engagements->remove_enrollment_check_errors( true, 1, 2, 3, $exempt->ID ) );
		$this->assertEquals(
			array( $enrollment_error ),
			$engagements->remove_enrollment_check_errors( array( $enrollment_error ), 1, 2, 3, null )
		);
	}

	/**
	 * Test that re-armed fires bypass the sent-email dupcheck while other emails don't.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_maybe_bypass_email_dupcheck() {

		$scan_engagement  = $this->create_scan_engagement( 'course_inactivity', $this->factory->post->create( array( 'post_type' => 'course' ) ), 14 );
		$event_engagement = $this->create_mock_engagement( 'course_completed', 'email' );

		// Scan-based trigger: duplicate emails are allowed (the re-arm marker is the dupcheck).
		$this->assertFalse( $this->scanner->maybe_bypass_email_dupcheck( true, 1, 2, 3, $scan_engagement->ID ) );

		// Event-based triggers keep the normal dupcheck.
		$this->assertTrue( $this->scanner->maybe_bypass_email_dupcheck( true, 1, 2, 3, $event_engagement->ID ) );

		// Unknown engagement: unchanged.
		$this->assertTrue( $this->scanner->maybe_bypass_email_dupcheck( true, 1, 2, 3, null ) );

		// Non-duplicates are never modified.
		$this->assertFalse( $this->scanner->maybe_bypass_email_dupcheck( false, 1, 2, 3, $scan_engagement->ID ) );
	}

	/**
	 * Test do_batch() schedules a follow-up batch when a full page is returned.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_do_batch_pagination() {

		$course_id = $this->factory->course->create(
			array(
				'sections' => 1,
				'lessons'  => 1,
				'quizzes'  => 0,
			)
		);

		$students = $this->factory->student->create_many( 3 );
		foreach ( $students as $student ) {
			llms_enroll_student( $student, $course_id );
		}

		$engagement = $this->create_scan_engagement( 'course_never_started', $course_id, 14 );

		$batch_size = function () {
			return 2;
		};
		add_filter( 'llms_engagements_scan_batch_size', $batch_size );

		as_unschedule_all_actions( LLMS_Engagements_Scanner::BATCH_HOOK );
		$this->scanner->do_batch( $engagement->ID, 0 );

		// A full page (2 of 3 enrollments) was scanned: the next page is scheduled,
		// cursored on the second student's enrollment `_status` row.
		global $wpdb;
		$expected_cursor = absint(
			$wpdb->get_var(
				$wpdb->prepare(
					"SELECT MAX( meta_id ) FROM {$wpdb->prefix}lifterlms_user_postmeta WHERE user_id = %d AND post_id = %d AND meta_key = '_status'",
					$students[1],
					$course_id
				)
			)
		);

		$this->assertTrue(
			as_has_scheduled_action(
				LLMS_Engagements_Scanner::BATCH_HOOK,
				array( $engagement->ID, $expected_cursor ),
				LLMS_Engagements_Scanner::AS_GROUP
			)
		);

		remove_filter( 'llms_engagements_scan_batch_size', $batch_size );
	}

	/**
	 * Test do_scan() skips engagements whose previous scan chain is still pending.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_do_scan_skips_active_chains() {

		$course_id  = $this->factory->post->create( array( 'post_type' => 'course' ) );
		$engagement = $this->create_scan_engagement( 'course_never_started', $course_id, 14 );

		as_unschedule_all_actions( LLMS_Engagements_Scanner::BATCH_HOOK );

		// A previous scan is still mid-chain (pending page with a non-zero cursor).
		as_enqueue_async_action( LLMS_Engagements_Scanner::BATCH_HOOK, array( $engagement->ID, 500 ), LLMS_Engagements_Scanner::AS_GROUP );

		$this->scanner->do_scan();
		$this->assertFalse(
			as_has_scheduled_action(
				LLMS_Engagements_Scanner::BATCH_HOOK,
				array( $engagement->ID, 0 ),
				LLMS_Engagements_Scanner::AS_GROUP
			),
			'A new chain must not start while the previous chain is unfinished.'
		);

		// Once the previous chain has drained, the next daily scan starts a new one.
		as_unschedule_all_actions( LLMS_Engagements_Scanner::BATCH_HOOK );
		$this->scanner->do_scan();
		$this->assertTrue(
			as_has_scheduled_action(
				LLMS_Engagements_Scanner::BATCH_HOOK,
				array( $engagement->ID, 0 ),
				LLMS_Engagements_Scanner::AS_GROUP
			)
		);
	}

	/**
	 * Test "any course" scan engagements produce one candidate per idle enrollment with per-course re-arm markers.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_query_course_inactivity_any_course() {

		$course_args = array(
			'sections' => 1,
			'lessons'  => 2,
			'quizzes'  => 0,
		);
		$course_a    = $this->factory->course->create( $course_args );
		$course_b    = $this->factory->course->create( $course_args );

		$student = $this->factory->student->create();

		$backdate = gmdate( 'Y-m-d H:i:s', llms_current_time( 'timestamp' ) - ( 30 * DAY_IN_SECONDS ) );

		foreach ( array( $course_a, $course_b ) as $course_id ) {
			$course = llms_get_post( $course_id );
			llms_enroll_student( $student, $course_id );
			llms_mark_complete( $student, $course->get_lessons( 'ids' )[0], 'lesson' );
			foreach ( array_merge( array( $course_id ), $course->get_lessons( 'ids' ), $course->get_sections( 'ids' ) ) as $post_id ) {
				$this->backdate_user_postmeta( $student, $post_id, $backdate );
			}
		}

		$post = $this->create_scan_engagement( 'course_inactivity', 0, 14 );
		update_post_meta( $post->ID, '_llms_engagement_trigger_post', 'any' );

		$result     = $this->scanner->query_course_inactivity( get_post( $post->ID ), 0, 500 );
		$candidates = array();
		foreach ( $result['candidates'] as $candidate ) {
			if ( $student === $candidate['user_id'] ) {
				$candidates[ $candidate['related_post_id'] ] = $candidate;
			}
		}

		// One candidate per idle enrollment, each related to its own course.
		$this->assertArrayHasKey( $course_a, $candidates );
		$this->assertArrayHasKey( $course_b, $candidates );

		$engagement = (object) array(
			'trigger_id'    => $post->ID,
			'engagement_id' => get_post_meta( $post->ID, '_llms_engagement', true ),
			'trigger_event' => 'course_inactivity',
			'event_type'    => 'email',
			'delay'         => 0,
		);

		// Both courses fire independently: no "first idle course wins".
		$this->assertTrue( $this->scanner->maybe_fire( $engagement, $candidates[ $course_a ] ) );
		$this->assertTrue( $this->scanner->maybe_fire( $engagement, $candidates[ $course_b ] ) );

		// Markers hold per course on the next scan.
		$this->assertFalse( $this->scanner->maybe_fire( $engagement, $candidates[ $course_a ] ) );
		$this->assertFalse( $this->scanner->maybe_fire( $engagement, $candidates[ $course_b ] ) );

		// New activity in one course re-arms only that course.
		$candidates[ $course_a ]['anchor'] = gmdate( 'Y-m-d H:i:s', llms_current_time( 'timestamp' ) - ( 20 * DAY_IN_SECONDS ) );
		$this->assertTrue( $this->scanner->maybe_fire( $engagement, $candidates[ $course_a ] ) );
		$this->assertFalse( $this->scanner->maybe_fire( $engagement, $candidates[ $course_b ] ) );
	}

	/**
	 * Test the course_progress threshold trigger fires once when crossed.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_course_progress_trigger() {

		$course_id = $this->factory->course->create(
			array(
				'sections' => 1,
				'lessons'  => 2,
				'quizzes'  => 0,
			)
		);
		$course    = llms_get_post( $course_id );
		$lessons   = $course->get_lessons( 'ids' );

		$engagement = $this->create_mock_engagement( 'course_progress', 'email', 0, $course_id );
		update_post_meta( $engagement->ID, '_llms_engagement_trigger_percentage', 50 );

		$student = $this->factory->student->create();
		llms_enroll_student( $student, $course_id );

		$actions = did_action( 'lifterlms_engagement_send_email' );

		// Completing lesson 1 of 2 crosses the 50% threshold.
		llms_mark_complete( $student, $lessons[0], 'lesson' );
		$this->assertEquals( $actions + 1, did_action( 'lifterlms_engagement_send_email' ) );

		// Completing the second lesson does not re-fire.
		llms_mark_complete( $student, $lessons[1], 'lesson' );
		$this->assertEquals( $actions + 1, did_action( 'lifterlms_engagement_send_email' ) );
	}

	/**
	 * Test an "Any course" course_progress engagement fires independently for each course.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_course_progress_trigger_any_course() {

		$course_args = array(
			'sections' => 1,
			'lessons'  => 2,
			'quizzes'  => 0,
		);
		$course_a    = $this->factory->course->create( $course_args );
		$course_b    = $this->factory->course->create( $course_args );

		$engagement = $this->create_mock_engagement( 'course_progress', 'email', 0, 0 );
		update_post_meta( $engagement->ID, '_llms_engagement_trigger_post', 'any' );
		update_post_meta( $engagement->ID, '_llms_engagement_trigger_percentage', 50 );

		$student = $this->factory->student->create();
		llms_enroll_student( $student, $course_a );
		llms_enroll_student( $student, $course_b );

		$actions = did_action( 'lifterlms_engagement_send_email' );

		// Crossing the threshold in course A fires.
		llms_mark_complete( $student, llms_get_post( $course_a )->get_lessons( 'ids' )[0], 'lesson' );
		$this->assertEquals( $actions + 1, did_action( 'lifterlms_engagement_send_email' ) );

		// The marker for course A must not block course B.
		llms_mark_complete( $student, llms_get_post( $course_b )->get_lessons( 'ids' )[0], 'lesson' );
		$this->assertEquals( $actions + 2, did_action( 'lifterlms_engagement_send_email' ) );

		// But each course still only fires once.
		llms_mark_complete( $student, llms_get_post( $course_b )->get_lessons( 'ids' )[1], 'lesson' );
		$this->assertEquals( $actions + 2, did_action( 'lifterlms_engagement_send_email' ) );
	}

	/**
	 * Test an "Any quiz" quiz_failed_multiple engagement tracks each quiz independently.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_quiz_failed_multiple_trigger_any_quiz() {

		global $wpdb;

		$quiz_a  = $this->factory->post->create( array( 'post_type' => 'llms_quiz' ) );
		$quiz_b  = $this->factory->post->create( array( 'post_type' => 'llms_quiz' ) );
		$student = $this->factory->student->create();

		$engagement = $this->create_mock_engagement( 'quiz_failed_multiple', 'email', 0, 0 );
		update_post_meta( $engagement->ID, '_llms_engagement_trigger_post', 'any' );
		update_post_meta( $engagement->ID, '_llms_engagement_trigger_count', 1 );

		$thresholds = llms()->engagements()->thresholds;

		$add_fail = function ( $quiz_id ) use ( $wpdb, $student ) {
			$wpdb->insert(
				"{$wpdb->prefix}lifterlms_quiz_attempts",
				array(
					'student_id'  => $student,
					'quiz_id'     => $quiz_id,
					'status'      => 'fail',
					'update_date' => llms_current_time( 'mysql' ),
				)
			);
		};

		$actions = did_action( 'lifterlms_engagement_send_email' );

		$add_fail( $quiz_a );
		$thresholds->maybe_trigger_quiz_failed_multiple( $student, $quiz_a );
		$this->assertEquals( $actions + 1, did_action( 'lifterlms_engagement_send_email' ) );

		// Quiz A's marker must not block quiz B.
		$add_fail( $quiz_b );
		$thresholds->maybe_trigger_quiz_failed_multiple( $student, $quiz_b );
		$this->assertEquals( $actions + 2, did_action( 'lifterlms_engagement_send_email' ) );

		// Passing quiz A clears only quiz A's marker: quiz B stays fired.
		$thresholds->clear_quiz_failed_markers( $student, $quiz_a );
		$add_fail( $quiz_b );
		$thresholds->maybe_trigger_quiz_failed_multiple( $student, $quiz_b );
		$this->assertEquals( $actions + 2, did_action( 'lifterlms_engagement_send_email' ) );

		// And quiz A can fire again after the clear.
		$add_fail( $quiz_a );
		$thresholds->maybe_trigger_quiz_failed_multiple( $student, $quiz_a );
		$this->assertEquals( $actions + 3, did_action( 'lifterlms_engagement_send_email' ) );
	}

	/**
	 * Test the quiz_failed_multiple threshold count logic and marker reset on pass.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_quiz_failed_multiple_trigger() {

		global $wpdb;

		$quiz_id    = $this->factory->post->create( array( 'post_type' => 'llms_quiz' ) );
		$student    = $this->factory->student->create();
		$engagement = $this->create_mock_engagement( 'quiz_failed_multiple', 'email', 0, $quiz_id );
		update_post_meta( $engagement->ID, '_llms_engagement_trigger_count', 2 );

		$thresholds = llms()->engagements()->thresholds;

		$add_fail = function () use ( $wpdb, $student, $quiz_id ) {
			$wpdb->insert(
				"{$wpdb->prefix}lifterlms_quiz_attempts",
				array(
					'student_id'  => $student,
					'quiz_id'     => $quiz_id,
					'status'      => 'fail',
					'update_date' => llms_current_time( 'mysql' ),
				)
			);
		};

		$actions = did_action( 'lifterlms_engagement_send_email' );

		// One failure: below the threshold of 2.
		$add_fail();
		$thresholds->maybe_trigger_quiz_failed_multiple( $student, $quiz_id );
		$this->assertEquals( $actions, did_action( 'lifterlms_engagement_send_email' ) );

		// Second failure reaches the threshold.
		$add_fail();
		$thresholds->maybe_trigger_quiz_failed_multiple( $student, $quiz_id );
		$this->assertEquals( $actions + 1, did_action( 'lifterlms_engagement_send_email' ) );

		// A third failure does not re-fire (marker).
		$add_fail();
		$thresholds->maybe_trigger_quiz_failed_multiple( $student, $quiz_id );
		$this->assertEquals( $actions + 1, did_action( 'lifterlms_engagement_send_email' ) );

		// Passing clears the marker: two more failures fire again.
		$thresholds->clear_quiz_failed_markers( $student, $quiz_id );
		$add_fail();
		$thresholds->maybe_trigger_quiz_failed_multiple( $student, $quiz_id );
		$this->assertEquals( $actions + 2, did_action( 'lifterlms_engagement_send_email' ) );
	}

	/**
	 * Test the {related_post_title} merge code in engagement emails.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_related_post_title_merge_code() {

		$course_id = $this->factory->post->create(
			array(
				'post_type'  => 'course',
				'post_title' => 'Photography 101',
			)
		);
		$student   = $this->factory->student->create();
		$email_id  = $this->factory->post->create(
			array(
				'post_type'    => 'llms_email',
				'post_content' => 'You have not made progress in {related_post_title}.',
				'meta_input'   => array(
					'_llms_email_subject' => 'Keep going in {related_post_title}',
				),
			)
		);

		$email = llms()->mailer()->get_email(
			'engagement',
			array(
				'person_id'  => $student,
				'email_id'   => $email_id,
				'related_id' => $course_id,
			)
		);

		$this->assertEquals( 'Keep going in Photography 101', $email->get_subject() );

		// No related post: the merge code outputs an empty string.
		$email = llms()->mailer()->get_email(
			'engagement',
			array(
				'person_id'  => $student,
				'email_id'   => $email_id,
				'related_id' => '',
			)
		);

		$this->assertEquals( 'Keep going in', trim( $email->get_subject() ) );
	}

	/**
	 * Test the "Activity on or after" floor on the days_since_login candidate query.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_query_days_since_login_since_floor() {

		$engagement = $this->create_scan_engagement( 'days_since_login', 0, 14 );
		update_post_meta( $engagement->ID, '_llms_engagement_trigger_post', 'any' );

		$course_id = $this->factory->course->create(
			array(
				'sections' => 1,
				'lessons'  => 1,
				'quizzes'  => 0,
			)
		);

		$recent_idle  = $this->factory->student->create();
		$ancient_idle = $this->factory->student->create();
		llms_enroll_student( $recent_idle, $course_id );
		llms_enroll_student( $ancient_idle, $course_id );

		$now = llms_current_time( 'timestamp' );
		update_user_meta( $recent_idle, 'llms_last_login', gmdate( 'Y-m-d H:i:s', $now - ( 20 * DAY_IN_SECONDS ) ) );
		update_user_meta( $ancient_idle, 'llms_last_login', gmdate( 'Y-m-d H:i:s', $now - ( 100 * DAY_IN_SECONDS ) ) );

		// Blank floor: the whole backlog matches, including students idle for months.
		$result   = $this->scanner->query_days_since_login( get_post( $engagement->ID ), 0, 500 );
		$user_ids = wp_list_pluck( $result['candidates'], 'user_id' );
		$this->assertContains( $recent_idle, $user_ids );
		$this->assertContains( $ancient_idle, $user_ids );

		// Floor set between the two logins: only activity on or after the floor matches.
		update_post_meta( $engagement->ID, '_llms_engagement_trigger_since', gmdate( 'Y-m-d', $now - ( 60 * DAY_IN_SECONDS ) ) );
		$result   = $this->scanner->query_days_since_login( get_post( $engagement->ID ), 0, 500 );
		$user_ids = wp_list_pluck( $result['candidates'], 'user_id' );
		$this->assertContains( $recent_idle, $user_ids );
		$this->assertNotContains( $ancient_idle, $user_ids );

		// Floor before both logins, both still older than the period: both match.
		update_post_meta( $engagement->ID, '_llms_engagement_trigger_since', gmdate( 'Y-m-d', $now - ( 200 * DAY_IN_SECONDS ) ) );
		$result   = $this->scanner->query_days_since_login( get_post( $engagement->ID ), 0, 500 );
		$user_ids = wp_list_pluck( $result['candidates'], 'user_id' );
		$this->assertContains( $recent_idle, $user_ids );
		$this->assertContains( $ancient_idle, $user_ids );
	}

	/**
	 * Test the "Activity on or after" floor on the course_inactivity candidate query.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_query_course_inactivity_since_floor() {

		$course_id = $this->factory->course->create(
			array(
				'sections' => 1,
				'lessons'  => 2,
				'quizzes'  => 0,
			)
		);
		$course    = llms_get_post( $course_id );
		$lesson_id = $course->get_lessons( 'ids' )[0];
		$tree      = array_merge( array( $course_id ), $course->get_lessons( 'ids' ), $course->get_sections( 'ids' ) );

		$now          = llms_current_time( 'timestamp' );
		$recent_idle  = $this->factory->student->create();
		$ancient_idle = $this->factory->student->create();

		foreach ( array( $recent_idle => 30, $ancient_idle => 100 ) as $student => $days ) {
			llms_enroll_student( $student, $course_id );
			llms_mark_complete( $student, $lesson_id, 'lesson' );
			foreach ( $tree as $post_id ) {
				$this->backdate_user_postmeta( $student, $post_id, gmdate( 'Y-m-d H:i:s', $now - ( $days * DAY_IN_SECONDS ) ) );
			}
		}

		$engagement = $this->create_scan_engagement( 'course_inactivity', $course_id, 14 );

		// Blank floor: both stalled students match.
		$result   = $this->scanner->query_course_inactivity( get_post( $engagement->ID ), 0, 500 );
		$user_ids = wp_list_pluck( $result['candidates'], 'user_id' );
		$this->assertContains( $recent_idle, $user_ids );
		$this->assertContains( $ancient_idle, $user_ids );

		// Floor between the two activity dates: the long-idle student is excluded.
		update_post_meta( $engagement->ID, '_llms_engagement_trigger_since', gmdate( 'Y-m-d', $now - ( 60 * DAY_IN_SECONDS ) ) );
		$result   = $this->scanner->query_course_inactivity( get_post( $engagement->ID ), 0, 500 );
		$user_ids = wp_list_pluck( $result['candidates'], 'user_id' );
		$this->assertContains( $recent_idle, $user_ids );
		$this->assertNotContains( $ancient_idle, $user_ids );
	}

	/**
	 * Test the "Activity on or after" floor on the quiz_attempt_abandoned candidate query.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_query_quiz_attempt_abandoned_since_floor() {

		global $wpdb;

		$quiz_id    = $this->factory->post->create( array( 'post_type' => 'llms_quiz' ) );
		$student    = $this->factory->student->create();
		$engagement = $this->create_scan_engagement( 'quiz_attempt_abandoned', $quiz_id, 7 );

		$now    = llms_current_time( 'timestamp' );
		$insert = function ( $date ) use ( $wpdb, $student, $quiz_id ) {
			$wpdb->insert(
				"{$wpdb->prefix}lifterlms_quiz_attempts",
				array(
					'student_id'  => $student,
					'quiz_id'     => $quiz_id,
					'status'      => 'incomplete',
					'update_date' => $date,
				)
			);
			return $wpdb->insert_id;
		};

		$recent_attempt  = $insert( gmdate( 'Y-m-d H:i:s', $now - ( 10 * DAY_IN_SECONDS ) ) );
		$ancient_attempt = $insert( gmdate( 'Y-m-d H:i:s', $now - ( 100 * DAY_IN_SECONDS ) ) );

		// Blank floor: both abandoned attempts match.
		$result  = $this->scanner->query_quiz_attempt_abandoned( get_post( $engagement->ID ), 0, 500 );
		$anchors = wp_list_pluck( $result['candidates'], 'anchor' );
		$this->assertContains( $recent_attempt, $anchors );
		$this->assertContains( $ancient_attempt, $anchors );

		// Floor between the two attempts: the long-abandoned attempt is excluded.
		update_post_meta( $engagement->ID, '_llms_engagement_trigger_since', gmdate( 'Y-m-d', $now - ( 60 * DAY_IN_SECONDS ) ) );
		$result  = $this->scanner->query_quiz_attempt_abandoned( get_post( $engagement->ID ), 0, 500 );
		$anchors = wp_list_pluck( $result['candidates'], 'anchor' );
		$this->assertContains( $recent_attempt, $anchors );
		$this->assertNotContains( $ancient_attempt, $anchors );
	}

	/**
	 * Test unscoped days_since_login candidates require an enrolled, non-completed course.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_query_days_since_login_requires_incomplete_course() {

		$engagement = $this->create_scan_engagement( 'days_since_login', 0, 14 );
		update_post_meta( $engagement->ID, '_llms_engagement_trigger_post', 'any' );

		$course_args = array(
			'sections' => 1,
			'lessons'  => 1,
			'quizzes'  => 0,
		);

		$membership_id = $this->factory->post->create( array( 'post_type' => 'llms_membership' ) );
		$old_login     = gmdate( 'Y-m-d H:i:s', llms_current_time( 'timestamp' ) - ( 20 * DAY_IN_SECONDS ) );

		// Membership-only: nothing to finish, never a candidate.
		$membership_only = $this->factory->student->create();
		llms_enroll_student( $membership_only, $membership_id );

		// Only course is completed: nothing left to do, never a candidate.
		$completed_only     = $this->factory->student->create();
		$completed_course   = $this->factory->course->create( $course_args );
		llms_enroll_student( $completed_only, $completed_course );
		llms_mark_complete( $completed_only, llms_get_post( $completed_course )->get_lessons( 'ids' )[0], 'lesson' );

		// One completed and one in-progress course: still a candidate.
		$mixed        = $this->factory->student->create();
		$mixed_done   = $this->factory->course->create( $course_args );
		$mixed_active = $this->factory->course->create( $course_args );
		llms_enroll_student( $mixed, $mixed_done );
		llms_enroll_student( $mixed, $mixed_active );
		llms_mark_complete( $mixed, llms_get_post( $mixed_done )->get_lessons( 'ids' )[0], 'lesson' );

		foreach ( array( $membership_only, $completed_only, $mixed ) as $student ) {
			update_user_meta( $student, 'llms_last_login', $old_login );
		}

		$result   = $this->scanner->query_days_since_login( get_post( $engagement->ID ), 0, 500 );
		$user_ids = wp_list_pluck( $result['candidates'], 'user_id' );

		$this->assertNotContains( $membership_only, $user_ids );
		$this->assertNotContains( $completed_only, $user_ids );
		$this->assertContains( $mixed, $user_ids );
	}

	/**
	 * Test scoped days_since_login engagements also exclude students with nothing left to complete.
	 *
	 * Course scope excludes completers of that course; membership scope requires a
	 * non-completed course enrollment elsewhere on the site.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_query_days_since_login_scoped_excludes_finished_students() {

		$course_args = array(
			'sections' => 1,
			'lessons'  => 1,
			'quizzes'  => 0,
		);
		$old_login   = gmdate( 'Y-m-d H:i:s', llms_current_time( 'timestamp' ) - ( 20 * DAY_IN_SECONDS ) );

		// Scoped to a course: completers of that course are excluded.
		$course_id = $this->factory->course->create( $course_args );
		$completer = $this->factory->student->create();
		$stalled   = $this->factory->student->create();
		llms_enroll_student( $completer, $course_id );
		llms_enroll_student( $stalled, $course_id );
		llms_mark_complete( $completer, llms_get_post( $course_id )->get_lessons( 'ids' )[0], 'lesson' );
		update_user_meta( $completer, 'llms_last_login', $old_login );
		update_user_meta( $stalled, 'llms_last_login', $old_login );

		$engagement = $this->create_scan_engagement( 'days_since_login', $course_id, 14 );
		$result     = $this->scanner->query_days_since_login( get_post( $engagement->ID ), 0, 500 );
		$user_ids   = wp_list_pluck( $result['candidates'], 'user_id' );

		$this->assertContains( $stalled, $user_ids );
		$this->assertNotContains( $completer, $user_ids );

		// Scoped to a membership: members must have a non-completed course somewhere.
		$membership_id = $this->factory->post->create( array( 'post_type' => 'llms_membership' ) );
		$member_only   = $this->factory->student->create();
		$member_active = $this->factory->student->create();
		llms_enroll_student( $member_only, $membership_id );
		llms_enroll_student( $member_active, $membership_id );
		llms_enroll_student( $member_active, $this->factory->course->create( $course_args ) );
		update_user_meta( $member_only, 'llms_last_login', $old_login );
		update_user_meta( $member_active, 'llms_last_login', $old_login );

		$engagement = $this->create_scan_engagement( 'days_since_login', $membership_id, 14 );
		$result     = $this->scanner->query_days_since_login( get_post( $engagement->ID ), 0, 500 );
		$user_ids   = wp_list_pluck( $result['candidates'], 'user_id' );

		$this->assertContains( $member_active, $user_ids );
		$this->assertNotContains( $member_only, $user_ids );
	}

	/**
	 * Test count_pending() counts un-fired candidates, respects re-arm markers, and caps early.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_count_pending() {

		$course_id = $this->factory->course->create(
			array(
				'sections' => 1,
				'lessons'  => 1,
				'quizzes'  => 0,
			)
		);

		$backdate = gmdate( 'Y-m-d H:i:s', llms_current_time( 'timestamp' ) - ( 30 * DAY_IN_SECONDS ) );
		$students = $this->factory->student->create_many( 3 );
		foreach ( $students as $student ) {
			llms_enroll_student( $student, $course_id );
			$this->backdate_user_postmeta( $student, $course_id, $backdate );
		}

		$post = $this->create_scan_engagement( 'course_never_started', $course_id, 14 );

		// Exact count under the limit.
		$this->assertSame( 3, $this->scanner->count_pending( $post, 200 ) );

		// Counting stops just past the limit: a return greater than the limit means "more than".
		$this->assertSame( 3, $this->scanner->count_pending( $post, 2 ) );

		// A student who already fired (marker holds) is not counted.
		$engagement = (object) array(
			'trigger_id'    => $post->ID,
			'engagement_id' => get_post_meta( $post->ID, '_llms_engagement', true ),
			'trigger_event' => 'course_never_started',
			'event_type'    => 'email',
			'delay'         => 0,
		);
		$this->assertTrue(
			$this->scanner->maybe_fire(
				$engagement,
				array(
					'user_id'         => $students[0],
					'related_post_id' => $course_id,
					'anchor'          => $backdate,
				)
			)
		);
		$this->assertSame( 2, $this->scanner->count_pending( $post, 200 ) );

		// The page cap bounds the dry run: with one-row pages and a single page, only one row is read.
		$batch_size = function () {
			return 1;
		};
		add_filter( 'llms_engagements_scan_batch_size', $batch_size );
		$this->assertLessThanOrEqual( 1, $this->scanner->count_pending( $post, 200, 1 ) );
		remove_filter( 'llms_engagements_scan_batch_size', $batch_size );

		// Non-scannable or invalid engagements count zero.
		$event_engagement = $this->create_mock_engagement( 'course_completed', 'email' );
		$this->assertSame( 0, $this->scanner->count_pending( $event_engagement->ID ) );
		$this->assertSame( 0, $this->scanner->count_pending( 0 ) );
	}

	/**
	 * Test the save-time send-volume gate: pause flag, red notice, and confirmation lifting the pause.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_maybe_warn_send_volume() {

		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		$transient_key = sprintf( 'llms_engagement_send_warning_%d', get_current_user_id() );

		$course_id = $this->factory->course->create(
			array(
				'sections' => 1,
				'lessons'  => 1,
				'quizzes'  => 0,
			)
		);

		$backdate = gmdate( 'Y-m-d H:i:s', llms_current_time( 'timestamp' ) - ( 30 * DAY_IN_SECONDS ) );
		foreach ( $this->factory->student->create_many( 3 ) as $student ) {
			llms_enroll_student( $student, $course_id );
			$this->backdate_user_postmeta( $student, $course_id, $backdate );
		}

		$post = $this->create_scan_engagement( 'course_never_started', $course_id, 14 );

		// Under the default threshold of 200: no warning, no pause.
		$this->scanner->maybe_warn_send_volume( $post->ID, get_post( $post->ID ) );
		$this->assertFalse( get_transient( $transient_key ) );
		$this->assertFalse( $this->scanner->is_paused( $post->ID ) );

		// Lower the threshold below the candidate count: the engagement is paused and the notice stored.
		$threshold = function () {
			return 2;
		};
		add_filter( 'llms_engagement_send_warning_threshold', $threshold );
		$this->scanner->maybe_warn_send_volume( $post->ID, get_post( $post->ID ) );

		$this->assertTrue( $this->scanner->is_paused( $post->ID ) );
		$data = get_transient( $transient_key );
		$this->assertIsArray( $data );
		$this->assertEquals( $post->ID, $data['post_id'] );
		$this->assertEquals( 2, $data['threshold'] );

		ob_start();
		$this->scanner->output_send_volume_notice();
		$notice = ob_get_clean();

		$this->assertStringContainsString( 'notice-error', $notice );
		$this->assertStringContainsString( 'more than 2 students', $notice );
		$this->assertStringContainsString( 'Allow sending above the volume limit', $notice );
		$this->assertStringContainsString( 'sending has been disabled', $notice );

		// The save notice is shown once. The standing pause notice remains while the engagement is paused.
		$this->assertFalse( get_transient( $transient_key ) );
		ob_start();
		$this->scanner->output_send_volume_notice();
		$standing = ob_get_clean();
		$this->assertStringNotContainsString( 'more than 2 students', $standing );
		$this->assertStringContainsString( 'Sending is disabled for these engagements', $standing );

		// Saving with the confirmation checked lifts the pause, even over the threshold, and keeps the confirmation.
		update_post_meta( $post->ID, LLMS_Engagements_Scanner::CONFIRMED_META, 'yes' );
		$this->scanner->maybe_warn_send_volume( $post->ID, get_post( $post->ID ) );
		$this->assertFalse( $this->scanner->is_paused( $post->ID ) );
		$this->assertEquals( 'yes', get_post_meta( $post->ID, LLMS_Engagements_Scanner::CONFIRMED_META, true ) );

		// Lifting a pause is never silent: a green re-enabled notice is queued.
		$data = get_transient( $transient_key );
		$this->assertIsArray( $data );
		$this->assertEquals( 'enabled', $data['status'] );

		ob_start();
		$this->scanner->output_send_volume_notice();
		$notice = ob_get_clean();
		$this->assertStringContainsString( 'notice-success', $notice );
		$this->assertStringContainsString( 'has been re-enabled', $notice );
		remove_filter( 'llms_engagement_send_warning_threshold', $threshold );

		// Back at or below the threshold (default threshold): a stale confirmation is removed and the engagement stays enabled.
		$this->scanner->maybe_warn_send_volume( $post->ID, get_post( $post->ID ) );
		$this->assertFalse( $this->scanner->is_paused( $post->ID ) );
		$this->assertEmpty( get_post_meta( $post->ID, LLMS_Engagements_Scanner::CONFIRMED_META, true ) );
		// Not previously paused, so no notice this time.
		$this->assertFalse( get_transient( $transient_key ) );

		// A prediction back at or below the threshold also lifts the pause (unconfirmed) with a re-enabled notice.
		update_post_meta( $post->ID, LLMS_Engagements_Scanner::PAUSED_META, 'yes' );
		$this->scanner->maybe_warn_send_volume( $post->ID, get_post( $post->ID ) );
		$this->assertFalse( $this->scanner->is_paused( $post->ID ) );
		$data = get_transient( $transient_key );
		$this->assertIsArray( $data );
		$this->assertEquals( 'enabled', $data['status'] );
		delete_transient( $transient_key );

		// Unpublished engagements never warn or pause.
		$draft = $this->create_scan_engagement( 'course_never_started', $course_id, 14 );
		wp_update_post(
			array(
				'ID'          => $draft->ID,
				'post_status' => 'draft',
			)
		);
		// Creating/updating the post above runs the save hook itself; clear any leftovers first.
		delete_transient( $transient_key );
		delete_post_meta( $draft->ID, LLMS_Engagements_Scanner::PAUSED_META );
		add_filter( 'llms_engagement_send_warning_threshold', $threshold );
		$this->scanner->maybe_warn_send_volume( $draft->ID, get_post( $draft->ID ) );
		$this->assertFalse( get_transient( $transient_key ) );
		$this->assertFalse( $this->scanner->is_paused( $draft->ID ) );
		remove_filter( 'llms_engagement_send_warning_threshold', $threshold );
	}

	/**
	 * Test the send-volume gate through the full save pipeline (metabox save + scanner gate) across resaves.
	 *
	 * Regression test: resaving an over-threshold engagement must not silently lift the pause,
	 * and a confirmation must be cleared automatically once the predicted volume drops back
	 * under the threshold.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_send_volume_gate_full_save_pipeline() {

		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		$transient_key = sprintf( 'llms_engagement_send_warning_%d', get_current_user_id() );

		$course_id = $this->factory->course->create(
			array(
				'sections' => 1,
				'lessons'  => 1,
				'quizzes'  => 0,
			)
		);

		$backdate = gmdate( 'Y-m-d H:i:s', llms_current_time( 'timestamp' ) - ( 30 * DAY_IN_SECONDS ) );
		foreach ( $this->factory->student->create_many( 3 ) as $student ) {
			llms_enroll_student( $student, $course_id );
			$this->backdate_user_postmeta( $student, $course_id, $backdate );
		}

		$post = $this->create_scan_engagement( 'course_never_started', $course_id, 14 );

		// Register the admin metabox save (priority 10) alongside the scanner gate (priority 30),
		// as in a real admin save request. Hooks are restored automatically after the test.
		$metabox = new LLMS_Meta_Box_Engagement();

		$threshold = function () {
			return 2;
		};
		add_filter( 'llms_engagement_send_warning_threshold', $threshold );

		$postdata = array(
			'lifterlms_meta_nonce'              => wp_create_nonce( 'lifterlms_save_data' ),
			'_llms_trigger_type'                => 'course_never_started',
			'_faux_engagement_trigger_post_course' => (string) $course_id,
			'_llms_engagement_trigger_period'   => '14',
			'_llms_engagement_trigger_since'    => '2020-01-01',
			'_llms_engagement_type'             => 'email',
			'_llms_engagement'                  => get_post_meta( $post->ID, '_llms_engagement', true ),
		);

		// Save 1: over the threshold, unconfirmed -> paused with a red notice.
		$this->mockPostRequest( $postdata );
		wp_update_post( array( 'ID' => $post->ID ) );

		$this->assertTrue( $this->scanner->is_paused( $post->ID ) );
		$data = get_transient( $transient_key );
		$this->assertEquals( 'paused', $data['status'] );
		delete_transient( $transient_key );

		// Save 2 (identical resave): the pause and its notice must persist, not silently disappear.
		$this->reset_metabox_saved_guard( $metabox );
		$this->mockPostRequest( $postdata );
		wp_update_post( array( 'ID' => $post->ID ) );

		$this->assertTrue( $this->scanner->is_paused( $post->ID ) );
		$data = get_transient( $transient_key );
		$this->assertEquals( 'paused', $data['status'] );
		delete_transient( $transient_key );

		// Save 3: confirmation checked -> unpaused (green notice), confirmation kept while over threshold.
		$this->reset_metabox_saved_guard( $metabox );
		$postdata['_llms_engagement_send_volume_confirmed'] = 'yes';
		$this->mockPostRequest( $postdata );
		wp_update_post( array( 'ID' => $post->ID ) );

		$this->assertFalse( $this->scanner->is_paused( $post->ID ) );
		$this->assertEquals( 'yes', get_post_meta( $post->ID, LLMS_Engagements_Scanner::CONFIRMED_META, true ) );
		$data = get_transient( $transient_key );
		$this->assertEquals( 'enabled', $data['status'] );
		delete_transient( $transient_key );

		// Save 4: a recent since date drops the prediction under the threshold -> the stale confirmation is cleared.
		$this->reset_metabox_saved_guard( $metabox );
		$postdata['_llms_engagement_trigger_since'] = gmdate( 'Y-m-d', llms_current_time( 'timestamp' ) - DAY_IN_SECONDS );
		$this->mockPostRequest( $postdata );
		wp_update_post( array( 'ID' => $post->ID ) );

		$this->assertFalse( $this->scanner->is_paused( $post->ID ) );
		$this->assertEmpty( get_post_meta( $post->ID, LLMS_Engagements_Scanner::CONFIRMED_META, true ) );
		$this->assertFalse( get_transient( $transient_key ) );

		remove_filter( 'llms_engagement_send_warning_threshold', $threshold );
		$this->mockPostRequest( array() );
	}

	/**
	 * Set an engagement's author without running the save-time send-volume gate.
	 *
	 * @since [version]
	 *
	 * @param int $post_id WP_Post ID of the `llms_engagement` post.
	 * @param int $user_id WP_User ID.
	 * @return void
	 */
	private function set_engagement_author( $post_id, $user_id ) {

		global $wpdb;
		$wpdb->update(
			$wpdb->posts,
			array( 'post_author' => $user_id ),
			array( 'ID' => $post_id )
		);
		clean_post_cache( $post_id );
	}

	/**
	 * Email addresses recorded on the latest mocked `wp_mail()` call.
	 *
	 * @since [version]
	 *
	 * @param string $field Mock mailer property, `to` or `cc`.
	 * @return string[]
	 */
	private function sent_mail_addresses( $field ) {

		$mail      = tests_retrieve_phpmailer_instance()->get_sent();
		$addresses = array();
		foreach ( (array) ( $mail->$field ?? array() ) as $row ) {
			$addresses[] = strtolower( is_array( $row ) ? (string) ( $row[0] ?? '' ) : (string) $row );
		}

		return $addresses;
	}

	/**
	 * Test the daily scan pauses an unconfirmed over-threshold engagement before queueing a batch.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_do_scan_pauses_unconfirmed_over_threshold() {

		$course_id = $this->factory->course->create(
			array(
				'sections' => 1,
				'lessons'  => 1,
				'quizzes'  => 0,
			)
		);

		$backdate = gmdate( 'Y-m-d H:i:s', llms_current_time( 'timestamp' ) - ( 30 * DAY_IN_SECONDS ) );
		foreach ( $this->factory->student->create_many( 3 ) as $student ) {
			llms_enroll_student( $student, $course_id );
			$this->backdate_user_postmeta( $student, $course_id, $backdate );
		}

		$post = $this->create_scan_engagement( 'course_never_started', $course_id, 14 );
		$this->set_engagement_author(
			$post->ID,
			$this->factory->user->create(
				array(
					'role'       => 'administrator',
					'user_email' => 'author@example.com',
				)
			)
		);

		update_option( 'admin_email', 'site-admin@example.com' );

		$threshold = function () {
			return 2;
		};
		add_filter( 'llms_engagement_send_warning_threshold', $threshold );

		as_unschedule_all_actions( LLMS_Engagements_Scanner::BATCH_HOOK );
		reset_phpmailer_instance();
		$this->scanner->do_scan();

		$this->assertTrue( $this->scanner->is_paused( $post->ID ) );
		$this->assertFalse(
			as_has_scheduled_action(
				LLMS_Engagements_Scanner::BATCH_HOOK,
				array( $post->ID, 0 ),
				LLMS_Engagements_Scanner::AS_GROUP
			)
		);

		$mailer = tests_retrieve_phpmailer_instance();
		$this->assertCount( 1, $mailer->mock_sent );
		$this->assertContains( 'site-admin@example.com', $this->sent_mail_addresses( 'to' ) );
		$this->assertContains( 'author@example.com', $this->sent_mail_addresses( 'cc' ) );
		$this->assertStringContainsString( 'Allow sending above the volume limit', $mailer->get_sent()->body );

		// Still paused: the next daily scan does not email again.
		reset_phpmailer_instance();
		$this->scanner->do_scan();
		$this->assertEmpty( tests_retrieve_phpmailer_instance()->mock_sent );

		remove_filter( 'llms_engagement_send_warning_threshold', $threshold );
	}

	/**
	 * Test a confirmed over-threshold engagement still scans, and an under-threshold one is not paused.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_do_scan_confirmed_and_under_threshold_still_enqueue() {

		$course_id = $this->factory->course->create(
			array(
				'sections' => 1,
				'lessons'  => 1,
				'quizzes'  => 0,
			)
		);

		$backdate = gmdate( 'Y-m-d H:i:s', llms_current_time( 'timestamp' ) - ( 30 * DAY_IN_SECONDS ) );
		foreach ( $this->factory->student->create_many( 3 ) as $student ) {
			llms_enroll_student( $student, $course_id );
			$this->backdate_user_postmeta( $student, $course_id, $backdate );
		}

		$post = $this->create_scan_engagement( 'course_never_started', $course_id, 14 );
		update_post_meta( $post->ID, LLMS_Engagements_Scanner::CONFIRMED_META, 'yes' );

		$threshold = function () {
			return 2;
		};
		add_filter( 'llms_engagement_send_warning_threshold', $threshold );

		as_unschedule_all_actions( LLMS_Engagements_Scanner::BATCH_HOOK );
		reset_phpmailer_instance();
		$this->scanner->do_scan();

		$this->assertFalse( $this->scanner->is_paused( $post->ID ) );
		$this->assertTrue(
			as_has_scheduled_action(
				LLMS_Engagements_Scanner::BATCH_HOOK,
				array( $post->ID, 0 ),
				LLMS_Engagements_Scanner::AS_GROUP
			)
		);
		$this->assertEmpty( tests_retrieve_phpmailer_instance()->mock_sent );
		remove_filter( 'llms_engagement_send_warning_threshold', $threshold );

		// Default threshold of 200 is above the three candidates: enqueue, no pause, no email.
		as_unschedule_all_actions( LLMS_Engagements_Scanner::BATCH_HOOK );
		delete_post_meta( $post->ID, LLMS_Engagements_Scanner::CONFIRMED_META );
		reset_phpmailer_instance();
		$this->scanner->do_scan();

		$this->assertFalse( $this->scanner->is_paused( $post->ID ) );
		$this->assertTrue(
			as_has_scheduled_action(
				LLMS_Engagements_Scanner::BATCH_HOOK,
				array( $post->ID, 0 ),
				LLMS_Engagements_Scanner::AS_GROUP
			)
		);
		$this->assertEmpty( tests_retrieve_phpmailer_instance()->mock_sent );
	}

	/**
	 * Test the pause email omits the author CC when they are ineligible.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_do_scan_pause_email_omits_ineligible_author() {

		$course_id = $this->factory->course->create(
			array(
				'sections' => 1,
				'lessons'  => 1,
				'quizzes'  => 0,
			)
		);

		$backdate = gmdate( 'Y-m-d H:i:s', llms_current_time( 'timestamp' ) - ( 30 * DAY_IN_SECONDS ) );
		foreach ( $this->factory->student->create_many( 3 ) as $student ) {
			llms_enroll_student( $student, $course_id );
			$this->backdate_user_postmeta( $student, $course_id, $backdate );
		}

		$post = $this->create_scan_engagement( 'course_never_started', $course_id, 14 );
		update_option( 'admin_email', 'site-admin@example.com' );

		$threshold = function () {
			return 2;
		};
		add_filter( 'llms_engagement_send_warning_threshold', $threshold );

		$rescan = function () use ( $post ) {
			delete_post_meta( $post->ID, LLMS_Engagements_Scanner::PAUSED_META );
			as_unschedule_all_actions( LLMS_Engagements_Scanner::BATCH_HOOK );
			reset_phpmailer_instance();
			$this->scanner->do_scan();
			$this->assertContains( 'site-admin@example.com', $this->sent_mail_addresses( 'to' ) );
		};

		// Author is the site admin: no CC.
		$admin_author = $this->factory->user->create(
			array(
				'role'       => 'administrator',
				'user_email' => 'site-admin@example.com',
			)
		);
		$this->set_engagement_author( $post->ID, $admin_author );
		$rescan();
		$this->assertEmpty( $this->sent_mail_addresses( 'cc' ) );

		// Author lacks manage_lifterlms.
		$this->set_engagement_author( $post->ID, $this->factory->student->create() );
		$rescan();
		$this->assertEmpty( $this->sent_mail_addresses( 'cc' ) );

		// Author no longer exists.
		$this->set_engagement_author( $post->ID, 0 );
		$rescan();
		$this->assertEmpty( $this->sent_mail_addresses( 'cc' ) );

		// Author has no email address.
		$blank = $this->factory->user->create(
			array(
				'role'       => 'administrator',
				'user_email' => 'blank-author@example.com',
			)
		);
		global $wpdb;
		$wpdb->update( $wpdb->users, array( 'user_email' => '' ), array( 'ID' => $blank ) );
		clean_user_cache( $blank );
		$this->set_engagement_author( $post->ID, $blank );
		$rescan();
		$this->assertEmpty( $this->sent_mail_addresses( 'cc' ) );

		remove_filter( 'llms_engagement_send_warning_threshold', $threshold );
	}

	/**
	 * Test the standing pause notice lists paused engagements for engagement managers only.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_output_paused_engagements_notice() {

		$course_id = $this->factory->post->create( array( 'post_type' => 'course' ) );
		$post      = $this->create_scan_engagement( 'course_never_started', $course_id, 14 );
		wp_update_post(
			array(
				'ID'         => $post->ID,
				'post_title' => 'Idle students notice',
			)
		);

		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );

		ob_start();
		$this->scanner->output_send_volume_notice();
		$this->assertSame( '', ob_get_clean() );

		update_post_meta( $post->ID, LLMS_Engagements_Scanner::PAUSED_META, 'yes' );

		ob_start();
		$this->scanner->output_send_volume_notice();
		$notice = ob_get_clean();

		$this->assertStringContainsString( 'notice-error', $notice );
		$this->assertStringContainsString( 'Idle students notice', $notice );
		$this->assertStringContainsString( get_edit_post_link( $post->ID ), $notice );
		$this->assertStringContainsString( 'Allow sending above the volume limit', $notice );

		wp_set_current_user( $this->factory->user->create( array( 'role' => 'subscriber' ) ) );
		ob_start();
		$this->scanner->output_send_volume_notice();
		$this->assertSame( '', ob_get_clean() );
	}

	/**
	 * Test paused engagements are skipped by the daily scan and never send.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_paused_engagements_do_not_scan_or_send() {

		$course_id = $this->factory->course->create(
			array(
				'sections' => 1,
				'lessons'  => 1,
				'quizzes'  => 0,
			)
		);

		$backdate = gmdate( 'Y-m-d H:i:s', llms_current_time( 'timestamp' ) - ( 30 * DAY_IN_SECONDS ) );
		foreach ( $this->factory->student->create_many( 2 ) as $student ) {
			llms_enroll_student( $student, $course_id );
			$this->backdate_user_postmeta( $student, $course_id, $backdate );
		}

		$post = $this->create_scan_engagement( 'course_never_started', $course_id, 14 );
		update_post_meta( $post->ID, LLMS_Engagements_Scanner::PAUSED_META, 'yes' );

		// The daily scan never enqueues a batch for a paused engagement.
		as_unschedule_all_actions( LLMS_Engagements_Scanner::BATCH_HOOK );
		$this->scanner->do_scan();
		$this->assertFalse(
			as_has_scheduled_action(
				LLMS_Engagements_Scanner::BATCH_HOOK,
				array( $post->ID, 0 ),
				LLMS_Engagements_Scanner::AS_GROUP
			)
		);

		// An already-queued batch bails without sending.
		$actions = did_action( 'lifterlms_engagement_send_email' );
		$this->scanner->do_batch( $post->ID, 0 );
		$this->assertEquals( $actions, did_action( 'lifterlms_engagement_send_email' ) );

		// Lifting the pause restores sending.
		delete_post_meta( $post->ID, LLMS_Engagements_Scanner::PAUSED_META );
		$this->scanner->do_batch( $post->ID, 0 );
		$this->assertEquals( $actions + 2, did_action( 'lifterlms_engagement_send_email' ) );
	}

	/**
	 * Test the paused post state in the engagements list table.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_add_paused_post_state() {

		$course_id = $this->factory->post->create( array( 'post_type' => 'course' ) );
		$post      = $this->create_scan_engagement( 'course_never_started', $course_id, 14 );

		// Not paused: no state added.
		$this->assertEmpty( $this->scanner->add_paused_post_state( array(), get_post( $post->ID ) ) );

		update_post_meta( $post->ID, LLMS_Engagements_Scanner::PAUSED_META, 'yes' );
		$states = $this->scanner->add_paused_post_state( array(), get_post( $post->ID ) );
		$this->assertArrayHasKey( 'llms_engagement_scan_paused', $states );
		$this->assertStringContainsString( 'Sending disabled', $states['llms_engagement_scan_paused'] );

		// Other post types are never touched, even with the meta present.
		$page = $this->factory->post->create();
		update_post_meta( $page, LLMS_Engagements_Scanner::PAUSED_META, 'yes' );
		$this->assertEmpty( $this->scanner->add_paused_post_state( array(), get_post( $page ) ) );
	}

	/**
	 * Test enrollment cancellation fires the course_enrollment_cancelled trigger.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_enrollment_cancelled_trigger() {

		$course_id  = $this->factory->course->create(
			array(
				'sections' => 1,
				'lessons'  => 1,
				'quizzes'  => 0,
			)
		);
		$engagement = $this->create_mock_engagement( 'course_enrollment_cancelled', 'email', 0, $course_id );

		$student = $this->factory->student->create();
		llms_enroll_student( $student, $course_id );

		$actions = did_action( 'lifterlms_engagement_send_email' );

		llms_unenroll_student( $student, $course_id, 'cancelled', 'any' );

		$this->assertEquals( $actions + 1, did_action( 'lifterlms_engagement_send_email' ) );
	}

	/**
	 * Test order failure fires the order_failed trigger with the order's product as the related post.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_order_failed_trigger() {

		$order      = $this->get_mock_order();
		$product_id = $order->get( 'product_id' );

		$engagement = $this->create_mock_engagement( 'order_failed', 'email', 0, $product_id );

		$actions = did_action( 'lifterlms_engagement_send_email' );

		$order->set( 'status', 'llms-failed' );

		$this->assertEquals( $actions + 1, did_action( 'lifterlms_engagement_send_email' ) );
	}
}
