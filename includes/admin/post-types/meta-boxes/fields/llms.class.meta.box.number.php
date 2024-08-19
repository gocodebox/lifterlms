<?php
/**
 * Meta box Field: Number
 *
 * @package LifterLMS/Admin/PostTypes/MetaBoxes/Fields/Classes
 *
 * @since Unknown
 * @version Unknown
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Metabox_Number_Field class
 *
 * @since Unknown
 */
class LLMS_Metabox_Number_Field extends LLMS_Metabox_Field implements Meta_Box_Field_Interface {


	public function __construct( $_field ) {

		$this->field = $_field;
	}

	/**
	 * outputs the Html for the given field
	 *
	 * @return void
	 * @since    1.0.0
	 * @version  3.16.0
	 */
	public function output() {

		global $post;

		parent::output();

		// Clear an invalid value, usually from a clone or import.
		if (
			is_numeric( $this->meta ) &&
			isset( $this->field['min'] ) &&
			is_numeric( $this->field['min'] ) &&
			$this->field['min'] > 0 &&
			$this->meta < $this->field['min'] ) {
			$this->meta = '';
		}
		?>

		<input type="number"
			<?php if ( isset( $this->field['min'] ) ) : ?>
			min="<?php echo esc_attr( $this->field['min'] ); ?>"
			<?php endif; ?>
			<?php if ( isset( $this->field['max'] ) ) : ?>
			max="<?php echo esc_attr( $this->field['max'] ); ?>"
			<?php endif; ?>
			name="<?php echo esc_attr( $this->field['id'] ); ?>"
			id="<?php echo esc_attr( $this->field['id'] ); ?>"
			class="<?php echo esc_attr( $this->field['class'] ); ?>"
			value="<?php echo esc_attr( $this->meta ); ?>"
			size="30"
			<?php if ( isset( $this->field['step'] ) ) : ?>
			step="<?php echo esc_attr( $this->field['step'] ); ?>"
			<?php endif; ?>
			<?php if ( isset( $this->field['required'] ) && $this->field['required'] ) : ?>
			required="required"
			<?php endif; ?>
		/>

		<?php
		parent::close_output();
	}
}

