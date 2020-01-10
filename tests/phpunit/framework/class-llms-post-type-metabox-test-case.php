<?php
/**
 * Unit Test Case with tests and utilities specific to testing LifterLMS post type Metabox classes.
 * @since 3.33.0
 * @version 3.36.1
 */

require_once 'class-llms-unit-test-case.php';

class LLMS_PostTypeMetaboxTestCase extends LLMS_UnitTestCase {

	/**
	 * Require all necessary files.
	 *
	 * @since 3.33.0
	 * @since 3.36.1 Conditionally require LLMS_Admin_Meta_Boxes.
	 *
	 * @return void
	 */
	public static function setUpBeforeClass() {
		// manually include required files
		include_once LLMS_PLUGIN_DIR . 'includes/admin/post-types/meta-boxes/fields/llms.class.meta.box.fields.php';
		include_once LLMS_PLUGIN_DIR . 'includes/admin/post-types/meta-boxes/fields/llms.interface.meta.box.field.php';
		include_once LLMS_PLUGIN_DIR . 'includes/abstracts/abstract.llms.admin.metabox.php';
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
	 * @param bool $real If true, uses a real nonce. Otherwise uses a fake nonce (useful for testing negative cases).
	 * @return array
	 */
	protected function add_nonce_to_array( $data = array(), $real = true ) {

		$nonce_string = $real ? wp_create_nonce( 'lifterlms_save_data' ) : wp_create_nonce( 'fake' );

		return wp_parse_args( $data, array(
			'lifterlms_meta_nonce' => $nonce_string,
		) );

	}

}
