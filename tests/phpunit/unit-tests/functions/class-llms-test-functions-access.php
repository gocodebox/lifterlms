<?php
/**
 * Tests for LifterLMS Access Functions.
 *
 * @group functions
 * @group functions_access
 *
 * @since 3.7.3
 * @since 3.16.0 Unknown.
 * @since 3.37.10 Added tests on sitewide membership restriction.
 * @since [version] Added new tests.
 */
class LLMS_Test_Functions_Access extends LLMS_UnitTestCase {

	/**
	 * Temporary globals reset via tearDown()
	 *
	 * @var array
	 */
	protected $temp = array();

	/**
	 * Setup the test case.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function setUp() {

		parent::setUp();
		$this->uid = $this->factory->student->create();

		wp_set_current_user( $this->uid );

	}

	/**
	 * Tear down the test case.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function tearDown() {

		parent::tearDown();
		wp_set_current_user( null );
		$this->reset_query();

	}

	/**
	 * Get a formatted date for setting time period related restrictions.
	 *
	 * @since 3.7.3
	 *
	 * @param string $offset Adjust day via strtotime.
	 * @param string $format Desired returned format, passed to date().
	 * @return string
	 */
	private function get_date( $offset = '+7 days', $format = 'm/d/y' ) {
		return date( $format, strtotime( $offset, current_time( 'timestamp' ) ) );
	}

	/**
	 * Mock the `$wp
	 *
	 * @since [version]
	 *
	 * @see [Reference]
	 * @link [URL]
	 *
	 * @param [type] $post_id [description]
	 * @return [type] [description]
	 */
	private function mock_query( $post_id ) {

		global $post, $wp_query;
		$this->temp = compact( 'post', 'wp_query' );

		$wp_query = new WP_Query( array( 'p' => $post_id ) );

	}

