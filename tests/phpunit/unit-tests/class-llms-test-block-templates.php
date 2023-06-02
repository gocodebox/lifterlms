<?php
/**
 * Test LLMS_Block_Templates
 *
 * @package LifterLMS/Tests
 *
 * @group block_templates
 *
 * @since 5.8.0
 * @version [version]
 */
class LLMS_Test_Block_Templates extends LLMS_UnitTestCase {

	/**
	 * Setup the test case.
	 *
	 * @since 5.8.0
	 *
	 * @return void
	 */
	public function set_up() {

		$this->main = LLMS_Block_Templates::instance();
		parent::set_up();

	}

	/**
	 * Test __construct().
	 *
	 * @since 5.8.0
	 *
	 * @return void
	 */
	public function test_constructor() {

		// Reset data.
		LLMS_Unit_Test_Util::set_private_property( $this->main, 'block_templates_config', null );
		$this->assertNull( LLMS_Unit_Test_Util::get_private_property_value( $this->main, 'block_templates_config' ) );

		remove_filter( 'get_block_templates', array( $this->main, 'add_llms_block_templates' ), 10 );
		remove_filter( 'pre_get_block_file_template', array( $this->main, 'maybe_return_blocks_template' ), 10 );
		remove_action( 'admin_enqueue_scripts', array( $this->main, 'localize_blocks' ), 9999 );

		LLMS_Unit_Test_Util::call_method( $this->main, '__construct' );

		// Configuration runs.
		$this->assertNotNull( LLMS_Unit_Test_Util::get_private_property_value( $this->main, 'block_templates_config' ) );

		// Hooks added.
		$this->assertEquals( 10, has_filter( 'get_block_templates', array( $this->main, 'add_llms_block_templates' ) ) );
		$this->assertEquals( 10, has_filter( 'pre_get_block_file_template', array( $this->main, 'maybe_return_blocks_template' ) ) );
		$this->assertEquals( 9999, has_action( 'admin_enqueue_scripts', array( $this->main, 'localize_blocks' ) ) );

	}

	/**
	 * Test configure_block_templates().
	 *
	 * @since 5.9.0
	 *
	 * @return void
	 */
	public function test_configure_block_templates() {

		// Reset data.
		LLMS_Unit_Test_Util::set_private_property( $this->main, 'block_templates_config', null );
		$this->assertNull( LLMS_Unit_Test_Util::get_private_property_value( $this->main, 'block_templates_config' ) );

		// Run configurator.
		$this->main->configure_block_templates();

		$block_templates_config = array(
			llms()->plugin_path() . '/templates/' . $this->main::LLMS_BLOCK_TEMPLATES_DIRECTORY_NAME => array(
				'slug_prefix'       => $this->main::LLMS_BLOCK_TEMPLATES_PREFIX,
				'namespace'         => $this->main::LLMS_BLOCK_TEMPLATES_NAMESPACE,
				'blocks_dir'        => $this->main::LLMS_BLOCK_TEMPLATES_DIRECTORY_NAME, // Relative to the plugin's templates directory.
				'admin_blocks_l10n' => LLMS_Unit_Test_Util::call_method( $this->main, 'block_editor_l10n' ),
				'template_titles'   => LLMS_Unit_Test_Util::call_method( $this->main, 'template_titles' ),
			),
		);

		$this->assertNotNull( LLMS_Unit_Test_Util::get_private_property_value( $this->main, 'block_templates_config' ) );
		$this->assertEquals(
			$block_templates_config,
			LLMS_Unit_Test_Util::get_private_property_value( $this->main, 'block_templates_config' )
		);

		// Check that the configuration can be extended through a filter
		$additional_configuration = array(
			'/some/path/to' => array(
				'slug_prefix'       => 'some-slug-prefix_',
				'namespace'         => 'some/namespace',
				'blocks_dir'        => 'blocks-dir', // Relative to the plugin's templates directory.
				'admin_blocks_l10n' => array( 'string-1' => 'String 1' ),
				'template_titles'   => array( 'some-archive' => 'Some Archive title' ),
			),
		);
		$add_configuration_cb = function( $config ) use ( $additional_configuration ) {
			return array_merge( $config, $additional_configuration );
		};
		add_filter( 'llms_block_templates_config', $add_configuration_cb );

		// Run configurator again.
		$this->main->configure_block_templates();
		remove_filter( 'llms_block_templates_config', $add_configuration_cb );

		$this->assertNotNull( LLMS_Unit_Test_Util::get_private_property_value( $this->main, 'block_templates_config' ) );
		$this->assertEquals(
			array_merge( $block_templates_config, $additional_configuration ),
			LLMS_Unit_Test_Util::get_private_property_value( $this->main, 'block_templates_config' )
		);

		// Run configurator again, without the filter, to reinit the configuration.
		$this->main->configure_block_templates();
		$this->assertEquals(
			$block_templates_config,
			LLMS_Unit_Test_Util::get_private_property_value( $this->main, 'block_templates_config' )
		);

	}

