<?php
/**
 * Tests for the LLMS_Admin_Post_Table_Certificates class.
 *
 * @package LifterLMS/Tests
 *
 * @group admin
 * @group post_tables
 * @group post_table_certificates
 *
 * @since 6.0.0
 * @since [version] Added tests for `add_states()` method on posts that aren't certificates.
 */
class LLMS_Test_Admin_Post_Table_Certificates extends LLMS_UnitTestCase {

	/**
	 * Set up before class.
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public static function set_up_before_class() {

		parent::set_up_before_class();
		require_once LLMS_PLUGIN_DIR . 'includes/admin/post-types/post-tables/class-llms-admin-post-table-certificates.php';

	}

	/**
	 * Setup test.
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function set_up() {

		parent::set_up();
		$this->main = new LLMS_Admin_Post_Table_Certificates();

	}

	/**
	 * Test __construct().
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function test_constructor() {

		if ( ! llms_is_block_editor_supported_for_certificates() ) {
			$this->markTestSkipped( 'No actions are registered for this version of WordPress.' );
		}

		// Always added.
		$this->assertEquals( 10, has_filter( 'manage_llms_my_certificate_posts_columns', array( $this->main, 'mod_cols' ) ) );

		// Wrong screen.
		$this->assertFalse( has_filter( 'display_post_states', array( $this->main, 'add_states' ) ) );
		$this->assertFalse( has_filter( 'post_row_actions', array( $this->main, 'add_actions' ) ) );
		$this->assertFalse( has_filter( 'llms_certificate_template_version', array( $this->main, 'upgrade_template' ) ) );

		// Right screen.
		$this->mockGetRequest( array( 'post_type' => 'llms_certificate' ) );
		$this->main = new LLMS_Admin_Post_Table_Certificates();

		$this->assertEquals( 20, has_filter( 'display_post_states', array( $this->main, 'add_states' ) ) );
		$this->assertEquals( 20, has_filter( 'post_row_actions', array( $this->main, 'add_actions' ) ) );

		// Action set but invalid.
		$this->mockGetRequest( array( $this->main::MIGRATE_ACTION => 'yes' ) );
		$this->main = new LLMS_Admin_Post_Table_Certificates();
		$this->assertFalse( has_filter( 'llms_certificate_template_version', array( $this->main, 'upgrade_template' ) ) );

		// Valid action set.
		$this->mockGetRequest( array( $this->main::MIGRATE_ACTION => 1 ) );
		$this->main = new LLMS_Admin_Post_Table_Certificates();
		$this->assertEquals( 10, has_filter( 'llms_certificate_template_version', array( $this->main, 'upgrade_template' ) ) );

	}

	/**
	 * Test add_actions().
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function test_add_actions() {

		// Is legacy.
		$post = $this->factory->post->create_and_get( array( 'post_type' => 'llms_certificate' ) );
		$this->assertArrayHasKey(
			'llms-migrate-legacy-certificate',
			$this->main->add_actions( array(), $post )
		);

		// Use block editor.
		$post->post_content = '';
		$this->assertEquals( array(), $this->main->add_actions( array(), $post ) );

	}

	/**
	 * Test add_states().
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function test_add_states() {

		// Is legacy.
		$post = $this->factory->post->create_and_get( array( 'post_type' => 'llms_certificate' ) );
		$this->assertArrayHasKey(
			'llms-legacy-template',
			$this->main->add_states( array(), $post )
		);

		// Use block editor.
		$post->post_content = '';
		$this->assertEquals( array(), $this->main->add_states( array(), $post ) );

	}

	/**
	 * Test add_states() on posts which are not certificates.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_add_states_no_certificate_post() {

		$post = $this->factory->post->create_and_get();
		$this->assertEquals( array(), $this->main->add_states( array(), $post ) );

		$post = null;
		$this->assertEquals( array(), $this->main->add_states( array(), $post ) );

	}

	/**
	 * Test mod_cols().
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function test_mod_cols() {

		$cols = array(
			'col1'   => 'retain',
			'author' => 'remove',
			'col3'   => 'retain',
		);

		$this->assertEquals( array( 'col1', 'col3' ), array_keys( $this->main->mod_cols( $cols ) ) );

	}

}
