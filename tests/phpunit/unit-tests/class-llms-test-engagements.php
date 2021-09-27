<?php
/**
 * Tests for LLMS_Engagements class
 *
 * @package LifterLMS/Tests
 *
 * @group engagements
 *
 * @since 4.4.1
 * @since 4.4.3 Test different emails triggered by the same post are correctly sent.
 */
class LLMS_Test_Engagements extends LLMS_Unit_Test_Case {

	/**
	 * Setup test case
	 *
	 * @since 4.4.1
	 * @since [version] Renamed from `setUp()` for compat with WP core changes.
	 *
	 * @return void
	 */
	public function set_up() {
		parent::set_up();
		$this->main = llms()->engagements();
		reset_phpmailer_instance();
	}

	/**
	 * Teardown test case
	 *
	 * @since 4.4.1
	 * @since [version] Renamed from `tearDown()` for compat with WP core changes.
	 *
	 * @return void
	 */
	public function tear_down() {

		parent::tear_down();
		reset_phpmailer_instance();

	}

	/**
	 * Test handle_email() as triggered by a related post type that's enrollable.
	 *
	 * @since 4.4.1
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
	 * Test handle_email() as triggered by the same related post type with different emails.
	 *
	 * @since 4.4.3
	 *
	 * @return void
	 */
	public function test_handle_different_emails_same_trigger() {

		$mailer = tests_retrieve_phpmailer_instance();

		$user  = $this->factory->user->create_and_get();

		$emails = $this->factory->post->create_many(
			2,
			array(
				'post_type' => 'llms_email',
				'meta_input' => array(
					'_llms_email_subject' => 'Engagement Email',
				),
			)
		);

		$course = $this->factory->course->create( array(
			'sections' => 0,
			'lessons'  => 0,
			'quizzes'  => 0,
		) );

		llms_enroll_student( $user->ID, $course );

		// Send the email.
		$this->assertTrue( $this->main->handle_email( array( $user->ID, $emails[0], $course ) ) );

		// Email sent.
		$sent = $mailer->get_sent();
		$this->assertEquals( $user->user_email, $sent->to[0][0] );
		$this->assertEquals( 'Engagement Email', $sent->subject );

		// User meta recorded.
		$this->assertEquals( $emails[0], llms_get_user_postmeta( $user->ID, $course, '_email_sent' ) );

		// Reset the mailer.
		reset_phpmailer_instance();
		$mailer = tests_retrieve_phpmailer_instance();

		// Should send the new mail.
		$this->assertTrue( $this->main->handle_email( array( $user->ID, $emails[1], $course ) ) );

		// Email sent.
		$sent = $mailer->get_sent();
		$this->assertEquals( $user->user_email, $sent->to[0][0] );
		$this->assertEquals( 'Engagement Email', $sent->subject );

		// User meta recorded.
		$this->assertEquals( $emails[1], llms_get_user_postmeta( $user->ID, $course, '_email_sent' ) );

	}

	/**
	 * Test handle_email() with no related post (as found during registration)
	 *
	 * @since 4.4.1
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

}
