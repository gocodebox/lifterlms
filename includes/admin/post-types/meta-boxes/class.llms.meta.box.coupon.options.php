<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
* Meta Box Coupon Options
*
* displays coupon options metabox. Only displays on coupon post.
*/
class LLMS_Meta_Box_Coupon_Options {

	public $prefix = '_llms_';

	/**
	 * Static output class.
	 *
	 * Displays MetaBox
	 * Calls static class metabox_options
	 * Loops through meta-options array and displays appropriate fields based on type.
	 *
	 * @param  object $post [WP post object]
	 *
	 * @return void
	 */
	public static function output( $post ) {
		$prefix = '_llms_';
		global $post;
		wp_nonce_field( 'lifterlms_save_data', 'lifterlms_meta_nonce' );

		$coupon_creator_meta_fields = self::metabox_options();

		ob_start(); ?>

		<table class="form-table">
		<?php foreach ($coupon_creator_meta_fields as $field) {

			$meta = get_post_meta( $post->ID, $field['id'], true ); ?>

				<tr>
					<th><label for="<?php echo $field['id']; ?>"><?php echo $field['label']; ?></label></th>
					<td>
					<?php switch ($field['type']) {
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
							<?php foreach ($field['options'] as $id => $option) :
								if ($meta == $id) : ?>
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
							if (is_numeric( $meta )) {
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
							if ( ! $meta) {
								$meta = $field['value'];
							}
							?>
							<input class="color-picker" type="text" name="<?php echo $field['id']; ?>" id="<?php echo $field['id']; ?>" value="<?php echo $meta; ?>" data-default-color="<?php echo $field['value']; ?>"/>
								<br /><span class="description"><?php echo $field['desc']; ?></span>

					<?php break;

} //end switch

					?>
				</td></tr>
		<?php
			//endif; //end if in section check

} // end foreach ?>
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

		$coupon_creator_meta_fields = apply_filters('lifterlms_coupon_options_output', array(
			// Coupon code text field
			array(
				'label' => 'Coupon Code',
				'desc' => 'Enter a code that users will enter to apply this coupon to thier item.',
				'id' => $prefix . 'coupon_title',
				'type'  => 'text',
				'section' => 'coupon_meta_box',
			),
			// Discount type select
			array(
				'label' => 'Discount Type',
				'desc' => 'Select a dollar or percentage discount.',
				'id' => $prefix . 'discount_type',
				'type'  => 'dropdown',
				'section' => 'coupon_meta_box',
				'options' => array(
						'percent' => '% Discount',
						'dollar' => sprintf( __( '%s Discount', 'lifterlms' ), get_lifterlms_currency_symbol() ),
					),
			),
			//Coupon amount text field
			array(
				'label'  => 'Coupon Amount',
				'desc' => 'The value of the coupon. do not include symbols such as $ or %.',
				'id'    => $prefix . 'coupon_amount',
				'type'  => 'text',
				'section' => 'coupon_meta_box',
			),
			//Usage limit text field
			array(
				'label'  => 'Usage Limit',
				'desc' => 'The amount of times this coupon can be used. Leave empty if unlimited.',
				'id'    => $prefix . 'usage_limit',
				'type'  => 'text',
				'section' => 'coupon_meta_box',
			),
		) );

		if (has_filter( 'llms_meta_fields' )) {
			//Add Fields to the coupon Creator Meta Box
			$coupon_creator_meta_fields = apply_filters( 'llms_meta_fields', $coupon_creator_meta_fields );
		}

		return $coupon_creator_meta_fields;
	}

	/**
	 * Static save method
	 *
	 * cleans variables and saves using update_post_meta
	 *
	 * @param  int 		$post_id [id of post object]
	 * @param  object 	$post [WP post object]
	 *
	 * @return void
	 */
	public static function save( $post_id, $post ) {
		global $wpdb;
		$prefix = '_llms_';

		$title = $prefix 	. 'coupon_title';
		$type = $prefix 	. 'discount_type';
		$amount = $prefix 	. 'coupon_amount';
		$limit = $prefix 	. 'usage_limit';
		$products_meta_key = $prefix 	. 'coupon_products';

		if (isset( $_POST[ $title ] )) {
			//update title
			$update_title = ( llms_clean( $_POST[ $title ] ) );
			update_post_meta( $post_id, $title, ( $update_title === '' ) ? '' : $update_title );
		}
		if (isset( $_POST[ $type ] )) {
			//update discount type
			$update_type = ( llms_clean( $_POST[ $type ] ) );
			update_post_meta( $post_id, $type, ( $update_type === '' ) ? '' : $update_type );
		}
		if (isset( $_POST[ $amount ] )) {
			//update coupon amount
			$update_amount = ( llms_clean( $_POST[ $amount ] ) );
			if ($update_type == 'percent' && $update_amount > 100) {
				$update_amount = 100;
			}
			update_post_meta( $post_id, $amount, ( $update_amount === '' ) ? '' : $update_amount );
		}
		if (isset( $_POST[ $limit ] )) {
			//update usage limit
			$update_limit = ( llms_clean( $_POST[ $limit ] ) );
			update_post_meta( $post_id, $limit, ( $update_limit === '' ) ? '' : $update_limit );
		}

		$courses = isset( $_POST['_llms_coupon_courses'] ) ? $_POST['_llms_coupon_courses'] : false;
		$memberships = isset( $_POST['_llms_coupon_membership'] ) ? $_POST['_llms_coupon_membership'] : false;

		$products = array();

		if (isset( $courses ) && ! empty( $courses )) {
			foreach ($courses as $item) {
				$products[] = intval( $item );
			}
		}

		if (isset( $memberships ) && ! empty( $memberships )) {
			foreach ($memberships as $item) {
				$products[] = intval( $item );
			}
		}

		update_post_meta( $post_id, $products_meta_key, ( empty( $products ) ) ? '' : $products );

		//save coupon action
		do_action( 'lifterlms_after_save_coupon_meta_box', $post_id, $post );
	}

}
