<?php
/**
 * Meta box Field: Textarea with Tags.
 *
 * @package LifterLMS/Admin/PostTypes/MetaBoxes/Fields/Classes
 *
 * @since Unknown
 * @version [version]
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
	 * @since unknown
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
	 * @since unknown
	 * @since [version] Allow displaying a custom value.
	 *
	 * @return void
	 */
	public function output() {
		parent::output(); ?>

		<textarea name="<?php echo $this->field['id']; ?>" id="<?php echo $this->field['id']; ?>" cols="60" rows="4"><?php echo ! empty( $this->field['value'] ) ? $this->field['value'] : $this->meta; ?></textarea>
		<br /><span class="description"><?php echo $this->field['desc']; ?></span>
		<?php
		parent::close_output();
	}
}

