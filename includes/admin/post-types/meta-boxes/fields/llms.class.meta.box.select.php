<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 *
 */
class LLMS_Metabox_Select_Field extends LLMS_Metabox_Field implements Meta_Box_Field_Interface {


	/**
	 * Class constructor
	 * @param array $_field Array containing information about field
	 */
	function __construct( $_field ) {

		$this->field = $_field;
	}

	/**
	 * outputs the Html for the given field
	 * @return HTML
	 * @since    1.0.0
	 * @version  3.1.0 - allow regular key=>val arrays to be passed
	 */
	public function output() {

		global $post;

		parent::output();

		$id = esc_attr( $this->field['id'] );
		$name = $id;

		$allow_null = ( isset( $this->field['allow_null'] ) ) ? $this->field['allow_null'] : true;

		$controls = isset( $this->field['is_controller'] ) ? 'data-is-controller="true"' : '';

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
			<?php echo $controls; ?>
			id="<?php echo $id; ?>"
			name="<?php echo $name; ?>"
			class="<?php echo esc_attr( $this->field['class'] ); ?>"
			<?php if ( array_key_exists( 'multi', $this->field ) && $this->field['multi'] ) : ?>
				multiple="multiple"
			<?php endif; ?>
			<?php foreach ( $attrs as $attr => $attr_val ) { echo ' data-' . $attr . '="' . $attr_val . '"'; } ?>>
			<?php if ( $allow_null ) : ?>
				<option value="">None</option>
			<?php endif; ?>

			<?php if ( isset( $this->field['value'] ) ) : ?>

				<?php foreach ( $this->field['value'] as $key => $option ) :
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
					<option value="<?php echo isset( $option['key'] ) ? $option['key'] : $key; ?>"<?php echo $selected_text ?>><?php echo isset( $option['title'] ) ? $option['title'] : $option; ?></option>

				<?php endforeach; ?>

			<?php endif; ?>
		</select>
		<?php
		parent::close_output();
	}

}
