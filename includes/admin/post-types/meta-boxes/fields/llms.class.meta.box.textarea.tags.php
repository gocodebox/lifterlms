<?php
/**
 * Meta box Field: Textarea with Tags
 *
 * @package LifterLMS/Admin/PostTypes/MetaBoxes/Fields/Classes
 *
 * @since Unknown
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Metabox_Textarea_W_Tags_Field class
 *
 * @since Unknown
 */
class LLMS_Metabox_Textarea_W_Tags_Field extends LLMS_Metabox_Field implements Meta_Box_Field_Interface {

	/**
	 * Class constructor
	 *
	 * @since Unknown
	 *
	 * @param array $_field Array containing information about field
	 * @return void
	 */
	public function __construct( $_field ) {
		$this->field = $_field;
	}

	/**
	 * Outputs the Html for the given field
	 *
	 * @since Unknown
	 * @since [version] Don't double-output the field description.
	 *
	 * @return void
	 */
	public function output() {

		global $post;

		parent::output(); ?>
		<textarea name="<?php echo $this->field['id']; ?>" id="<?php echo $this->field['id']; ?>" cols="60" rows="4"><?php echo $this->meta; ?></textarea>
		<?php
		parent::close_output();
	}
}

