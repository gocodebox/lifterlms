<?php
/**
 * Meta box Field: Checkbox
 *
 * @package LifterLMS/Admin/PostTypes/MetaBoxes/Fields/Classes
 *
 * @since Unknown
 * @version 4.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Metabox_Checkbox_Field class
 *
 * @since Unknown
 * @since 4.0.0 Remove reliance on `LLMS_Svg` class.
 */
class LLMS_Metabox_Checkbox_Field extends LLMS_Metabox_Field implements Meta_Box_Field_Interface {

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
	 * outputs the Html for the given field
	 *
	 * @since Unknown
	 * @since 4.0.0 Remove reliance on `LLMS_Svg` class, refactor to closely match appearance of WP core block editor toggles.
	 *
	 * @return void
	 */
	public function output() {

		global $post;

		parent::output(); ?>

		<div class="llms-switch d-1of4 t-1of4 m-1of2">
			<input
				<?php if ( isset( $this->field['controls'] ) ) : ?>
				data-controls="<?php echo esc_attr( $this->field['controls'] ); ?>"
				<?php endif; ?>
				<?php if ( isset( $this->field['is_controller'] ) ) : ?>
				data-is-controller="true"
				<?php endif; ?>
				name="<?php echo esc_attr( $this->field['id'] ); ?>"
				id="<?php echo esc_attr( $this->field['id'] ); ?>"
				class="llms-toggle llms-toggle-round"
				type="checkbox"
				value="<?php echo esc_attr( $this->field['value'] ); ?>"
				<?php echo ( $this->field['value'] === $this->meta ) ? 'checked' : ''; ?>
			/>

			<label for="<?php echo esc_attr( $this->field['id'] ); ?>"></label>

		</div>
		<?php
		parent::close_output();
	}
}

