<?php
/**
 * Metabox Field: Textarea
 *
 * @since Unknown
 * @version Unknown
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Metabox_Textarea_Field
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

		<textarea name="<?php echo $this->field['id']; ?>" id="<?php echo $this->field['id']; ?>" cols="60" rows="4"
								   <?php
									if ( isset( $this->field['required'] ) && $this->field['required'] ) :
										?>
			required="required"<?php endif; ?>><?php echo $this->meta; ?></textarea>

		<?php
		parent::close_output();
	}
}

