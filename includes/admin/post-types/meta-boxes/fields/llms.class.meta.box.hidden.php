<?php
/**
 * Meta box Field: Hidden.
 *
 * @package LifterLMS/Admin/PostTypes/MetaBoxes/Fields/Classes
 *
 * @since 6.0.0
 * @version 6.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Metabox_Hidden_Field class.
 *
 * @since Unknown
 */
class LLMS_Metabox_Hidden_Field extends LLMS_Metabox_Field implements Meta_Box_Field_Interface {

	/**
	 * Class constructor.
	 *
	 * @param array $_field Array containing information about field
	 */
	public function __construct( $_field ) {

		$this->field = $_field;
	}

	/**
	 * Outputs the Html for the given field.
	 *
	 * @return void
	 */
	public function output() {

		parent::output(); ?>

		<input
			name="<?php echo esc_attr( $this->field['id'] ); ?>"
			id="<?php echo esc_attr( $this->field['id'] ); ?>"
			<?php if ( isset( $this->field['required'] ) && $this->field['required'] ) : ?>
			required="required"
			<?php endif; ?>
			type="hidden"
			value="<?php echo esc_attr( $this->field['value'] ); ?>"
		>

		<?php
		parent::close_output();
	}
}
