<?php
defined( 'ABSPATH' ) || exit;

/**
 * Meta Box Expiration
 * Displays expiration fields for membership post. Displays only on membership post.
 * @since    ??
 * @version  3.24.0
 */
class LLMS_Meta_Box_Expiration {

	public $prefix = '_llms_';

	/**
	 * Static output class.
	 *
	 * Displays MetaBox
	 * Calls static class metabox_options
	 * Loops through meta-options array and displays appropriate fields based on type.
	 * @param  object $post [WP post object]
	 * @return void
	 */
	public static function output( $post ) {
		$prefix = '_llms_';
		global $post;
		wp_nonce_field( 'lifterlms_save_data', 'lifterlms_meta_nonce' );

		$expiration_meta_fields = self::metabox_options();

		ob_start(); ?>

		<table class="form-table">
		<?php foreach ( $expiration_meta_fields as $field ) {

				$meta = get_post_meta( $post->ID, $field['id'], true ); ?>

				<tr>
					<th><label for="<?php echo $field['id']; ?>"><?php echo $field['label']; ?></label></th>
					<td>
					<?php switch ( $field['type'] ) {
						// text
						case 'text':?>

							<input type="text" name="<?php echo $field['id']; ?>" id="<?php echo $field['id']; ?>" value="<?php echo $meta; ?>" size="30" />
								<br /><span class="description"><?php echo $field['desc']; ?></span>

						<?php break;
						// textarea
						case 'textarea': ?>

							<textarea name="<?php echo $field['id']; ?>" id="<?php echo $field['id']; ?>" cols="60" rows="4"><?php echo $meta; ?></textarea>
								<br /><span class="description"><?php echo $field['desc']; ?></span>

						<?php break;
						// textarea
						case 'textarea_w_tags': ?>

							<textarea name="<?php echo $field['id']; ?>" id="<?php echo $field['id']; ?>" cols="60" rows="4"><?php echo $meta; ?></textarea>
								<br /><span class="description"><?php echo $field['desc']; ?></span>

						<?php break;
						//dropdown
						case 'dropdown': ?>

							<select name="<?php echo $field['id']; ?>" id="<?php echo $field['id']; ?>">
							<option value="" disabled selected><?php _e( 'Please select an option...', 'lifterlms' ); ?></option>
							<?php foreach ( $field['options'] as $id => $option ) :
								if ( $meta == $id ) : ?>
								<option value="<?php echo $id; ?>" selected><?php echo $option; ?></option>
							<?php else : ?>
							<option value="<?php echo $id; ?>"><?php echo $option; ?></option>
							<?php endif; ?>
							<?php endforeach; ?>
							</select>
							<br /><span class="description"><?php echo $field['desc']; ?></span>

						<?php break;
						// image
						case 'image':

							$image = apply_filters( 'lifterlms_placeholder_img_src', LLMS()->plugin_url() . '/assets/images/optional_coupon.png' ); ?>
							<img id="<?php echo $field['id']; ?>" class="llms_achievement_default_image" style="display:none" src="<?php echo $image; ?>">
							<?php //Check existing field and if numeric
							if ( is_numeric( $meta ) ) {
								$image = wp_get_attachment_image_src( $meta, 'medium' );
								$image = $image[0];
							} ?>
									<img src="<?php echo $image; ?>" id="<?php echo $field['id']; ?>" class="llms_achievement_image" /><br />
									<input name="<?php echo $field['id']; ?>" id="<?php echo $field['id']; ?>" type="hidden" class="upload_achievement_image" type="text" size="36" name="ad_image" value="<?php echo $meta; ?>" />
									<input id="<?php echo $field['id']; ?>" class="achievement_image_button" type="button" value="Upload Image" />
									<small> <a href="#" id="<?php echo $field['id']; ?>" class="llms_achievement_clear_image_button">Remove Image</a></small>
									<br /><span class="description"><?php echo $field['desc']; ?></span>

						<?php break;
						// color
						case 'color': ?>
							<?php //Check if Values and If None, then use default
							if ( ! $meta ) {
								$meta = $field['value'];
							}
							?>
							<input class="color-picker" type="text" name="<?php echo $field['id']; ?>" id="<?php echo $field['id']; ?>" value="<?php echo $meta; ?>" data-default-color="<?php echo $field['value']; ?>"/>
								<br /><span class="description"><?php echo $field['desc']; ?></span>

					<?php break;

} // End switch().

					?>
				</td></tr>
		<?php
			//endif; //end if in section check

} // End foreach().
?>
			</table>
	<?php
	echo ob_get_clean();
	}

	/**
	 * Builds array of metabox options.
	 * Array is called in output method to display options.
	 * Appropriate fields are generated based on type.
	 *
	 * @return array [md array of metabox fields]
	 */
	public static function metabox_options() {
		$prefix = '_llms_';

		$expiration_meta_fields = apply_filters('lifterlms_metabox_expiration_output', array(
			array(
				'label' => 'Interval',
				'desc' => 'Enter the interval. IE: enter 1 and select year below to set expiration to 1 year.',
				'id' => $prefix . 'expiration_interval',
				'type'  => 'text',
				'section' => 'expiration_interval',
			),
			array(
				'label' => 'Expiration Period',
				'desc' => 'Combine the period with the interval above to set an expiration time line.',
				'id' => $prefix . 'expiration_period',
				'type'  => 'dropdown',
				'section' => 'expiration_period',
				'options' => array(
					'day' => 'Day',
					'month' => 'Month',
					'year' => 'Year',
				),
			),
		) );

		if ( has_filter( 'llms_meta_fields' ) ) {
			$expiration_meta_fields = apply_filters( 'llms_meta_fields', $expiration_meta_fields );
		}

		return $expiration_meta_fields;
	}

	/**
	 * Static save method
	 * cleans variables and saves using update_post_meta
	 * @param    int 		$post_id  id of post object
	 * @param    object 	$post     WP post object
	 * @return   void
	 * @since    ??
	 * @version  3.24.0
	 */
	public static function save( $post_id, $post ) {
		global $wpdb;

		$prefix = '_llms_';

		$interval = $prefix . 'expiration_interval';
		$period = $prefix . 'expiration_period';

		//upate interval textbox
		if ( isset( $_POST[ $interval ] ) ) {
			$update_interval = llms_clean( $_POST[ $interval ] );
			update_post_meta( $post_id, $interval, ( '' === $update_interval ) ? '' : $update_interval );
		}

		//update period select
		if ( isset( $_POST[ $period ] ) ) {
			$update_period = llms_clean( $_POST[ $period ] );
			update_post_meta( $post_id, $period, ( '' === $update_period ) ? '' : $update_period );
		}
	}

}
