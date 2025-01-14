<?php
/**
 * Meta box Field: Textarea with Tags.
 *
 * @package LifterLMS/Admin/PostTypes/MetaBoxes/Fields/Classes
 *
 * @since Unknown
 * @version 6.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Metabox_Textarea_W_Tags_Field class.
 *
 * @since Unknown
 */
class LLMS_Metabox_Textarea_W_Tags_Field extends LLMS_Metabox_Field implements Meta_Box_Field_Interface {

	/**
	 * Class constructor.
	 *
	 * @since Unknown
	 *
	 * @param array $_field Array containing information about field.
	 * @return void
	 */
	public function __construct( $_field ) {
		$this->field = $_field;
	}

	/**
	 * Outputs the Html for the given field.
	 *
	 * @since Unknown
	 * @since 6.0.0 Allow displaying a custom value.
	 *               Added options for defining textarea rows and columns.
	 *
	 * @return void
	 */
	public function output() {
		parent::output();
		$cols = $this->field['cols'] ?? 60;
		$rows = $this->field['rows'] ?? 4;
		?>
		<textarea
			name="<?php echo esc_attr( $this->field['id'] ); ?>"
			id="<?php echo esc_attr( $this->field['id'] ); ?>"
			cols="<?php echo esc_attr( $cols ); ?>"
			rows="<?php echo esc_attr( $rows ); ?>"
			><?php echo ! empty( $this->field['value'] ) ? esc_textarea( $this->field['value'] ) : esc_textarea( $this->meta ); ?></textarea>
		<?php
		parent::close_output();
	}
}

