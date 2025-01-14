<?php
/**
 * Meta box Field: Search
 *
 * @package LifterLMS/Admin/PostTypes/MetaBoxes/Fields/Classes
 *
 * @since Unknown
 * @version Unknown
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Metabox_Search_Field
 *
 * @since Unknown
 */
class LLMS_Metabox_Search_Field extends LLMS_Metabox_Field implements Meta_Box_Field_Interface {

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

		<select
			id="<?php echo esc_attr( $this->field['id'] ); ?>"
			name="<?php echo esc_attr( $this->field['id'] ); ?>"
			class="<?php echo esc_attr( $this->field['class'] ); ?>"
		>
		</select>
		<?php
		parent::close_output();
	}
}