	/**
	 * Test generate_template_slug_from_path().
	 *
	 * @since 5.9.0
	 * @since [version] Remove unneded tests after switching `generate_template_slug_from_path()` logic to use `basename()`.
	 *
	 * @return void
	 */
	public function test_generate_template_slug_from_path() {

		$block_templates_config = LLMS_Unit_Test_Util::get_private_property_value( $this->main, 'block_templates_config' );
		$this->assertNotNull( $block_templates_config );
		$this->assertNotEmpty( reset( $block_templates_config )['slug_prefix'] );

		// Expecting the slug to be the template name, without the extension, plus the configured slug prefix.
		$this->assertEquals(
			reset( $block_templates_config )['slug_prefix'] . 'template',
			LLMS_Unit_Test_Util::call_method(
				$this->main,
				'generate_template_slug_from_path',
				array(
					key( $block_templates_config ) . '/template.html',
				)
			)
		);

	}

	/**
	 * Test generate_template_namespace_from_path().
	 *
	 * @since 5.9.0
	 *
	 * @return void
	 */
	public function test_generate_template_namespace_from_path() {

		$block_templates_config = LLMS_Unit_Test_Util::get_private_property_value( $this->main, 'block_templates_config' );
		$this->assertNotNull( $block_templates_config );
		$this->assertNotEmpty( reset( $block_templates_config )['namespace'] );

		// Expecting the namespace to be the class constant LLMS_BLOCK_TEMPLATES_NAMESPACE.
		$this->assertEquals(
			$this->main::LLMS_BLOCK_TEMPLATES_NAMESPACE,
			LLMS_Unit_Test_Util::call_method(
				$this->main,
				'generate_template_namespace_from_path',
				array(
					key( $block_templates_config ) . '/template.html',
				)
			)
		);

		// If you pass a path which is not in the configuration I expect an empty namespace.
		$this->assertEquals(
			'',
			LLMS_Unit_Test_Util::call_method(
				$this->main,
				'generate_template_namespace_from_path',
				array(
					'/whateverpath/template.html',
				)
			)
		);

	}

	/**
	 * Test generate_template_prefix_from_path().
	 *
	 * @since 5.9.0
	 *
	 * @return void
	 */
	public function test_generate_template_prefix_from_path() {

		$block_templates_config = LLMS_Unit_Test_Util::get_private_property_value( $this->main, 'block_templates_config' );
		$this->assertNotNull( $block_templates_config );
		$this->assertNotEmpty( reset( $block_templates_config )['slug_prefix'] );

		// Expecting the prefix to be the class constant LLMS_BLOCK_TEMPLATES_DIRECTORY_NAME.
		$this->assertEquals(
			$this->main::LLMS_BLOCK_TEMPLATES_DIRECTORY_NAME ,
			LLMS_Unit_Test_Util::call_method(
				$this->main,
				'generate_template_blocks_dir_from_path',
				array(
					key( $block_templates_config ) . '/template.html',
				)
			)
		);

		// If you pass a path which is not in the configuration I expect an empty blocks directory.
		$this->assertEquals(
			'',
			LLMS_Unit_Test_Util::call_method(
				$this->main,
				'generate_template_blocks_dir_from_path',
				array(
					'/whateverpath/template.html',
				)
			)
		);

	}

