<?php

/**
 * Tests for LLMS_Trait_Award_Templates_Post_List_Table
 *
 * @group Traits
 *
 * @since [version]
 */
class LLMS_Test_Trait_Award_Templates_Post_List_Table extends LLMS_UnitTestCase {

	protected $tables;

	/**
	 * Setup before running each test in this class.
	 *
	 * @since [versopm]
	 */
	public function set_up() {

		parent::set_up();
		$this->tables = array(
			'achievement' => class_exists( 'LLMS_Admin_Post_Table_Achievements' ) ?
				new LLMS_Admin_Post_Table_Achievements()
				:
				require_once LLMS_PLUGIN_DIR . 'includes/admin/post-types/post-tables/class-llms-admin-post-table-achievements.php',
			'certificate' => class_exists( 'LLMS_Admin_Post_Table_Certificates' ) ?
				new LLMS_Admin_Post_Table_Certificates()
				:
				require_once LLMS_PLUGIN_DIR . 'includes/admin/post-types/post-tables/class-llms-admin-post-table-certificates.php',
		);

		require_once LLMS_PLUGIN_DIR . 'includes/admin/post-types/post-tables/class-llms-admin-post-table-awards.php';

	}

	/**
	 * Test the add_post_actions() method.
	 *
	 * @since [version]
	 */
	public function test_add_post_actions() {

		foreach ( $this->tables as $pt => $table ) {
			// No post passed, no actions added.
			$this->assertEquals(
				array(),
				$table->add_post_actions( array(), null ),
				$pt
			);

			// Create a post.
			$post = $this->factory->post->create_and_get();

			// No actions added.
			$this->assertEquals(
				array(),
				$table->add_post_actions( array(), $post ),
				$pt
			);

			// Create a valid post type.
			$post = $this->factory->post->create_and_get(
				array(
					'post_type' => "llms_{$pt}",
				)
			);

			// Force the post type to not show the ui.
			get_post_type_object( "llms_{$pt}" )->show_ui = false;
			// Actions not added because by default the post type `show_ui` is not true.
			$this->assertEquals(
				array(),
				$table->add_post_actions( array(), $post ),
				$pt
			);

			// Force the post type to show the ui.
			get_post_type_object( "llms_{$pt}" )->show_ui = true;
			$actions = $table->add_post_actions( array(), $post );

			$this->assertNotEmpty(
				$table->add_post_actions( array(), $post ),
				$pt
			);

			$this->assertEquals(
				$actions['llms-awards-list'],
				'achievement' === $pt ?
					'<a href="' . admin_url( 'edit.php' ) . '?llms_filter_template=' . $post->ID . '&post_type=llms_my_' . $pt . '">View Awarded Achievements</a>'
					:
					'<a href="' . admin_url( 'edit.php' ) . '?llms_filter_template=' . $post->ID . '&post_type=llms_my_' . $pt . '">View Awarded Certificates</a>',
				$pt
			);

		}

	}

}
