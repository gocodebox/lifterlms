<?php
/**
 * Test Admin Site Health integration
 *
 * @package LifterLMS/Tests/Admin
 *
 * @group admin
 * @group status
 *
 * @since [version]
 */
class LLMS_Test_Admin_Site_Health extends LLMS_Unit_Test_Case {

	/**
	 * Instance of the class being tested.
	 *
	 * @var string
	 */
	protected $main = 'LLMS_Admin_Site_Health';

	/**
	 * Set up before class
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public static function set_up_before_class() {

		// WP_Debug_Data is lazy-loaded in production; load explicitly here so the
		// add_debug_info() guard sees it and the tests can exercise the real path.
		if ( file_exists( ABSPATH . 'wp-admin/includes/class-wp-debug-data.php' ) ) {
			require_once ABSPATH . 'wp-admin/includes/class-wp-debug-data.php';
		}

		include_once LLMS_PLUGIN_DIR . 'includes/admin/class-llms-admin-site-health.php';

	}

	/**
	 * Test that the LLMS sections are added to the debug information array.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_add_debug_info_has_llms_sections() {

		$info = LLMS_Admin_Site_Health::add_debug_info( array() );

		$expected = array(
			'lifterlms-settings',
			'lifterlms-gateways',
			'lifterlms-integrations',
			'lifterlms-templates',
			'lifterlms-constants',
		);

		foreach ( $expected as $section_id ) {
			$this->assertArrayHasKey( $section_id, $info, "$section_id section missing." );
			$this->assertNotEmpty( $info[ $section_id ]['label'], "$section_id has no label." );
			$this->assertArrayHasKey( 'fields', $info[ $section_id ], "$section_id has no fields." );
			$this->assertNotEmpty( $info[ $section_id ]['fields'], "$section_id has empty fields." );
		}
	}

	/**
	 * Test that init() registers the debug_information filter.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_init_registers_filter() {

		remove_filter( 'debug_information', array( 'LLMS_Admin_Site_Health', 'add_debug_info' ) );

		LLMS_Admin_Site_Health::init();

		$this->assertNotFalse( has_filter( 'debug_information', array( 'LLMS_Admin_Site_Health', 'add_debug_info' ) ) );
	}

	/**
	 * Test that core-provided sections are not duplicated.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_add_debug_info_skips_core_sections() {

		$info = LLMS_Admin_Site_Health::add_debug_info( array() );

		$not_expected = array(
			'wp-constants',
			'wp-files',
			'wp-database',
			'wp-media',
			'wp-server',
			'wp-dropins',
		);

		foreach ( $not_expected as $section_id ) {
			$this->assertArrayNotHasKey( $section_id, $info, "$section_id should not be added." );
		}
	}

	/**
	 * Test that the template overrides section lists overrides correctly.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_add_debug_info_templates_section() {

		$info    = LLMS_Admin_Site_Health::add_debug_info( array() );
		$section = $info['lifterlms-templates'];

		$this->assertNotEmpty( $section['show_count'] );
		$this->assertArrayHasKey( 'overrides', $section['fields'] );

		// Even when no overrides exist the field value is a defined string, never empty.
		$this->assertNotEmpty( $section['fields']['overrides']['value'] );
	}

	/**
	 * Test that sensitive keys are marked as private.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_sensitive_keys_are_private() {

		$info    = LLMS_Admin_Site_Health::add_debug_info( array() );
		$fields  = $info['lifterlms-settings']['fields'];
		$private = array();

		foreach ( $fields as $key => $field ) {
			if ( ! empty( $field['private'] ) ) {
				$private[] = $key;
			}
		}

		// At least one URL or email related key should be flagged as private.
		$this->assertNotEmpty(
			array_filter(
				$private,
				function ( $key ) {
					return (bool) preg_match( '/email|url|login|permalink/i', $key );
				}
			)
		);
	}

	/**
	 * Test that the returned info still contains previously registered sections.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_add_debug_info_preserves_existing_sections() {

		$existing = array(
			'wp-core' => array(
				'label'  => 'WordPress',
				'fields' => array( 'version' => array( 'label' => 'Version', 'value' => '6.5' ) ),
			),
		);

		$info = LLMS_Admin_Site_Health::add_debug_info( $existing );

		$this->assertArrayHasKey( 'wp-core', $info );
		$this->assertSame( 'WordPress', $info['wp-core']['label'] );
		$this->assertArrayHasKey( 'lifterlms-settings', $info );
	}

	/**
	 * Test that every LifterLMS section renders even when its source data is empty.
	 *
	 * WordPress core skips any Site Health section with empty fields. On a fresh install
	 * with no gateways or integrations the source maps are empty, so each section must
	 * still carry a placeholder row.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_add_debug_info_sections_visible_when_data_empty() {

		$info = LLMS_Admin_Site_Health::add_debug_info( array() );

		$expected = array(
			'lifterlms-settings',
			'lifterlms-gateways',
			'lifterlms-integrations',
			'lifterlms-templates',
			'lifterlms-constants',
		);

		foreach ( $expected as $section_id ) {
			$this->assertNotEmpty( $info[ $section_id ]['fields'], "$section_id must render even with no data." );
		}
	}

	/**
	 * Test that all LifterLMS sections render after every non-LifterLMS section.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_add_debug_info_pins_llms_sections_to_bottom() {

		$existing = array(
			'wp-core'     => array( 'label' => 'WordPress', 'fields' => array( 'v' => array( 'label' => 'Version', 'value' => '7.0' ) ) ),
			'wp-database' => array( 'label' => 'Database', 'fields' => array( 'v' => array( 'label' => 'Version', 'value' => '10.6' ) ) ),
		);

		$info = LLMS_Admin_Site_Health::add_debug_info( $existing );
		$keys = array_keys( $info );

		// Verify that once the first LifterLMS section appears, no non-LifterLMS section follows.
		$seen_llms = false;
		foreach ( $keys as $slug ) {
			if ( 0 === strpos( $slug, 'lifterlms-' ) ) {
				$seen_llms = true;
			} elseif ( $seen_llms ) {
				$this->fail( "Non-LifterLMS section '$slug' appeared after a LifterLMS section. Expected LifterLMS sections only at the bottom." );
			}
		}

		$this->assertTrue( $seen_llms, 'Expected at least one LifterLMS section in debug info.' );
	} // end test_add_debug_info_pins_llms_sections_to_bottom

} // class LLMS_Test_Admin_Site_Health
