<?php
/**
 * Test functions template
 *
 * @package LifterLMS/Tests/Functions
 *
 * @group functions
 * @group functions_template
 *
 * @since 4.8.0
 */
class LLMS_Test_Functions_Template extends LLMS_UnitTestCase {

	private $themes = array(
		'fake_parent',
		'fake_child',
	);

	/**
	 * Setup test cases
	 *
	 * @since 4.8.0
	 * @since [version] Renamed from `setUp()` for compat with WP core changes.
	 *
	 * @return void
	 */
	public function set_up() {
		parent::set_up();
		foreach ( $this->themes as $theme ) {
			$this->_delete_theme_override_directory( $theme );
		}
	}

	/**
	 * Test llms_get_template_override_directories() when only parent theme override dir is present
	 *
	 * @since 4.8.0
	 *
	 * @return void
	 */
	public function test_lms_get_template_override_directories_only_parent_theme() {
		$original_template = get_option( 'template', '' );
		update_option( 'template', 'fake_parent' );
		$this->_create_theme_override_directory( 'fake_parent' );
		$template_directories = llms_get_template_override_directories();

		$this->assertEquals(
			array(
				get_theme_root() . '/fake_parent/lifterlms'
			),
			array_values( $template_directories )
		);

		$this->_delete_theme_override_directory( 'fake_parent' );
		update_option( 'template', $original_template );
	}

	/**
	 * Test llms_get_template_override_directories() when parent and child theme override dir are present
	 *
	 * @since 4.8.0
	 *
	 * @return void
	 */
	public function test_lms_get_template_override_directories_parent_and_child_theme() {
		$original_template   = get_option( 'template', '' );
		$original_stylesheet = get_option( 'stylesheet', '' );
		update_option( 'template', 'fake_parent' );
		update_option( 'stylesheet', 'fake_child' );
		$this->_create_theme_override_directory( 'fake_parent' );
		$this->_create_theme_override_directory( 'fake_child' );

		$template_directories = llms_get_template_override_directories();

		$this->assertEquals(
			array(
				get_theme_root() . '/fake_child/lifterlms',
				get_theme_root() . '/fake_parent/lifterlms'
			),
			$template_directories
		);

		update_option( 'template', $original_template );
		update_option( 'stylesheet', $original_stylesheet );

	}

	/**
	 * Test llms_get_template_override_directories() when parent and child theme are present but only parent overrides
	 *
	 * @since 4.8.0
	 *
	 * @return void
	 */
	public function test_lms_get_template_override_directories_parent_and_child_theme_parent_overrides() {
		$original_template   = get_option( 'template', '' );
		$original_stylesheet = get_option( 'stylesheet', '' );
		update_option( 'template', 'fake_parent' );
		update_option( 'stylesheet', 'fake_child' );
		$this->_create_theme_override_directory( 'fake_parent' );
		$this->_create_theme_override_directory( 'fake_child' );

		rmdir(  get_theme_root() . '/fake_child/lifterlms' );

		$template_directories = llms_get_template_override_directories();

		$this->assertEquals(
			array(
				get_theme_root() . '/fake_parent/lifterlms'
			),
			array_values( $template_directories )
		);

		update_option( 'template', $original_template );
		update_option( 'stylesheet', $original_stylesheet );

	}

	/**
	 * Test llms_get_template_override_directories() when parent and child theme are present but only child overrides
	 *
	 * @since 4.8.0
	 *
	 * @return void
	 */
	public function test_lms_get_template_override_directories_parent_and_child_theme_child_overrides() {
		$original_template   = get_option( 'template', '' );
		$original_stylesheet = get_option( 'stylesheet', '' );
		update_option( 'template', 'fake_parent' );
		update_option( 'stylesheet', 'fake_child' );
		$this->_create_theme_override_directory( 'fake_parent' );
		$this->_create_theme_override_directory( 'fake_child' );

		rmdir(  get_theme_root() . '/fake_parent/lifterlms' );

		$template_directories = llms_get_template_override_directories();

		$this->assertEquals(
			array(
				get_theme_root() . '/fake_child/lifterlms'
			),
			array_values( $template_directories )
		);

		update_option( 'template', $original_template );
		update_option( 'stylesheet', $original_stylesheet );

	}

	/**
	 * Test llms_get_template_override_directories() when parent and child theme are present but none of them overrides
	 *
	 * @since 4.8.0
	 *
	 * @return void
	 */
	public function test_lms_get_template_override_directories_parent_and_child_theme_no_override() {
		$original_template   = get_option( 'template', '' );
		$original_stylesheet = get_option( 'stylesheet', '' );
		update_option( 'template', 'fake_parent' );
		update_option( 'stylesheet', 'fake_child' );
		$this->_create_theme_override_directory( 'fake_parent' );
		$this->_create_theme_override_directory( 'fake_child' );

		rmdir(  get_theme_root() . '/fake_parent/lifterlms' );
		rmdir(  get_theme_root() . '/fake_child/lifterlms' );

		$template_directories = llms_get_template_override_directories();

		$this->assertEmpty( $template_directories );

		$this->_delete_theme_override_directory( 'fake_child' );
		$this->_delete_theme_override_directory( 'fake_parent' );

		update_option( 'template', $original_template );
		update_option( 'stylesheet', $original_stylesheet );

	}

	/**
	 * Creates a theme and override lifterlms template diretoris
	 *
	 * @since 4.8.0
	 *
	 * @param string $theme_dir_name Theme directory name.
	 * @return void
	 */
	private function _create_theme_override_directory( $theme_dir_name ) {
		$theme_root = get_theme_root();
		mkdir( "{$theme_root}/{$theme_dir_name}/lifterlms", 0777, true );
	}

	/**
	 * Deletes a theme and override lifterlms template diretoris
	 *
	 * @since 4.8.0
	 *
	 * @param string $theme_dir_name Theme directory name.
	 * @return void
	 */
	private function _delete_theme_override_directory( $theme_dir_name ) {
		$theme_root = get_theme_root();
		if ( is_dir( "{$theme_root}/{$theme_dir_name}/lifterlms" ) ) {
			rmdir( "{$theme_root}/{$theme_dir_name}/lifterlms" );
		}
		if ( is_dir( "{$theme_root}/{$theme_dir_name}" ) ) {
			rmdir(  "{$theme_root}/{$theme_dir_name}" );
		}
	}
}
