<?php
/**
 * Tests for LLMS_Engagement_Handler class
 *
 * @package LifterLMS/Tests
 *
 * @group engagements
 * @group engagement_handler
 *
 * @since 6.0.0
 */
class LLMS_Test_Engagement_Handler extends LLMS_UnitTestCase {

	/**
	 * Test can_process()
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function test_can_process() {

		$related = $this->factory->course->create( array( 'sections' => 0 ) );

		$user_not_enrolled = $this->factory->user->create();

		$user_enrolled = $this->factory->user->create();
		llms_enroll_student( $user_enrolled, $related );

		foreach ( array( 'achievement', 'certificate', 'email' ) as $type ) {

			$engagement = $this->create_mock_engagement( 'course_enrollment', $type, 0, $related );
			$template   = get_post_meta( $engagement->ID, '_llms_engagement', true );

			// User doesn't exist.
			$res = LLMS_Unit_Test_Util::call_method( 'LLMS_Engagement_Handler', 'can_process', array(
				$type,
				$user_enrolled + 1,
				$template,
				$related,
				$engagement->ID,
			) );

			$this->assertEquals( 1, count( $res ) );
			$this->assertIsWpError( $res[0] );
			$this->assertWPErrorCodeEquals( 'llms-engagement-check-user--not-found', $res[0] );

			// Template post problem.
			$res = LLMS_Unit_Test_Util::call_method( 'LLMS_Engagement_Handler', 'can_process', array(
				$type,
				$user_enrolled,
				$template + 1,
				$related,
				$engagement->ID,
			) );

			$this->assertEquals( 1, count( $res ) );
			$this->assertIsWpError( $res[0] );
			$this->assertWPErrorCodeEquals( 'llms-engagement-post--type', $res[0] );

			// Engagement post problem.
			$res = LLMS_Unit_Test_Util::call_method( 'LLMS_Engagement_Handler', 'can_process', array(
				$type,
				$user_enrolled,
				$template,
				$related,
				$engagement->ID + 1,
			) );

			$this->assertEquals( 1, count( $res ) );
			$this->assertIsWpError( $res[0] );
			$this->assertWPErrorCodeEquals( 'llms-engagement-post--not-found', $res[0] );

			// Not enrolled.
			$res = LLMS_Unit_Test_Util::call_method( 'LLMS_Engagement_Handler', 'can_process', array(
				$type,
				$user_not_enrolled,
				$template,
				$related,
				$engagement->ID,
			) );

			$this->assertEquals( 1, count( $res ) );
			$this->assertIsWpError( $res[0] );
			$this->assertWPErrorCodeEquals( 'llms-engagement-check-post--enrollment', $res[0] );

			// All Good.
			$res = LLMS_Unit_Test_Util::call_method( 'LLMS_Engagement_Handler', 'can_process', array(
				$type,
				$user_enrolled,
				$template,
				$related,
				$engagement->ID,
			) );
			$this->assertTrue( $res );

		}

	}

	/**
	 * Test handle_achievement()
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function test_handle_achievement() {

		$actions = did_action( 'llms_user_earned_achievement' );

		$user_id     = $this->factory->student->create();
		$engagement  = $this->create_mock_engagement( 'course_completed', 'achievement' );
		$related_id  = get_post_meta( $engagement->ID, '_llms_engagement_trigger_post', true );
		$template_id = get_post_meta( $engagement->ID, '_llms_engagement', true );

		$handle_args = array( $user_id, $template_id, $related_id, $engagement->ID );

		// Add a thumbnail to the template.
		$attachment_id = $this->create_attachment( 'christian-fregnan-unsplash.jpg' );
		set_post_thumbnail( $template_id, $attachment_id );

		$earned = LLMS_Engagement_Handler::handle_achievement( $handle_args );

		// Enrollment error.
		$this->assertIsWPError( $earned[0] );
		$this->assertWPErrorCodeEquals( 'llms-engagement-check-post--enrollment', $earned[0] );

		llms_enroll_student( $user_id, $related_id );

		// No errors.
		$earned = LLMS_Engagement_Handler::handle_achievement( $handle_args );

		// Proper object returned.
		$this->assertInstanceOf( 'LLMS_User_Achievement', $earned );

		// Relationships saved as meta.
		$this->assertEquals( $related_id, $earned->get( 'related' ) );
		$this->assertEquals( $engagement->ID, $earned->get( 'engagement' ) );
		$this->assertEquals( $template_id, $earned->get( 'parent' ) );

		// Content and Title.
		$this->assertEquals( get_post_meta( $template_id, '_llms_achievement_title', true ), $earned->get( 'title' ) );
		$this->assertEquals( get_the_content( null, false, $template_id ), $earned->get( 'content', true ) );

		// Author.
		$this->assertEquals( $user_id, $earned->get( 'author' ) );

		// Featured Image.
		$this->assertEquals( $attachment_id, get_post_thumbnail_id( $earned->get( 'id' ) ) );

		// Ran action.
		$this->assertEquals( ++$actions, did_action( 'llms_user_earned_achievement' ) );

		// Added user postmeta.
		$this->assertEquals( $earned->get( 'id' ), llms_get_user_postmeta( $user_id, $related_id, '_achievement_earned', true ) );

		// Try it again, we should get a dupcheck.
		$earned = LLMS_Engagement_Handler::handle_achievement( $handle_args );
		$this->assertIsWPError( $earned[0] );
		$this->assertWPErrorCodeEquals( 'llms-engagement--is-duplicate', $earned[0] );

	}

	/**
	 * Test handle_certificate().
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function test_handle_certificate() {

		$actions = did_action( 'llms_user_earned_certificate' );

		$user_id     = $this->factory->student->create();
		$engagement  = $this->create_mock_engagement( 'course_completed', 'certificate' );
		$related_id  = get_post_meta( $engagement->ID, '_llms_engagement_trigger_post', true );
		$template_id = get_post_meta( $engagement->ID, '_llms_engagement', true );

		$handle_args = array( $user_id, $template_id, $related_id, $engagement->ID );

		// Add a thumbnail to the template.
		$attachment_id = $this->create_attachment( 'christian-fregnan-unsplash.jpg' );
		set_post_thumbnail( $template_id, $attachment_id );

		$expected_date = date( get_option( 'date_format' ) );

		$earned = LLMS_Engagement_Handler::handle_certificate( $handle_args );

		// Enrollment error.
		$this->assertIsWPError( $earned[0] );
		$this->assertWPErrorCodeEquals( 'llms-engagement-check-post--enrollment', $earned[0] );

		llms_enroll_student( $user_id, $related_id );

		// No errors.
		$earned = LLMS_Engagement_Handler::handle_certificate( $handle_args );

		// Proper object returned.
		$this->assertInstanceOf( 'LLMS_User_Certificate', $earned );

		// Relationships saved as meta.
		$this->assertEquals( $related_id, $earned->get( 'related' ) );
		$this->assertEquals( $engagement->ID, $earned->get( 'engagement' ) );
		$this->assertEquals( $template_id, $earned->get( 'parent' ) );

		// Content and Title.
		$this->assertEquals( get_post_meta( $template_id, '_llms_certificate_title', true ), $earned->get( 'title' ) );
		$this->assertEquals( "Test Blog, {$expected_date}", $earned->get( 'content', true ) );

		// Author.
		$this->assertEquals( $user_id, $earned->get( 'author' ) );

		// Featured Image.
		$this->assertEquals( $attachment_id, get_post_thumbnail_id( $earned->get( 'id' ) ) );

		// Ran action.
		$this->assertEquals( ++$actions, did_action( 'llms_user_earned_certificate' ) );

		// Added user postmeta.
		$this->assertEquals( $earned->get( 'id' ), llms_get_user_postmeta( $user_id, $related_id, '_certificate_earned', true ) );

		// Try it again, we should get a dupcheck.
		$earned = LLMS_Engagement_Handler::handle_certificate( $handle_args );
		$this->assertIsWPError( $earned[0] );
		$this->assertWPErrorCodeEquals( 'llms-engagement--is-duplicate', $earned[0] );

	}

	/**
	 * Test do_deprecated_creation_filters()
	 *
	 * @since 6.0.0
	 *
	 * @expectedDeprecated lifterlms_new_achievement
	 * @expectedDeprecated lifterlms_new_page
	 *
	 * @return void
	 */
	public function test_do_deprecated_creation_filters() {

		$callback = function( $args ) {
			return 'changed';
		};

		$tests = array(
			'achievement' => 'lifterlms_new_achievement',
			'certificate' => 'lifterlms_new_page',
		);

		foreach ( $tests as $type => $deprecated_hook ) {

			// Nothing attached.
			$this->assertEquals( 'args', LLMS_Unit_Test_Util::call_method( 'LLMS_Engagement_Handler', 'do_deprecated_creation_filters', array( 'args', $type ) ) );

			// Attached.
			add_filter( $deprecated_hook, $callback );
			$this->assertEquals( 'changed', LLMS_Unit_Test_Util::call_method( 'LLMS_Engagement_Handler', 'do_deprecated_creation_filters', array( 'args', $type ) ) );

			// Invalid type.
			$this->assertEquals( 'args', LLMS_Unit_Test_Util::call_method( 'LLMS_Engagement_Handler', 'do_deprecated_creation_filters', array( 'args', 'fake' ) ) );
			remove_filter( $deprecated_hook, $callback );

		}

	}

}
