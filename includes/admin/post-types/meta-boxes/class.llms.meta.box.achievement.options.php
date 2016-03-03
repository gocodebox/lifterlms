<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
* Meta Box Achievement Options
*
* displays achievement options metabox. Only displays on achievement post.
*/
class LLMS_Meta_Box_Achievement_Options {

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

		$achievement_creator_meta_fields = self::metabox_options();

		ob_start(); ?>

		<table class="form-table">
		<?php foreach ($achievement_creator_meta_fields as $field) {

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
						// image
						case 'image':

							$image = apply_filters( 'lifterlms_placeholder_img_src', LLMS()->plugin_url() . '/assets/images/optional_achievement.png' ); ?>
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

		$achievement_creator_meta_fields = array(
			//Achievement title text box
			array(
				'label' => 'Achievement Title',
				'desc' => 'Enter a title for your achievement. IE: Achievement of Completion',
				'id' => $prefix . 'achievement_title',
				'type'  => 'text',
				'section' => 'achievement_meta_box',
			),
			//Achievment content textarea
			array(
				'label' => 'Achievement Content',
				'desc' => 'Enter any information you would like to display on the achievement.',
				'id' => $prefix . 'achievement_content',
				'type'  => 'textarea_w_tags',
				'section' => 'achievement_meta_box',
			),
			//Achievement background image
			array(
				'label'  => 'Background Image',
				'desc'  => 'Select an Image to use for the achievement.',
				'id'    => $prefix . 'achievement_image',
				'type'  => 'image',
				'section' => 'achievement_meta_box',
			),
		);

		if (has_filter( 'llms_meta_fields' )) {
			//Add Fields to the achievement Creator Meta Box
			$achievement_creator_meta_fields = apply_filters( 'llms_meta_fields', $achievement_creator_meta_fields );
		}

		return $achievement_creator_meta_fields;
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

		$title = $prefix . 'achievement_title';
		$content = $prefix . 'achievement_content';
		$image = $prefix . 'achievement_image';

		//update title
		$update_title = ( llms_clean( $_POST[ $title ] ) );
		update_post_meta( $post_id, $title, ( $update_title === '' ) ? '' : $update_title );

		//update content
		$update_content = ( llms_clean( $_POST[ $content ] ) );
		update_post_meta( $post_id, $content, ( $update_content === '' ) ? '' : $update_content );

		//update image
		$update_image = ( llms_clean( $_POST[ $image ] ) );
		update_post_meta( $post_id, $image, ( $update_image === '' ) ? '' : $update_image );
	}

}
