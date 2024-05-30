<?php
/**
 * Meta box field: Select.
 *
 * @package LifterLMS/Admin/PostTypes/MetaBoxes/Fields/Classes
 *
 * @since Unknown
 * @version 6.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Metabox_Select_Field class.
 *
 * @since Unknown
 */
class LLMS_Metabox_Select_Field extends LLMS_Metabox_Field implements Meta_Box_Field_Interface {


	/**
	 * Class constructor.
	 *
	 * @param array $_field Array containing information about field.
	 */
	public function __construct( $_field ) {

		$this->field = $_field;
	}

	/**
	 * Outputs the Html for the given field.
	 *
	 * @since 1.0.0
	 * @since 3.1.0 Allow regular key=>val arrays to be passed.
	 * @since 6.0.0 Added required attribute when required :D.
	 *
	 * @return void
	 */
	public function output() {

		global $post;

		parent::output();

		$name = $this->field['id'];

		$allow_null = ( isset( $this->field['allow_null'] ) ) ? $this->field['allow_null'] : true;

		if ( array_key_exists( 'multi', $this->field ) ) {
			$name .= '[]';
		}

		$selected = $this->meta;
		if ( array_key_exists( 'selected', $this->field ) ) {
			$selected = $this->field['selected'];
		}
		$attrs = isset( $this->field['data_attributes'] ) ? $this->field['data_attributes'] : array();
		?>

		<select
			<?php echo isset( $this->field['is_controller'] ) ? 'data-is-controller="true"' : ''; ?>
			id="<?php echo esc_attr( $this->field['id'] ); ?>"
			name="<?php echo esc_attr( $name ); ?>"
		<?php if ( ! empty( $this->field['required'] ) && ! $allow_null ) : ?>
			required="required"
		<?php endif; ?>
			class="<?php echo esc_attr( $this->field['class'] ); ?>"
			<?php if ( array_key_exists( 'multi', $this->field ) && $this->field['multi'] ) : ?>
				multiple="multiple"
			<?php endif; ?>
			<?php
			foreach ( $attrs as $attr => $attr_val ) {
				echo ' data-' . esc_attr( $attr ) . '="' . esc_attr( $attr_val ) . '"'; }
			?>
			>
			<?php if ( $allow_null ) : ?>
				<option value="">None</option>
			<?php endif; ?>

			<?php if ( isset( $this->field['value'] ) ) : ?>

				<?php
				foreach ( $this->field['value'] as $key => $option ) :
					$selected_text = '';
					if ( is_array( $selected ) ) {
						if ( in_array( $option['key'], $selected ) ) {
							$selected_text = ' selected="selected" ';
						}
					} elseif ( isset( $option['key'] ) && $option['key'] == $selected ) {
						$selected_text = ' selected="selected" ';
					} elseif ( $key === $selected ) {
						$selected_text = ' selected="selected" ';
					}
					?>
					<option value="<?php echo isset( $option['key'] ) ? esc_attr( $option['key'] ) : esc_attr( $key ); ?>"<?php echo esc_html( $selected_text ); ?>><?php echo isset( $option['title'] ) ? esc_html( $option['title'] ) : esc_html( $option ); ?></option>

				<?php endforeach; ?>

			<?php endif; ?>
		</select>
		<?php
		parent::close_output();
	}
}
