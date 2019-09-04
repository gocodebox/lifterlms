<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; }

/**
 * Image metabox field
 *
 * @since    ??
 * @version  3.24.0
 */
class LLMS_Metabox_Image_Field extends LLMS_Metabox_Field implements Meta_Box_Field_Interface {

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
	 * @since    ??
	 * @version  3.24.0
	 */
	public function output() {

		global $post;
		$image;
		$imgclass;

		parent::output();

		if ( 'achievement_meta_box' === $this->field['section'] ) {
			$image = apply_filters( 'lifterlms_placeholder_img_src', LLMS()->plugin_url() . '/assets/images/optional_achievement.png' ); ?>
			<img id="<?php echo $this->field['id']; ?>" class="llms_achievement_default_image" style="display:none" src="<?php echo $image; ?>">
			<?php
			$imgclass = 'llms_achievement_image';
		} else {
			$image = apply_filters( 'lifterlms_placeholder_img_src', LLMS()->plugin_url() . '/assets/images/optional_certificate.png' );
			?>
			<img id="<?php echo $this->field['id']; ?>" class="llms_certificate_default_image" style="display:none" src="<?php echo $image; ?>">
			<?php
			$imgclass = 'llms_certificate_image';
		} // End if().
		if ( is_numeric( $this->meta ) ) {
			$image = wp_get_attachment_image_src( $this->meta, 'medium' );
			$image = $image[0];
		}
		?>
				<img src="<?php echo $image; ?>" id="<?php echo $this->field['id']; ?>" class="<?php echo $imgclass; ?>" /><br />
				<input name="<?php echo $this->field['id']; ?>" id="<?php echo $this->field['id']; ?>" type="hidden" class="upload_<?php echo $this->field['class']; ?>_image" type="text" size="36" name="ad_image" value="<?php echo $this->meta; ?>" />
				<input id="<?php echo $this->field['id']; ?>" class="button <?php echo $this->field['class']; ?>_image_button" type="button" value="Upload Image" />
				<small> <a href="#" id="<?php echo $this->field['id']; ?>" class="llms_<?php echo $this->field['class']; ?>_clear_image_button">Remove Image</a></small>
				<br /><span class="description"><?php echo $this->field['desc']; ?></span>
		<?php
		parent::close_output();
	}
}

