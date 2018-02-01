<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Metabox_Number_Field extends LLMS_Metabox_Field implements Meta_Box_Field_Interface {


	function __construct( $_field ) {

		$this->field = $_field;
	}

	/**
	 * outputs the Html for the given field
	 * @return   HTML
	 * @since    1.0.0
	 * @version  3.16.0
	 */
	public function output() {

		global $post;

		parent::output(); ?>

		<input type="number"
		<?php
		if ( isset( $this->field['min'] ) ) {
			echo 'min="' . $this->field['min'] . '"';
		}
		if ( isset( $this->field['max'] ) ) {
			echo 'max="' . $this->field['max'] . '"';
		}
		?>
			name="<?php echo $this->field['id']; ?>"
			id="<?php echo $this->field['id']; ?>"
			class="<?php echo esc_attr( $this->field['class'] ); ?>"
			value="<?php echo $this->meta; ?>" size="30"
			<?php if ( isset( $this->field['step'] ) ) : ?>
			step="<?php echo $this->field['step']; ?>"
			<?php endif; ?>
			<?php if ( isset( $this->field['required'] ) && $this->field['required'] ) : ?>
			required="required"
			<?php endif; ?>
		/>

		<?php
		parent::close_output();
	}
}

