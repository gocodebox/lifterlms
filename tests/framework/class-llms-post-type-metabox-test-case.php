<?php
/**
 * Unit Test Case with tests and utilities specific to testing LifterLMS post type Metabox classes.
 * @since [version]
 * @version [version]
 */

require_once 'class-llms-unit-test-case.php';

class LLMS_PostTypeMetaboxTestCase extends LLMS_UnitTestCase {

	/**
	 * @since [version]
	 */
	public static function setUpBeforeClass() {
		// manually include required files
		include_once LLMS_PLUGIN_DIR . 'includes/admin/post-types/meta-boxes/fields/llms.class.meta.box.fields.php';
		include_once LLMS_PLUGIN_DIR . 'includes/admin/post-types/meta-boxes/fields/llms.interface.meta.box.field.php';
		include_once LLMS_PLUGIN_DIR . 'includes/abstracts/abstract.llms.admin.metabox.php';
		include_once LLMS_PLUGIN_DIR . 'includes/admin/class.llms.admin.post-types.php';
		( new LLMS_Admin_Post_Types() )->include_post_type_metabox_class();
	}

}
