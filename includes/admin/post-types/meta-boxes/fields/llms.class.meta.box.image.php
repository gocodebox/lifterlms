<?php
/**
 * Meta box field: Image meta box field
 *
 * @package LifterLMS/Admin/PostTypes/MetaBoxes/Fields/Classes
 *
 * @since ??
 * @version 3.24.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Image meta box field class
 *
 * @since ??
 * @since 3.24.0 Unknown.
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

		parent::output();

		if ( 'achievement_meta_box' === $this->field['section'] ) {
			$image = apply_filters( 'lifterlms_placeholder_img_src', llms()->plugin_url() . '/assets/images/optional_achievement.png' ); ?>
			<img id="<?php echo esc_attr( $this->field['id'] ); ?>" class="llms_achievement_default_image" style="display:none" src="<?php echo esc_url( $image ); ?>">
			<?php
			$imgclass = 'llms_achievement_image';
		} else {
			$image = apply_filters( 'lifterlms_placeholder_img_src', llms()->plugin_url() . '/assets/images/optional_certificate.png' );
			?>
			<img id="<?php echo esc_attr( $this->field['id'] ); ?>" class="llms_certificate_default_image" style="display:none" src="<?php echo esc_url( $image ); ?>">
			<?php
			$imgclass = 'llms_certificate_image';
		} // End if().
		if ( is_numeric( $this->meta ) ) {
			$image = wp_get_attachment_image_src( $this->meta, 'medium' );
			$image = $image[0];
		}
		?>
				<img src="<?php echo esc_url( $image ); ?>" id="<?php echo esc_attr( $this->field['id'] ); ?>" class="<?php echo esc_attr( $imgclass ); ?>" /><br />
				<input name="<?php echo esc_attr( $this->field['id'] ); ?>" id="<?php echo esc_attr( $this->field['id'] ); ?>" type="hidden" class="upload_<?php echo esc_attr( $this->field['class'] ); ?>_image" type="text" size="36" name="ad_image" value="<?php echo esc_attr( $this->meta ); ?>" />
				<input id="<?php echo esc_attr( $this->field['id'] ); ?>" class="button <?php echo esc_attr( $this->field['class'] ); ?>_image_button" type="button" value="Upload Image" />
				<small> <a href="#" id="<?php echo esc_attr( $this->field['id'] ); ?>" class="llms_<?php echo esc_attr( $this->field['class'] ); ?>_clear_image_button">Remove Image</a></small>
				<br /><span class="description"><?php echo wp_kses_post( $this->field['desc'] ); ?></span>
		<?php
		parent::close_output();
	}
}

