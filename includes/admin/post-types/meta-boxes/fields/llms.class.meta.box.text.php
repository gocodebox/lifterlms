<?php
/**
 * Metabox Field: Text
 *
 * @since Unknown
 * @version Unknown
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Metabox_Text_Field
 *
 * @since Unknown
 */
class LLMS_Metabox_Text_Field extends LLMS_Metabox_Field implements Meta_Box_Field_Interface {


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
		parent::output(); ?>

		<input type="text"
			name="<?php echo $this->field['id']; ?>"
			id="<?php echo $this->field['id']; ?>"
			<?php if ( array_key_exists( 'required', $this->field ) && $this->field['required'] ) : ?>
				required="required"
			<?php endif; ?>
			class="<?php echo esc_attr( $this->field['class'] ); ?>"
			value="<?php echo htmlentities( $this->meta ); ?>" size="30"
			<?php if ( isset( $this->field['required'] ) && $this->field['required'] ) : ?>
			required="required"
			<?php endif; ?>
		/>

		<?php
		parent::close_output();
	}
}