	/**
	 * Reset mocked query data configured by $this->mock_query()
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	private function reset_query() {

		global $post, $wp_query;
		extract( $this->temp );

	}

	/**
	 * Test llms_page_restricted() against is_home() with sitewide membership enabled
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_llms_page_restricted_sitewide_membership_is_home() {

		global $wp_query;
		$temp = $wp_query->is_home;
		$wp_query->is_home = true;

		$membership = $this->factory->membership->create();

		update_option( 'lifterlms_membership_required', $membership );

		// Restricted.
		$res = llms_page_restricted( 123 );
		$this->assertTrue( $res['is_restricted'] );
		$this->assertEquals( 123, $res['content_id'] );
		$this->assertEquals( $membership, $res['restriction_id'] );
		$this->assertEquals( 'sitewide_membership', $res['reason'] );

		// Not restricted.
		llms_enroll_student( $this->uid, $membership );
		$res = llms_page_restricted( 123 );
		$this->assertFalse( $res['is_restricted'] );
		$this->assertEquals( 123, $res['content_id'] );
		$this->assertEquals( 0, $res['restriction_id'] );
		$this->assertEquals( 'accessible', $res['reason'] );

		delete_option( 'lifterlms_membership_required' );
		$wp_query->is_home = $temp;

	}

	/**
	 * Test llms_page_restricted() against is_search() with sitewide membership enabled
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_llms_page_restricted_is_search() {

		global $wp_query;
		$temp = $wp_query->is_search;
		$wp_query->is_search = true;

		// Search okay.
		$res = llms_page_restricted( 123 );
		$this->assertFalse( $res['is_restricted'] );
		$this->assertEquals( 123, $res['content_id'] );
		$this->assertEquals( 0, $res['restriction_id'] );
		$this->assertEquals( 'accessible', $res['reason'] );

		$membership = $this->factory->membership->create();

		update_option( 'lifterlms_membership_required', $membership );

		// Todo: fix this, see https://github.com/gocodebox/lifterlms/issues/1262
		return;

		// Restricted.
		$res = llms_page_restricted( 123 );
		$this->assertTrue( $res['is_restricted'] );
		$this->assertEquals( 123, $res['content_id'] );
		$this->assertEquals( $membership, $res['restriction_id'] );
		$this->assertEquals( 'sitewide_membership', $res['reason'] );

		// Not restricted.
		llms_enroll_student( $this->uid, $membership );
		$res = llms_page_restricted( 123 );
		$this->assertFalse( $res['is_restricted'] );
		$this->assertEquals( 123, $res['content_id'] );
		$this->assertEquals( 0, $res['restriction_id'] );
		$this->assertEquals( 'accessible', $res['reason'] );

		delete_option( 'lifterlms_membership_required' );
		$wp_query->is_search = $temp;

	}

	/**
	 * Test llms_page_restricted() against a single post when a sitewide membership is enabled
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_llms_page_restricted_sitewide_membership() {

		$post = $this->factory->post->create();
		$this->mock_query( $post );

		$membership = $this->factory->membership->create();
		update_option( 'lifterlms_membership_required', $membership );

		// Restricted.
		$res = llms_page_restricted( $post );
		$this->assertTrue( $res['is_restricted'] );
		$this->assertEquals( $post, $res['content_id'] );
		$this->assertEquals( $membership, $res['restriction_id'] );
		$this->assertEquals( 'sitewide_membership', $res['reason'] );

		// Not restricted.
		llms_enroll_student( $this->uid, $membership );
		$res = llms_page_restricted( $post );
		$this->assertFalse( $res['is_restricted'] );
		$this->assertEquals( $post, $res['content_id'] );
		$this->assertEquals( 0, $res['restriction_id'] );
		$this->assertEquals( 'accessible', $res['reason'] );

		delete_option( 'lifterlms_membership_required' );

	}

	/**
	 * Test llms_get_post_membership_restrictions() against skipped post types.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_llms_get_post_membership_restrictions_skipped_types() {

		$post_types = array(
			'course',
			'lesson',
			'llms_quiz',
			'llms_membership',
			'llms_question',
			'llms_certificate',
			'llms_my_certificate',
		);

		foreach ( $post_types as $post_type ) {

			$post = $this->factory->post->create( compact( 'post_type' ) );
			$this->assertEquals( array(), llms_get_post_membership_restrictions( $post ) );

		}

	}

	/**
	 * Test llms_get_post_membership_restrictions()
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_llms_get_post_membership_restrictions() {

		$memberships = $this->factory->post->create_many( 2 );
		$post = $this->factory->post->create();

		// No restrictions.
		$this->assertEquals( array(), llms_get_post_membership_restrictions( $post ) );

		update_post_meta( $post, '_llms_is_restricted', 'no' );
		update_post_meta( $post, '_llms_restricted_levels', $memberships );

		// Has memberships but restrictions not enabled.
		$this->assertEquals( array(), llms_get_post_membership_restrictions( $post ) );

		update_post_meta( $post, '_llms_is_restricted', 'yes' );
		$this->assertEquals( $memberships, llms_get_post_membership_restrictions( $post ) );

	}

	/**
	 * Test drip restrictions.
	 *
	 * @since 3.16.0
	 *
	 * @return void
	 */
	public function test_llms_is_post_restricted_by_drip_settings() {

		$course_id = $this->generate_mock_courses( 1, 1, 2, 0 )[0];
		$course = llms_get_post( $course_id );
		$lesson = $course->get_lessons()[0];
		$lesson_id = $lesson->get( 'id' );
		$student = $this->get_mock_student();
		wp_set_current_user( $student->get_id() );
		$student->enroll( $course_id );

		// no drip settings, lesson is currently available.
		$this->assertFalse( llms_is_post_restricted_by_drip_settings( $lesson_id ) );

		// date in past so the lesson is available.
		$lesson = llms_get_post( $lesson_id );
		$lesson->set( 'drip_method', 'date' );
		$lesson->set( 'date_available', '12/12/2012' );
		$lesson->set( 'time_available', '12:12 AM' );
		$this->assertFalse( llms_is_post_restricted_by_drip_settings( $lesson_id ) );

		// date in future so lesson not available.
		$lesson->set( 'date_available', date( 'm/d/Y', current_time( 'timestamp' ) + DAY_IN_SECONDS ) );
		$this->assertEquals( $lesson_id, llms_is_post_restricted_by_drip_settings( $lesson_id ) );

		// available 3 days after enrollment.
		$lesson->set( 'drip_method', 'enrollment' );
		$lesson->set( 'days_before_available', '3' );
		$this->assertEquals( $lesson_id, llms_is_post_restricted_by_drip_settings( $lesson_id ) );

		// now available.
		llms_mock_current_time( '+4 days' );
		$this->assertFalse( llms_is_post_restricted_by_drip_settings( $lesson_id ) );

		llms_reset_current_time();
		$lesson->set( 'drip_method', 'start' );
		$course->set( 'start_date', date( 'm/d/Y', current_time( 'timestamp' ) + DAY_IN_SECONDS ) );

		// not available until 3 days after course start date.
		$this->assertEquals( $lesson_id, llms_is_post_restricted_by_drip_settings( $lesson_id ) );

		// now available.
		llms_mock_current_time( '+4 days' );
		$this->assertFalse( llms_is_post_restricted_by_drip_settings( $lesson_id ) );

	}

