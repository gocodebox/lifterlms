<?php
/**
 * Test LLMS_Controller_Achievements.
 *
 * @package LifterLMS/Tests/Controllers
 *
 * @group controllers
 * @group achievements
 * @group controller_achievements
 *
 * @since [version]
 */
class LLMS_Test_Controller_Achievements extends LLMS_UnitTestCase {

	/**
	 * @var LLMS_Controller_Achievements
	 */
	private $instance;

	/**
	 * Add nonce to array.
	 *
	 * @since [version]
	 *
	 * @param array $data Data array.
	 * @param bool  $real If true, uses a real nonce. Otherwise uses a fake nonce (useful for testing negative cases).
	 * @return array
	 */
	protected function add_nonce_to_array( $data = array(), $real = true ) {
		$nonce_string = $real ? wp_create_nonce( 'llms-achievement-sync-actions' ) : wp_create_nonce( 'fake' );

		return wp_parse_args( $data, array(
			'_llms_achievement_sync_actions_nonce' => $nonce_string,
		) );
	}

	/**
	 * Setup the test case.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function set_up() {

		parent::set_up();
		$this->instance = new LLMS_Controller_Achievements();
	}

	/**
	 * Test maybe_handle_awarded_engagement_sync_actions() when not supplying an achievement/template ID.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_maybe_handle_awarded_achievements_sync_actions_missing_achievement_or_template_id() {

		// Not supplying an achievement ID.
		$this->mockGetRequest(
			$this->add_nonce_to_array(
				array(
					'action' => 'sync_awarded_achievement',
				)
			)
		);

		$this->assertWPErrorCodeEquals(
			'llms-sync-missing-awarded-achievement-id',
			$this->instance->maybe_handle_awarded_engagement_sync_actions()
		);

		// Not supplying an achievement template ID.
		$this->mockGetRequest(
			$this->add_nonce_to_array(
				array(
					'action' => 'sync_awarded_achievements',
				)
			)
		);

		$this->assertWPErrorCodeEquals(
			'llms-sync-missing-achievement-template-id',
			$this->instance->maybe_handle_awarded_engagement_sync_actions()
		);
	}

	/**
	 * Test maybe_handle_awarded_engagement_sync_actions() when not supplying an action or supplying an invalid action.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_maybe_handle_awarded_achievements_sync_actions_missing_invalid_action() {

		// Not supplying an action.
		$this->mockGetRequest(
			$this->add_nonce_to_array(
				array()
			)
		);

		$this->assertWPErrorCodeEquals(
			'llms-sync-awarded-achievements-missing-action',
			$this->instance->maybe_handle_awarded_engagement_sync_actions()
		);

		// Supplying an invalid nonce.
		$this->mockGetRequest(
			$this->add_nonce_to_array(
				array(
					'action' => 'sync_awarded_achievement_wrong',
				)
			)
		);

		$this->assertWPErrorCodeEquals(
			'llms-sync-awarded-achievements-invalid-action',
			$this->instance->maybe_handle_awarded_engagement_sync_actions()
		);
	}

	/**
	 * Test maybe_handle_awarded_engagement_sync_actions() when not supplying a nonce or supplying an invalid nonce.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_maybe_handle_awarded_achievements_sync_actions_missing_invalid_nonce() {

		// Not supplying a nonce.
		$this->mockGetRequest(
			array(
				'action' => 'sync_awarded_achievement',
			)
		);

		$this->assertWPErrorCodeEquals(
			'llms-sync-awarded-achievements-invalid-nonce',
			$this->instance->maybe_handle_awarded_engagement_sync_actions()
		);

		// Supplying an invalid nonce.
		$this->mockGetRequest(
			$this->add_nonce_to_array(
				array(
					'action' => 'sync_awarded_achievement',
				),
				false
			)
		);

		$this->assertWPErrorCodeEquals(
			'llms-sync-awarded-achievements-invalid-nonce',
			$this->instance->maybe_handle_awarded_engagement_sync_actions()
		);
	}

	/**
	 * Test sync_awarded_engagement() handling.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_sync_awarded_achievement_handling() {

		// Create an achievement template.
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );

		// Unregister the llms_my_achievement post type then re-register it so that the post type property _edit_link
		// is populated (admin can edit the post type).
		unregister_post_type( 'llms_my_achievement' );
		LLMS_Post_Types::register_post_types();
		$achievement_template_id = $this->factory->post->create(
			array(
				'post_type' => 'llms_achievement',
			)
		);
		$awarded_achievement_id  = $this->factory->post->create(
			array(
				'post_type'   => 'llms_my_achievement',
				'post_parent' => $achievement_template_id,
			)
		);

		// Current user cannot edit 'llms_my_achievement'.
		wp_set_current_user( 0 );
		$this->assertWPErrorCodeEquals(
			'llms-sync-awarded-achievement-insufficient-permissions',
			LLMS_Unit_Test_Util::call_method(
				$this->instance,
				'sync_awarded_engagement',
				array( $awarded_achievement_id )
			)
		);

		// Current user can edit 'llms_my_achievement'.
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'lms_manager' ) ) );
		$this->mockGetRequest(
			$this->add_nonce_to_array(
				array(
					'action' => 'sync_awarded_achievement',
					'post'   => $awarded_achievement_id,
				)
			)
		);
		$this->expectException( LLMS_Unit_Test_Exception_Redirect::class );
		$this->expectExceptionMessage( get_edit_post_link( $awarded_achievement_id, 'raw' ) . '&message=1 [302] YES' ); // Update success.
		$this->instance->maybe_handle_awarded_engagement_sync_actions();
	}

	/**
	 * Test sync_awarded_achievement() method.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_sync_awarded_achievement_method_invalid_template() {

		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );

		// Unregister the llms_my_achievement post type then re-register it so that the post type property _edit_link
		// is populated (admin can edit the post type).
		unregister_post_type( 'llms_my_achievement' );
		LLMS_Post_Types::register_post_types();

		// Invalid achievement template.
		$achievement_template_id = $this->factory->post->create(
			array(
				'post_type' => 'post',
			)
		);
		$awarded_achievement_id  = $this->factory->post->create(
			array(
				'post_type'   => 'llms_my_achievement',
				'post_parent' => $achievement_template_id,
			)
		);

		// Current user can edit 'llms_my_achievement'.
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'lms_manager' ) ) );

		$this->assertWPErrorCodeEquals(
			'llms-sync-awarded-achievement-invalid-template',
			LLMS_Unit_Test_Util::call_method(
				$this->instance,
				'sync_awarded_engagement',
				array( $awarded_achievement_id )
			)
		);
	}

	/**
	 * Test sync_awarded_engagements handling.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_sync_awarded_achievements_handling() {

		// Create an achievement template.
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );

		// Unregister the llms_achievement post type then re-register it so that the post type property _edit_link
		// is populated (admin can edit the post type).
		unregister_post_type( 'llms_achievement' );
		LLMS_Post_Types::register_post_types();
		$achievement_template_id = $this->factory->post->create( array( 'post_type' => 'llms_achievement' ) );

		// Current user cannot edit 'llms_my_achievement' post type.
		wp_set_current_user( 0 );
		$this->assertWPErrorCodeEquals(
			'llms-sync-awarded-achievements-insufficient-permissions',
			LLMS_Unit_Test_Util::call_method(
				$this->instance,
				'sync_awarded_engagements',
				array( $achievement_template_id )
			)
		);

		// Current user can edit 'llms_my_achievement' post type.
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'lms_manager' ) ) );
		$this->mockGetRequest(
			$this->add_nonce_to_array(
				array(
					'action' => 'sync_awarded_achievements',
					'post'   => $achievement_template_id,
				)
			)
		);
		$this->expectException( LLMS_Unit_Test_Exception_Redirect::class );
		$this->expectExceptionMessage( get_edit_post_link( $achievement_template_id, 'raw' ) . ' [302] YES' );
		$this->instance->maybe_handle_awarded_engagement_sync_actions();
		$this->assertEquals( 1, did_action( 'llms_do_awarded_achievements_bulk_sync' ) );
	}
}
