<?php
/**
 * Meta box Field: Basic Editor
 *
 * @package LifterLMS/Admin/PostTypes/MetaBoxes/Fields/Classes
 *
 * @since 8.0.0
 */

defined( 'ABSPATH' ) || exit;

class LLMS_Metabox_Basic_Editor_Field extends LLMS_Metabox_Field implements Meta_Box_Field_Interface {

	public function __construct( $_field ) {

		$this->field = $_field;
	}

	/**
	 * outputs the Html for the given field
	 *
	 * @return void
	 */
	public function output() {

		parent::output(); ?>

		<div
			data-name="<?php echo esc_attr( $this->field['id'] ); ?>"
			class="llms-editable-title llms-basic-editor llms-input-formatting"
			data-attribute="title"
			<?php if ( array_key_exists( 'placeholder', $this->field ) && $this->field['placeholder'] ) : ?>
				data-placeholder="<?php echo esc_attr( $this->field['placeholder'] ); ?>"
			<?php endif; ?>
		><?php echo wp_kses( $this->meta, LLMS_ALLOWED_HTML_PRICES ); ?></div>

		<input type="hidden" name="<?php echo esc_attr( $this->field['id'] ); ?>" value="<?php echo esc_attr( $this->meta ); ?>" />

		<?php
		parent::close_output();
	}
}

