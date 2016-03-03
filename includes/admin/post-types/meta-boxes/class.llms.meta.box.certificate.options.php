<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
* Meta Box Certificate Options
*
* displays certificate options metabox. Only displays on certificate post.
*/
class LLMS_Meta_Box_Certificate_Options {

	public static $prefix = '_llms_';

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
		global $post;
		wp_nonce_field( 'lifterlms_save_data', 'lifterlms_meta_nonce' );

		$certificate_creator_meta_fields = self::metabox_options();

		ob_start(); ?>

		<table class="form-table">
		<tbody>
		<?php foreach ($certificate_creator_meta_fields as $field) {

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

							$image = apply_filters( 'lifterlms_placeholder_img_src', LLMS()->plugin_url() . '/assets/images/optional_certificate.png' ); ?>
							<img id="<?php echo $field['id']; ?>" class="llms_certificate_default_image" style="display:none" src="<?php echo $image; ?>">
							<?php //Check existing field and if numeric
							if (is_numeric( $meta )) {
								$image = wp_get_attachment_image_src( $meta, 'medium' );
								$image = $image[0];
							} ?>
									<img src="<?php echo $image; ?>" id="<?php echo $field['id']; ?>" class="llms_certificate_image" /><br />
									<input name="<?php echo $field['id']; ?>" id="<?php echo $field['id']; ?>" type="hidden" class="upload_certificate_image" type="text" size="36" name="ad_image" value="<?php echo $meta; ?>" /> 
									<input id="<?php echo $field['id']; ?>" class="button certificate_image_button" type="button" value="Upload Image" />
									<small> <a href="#" id="<?php echo $field['id']; ?>" class="llms_certificate_clear_image_button">Remove Image</a></small>
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
									// Videos
						case 'llms_help':?>
						 
							<p>
							<?php _e( 'Use the text editor above to add content to your certificate.
							You can include any of the following merge fields.', 'lifterlms' ); ?> 
							</p>
							<ul>
							<!-- merge fields cannot be translated so are not echoed. -->
							<li>{site_title}</li>
							<li>{user_login}</li>
							<li>{site_url}</li>
							<li>{first_name}</li>
							<li>{last_name}</li>
							<li>{email_address}</li>
							<li>{current_date}</li>
							</ul>
							
						<?php break;

} //end switch

					?>
				</td></tr>
		<?php
			//endif; //end if in section check

} // end foreach ?>
		<tbody>
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

		$certificate_creator_meta_fields = array(
			array(
				'label' => 'Certificate Title',
				'desc' => 'Enter a title for your certificate. IE: Certificate of Completion',
				'id' => $prefix . 'certificate_title',
				'type'  => 'text',
				'section' => 'certificate_meta_box',
			),
			array(
				'label'  => 'Background Image',
				'desc'  => 'Select an Image to use for the certificate background.',
				'id'    => $prefix . 'certificate_image',
				'type'  => 'image',
				'section' => 'certificate_meta_box',
			),
			array(
					'label'  => 'How to use Certificates:',
					'id'    => $prefix . 'llms_help',
					'type'  => 'llms_help',
					'section' => 'certificate_meta_box',
				),
		);

		if (has_filter( 'llms_meta_fields' )) {
			//Add Fields to the certificate Creator Meta Box
			$certificate_creator_meta_fields = apply_filters( 'llms_meta_fields', $certificate_creator_meta_fields );
		}

		return $certificate_creator_meta_fields;
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
		$title = $prefix . 'certificate_title';
		$image = $prefix . 'certificate_image';

		//update title
		$update_title = ( llms_clean( $_POST[ $title ] ) );
		update_post_meta( $post_id, $title, ( $update_title === '' ) ? '' : $update_title );

		//update background image
		$update_image = ( llms_clean( $_POST[ $image ] ) );
		update_post_meta( $post_id, $image, ( $update_image === '' ) ? '' : $update_image );

	}

}
