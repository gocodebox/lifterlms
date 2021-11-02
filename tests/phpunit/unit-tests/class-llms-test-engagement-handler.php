<?php
/**
 * Tests for LLMS_Engagement_Handler class
 *
 * @package LifterLMS/Tests
 *
 * @group engagements
 * @group engagement_handler
 *
 * @since [version]
 */
class LLMS_Test_Engagement_Handler extends LLMS_UnitTestCase {


	/**
	 * Test can_process()
	 *
	 * @since [version]
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
	 * Test do_deprecated_creation_filters()
	 *
	 * @since [version]
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

	// public function test_create() {





	// }

}
