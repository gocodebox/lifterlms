<?php
/**
 * Admin Assets Class
 *
 * Sets up admin menu items.
 * @since   1.0.0
 * @version 3.2.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Admin_ImportExport {

	public function __construct() {
		add_action( 'init', array( $this, 'upload_import' ) );
	}


	public static function output() {
		llms_get_template( 'admin/scaffolding/import-export.php' );
	}

	public function upload_import() {

		if ( ! isset( $_FILES[ 'llms_import' ] ) || ! $_FILES[ 'llms_import' ] ) {
			return;
		}

		$raw = file_get_contents( $_FILES[ 'llms_import' ][ 'tmp_name' ] );

		$s = new LLMS_Scaffold( $raw );
		$s->build();
		var_dump( $s->get_results() );


	}

}

return new LLMS_Admin_ImportExport();
