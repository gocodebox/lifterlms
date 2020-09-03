<?php
/**
 * Tests for LLMS_Engagements class
 *
 * @package LifterLMS/Tests
 *
 * @group engagements
 *
 * @since [version]
 */
class LLMS_Test_Engagements extends LLMS_Unit_Test_Case {

	/**
	 * Setup test case
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$this->main = llms()->engagements();
		reset_phpmailer_instance();
	}

	/**
	 * Teardown test case
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function tearDown() {

		parent::tearDown();
		reset_phpmailer_instance();

	}

	/**
	 * Test handle_email() as triggered by a related post type that's enrollable.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_handle_email_with_course_posts() {

		$mailer = tests_retrieve_phpmailer_instance();

		$user  = $this->factory->user->create_and_get();
		$email = $this->factory->post->create( array(
			'post_type' => 'llms_email',
			'meta_input' => array(
				'_llms_email_subject' => 'Engagement Email',
			),
		) );
		$course = $this->factory->course->create_and_get( array(
			'sections' => 1,
			'lessons'  => 1,
			'quizzes'  => 0,
		) );

		// Shouldn't send because of enrollment.
		$send = $this->main->handle_email( array( $user->ID, $email, $course->get( 'id' ) ) );
		$this->assertIsWPError( $send );
		$this->assertWPErrorCodeEquals( 'llms_engagement_email_not_sent_enrollment', $send );
		$this->assertFalse( $mailer->get_sent() );

		llms_enroll_student( $user->ID, $course->get( 'id' ) );

		// Try from course, section, and lesson.
		$send_ids = array( $course->get( 'id' ), $course->get_sections( 'ids' )[0], $course->get_lessons( 'ids' )[0] );
		foreach ( $send_ids as $post_id ) {

			// Send the email.
			$this->assertTrue( $this->main->handle_email( array( $user->ID, $email, $post_id ) ) );

			// Email sent.
			$sent = $mailer->get_sent();
			$this->assertEquals( $user->user_email, $sent->to[0][0] );
			$this->assertEquals( 'Engagement Email', $sent->subject );

			// User meta recorded.
			$this->assertEquals( $email, llms_get_user_postmeta( $user->ID, $post_id, '_email_sent' ) );

			// Reset the mailer.
			reset_phpmailer_instance();
			$mailer = tests_retrieve_phpmailer_instance();

			// Shouldn't send again because of dupcheck.
			$send = $this->main->handle_email( array( $user->ID, $email, $post_id ) );
			$this->assertIsWPError( $send );
			$this->assertWPErrorCodeEquals( 'llms_engagement_email_not_sent_dupcheck', $send );
			$this->assertFalse( $mailer->get_sent() );

		}

	}

	/**
	 * Test handle_email() with no related post (as found during registration)
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_handle_email_with_registration() {

		$mailer = tests_retrieve_phpmailer_instance();

		$user  = $this->factory->user->create_and_get();
		$email = $this->factory->post->create( array(
			'post_type' => 'llms_email',
			'meta_input' => array(
				'_llms_email_subject' => 'Engagement Email',
			),
		) );

		$this->assertTrue( $this->main->handle_email( array( $user->ID, $email, '' ) ) );
		$sent = $mailer->get_sent();
		$this->assertEquals( $user->user_email, $sent->to[0][0] );
		$this->assertEquals( 'Engagement Email', $sent->subject );

	}

	/**
	 * Test get_engagement_query_args() during user registration
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_engagement_query_args_user_reg() {
		$expect = array(
			'related_post_id' => '',
			'trigger_type'    => 'user_registration',
			'user_id'         => 1,
		);
		$this->assertEquals( $expect, LLMS_Unit_Test_Util::call_method( $this->main, 'get_engagement_query_args', array( 'lifterlms_created_person', array( 1, array(), 'screen' ) ) ) );
	}

	/**
	 * Test get_engagement_query_args() during various post type completions
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_engagement_query_args_completion() {

		$expect = array(
			'related_post_id' => 2,
			'trigger_type'    => '',
			'user_id'         => 1,
		);

		$tests = array(
			'lifterlms_course_completed'       => 'course_completed',
			'lifterlms_course_track_completed' => 'course_track_completed',
			'lifterlms_lesson_completed'       => 'lesson_completed',
			'lifterlms_quiz_completed'         => 'quiz_completed',
			'lifterlms_quiz_failed'            => 'quiz_failed',
			'lifterlms_quiz_passed'            => 'quiz_passed',
			'lifterlms_section_completed'      => 'section_completed',
		);


		foreach ( $tests as $hook => $trigger_type ) {

			$expect['trigger_type'] = $trigger_type;
			$this->assertEquals( $expect, LLMS_Unit_Test_Util::call_method( $this->main, 'get_engagement_query_args', array( $hook, array( 1, 2 ) ) ) );

		}

	}

	/**
	 * Test get_engagement_query_args() during course enrollment
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_engagement_query_args_enrollment_course() {

		$post_id = $this->factory->post->create( array( 'post_type' => 'course' ) );

		$expect = array(
			'related_post_id' => $post_id,
			'trigger_type'    => 'course_enrollment',
			'user_id'         => 1,
		);
		$this->assertEquals( $expect, LLMS_Unit_Test_Util::call_method( $this->main, 'get_engagement_query_args', array( 'llms_user_enrolled_in_course', array( 1, $post_id ) ) ) );
	}

	/**
	 * Test get_engagement_query_args() during membership enrollment
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_engagement_query_args_enrollment_membership() {

		$post_id = $this->factory->post->create( array( 'post_type' => 'llms_membership' ) );

		$expect = array(
			'related_post_id' => $post_id,
			'trigger_type'    => 'membership_enrollment',
			'user_id'         => 1,
		);
		$this->assertEquals( $expect, LLMS_Unit_Test_Util::call_method( $this->main, 'get_engagement_query_args', array( 'llms_user_added_to_membership_level', array( 1, $post_id ) ) ) );
	}

	/**
	 * Test get_engagement_query_args() when an access plan is purchased
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_engagement_query_args_purchase_access_plan() {

		$post_id = $this->factory->post->create( array( 'post_type' => 'llms_access_plan' ) );

		$expect = array(
			'related_post_id' => $post_id,
			'trigger_type'    => 'access_plan_purchased',
			'user_id'         => 1,
		);
		$this->assertEquals( $expect, LLMS_Unit_Test_Util::call_method( $this->main, 'get_engagement_query_args', array( 'lifterlms_access_plan_purchased', array( 1, $post_id ) ) ) );

	}

	/**
	 * Test get_engagement_query_args() when a course is purchased
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_engagement_query_args_purchase_course() {

		$post_id = $this->factory->post->create( array( 'post_type' => 'course' ) );

		$expect = array(
			'related_post_id' => $post_id,
			'trigger_type'    => 'course_purchased',
			'user_id'         => 1,
		);
		$this->assertEquals( $expect, LLMS_Unit_Test_Util::call_method( $this->main, 'get_engagement_query_args', array( 'lifterlms_product_purchased', array( 1, $post_id ) ) ) );

	}

	/**
	 * Test get_engagement_query_args() when a membership is purchased
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_engagement_query_args_purchase_membership() {

		$post_id = $this->factory->post->create( array( 'post_type' => 'llms_membership' ) );

		$expect = array(
			'related_post_id' => $post_id,
			'trigger_type'    => 'membership_purchased',
			'user_id'         => 1,
		);
		$this->assertEquals( $expect, LLMS_Unit_Test_Util::call_method( $this->main, 'get_engagement_query_args', array( 'lifterlms_product_purchased', array( 1, $post_id ) ) ) );

	}

	/**
	 * Test get_engagement_query_args() for other (invalid) hooks
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_engagement_query_args_other_hooks() {

		$expect = array(
			'related_post_id' => null,
			'trigger_type'    => null,
			'user_id'         => null,
		);
		$this->assertEquals( $expect, LLMS_Unit_Test_Util::call_method( $this->main, 'get_engagement_query_args', array( 'customfakehook' ) ) );

	}

}
