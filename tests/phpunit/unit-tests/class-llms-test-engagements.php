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
class LLMS_Test_Engagements extends LLMS_UnitTestCase {

	/**
	 * Setup test case
	 *
	 * @since 4.4.1
	 * @since 5.3.3 Renamed from `setUp()` for compat with WP core changes.
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
	 * @since 5.3.3 Renamed from `tearDown()` for compat with WP core changes.
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

	/**
	 * Test maybe_trigger_engagement() for the user registration trigger
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_maybe_trigger_engagement_user_registration() {

		$this->run_engagement_tests( function( $engagement_type, $expected_action, $delay ) {

			$engagement        = $this->create_mock_engagement( 'user_registration', $engagement_type, $delay );
			$engagement_post_id = get_post_meta( $engagement->ID, '_llms_engagement', true );

			$user = $this->factory->user->create();

			$this->assertEngagementTriggered(
				'lifterlms_created_person', // Trigger hook.
				array( $user ), // Args passed to trigger hook.
				$expected_action,
				array( $user, $engagement_post_id, 'certificate' === $engagement_type ? $engagement_post_id : '' ), // Expected args passed to the expected action's callback.
				$delay
			);

		} );

	}

	/**
	 * Test maybe_trigger_engagement() for the completion hooks (course, section, lesson)
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_maybe_trigger_engagement_content_completed() {

		foreach ( array( 'course', 'section', 'lesson', 'quiz' ) as $post_type ) {

			$this->run_engagement_tests( function( $engagement_type, $expected_action, $delay ) use ( $post_type ) {

				$engagement        = $this->create_mock_engagement( $post_type . '_completed', $engagement_type, $delay );
				$engagement_post_id = get_post_meta( $engagement->ID, '_llms_engagement', true );
				$related_post_id    = get_post_meta( $engagement->ID, '_llms_engagement_trigger_post', true );

				$user = $this->factory->user->create();

				$this->assertEngagementTriggered(
					'lifterlms_' . $post_type . '_completed', // Trigger hook.
					array( $user, $related_post_id ), // Args passed to trigger hook.
					$expected_action,
					array( $user, $engagement_post_id, absint( $related_post_id ) ), // Expected args passed to the expected action's callback.
					$delay
				);

			} );

		}

	}

	/**
	 * Test maybe_trigger_engagement() for the enrollment hooks
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_maybe_trigger_engagement_enrollment() {

		$tests = array(
			'llms_user_enrolled_in_course'        => 'course',
			'llms_user_added_to_membership_level' => 'membership',
		);

		foreach ( $tests as $trigger_hook => $post_type ) {

			$this->run_engagement_tests( function( $engagement_type, $expected_action, $delay ) use ( $trigger_hook, $post_type ) {

				$engagement        = $this->create_mock_engagement( $post_type . '_enrollment', $engagement_type, $delay );
				$engagement_post_id = get_post_meta( $engagement->ID, '_llms_engagement', true );
				$related_post_id    = get_post_meta( $engagement->ID, '_llms_engagement_trigger_post', true );

				$user = $this->factory->user->create();

				$this->assertEngagementTriggered(
					$trigger_hook,
					array( $user, $related_post_id ), // Args passed to trigger hook.
					$expected_action,
					array( $user, $engagement_post_id, absint( $related_post_id ) ), // Expected args passed to the expected action's callback.
					$delay
				);

			} );

		}

	}

	/**
	 * Test maybe_trigger_engagement() for the purchase hooks
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_maybe_trigger_engagement_purchase() {

		$tests = array(
			'lifterlms_access_plan_purchased' => 'access_plan',
			'lifterlms_product_purchased'     => 'course',
			'lifterlms_product_purchased'     => 'membership',
		);

		foreach ( $tests as $trigger_hook => $post_type ) {

			$this->run_engagement_tests( function( $engagement_type, $expected_action, $delay ) use ( $trigger_hook, $post_type ) {

				$engagement        = $this->create_mock_engagement( $post_type . '_purchased', $engagement_type, $delay );
				$engagement_post_id = get_post_meta( $engagement->ID, '_llms_engagement', true );
				$related_post_id    = get_post_meta( $engagement->ID, '_llms_engagement_trigger_post', true );

				$user = $this->factory->user->create();

				$this->assertEngagementTriggered(
					$trigger_hook,
					array( $user, $related_post_id ), // Args passed to trigger hook.
					$expected_action,
					array( $user, $engagement_post_id, absint( $related_post_id ) ), // Expected args passed to the expected action's callback.
					$delay
				);

			} );

		}

	}


	private function run_engagement_tests( $callback ) {

		$tests = array(
			'achievement' => 'lifterlms_engagement_award_achievement',
			'certificate' => 'lifterlms_engagement_award_certificate',
			'email'       => 'lifterlms_engagement_send_email',
		);

		foreach ( $tests as $engagement_type => $expected_action ) {

			$delay = 0;
			while ( $delay <= 1 ) {
				$callback( $engagement_type, $expected_action, $delay );
				$delay++;
			}

		}

	}

	private function assertEngagementTriggered( $trigger_filter, $trigger_args, $expected_action, $expected_args, $delay = 0 ) {

		// Record the number of run actions so we can ensure it was properly incremented.
		$start_actions = did_action( $expected_action );

		// Mock the `current_filter()` return.
		global $wp_current_filter;
		$wp_current_filter = array( $trigger_filter );

		if ( ! $delay ) {

			// Add an action to assert the expected arguments.
			$callback = function( $args ) use ( $expected_args ) {
				$this->assertEquals( $expected_args, $args );
			};
			add_action( $expected_action, $callback, 15 );

		}

		// Simulate trigger callback.
		$this->main->maybe_trigger_engagement( ...$trigger_args );

		if ( ! $delay ) {

			// Assert the action ran.
			$this->assertEquals( ++$start_actions, did_action( $expected_action ), $expected_action );

			// Remove our assertion action.
			remove_action( $expected_action, $callback, 15 );

		} else {

			$expected_args = array( $expected_args );
			$event = wp_get_scheduled_event( $expected_action, $expected_args );
			$this->assertTrue( is_object( $event ), $expected_action );
			$this->assertEquals( $expected_action, $event->hook, $expected_action );
			$this->assertEquals( $expected_args, $event->args, $expected_action );
			$this->assertEquals( time() + ( DAY_IN_SECONDS * $delay ), $event->timestamp, $expected_action, 5 );
		}

	}

	public function create_mock_engagement( $trigger_type, $engagement_type, $delay = 0 ) {

		/**
		 * Trigger Types
		 *
		 * user_registration
		 *
		 * course_completed
		 * lesson_completed
		 * section_completed
		 *
		 * course_track_completed
		 *
		 * quiz_completed
		 * quiz_passed
		 * quiz_failed
		 *
		 * course_enrollment
		 * membership_enrollment
		 *
		 * access_plan_purchased
		 * course_purchased
		 * membership_purchased
		 */