	/**
	 * Test restricted by membership.
	 *
	 * @since Unknown.
	 *
	 * @return void
	 */
	public function test_llms_is_post_restricted_by_membership() {

		$memberships = $this->factory->post->create_many( 2, array(
			'post_type' => 'llms_membership',
		) );
		$post_id = $this->factory->post->create();
		$student = $this->get_mock_student();
		$uid = $student->get_id();

		$this->assertFalse( llms_is_post_restricted_by_membership( $post_id ) );
		$this->assertFalse( llms_is_post_restricted_by_membership( $post_id, $uid ) );

		update_post_meta( $post_id, '_llms_restricted_levels', $memberships );
		update_post_meta( $post_id, '_llms_is_restricted', 'yes' );

		$this->assertEquals( $memberships[0], llms_is_post_restricted_by_membership( $post_id ) );
		$this->assertEquals( $memberships[0], llms_is_post_restricted_by_membership( $post_id, $uid ) );

		$student->enroll( $memberships[1] );
		$this->assertEquals( $memberships[1], llms_is_post_restricted_by_membership( $post_id, $uid ) );

	}

	/**
	 * Test restriction by membership sitewide.
	 *
	 * @since Unknown.
	 *
	 * @return void
	 */
	public function test_llms_is_post_restricted_by_sitewide_membership() {

		$memberships = $this->factory->post->create_many( 2, array(
			'post_type' => 'llms_membership',
		) );
		$post_id     = $this->factory->post->create();

		// create a page where redirect to.
		$redirect_id = $this->factory->post->create( array(
			'post_type' => 'page'
		) );

		/**
		 * Create pages that must be always accessible.
		 */

		// create and set privacy policy page.
		update_option( 'wp_page_for_privacy_policy', $this->factory->post->create( array(
			'post_type' => 'page'
		) ) );

		// create and set terms page.
		update_option( 'lifterlms_terms_page_id', $this->factory->post->create( array(
			'post_type' => 'page'
		) ) );

		// create and set memberships catalog page.
		update_option( 'lifterlms_memberships_page_id', $this->factory->post->create( array(
			'post_type' => 'page'
		) ) );

		// create and set myaccount page.
		update_option( 'lifterlms_myaccount_page_id', $this->factory->post->create( array(
			'post_type' => 'page'
		) ) );

		// create and set checkout page.
		update_option( 'lifterlms_checkout_page_id', $this->factory->post->create( array(
			'post_type' => 'page'
		) ) );

		// require membership sitewide.
		update_option( 'lifterlms_membership_required', $memberships[1] );

		// set membership's restriction redirection.
		update_post_meta( $memberships[1], '_llms_redirect_page_id', $redirect_id );
		update_post_meta( $memberships[1], '_llms_restriction_redirect_type', 'page' );

		// I expect a post or another membership to be restricted.
		// While I expect the redirection page and the membership set as requirement to be accessible.
		$this->assertEquals( $memberships[1], llms_is_post_restricted_by_sitewide_membership( $post_id ) );
		$this->assertEquals( $memberships[1], llms_is_post_restricted_by_sitewide_membership( $memberships[0] ) );
		$this->assertFalse( llms_is_post_restricted_by_sitewide_membership( $memberships[1] ) );
		$this->assertFalse( llms_is_post_restricted_by_sitewide_membership( $redirect_id ) );

		$this->assertFalse( llms_is_post_restricted_by_sitewide_membership( absint( get_option( 'lifterlms_terms_page_id' ) ) ) );
		$this->assertFalse( llms_is_post_restricted_by_sitewide_membership( llms_get_page_id( 'memberships' ) ) );
		$this->assertFalse( llms_is_post_restricted_by_sitewide_membership( llms_get_page_id( 'myaccount' ) ) );
		$this->assertFalse( llms_is_post_restricted_by_sitewide_membership( llms_get_page_id( 'checkout' ) ) );
		$this->assertFalse( llms_is_post_restricted_by_sitewide_membership( absint( get_option( 'wp_page_for_privacy_policy' ) ) ) );

		// unset the redirection page.
		// I expect a post, the former redirection page or another membership to be restricted.
		// While I expect membership set as requirement to be accessible.
		update_post_meta( $memberships[1], '_llms_redirect_page_id', '' );
		$this->assertEquals( $memberships[1], llms_is_post_restricted_by_sitewide_membership( $post_id ) );
		$this->assertEquals( $memberships[1], llms_is_post_restricted_by_sitewide_membership( $memberships[0] ) );
		$this->assertEquals( $memberships[1], llms_is_post_restricted_by_sitewide_membership( $redirect_id ) );
		$this->assertFalse( llms_is_post_restricted_by_sitewide_membership( $memberships[1] ) );

		$this->assertFalse( llms_is_post_restricted_by_sitewide_membership( absint( get_option( 'lifterlms_terms_page_id' ) ) ) );
		$this->assertFalse( llms_is_post_restricted_by_sitewide_membership( llms_get_page_id( 'memberships' ) ) );
		$this->assertFalse( llms_is_post_restricted_by_sitewide_membership( llms_get_page_id( 'myaccount' ) ) );
		$this->assertFalse( llms_is_post_restricted_by_sitewide_membership( llms_get_page_id( 'checkout' ) ) );
		$this->assertFalse( llms_is_post_restricted_by_sitewide_membership( absint( get_option( 'wp_page_for_privacy_policy' ) ) ) );

		// re-set the redirection page, but set the restriction redirect type as 'custom'.
		// I expect a post, the former redirection page or another membership to be restricted.
		// While I expect membership set as requirement to be accessible.
		update_post_meta( $memberships[1], '_llms_redirect_page_id', $redirect_id );
		update_post_meta( $memberships[1], '_llms_restriction_redirect_type', 'custom' );
		$this->assertEquals( $memberships[1], llms_is_post_restricted_by_sitewide_membership( $post_id ) );
		$this->assertEquals( $memberships[1], llms_is_post_restricted_by_sitewide_membership( $memberships[0] ) );
		$this->assertEquals( $memberships[1], llms_is_post_restricted_by_sitewide_membership( $redirect_id ) );
		$this->assertFalse( llms_is_post_restricted_by_sitewide_membership( $memberships[1] ) );

		$this->assertFalse( llms_is_post_restricted_by_sitewide_membership( absint( get_option( 'lifterlms_terms_page_id' ) ) ) );
		$this->assertFalse( llms_is_post_restricted_by_sitewide_membership( llms_get_page_id( 'memberships' ) ) );
		$this->assertFalse( llms_is_post_restricted_by_sitewide_membership( llms_get_page_id( 'myaccount' ) ) );
		$this->assertFalse( llms_is_post_restricted_by_sitewide_membership( llms_get_page_id( 'checkout' ) ) );
		$this->assertFalse( llms_is_post_restricted_by_sitewide_membership( absint( get_option( 'wp_page_for_privacy_policy' ) ) ) );

		// unset the membership enrollment requirement.
		// I expect 'everything' to be not restricted.
		update_option( 'lifterlms_membership_required', '' );
		$this->assertFalse( llms_is_post_restricted_by_sitewide_membership( $post_id ) );
		$this->assertFalse( llms_is_post_restricted_by_sitewide_membership( $memberships[0] ) );
		$this->assertFalse( llms_is_post_restricted_by_sitewide_membership( $memberships[1] ) );
		$this->assertFalse( llms_is_post_restricted_by_sitewide_membership( $redirect_id ) );
		$this->assertFalse( llms_is_post_restricted_by_sitewide_membership( absint( get_option( 'lifterlms_terms_page_id' ) ) ) );
		$this->assertFalse( llms_is_post_restricted_by_sitewide_membership( llms_get_page_id( 'memberships' ) ) );
		$this->assertFalse( llms_is_post_restricted_by_sitewide_membership( llms_get_page_id( 'myaccount' ) ) );
		$this->assertFalse( llms_is_post_restricted_by_sitewide_membership( llms_get_page_id( 'checkout' ) ) );
		$this->assertFalse( llms_is_post_restricted_by_sitewide_membership( absint( get_option( 'wp_page_for_privacy_policy' ) ) ) );

	}

