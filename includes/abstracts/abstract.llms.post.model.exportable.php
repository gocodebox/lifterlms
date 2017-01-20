<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

abstract class LLMS_Post_Model_Exportable {

	/**
	 * Trigger an export download of the given post type
	 * @return   void
	 * @since    3.3.0
	 * @version  3.3.0
	 */
	public function export() {

		// if post type doesnt support exporting don't proceed
		if ( ! $this->is_exportable() ) {
			return;
		}

		$title = str_replace ( ' ', '-', $this->get( 'title' ) );
		$title = preg_replace( '/[^a-zA-Z0-9-]/', '', $title );

		$filename = apply_filters( 'llms_post_model_export_filename', $title . '_' . current_time( 'Ymd' ), $this );

		header( 'Content-type: application/json' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '.json"' );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );

		echo json_encode( $this );

		die();

	}

}
