<?php
/**
 * Metabox Field: Text
 *
 * @since Unknown
 * @version 3.36.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Metabox_Text_Field
 *
 * @since Unknown
 * @since 3.36.0 When outputting the field's value convert quotes (double and single) HTML entities back to characters.
 */
class LLMS_Metabox_Text_Field extends LLMS_Metabox_Field implements Meta_Box_Field_Interface {


	public function __construct( $_field ) {

		$this->field = $_field;
	}

	/**
	 * outputs the Html for the given field
	 *
	 * @since 3.36.0 Convert quotes (double and single) HTML entities back to characters.
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
			value="<?php echo htmlentities( htmlspecialchars_decode( $this->meta, ENT_QUOTES ) ); ?>" size="30"
			<?php if ( isset( $this->field['required'] ) && $this->field['required'] ) : ?>
			required="required"
			<?php endif; ?>
		/>

		<?php
		parent::close_output();
	}
}