	/**
	 * Test llms_is_post_restricted_by_sitewide_membership() for invalid post types.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_llms_is_post_restricted_by_sitewide_membership_invalid_posts() {

		$post_id = $this->factory->post->create( array( 'post_type' => 'course' ) );

		// Not a membership.
		$this->assertFalse( llms_is_post_restricted_by_sitewide_membership( $post_id ) );

		// Non-existent.
		$this->assertFalse( llms_is_post_restricted_by_sitewide_membership( ++$post_id ) );

	}

	/**
	 * Test the llms_is_post_restricted_by_prerequisite() function.
	 *
	 * @since 3.8.0
	 *
	 * @return void
	 */
	public function test_llms_is_post_restricted_by_prerequisite() {

		$courses = $this->generate_mock_courses( 3, 2, 1, 1 );

		$prereq_course_id = $courses[0];

		$course_id = $courses[1];
		$course = llms_get_post( $course_id );

		$track = wp_insert_term( 'mock track', 'course_track' );
		$track_id = $track['term_id'];
		$course_in_track_id = $courses[2];
		wp_set_post_terms( $course_in_track_id, $track_id, 'course_track' );

		$lessons = $course->get_lessons( 'ids' );

		$lesson_2 = llms_get_post( $lessons[1] );
		$lesson_2->set( 'has_prerequisite', 'yes' );
		$lesson_2->set( 'prerequisite', $lessons[0] );

		$test_ids = array_merge( $lessons, $course->get_quizzes() );

		$this->prereq_tests( $test_ids, $course, $prereq_course_id, $track_id );

		$student_id = $this->factory->user->create( array( 'role' => 'student' ) );

		// results should all be the same with the student b/c nothing completed.
		$this->prereq_tests( $test_ids, $course, $prereq_course_id, $track_id, $student_id );

		// results differ once student completes courses.
		$this->complete_courses_for_student( $student_id, $courses );

		$this->prereq_tests( $test_ids, $course, $prereq_course_id, $track_id, $student_id );

	}

