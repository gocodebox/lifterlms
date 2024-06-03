<?php
/**
 * Meta box Field: Color picker
 *
 * @package LifterLMS/Admin/PostTypes/MetaBoxes/Fields/Classes
 *
 * @since Unknown
 * @version Unknown
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Metabox_Color_Field class
 *
 * @since Unknown
 */
class LLMS_Metabox_Color_Field extends LLMS_Metabox_Field implements Meta_Box_Field_Interface {

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

		parent::output();

		if ( ! $this->meta ) {
			$this->meta = $this->field['value'];
		}
		?>
		<input class="color-picker" type="text" name="<?php echo esc_attr( $this->field['id'] ); ?>" id="<?php echo esc_attr( $this->field['id'] ); ?>" value="<?php echo esc_attr( $this->meta ); ?>" data-default-color="<?php echo esc_attr( $this->field['value'] ); ?>"/>
			<br /><span class="description"><?php echo wp_kses_post( $this->field['desc'] ); ?></span>
		<?php
		parent::close_output();
	}
}

