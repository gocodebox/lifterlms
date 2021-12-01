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
 * @since [version]
 */
class LLMS_Test_Admin_Post_Table_Certificates extends LLMS_UnitTestCase {

	/**
	 * Set up before class.
	 *
	 * @since [version]
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
	 * @since [version]
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
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_constructor() {

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
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_add_actions() {

		// Is legacy.
		$post = $this->factory->post->create_and_get( array( 'post_type' => 'llms_certificate' ) );
		$this->assertArrayHasKey(
			'llms-migrate-legacy-template',
			$this->main->add_actions( array(), $post )
		);

		// Use block editor.
		$post->post_content = '';
		$this->assertEquals( array(), $this->main->add_actions( array(), $post ) );

	}

	/**
	 * Test add_states().
	 *
	 * @since [version]
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

}