	/**
	 * test_llms_is_post_restricted_by_prerequisite() runs this series of assertions several times.
	 *
	 * @since 3.7.3
	 * @since 3.12.0 Unknown.
	 *
	 * @param array $test_ids         Array of post ids to test the llms_is_post_restricted_by_prerequisite() against.
	 * @param obj   $course           Course object.
	 * @param int   $prereq_course_id Post id of the prereq course.
	 * @param int   $track_id         Term id of the prereq track.
	 * @param int   $user_id          Wp user id of a student.
	 * @return void
	 */
	private function prereq_tests( $test_ids = array(), $course, $prereq_course_id, $track_id, $user_id = null ) {

		$student = $user_id ? new LLMS_Student( $user_id ) : null;

		foreach ( $test_ids as $test_id ) {

			$course->set( 'has_prerequisite', 'no' );
			$course->set( 'prerequisite', '' );
			$course->set( 'prerequisite_track', '' );

			$post = llms_get_post( $test_id );

			if ( 'lesson' === get_post_type( $test_id ) && $post->has_prerequisite() ) {

				$lesson_prereq_id = $post->get( 'prerequisite' );
				$lesson_res = $student && $student->is_complete( $lesson_prereq_id, 'lesson' ) ? false : array(
					'type' => 'lesson',
					'id' => $lesson_prereq_id,
				);
				$this->assertEquals( $lesson_res, llms_is_post_restricted_by_prerequisite( $test_id, $user_id ) );

			}

			// set a course prereq.
			$course->set( 'has_prerequisite', 'yes' );
			$course->set( 'prerequisite', $prereq_course_id );
			$prereq_course_res = $student && $student->is_complete( $prereq_course_id, 'course' ) ? false : array(
				'type' => 'course',
				'id' => $prereq_course_id,
			);
			$this->assertEquals( $prereq_course_res, llms_is_post_restricted_by_prerequisite( $test_id, $user_id ) );

			// set a track prereq.
			$course->set( 'prerequisite_track', $track_id );

			// checks course prereq first and only returns one.
			$this->assertEquals( $prereq_course_res, llms_is_post_restricted_by_prerequisite( $test_id, $user_id ) );

			// no course prereq, returns track id.
			$course->set( 'prerequisite', '' );
			$prereq_track_res = $student && $student->is_complete( $track_id, 'course_track' ) ? false : array(
				'type' => 'course_track',
				'id' => $track_id,
			);
			$this->assertEquals( $prereq_track_res, llms_is_post_restricted_by_prerequisite( $test_id, $user_id ) );

		}

	}

