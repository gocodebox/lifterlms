<?php
if (!defined('ABSPATH')) exit;

/**
 *
 */
class LLMS_Metabox_Select_Field extends LLMS_Metabox_Field implements Meta_Box_Field_Interface
{

	/**
	 * Class constructor
	 * @param array $_field Array containing information about field
	 */
	function __construct($_field)
	{
		$this->field = $_field;
	}

	/**
	 * Outputs the Html for the given field
	 * @return HTML
	 */
	public function Output()
	{
		global $post;

		parent::Output();

		$id = $name = esc_attr($this->field['id']);

		if (array_key_exists('multi', $this->field)) {
			$name .= '[]';
		}

		$selected = $this->meta;
		if (array_key_exists('selected', $this->field)) {
			$selected = $this->field['selected'];
		}

		?>

		<select
			id="<?php echo $id; ?>"
			name="<?php echo $name; ?>"
			class="<?php echo esc_attr($this->field['class']); ?>"
			<?php if (array_key_exists('multi', $this->field) && $this->field['multi']): ?>
				multiple="multiple"
			<?php endif; ?>
		>
			<option value="">None</option>

			<?php foreach ($this->field['value'] as $option) :

				$selectedText = '';
				if (is_array($selected)) {
					if (in_array($option['key'], $selected)) {
						$selectedText = ' selected="selected" ';
					}
				} elseif ($option['key'] == $selected) {
					$selectedText = ' selected="selected" ';
				}

				?>
				<option value="<?php echo $option['key']; ?>"
					<?php echo $selectedText ?>><?php echo $option['title']; ?></option>

			<?php endforeach; ?>
		</select>
		<?php
		parent::CloseOutput();
	}

}