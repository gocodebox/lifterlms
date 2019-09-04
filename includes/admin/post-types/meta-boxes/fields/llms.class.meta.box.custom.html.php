<?php
/**
 * Metabox Field: Custom HTML
 *
 * @since Unknown
 * @version Unknown
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Metabox_Custom_Html_Field
 *
 * @since Unknown
 */
class LLMS_Metabox_Custom_Html_Field extends LLMS_Metabox_Field implements Meta_Box_Field_Interface {

	/**
	 * Class constructor
	 *
	 * @param array $_field Array containing information about field
	 */
	public function __construct( $_field ) {

		$this->field = $_field;
	}

	/**
	 * outputs the Html for the given field
	 *
	 * @return void
	 */
	public function output() {

		global $post;

		parent::output();
		echo $this->field['value'];
		parent::close_output();
	}
}