	/**
	 * Test the llms_is_post_restricted_by_time_period() function.
	 *
	 * @since 3.7.3
	 *
	 * @return void
	 */
	public function test_llms_is_post_restricted_by_time_period() {

		$courses = $this->generate_mock_courses( 1, 1, 1, 1 );
		$course_id = $courses[0];
		$course = llms_get_post( $course_id );

		$test_ids = array_merge( array( $course_id ), $course->get_lessons( 'ids' ), $course->get_quizzes() );

		foreach ( $test_ids as $test_post_id ) {

			$course->set( 'time_period', 'no' );

			// no time period.
			$this->assertFalse( llms_is_post_restricted_by_time_period( $test_post_id ) );

			// enable the restriction.
			$course->set( 'time_period', 'yes' );

			// no dates set the course is closed without dates.
			$this->assertEquals( $course_id, llms_is_post_restricted_by_time_period( $test_post_id ) );

			// start date in the future.
			$course->set( 'start_date', $this->get_date( '+7 days' ) );
			$this->assertEquals( $course_id, llms_is_post_restricted_by_time_period( $test_post_id ) );

			// start date in past.
			$course->set( 'start_date', $this->get_date( '-7 days' ) );
			$this->assertFalse( llms_is_post_restricted_by_time_period( $test_post_id ) );

			// start date in past and end date in past.
			$course->set( 'end_date', $this->get_date( '-5 days' ) );
			$this->assertEquals( $course_id, llms_is_post_restricted_by_time_period( $test_post_id ) );

			// no start date, end date in past.
			$course->set( 'start_date', '' );
			$this->assertEquals( $course_id, llms_is_post_restricted_by_time_period( $test_post_id ) );

			// no start date end in future.
			$course->set( 'end_date', $this->get_date( '+7 days' ) );
			$this->assertEquals( $course_id, llms_is_post_restricted_by_time_period( $test_post_id ) );

		}

	}

}
