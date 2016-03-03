<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
*
*/
class LLMS_Metabox_Textarea_Field extends LLMS_Metabox_Field implements Meta_Box_Field_Interface
{
	/**
	 * Class constructor
	 * @param array $_field Array containing information about field
	 */
	function __construct( $_field ) {

		$this->field = $_field;
	}

	/**
	 * Outputs the Html for the given field
	 * @return HTML
	 */
	public function Output() {

		global $post;

		parent::Output(); ?>
					
		<textarea name="<?php echo $this->field['id']; ?>" id="<?php echo $this->field['id']; ?>" cols="60" rows="4"><?php echo $this->meta; ?></textarea>
		<br /><span class="description"><?php echo $this->field['desc']; ?></span>		
		<?php
		parent::CloseOutput();
	}
}

