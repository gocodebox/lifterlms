<?php
/**
 * WP Editor metabox field
 *
 * @since Unknown
 * @version 3.30.3
 */

defined( 'ABSPATH' ) || exit;

/**
 * WP Editor metabox field
 *
 * @since Unknown
 * @since 3.30.3 Explicitly define class properties.
 */
class LLMS_Metabox_Editor_Field extends LLMS_Metabox_Field implements Meta_Box_Field_Interface {

	/**
	 * Array of editor arguments.
	 *
	 * @see _WP_Editors::parse_settings()
	 * @var array
	 * @since 3.11.0
	 */
	public $settings;

	/**
	 * Class constructor
	 *
	 * @param array $_field Array containing information about field
	 */
	public function __construct( $_field ) {

		$this->field    = $_field;
		$this->settings = isset( $this->field['settings'] ) ? $this->field['settings'] : array();

	}

	/**
	 * outputs the Html for the given field
	 *
	 * @return void
	 */
	public function output() {

		global $post;

		parent::output();

		wp_editor( $this->meta, $this->field['id'], $this->settings );

		parent::close_output();

	}
}

