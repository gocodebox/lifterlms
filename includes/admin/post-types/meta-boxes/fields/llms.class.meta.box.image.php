<?php
/**
 * Meta box field: Image meta box field
 *
 * @package LifterLMS/Admin/PostTypes/MetaBoxes/Fields/Classes
 *
 * @since Unknown
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * Image meta box field class
 *
 * @since Unknown
 */
class LLMS_Metabox_Image_Field extends LLMS_Metabox_Field implements Meta_Box_Field_Interface {

	/**
	 * Class constructor
	 *
	 * @since Unknown
	 *
	 * @param array $_field Array containing information about field
	 *
	 * @return void
	 */
	public function __construct( $_field ) {
		$this->field = $_field;
	}

	/**
	 * Output the HTML for the field
	 *
	 * @since Unknown
	 * @since 3.24.0 Unknown.
	 * @since [version] Use `llms_admin_field_upload()` for field display.
	 *              Retrieve default image source paths using engagement class helper method: `get_default_image()`.
	 *
	 * @return void
	 */
	public function output() {

		parent::output();

		$image    = '';
		$imgclass = '';

		if ( is_numeric( $this->meta ) ) {
			$attachment = wp_get_attachment_image_src( $this->meta, 'medium' );
			$image      = $attachment[0];
		}

		if ( '_llms_achievement_image' === $this->field['id'] ) {
			$image    = $image ? $image : llms()->achievements()->get_default_image();
			$imgclass = 'llms_achievement_image';
		} elseif ( '_llms_certificate_image' === $this->field['id'] ) {
			$image    = $image ? $image : llms()->certificates()->get_default_image();
			$imgclass = 'llms_certificate_image';
		}

		llms_admin_field_upload( $this->field['id'], $image, $this->meta, array(
			'class'     => 'upload_' . $this->field['class'] . '_image',
			'img_class' => $imgclass,
		) );

		parent::close_output();
	}
}

