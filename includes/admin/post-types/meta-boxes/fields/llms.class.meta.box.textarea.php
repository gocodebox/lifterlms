<?php
/**
 * Meta box Field: Textarea
 *
 * @package LifterLMS/Admin/PostTypes/MetaBoxes/Fields/Classes
 *
 * @since Unknown
 * @version Unknown
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Metabox_Textarea_Field class
 *
 * @since Unknown
 */
class LLMS_Metabox_Textarea_Field extends LLMS_Metabox_Field implements Meta_Box_Field_Interface {

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

		parent::output(); ?>

		<textarea name="<?php echo esc_attr( $this->field['id'] ); ?>" id="<?php echo esc_attr( $this->field['id'] ); ?>" cols="60" rows="4"
									<?php
									if ( isset( $this->field['required'] ) && $this->field['required'] ) :
										?>
			required="required"<?php endif; ?>><?php echo esc_textarea( $this->meta ); ?></textarea>

		<?php
		parent::close_output();
	}
}

