<?php
if ( ! defined( 'ABSPATH' )) { exit; }

/**
 *
 */
class LLMS_Metabox_Select_Field extends LLMS_Metabox_Field implements Meta_Box_Field_Interface
{

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
	 */
	public function output() {

		global $post;

		parent::output();

		$id = $name = esc_attr( $this->field['id'] );

		$allow_null = ( isset( $this->field['allow_null'] ) ) ? $this->field['allow_null'] : true;

		if (array_key_exists( 'multi', $this->field )) {
			$name .= '[]';
		}

		$selected = $this->meta;
		if (array_key_exists( 'selected', $this->field )) {
			$selected = $this->field['selected'];
		}

		?>

		<select
			id="<?php echo $id; ?>"
			name="<?php echo $name; ?>"
			class="<?php echo esc_attr( $this->field['class'] ); ?>"
			<?php if (array_key_exists( 'multi', $this->field ) && $this->field['multi']) : ?>
				multiple="multiple"
			<?php endif; ?>
		>
			<?php if ( $allow_null ) : ?>
				<option value="">None</option>
			<?php endif; ?>

			<?php foreach ($this->field['value'] as $option) :

				$selected_text = '';
				if (is_array( $selected )) {
					if (in_array( $option['key'], $selected )) {
						$selected_text = ' selected="selected" ';
					}
				} elseif ($option['key'] == $selected) {
					$selected_text = ' selected="selected" ';
				}

				?>
				<option value="<?php echo $option['key']; ?>"
					<?php echo $selected_text ?>><?php echo $option['title']; ?></option>

			<?php endforeach; ?>
		</select>
		<?php
		parent::close_output();
	}

}
