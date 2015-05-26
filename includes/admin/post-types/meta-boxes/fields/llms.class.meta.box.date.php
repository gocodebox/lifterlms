<?php
if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! class_exists( 'LLMS_Admin_Metabox' ) ) 
{
	include_once 'llms.class.meta.box.fields.php';
}

/**
* 
*/
class LLMS_Metabox_Date_Field extends LLMS_Metabox_Field
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
		
		parent::Output(); ?>
					
		<input type="text" 
			name="<?php echo $this->field['id']; ?>" 
			id="<?php echo $this->field['id']; ?>" 
			class="<?php echo esc_attr( $this->field['class'] ); ?>"
			value="<?php echo !empty($this->meta) ? LLMS_Date::pretty_date($this->meta) : ''; ?>" size="30" 
		/>			
		<?php
		parent::CloseOutput();				
	}
}

