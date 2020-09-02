<?php
/**
 * Admin image upload field.
 *
 * This view is output by the admin function `llms_admin_field_upload()`.
 *
 * @package LifterLMS/Admin/Views
 *
 * @since [version]
 * @version [version]
 *
 * @property string $src       Image source URL.
 * @property string $img_class Class name(s) for the image preview element.
 * @property string $id        ID of the input field.
 * @property string $class     Class name(s) for the hidden input field.
 * @property string $name      Input field name attribute.
 * @property string $value     Input field value.
 * @property string $desc      HTML description of the field (displayed below the field).
 * @property string $after     Additional HTML displayed after the field.
 */

defined( 'ABSPATH' ) || exit;
?>

<img class="<?php echo $img_class; ?>" src="<?php echo $src; ?>">
<button class="llms-button-secondary llms-image-field-upload" data-id="<?php echo esc_attr( $id ); ?>" type="button">
	<span class="dashicons dashicons-admin-media"></span>
	<?php _e( 'Upload Image', 'lifterlms' ); ?>
</button>
<button class="llms-button-danger llms-image-field-remove<?php echo ( ! $src ) ? ' hidden' : ''; ?>" data-id="<?php echo esc_attr( $id ); ?>" type="button">
	<span class="dashicons dashicons-no"></span>
	<span class="screen-reader-text"><?php _e( 'Remove Image', 'lifterlms' ); ?></span>
</button>
<input
	class="<?php echo esc_attr( $class ); ?>"
	name="<?php echo esc_attr( $name ); ?>"
	id="<?php echo esc_attr( $id ); ?>"
	value="<?php echo esc_attr( $value ); ?>"
	type="hidden" />
<?php echo $desc; ?>
<?php echo $after; ?>