		switch ( $trigger_type ) {
			case 'user_registration':
				$trigger_post = 0;
				break;

			case 'course_completed':
			case 'lesson_completed':
			case 'section_completed':
			case 'quiz_completed':
			case 'quiz_passed':
			case 'quiz_failed':
			case 'course_enrollment':
			case 'membership_enrollment':
			case 'access_plan_purchased':
			case 'course_purchased':
			case 'membership_purchased':
				$post_type    = str_replace( array( '_completed', '_enrollment', '_passed', '_failed', '_purchased' ), '', $trigger_type );
				$post_type    = in_array( $post_type, array( 'access_plan', 'membership', 'quiz' ), true ) ? 'llms_' . $post_type : $post_type;
				$trigger_post = $this->factory->post->create( compact( 'post_type' ) );
				break;
		}

		$engagement_create_func = "create_{$engagement_type}_template";
		$engagement_post = $this->$engagement_create_func();

		return $this->factory->post->create_and_get( array(
			'post_type'  => 'llms_engagement',
			'meta_input' => array(
				'_llms_trigger_type'            => $trigger_type,
				'_llms_engagement_trigger_post' => $trigger_post,
				'_llms_engagement_type'         => $engagement_type,
				'_llms_engagement'              => $engagement_post,
				'_llms_engagement_delay'        => $delay,
			)
		) );

	}

}
