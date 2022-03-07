<?php
// Require main test case.
require_once 'class-llms-unit-test-case.php';

/**
 * Unit Test Case with tests and utilities specific to testing LifterLMS post type Metabox classes.
 *
 * @since 3.33.0
*/
class LLMS_PostTypeMetaboxTestCase extends LLMS_UnitTestCase {

	/**
	 * Require all necessary files.
	 *
	 * @since 3.33.0
	 * @since 3.36.1 Conditionally require LLMS_Admin_Meta_Boxes.
	 * @since 3.37.12 Call parent method.
	 * @since 5.3.3 Renamed from `setUpBeforeClass()` for compat with WP core changes.
	 * @since 6.0.0 Removed loading of class files that don't instantiate their class in favor of autoloading.
	 *
	 * @return void
	 */
	public static function set_up_before_class() {

		parent::set_up_before_class();

		// Manually include required files.
		include_once LLMS_PLUGIN_DIR . 'includes/admin/class.llms.admin.post-types.php';
		if ( ! class_exists( 'LLMS_Admin_Meta_Boxes' ) ) {
			( new LLMS_Admin_Post_Types() )->include_post_type_metabox_class();
		}

	}

	/**
	 * Metabox utility function to add the metabox nonce field to an array of data.
	 *
	 * @since 3.36.1
	 *
	 * @param array $data Data array.
	 * @param bool  $real If true, uses a real nonce. Otherwise uses a fake nonce (useful for testing negative cases).
	 * @return array
	 */
	protected function add_nonce_to_array( $data = array(), $real = true ) {

		$nonce_string = $real ? wp_create_nonce( 'lifterlms_save_data' ) : wp_create_nonce( 'fake' );

		return wp_parse_args( $data, array(
			'lifterlms_meta_nonce' => $nonce_string,
		) );

	}

}
