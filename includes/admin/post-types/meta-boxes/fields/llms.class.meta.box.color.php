<?php
if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! class_exists( 'LLMS_Admin_Metabox' ) ) 
{
	include_once 'llms.class.meta.box.fields.php';
}

/**
* 
*/
class LLMS_Metabox_Color_Field extends LLMS_Metabox_Field
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
					
		if (!$this->meta) {
			$this->meta = $this->field['value'];
		}
		?>
		<input class="color-picker" type="text" name="<?php echo $this->field['id']; ?>" id="<?php echo $this->field['id']; ?>" value="<?php echo $this->meta; ?>" data-default-color="<?php echo $this->field['value']; ?>"/>
			<br /><span class="description"><?php echo $this->field['desc']; ?></span>			
		<?php
		parent::CloseOutput();				
	}
}