	/**
	 * Test generate_template_prefix_from_path().
	 *
	 * @since 5.9.0
	 *
	 * @return void
	 */
	public function test_generate_blocks_dir_from_path() {

		$block_templates_config = LLMS_Unit_Test_Util::get_private_property_value( $this->main, 'block_templates_config' );
		$this->assertNotNull( $block_templates_config );
		$this->assertNotEmpty( reset( $block_templates_config )['blocks_dir'] );

		// Expecting the prefix to be the class constant LLMS_BLOCK_TEMPLATES_PREFIX
		$this->assertEquals(
			$this->main::LLMS_BLOCK_TEMPLATES_PREFIX ,
			LLMS_Unit_Test_Util::call_method(
				$this->main,
				'generate_template_prefix_from_path',
				array(
					key( $block_templates_config ) . '/template.html',
				)
			)
		);

		// If you pass a path which is not in the configuration I expect an empty prefix.
		$this->assertEquals(
			'',
			LLMS_Unit_Test_Util::call_method(
				$this->main,
				'generate_template_prefix_from_path',
				array(
					'/whateverpath/template.html',
				)
			)
		);

	}

	/**
	 * Test block_template_config_property_from_path().
	 *
	 * @since 5.9.0
	 *
	 * @return void
	 */
	public function test_block_template_config_property_from_path() {

		$block_templates_config = LLMS_Unit_Test_Util::get_private_property_value( $this->main, 'block_templates_config' );
		$this->assertNotNull( $block_templates_config );

		// Non-existent property for an existent path => empty string.
		$this->assertEquals(
			'',
			LLMS_Unit_Test_Util::call_method(
				$this->main,
				'block_template_config_property_from_path',
				array(
					key( $block_templates_config ) . '/some/block/template.html',
					'this-property-does-not-exist'
				)
			)
		);

		// Non-existent property for a non-existent path => empty string.
		$this->assertEquals(
			'',
			LLMS_Unit_Test_Util::call_method(
				$this->main,
				'block_template_config_property_from_path',
				array(
					'/some/block/template.html',
					'this-property-does-not-exist'
				)
			)
		);

		// Existent property for non-existent path => empty string.
		$this->assertEquals(
			'',
			LLMS_Unit_Test_Util::call_method(
				$this->main,
				'block_template_config_property_from_path',
				array(
					'/some/block/template.html',
					'slug_prefix'
				)
			)
		);

		// Existent property for existent path => property value.
		$this->assertEquals(
			reset( $block_templates_config )['slug_prefix'],
			LLMS_Unit_Test_Util::call_method(
				$this->main,
				'block_template_config_property_from_path',
				array(
					key( $block_templates_config ) . '/some/block/template.html',
					'slug_prefix'
				)
			)
		);

	}

	/**
	 * Test convert_slug_to_title().
	 *
	 * @since 5.9.0
	 *
	 * @return string Human friendly title converted from the slug.
	 */
	public function test_convert_slug_to_title() {

		$block_templates_config = LLMS_Unit_Test_Util::get_private_property_value( $this->main, 'block_templates_config' );
		$this->assertNotNull( $block_templates_config );

		// Existent slugs.
		$titles = reset( $block_templates_config )['template_titles'];
		foreach ( $titles as $slug => $title ) {
			$this->assertEquals(
				$title,
				LLMS_Unit_Test_Util::call_method(
					$this->main,
					'convert_slug_to_title',
					array(
						$slug,
					)
				),
				$slug
			);
		}

		// Non-existent slug.
		$this->assertEquals(
			"This Slug Does Not Exist",
			LLMS_Unit_Test_Util::call_method(
				$this->main,
				'convert_slug_to_title',
				array(
					'this-slug-does-not-exist',
				)
			),
			$slug
		);

	}

	/**
	 * Test localize_blocks().
	 *
	 * @since 5.9.0
	 *
	 * @return void
	 */
	public function test_localize_blocks() {
		global $wp_scripts;

		$block_templates_config = LLMS_Unit_Test_Util::get_private_property_value( $this->main, 'block_templates_config' );
		$this->assertNotNull( $block_templates_config );

		// Add a fake llms-blocks-editor script.
		$wp_scripts->registered['llms-blocks-editor'] = new _WP_Dependency(
			'llms-blocks-editor',
			'/fake/',
			array(),
			'ver',
			array()
		);

		// Check localization went through.
		$this->assertTrue( $this->main->localize_blocks() );

		// Check localization is what we expect.
		$this->assertEquals(
			sprintf(
				'var %1$s = %2$s;',
				'llmsBlockTemplatesL10n',
				wp_json_encode(
					array_map(
						function( $value ) {
							return html_entity_decode( (string) $value, ENT_QUOTES, 'UTF-8' );
						},
						array_merge( ...array_column( $block_templates_config, 'admin_blocks_l10n' ) )
					)
				)
			),

			$wp_scripts->get_data( 'llms-blocks-editor', 'data' ),

		);

	}

}
