<?php
if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! class_exists( 'LLMS_Admin_Metabox' ) ) 
{
	include_once 'llms.class.meta.box.fields.php';
}

/**
* 
*/
class LLMS_Metabox_Image_Field extends LLMS_Metabox_Field
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
					
		$image = apply_filters( 'lifterlms_placeholder_img_src', LLMS()->plugin_url() . '/assets/images/optional_certificate.png' ); ?>
		<img id="<?php echo $this->field['id']; ?>" class="llms_certificate_default_image" style="display:none" src="<?php echo $image; ?>">
		<?php //Check existing field and if numeric
		if (is_numeric($this->meta)) { 
			$image = wp_get_attachment_image_src($this->meta, 'medium'); 
			$image = $image[0];
		} ?>
				<img src="<?php echo $image; ?>" id="<?php echo $this->field['id']; ?>" class="llms_certificate_image" /><br />
				<input name="<?php echo $this->field['id']; ?>" id="<?php echo $this->field['id']; ?>" type="hidden" class="upload_certificate_image" type="text" size="36" name="ad_image" value="<?php echo $this->meta; ?>" /> 
				<input id="<?php echo $this->field['id']; ?>" class="button certificate_image_button" type="button" value="Upload Image" />
				<small> <a href="#" id="<?php echo $this->field['id']; ?>" class="llms_certificate_clear_image_button">Remove Image</a></small>
				<br /><span class="description"><?php echo $this->field['desc']; ?></span>			
		<?php
		parent::CloseOutput();				
	}
}

