<?php
/**
 * Tests for LifterLMS Achievement Sync Meta Box.
 *
 * @package LifterLMS/Tests
 *
 * @group metabox_achievement_sync
 * @group admin
 * @group metaboxes
 * @group metaboxes_post_type
 *
 * @since [version]
 * @version [version]
 */
class LLMS_Test_Meta_Box_Achievement_Sync extends LLMS_PostTypeMetaboxTestCase {

	/**
	 * @var LLMS_Meta_Box_Achievement_Sync
	 */
	private $metabox;

	/**
	 * Setup test.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function set_up() {

		parent::set_up();
		$this->metabox = new LLMS_Meta_Box_Achievement_Sync();
	}

	/**
	 * Tear down test.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function tear_down() {

		// Reset current screen.
		llms_tests_reset_current_screen();
	}


	/**
	 * Test the get_screens() method.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_screens() {

		$this->assertEquals(
			array( 'llms_achievement', 'llms_my_achievement' ),
			LLMS_Unit_Test_Util::call_method( $this->metabox, 'get_screens' )
		);
	}

	/**
	 * Test sync awarded achievement action.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_sync_action_achievement() {

		$action = 'action=sync_awarded_achievement';

		$post                = $this->factory->post->create_and_get();
		$this->metabox->post = $post;
		$this->metabox->configure();

		// Not llms_my_achievement post type.
		$this->assertStringNotContainsString(
			$action,
			LLMS_Unit_Test_Util::call_method( $this->metabox, 'sync_action' )
		);

		$my_achievement      = $this->factory->post->create_and_get( array( 'post_type' => 'llms_my_achievement' ) );
		$this->metabox->post = $my_achievement;
		$this->metabox->configure();

		// llms_my_achievement post type but no achievement template parent.
		$this->assertStringNotContainsString(
			$action,
			LLMS_Unit_Test_Util::call_method( $this->metabox, 'sync_action' )
		);

		// Set a template which is not an `llms_achievement`.
		$template = $this->factory->post->create_and_get();
		wp_update_post(
			array(
				'ID'          => $my_achievement->ID,
				'post_parent' => $template->ID,
			)
		);
		$this->assertStringNotContainsString(
			$action,
			LLMS_Unit_Test_Util::call_method( $this->metabox, 'sync_action' )
		);

		// Set a template which is a `llms_achievement`.
		wp_update_post(
			array(
				'ID'        => $template->ID,
				'post_type' => 'llms_achievement',
			)
		);
		$this->assertStringContainsString(
			$action,
			LLMS_Unit_Test_Util::call_method( $this->metabox, 'sync_action' )
		);

		// Delete created posts.
		foreach ( array( $post, $my_achievement, $template ) as $to_delete ) {
			wp_delete_post( $to_delete->ID );
		}
	}

	/**
	 * Test sync awarded achievements action.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_sync_action_achievements() {

		$action = 'action=sync_awarded_achievements';

		$post                = $this->factory->post->create_and_get();
		$this->metabox->post = $post;
		$this->metabox->configure();

		// Not llms_achievement post type.
		$this->assertStringNotContainsString(
			$action,
			LLMS_Unit_Test_Util::call_method( $this->metabox, 'sync_action' )
		);
		wp_delete_post( $post->ID, true );

		$post                = $this->factory->post->create_and_get( array( 'post_type' => 'llms_achievement' ) );
		$this->metabox->post = $post;
		$this->metabox->configure();

		// llms_achievement post type but no awarded achievements.
		$this->assertStringNotContainsString(
			$action,
			LLMS_Unit_Test_Util::call_method( $this->metabox, 'sync_action' )
		);

		$awarded_achievements = array();

		// Create various awarded achievements but with a different template.
		foreach ( get_available_post_statuses( 'llms_my_achievement' ) as $status ) {
			$awarded_achievements[] = $this->factory->post->create(
				array(
					'post_type'   => 'llms_my_achievement',
					'post_parent' => 999,
					'post_status' => $status,
				)
			);
		}
		$this->assertStringNotContainsString(
			$action,
			LLMS_Unit_Test_Util::call_method( $this->metabox, 'sync_action' )
		);

		// Create various awarded achievements: only 2 of them have the required post_status (publish and future).
		foreach ( get_available_post_statuses( 'llms_my_achievement' ) as $status ) {
			$awarded_achievements[] = $this->factory->post->create(
				array(
					'post_type'   => 'llms_my_achievement',
					'post_parent' => $post->ID,
					'post_status' => $status,
				)
			);
		}

		$this->assertStringContainsString(
			$action,
			LLMS_Unit_Test_Util::call_method( $this->metabox, 'sync_action' )
		);

		$this->assertStringContainsString(
			'2 awarded achievements',
			LLMS_Unit_Test_Util::call_method( $this->metabox, 'sync_action' )
		);

		// Delete created posts.
		foreach ( array_merge( $awarded_achievements, array( $post->ID ) ) as $to_delete ) {
			wp_delete_post( $to_delete );
		}
	}
}
