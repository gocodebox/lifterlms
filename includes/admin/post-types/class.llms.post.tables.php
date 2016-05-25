<?php
/**
 * General post table management
 *
 * @since  3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Admin_Post_Tables {

	/**
	 * Constructor
	 *
	 * @since  3.0.0
	 */
	public function __construct() {

		// load all post table classes
		foreach ( glob( LLMS_PLUGIN_DIR . '/includes/admin/post-types/post-tables/*.php' ) as $filename ) {
			include_once $filename;
		}

	}

}
return new LLMS_Admin_Post_Tables();
