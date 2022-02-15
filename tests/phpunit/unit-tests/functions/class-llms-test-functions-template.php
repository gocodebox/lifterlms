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
 * @since 5.9.0 Added tests on llms_template_file_path().
 */
class LLMS_Test_Functions_Template extends LLMS_UnitTestCase {

	private $themes = array(
		'fake_parent',
		'fake_child',
	);

	/**
	 * Setup test cases.
	 *
	 * @since 4.8.0
	 * @since 5.3.3 Renamed from `setUp()` for compat with WP core changes.
	 * @since 5.9.0 Clean theme overrides directories cache.
	 *
	 * @return void
	 */
	public function set_up() {
		parent::set_up();
		foreach ( $this->themes as $theme ) {
			$this->_delete_theme_override_directory( $theme );
		}
		wp_cache_delete( 'theme-override-directories', 'llms_template_functions' );
	}

	/**
	 * Test llms_get_template_override_directories() when only parent theme override dir is present
	 *
	 * @since 4.8.0
	 *
	 * @return void
	 */
	public function test_llms_get_template_override_directories_only_parent_theme() {
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
	public function test_llms_get_template_override_directories_parent_and_child_theme() {
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
	public function test_llms_get_template_override_directories_parent_and_child_theme_parent_overrides() {
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
	public function test_llms_get_template_override_directories_parent_and_child_theme_child_overrides() {
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
	public function test_llms_get_template_override_directories_parent_and_child_theme_no_override() {
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
	 * Test llms_template_file_path() passing an empty template file.
	 *
	 * @since 5.9.0
	 *
	 * @return void
	 */
	public function test_llms_template_file_path_empty_template_file_passed() {

		$this->assertEquals(
			llms()->plugin_path() . '/templates/',
			llms_template_file_path( '' )
		);

		/**
		 * Simulate the activation of a theme with the templates directory overridden.
		 */
		$original_template   = get_option( 'template', '' );
		$original_stylesheet = get_option( 'stylesheet', '' );
		wp_cache_delete( 'theme-override-directories', 'llms_template_functions' );
		update_option( 'template', 'fake' );
		update_option( 'stylesheet', 'fake' );
		$this->_create_theme_override_directory( 'fake' );

		$this->assertEquals(
			get_theme_root() . '/fake/lifterlms/',
			llms_template_file_path( '' )
		);

		$this->_delete_theme_override_directory( 'fake' );

		update_option( 'template', $original_template );
		update_option( 'stylesheet', $original_stylesheet );

	}

	/**
	 * Test llms_template_file_path() passing a template file that doesn't exist in the theme.
	 *
	 * @since 5.9.0
	 *
	 * @return void
	 */
	public function test_llms_template_file_path_template_file_not_in_theme() {

		/**
		 * Simulate the activation of a theme with the templates directory overridden.
		 */
		$original_template   = get_option( 'template', '' );
		$original_stylesheet = get_option( 'stylesheet', '' );
		update_option( 'template', 'fake' );
		update_option( 'stylesheet', 'fake' );
		$this->_create_theme_override_directory( 'fake' );

		$this->assertEquals(
			llms()->plugin_path() . '/templates/single-certificate.php',
			llms_template_file_path( 'single-certificate.php' )
		);

		$this->_delete_theme_override_directory( 'fake' );

		update_option( 'template', $original_template );
		update_option( 'stylesheet', $original_stylesheet );

	}

	/**
	 * Test llms_template_file_path() when passing an absolute template directory (not relative to the plugin dir).
	 *
	 * @since 5.9.0
	 *
	 * @return void
	 */
	public function test_llms_template_file_path_template_directory_absolute() {
		$this->_delete_theme_override_directory( 'fake' );

		$this->assertEquals(
			'/path/to/absolute/single-certificate.php',
			llms_template_file_path( 'single-certificate.php', 'path/to/absolute', true )
		);

		/**
		 * Simulate the activation of a theme with the templates directory overridden.
		 */
		$original_template   = get_option( 'template', '' );
		$original_stylesheet = get_option( 'stylesheet', '' );
		update_option( 'template', 'fake' );
		update_option( 'stylesheet', 'fake' );
		$this->_create_theme_override_directory( 'fake' );
		wp_cache_delete( 'theme-override-directories', 'llms_template_functions' );

		$this->assertEquals(
			get_theme_root() . '/fake/lifterlms/',
			llms_template_file_path( '', 'path/to/absolute', true )
		);

		$this->_delete_theme_override_directory( 'fake' );

		update_option( 'template', $original_template );
		update_option( 'stylesheet', $original_stylesheet );

	}

	/**
	 * Creates a theme and override lifterlms template directory.
	 *
	 * @since 4.8.0
	 * @since 5.9.0 always remove the theme directory if it already exists.
	 *
	 * @param string $theme_dir_name Theme directory name.
	 * @return void
	 */
	private function _create_theme_override_directory( $theme_dir_name ) {
		$theme_root = get_theme_root();
		$this->_delete_theme_override_directory( 'fake' );
		mkdir( "{$theme_root}/{$theme_dir_name}/lifterlms", 0777, true );
	}

	/**
	 * Deletes a theme and override lifterlms template directory.
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
