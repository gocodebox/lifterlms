<?php
if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! class_exists( 'LLMS_Admin_Metabox' ) ) :

/**
* Admin Settings Class
*
* Settings field Factory
*
* @author codeBOX
* @project lifterLMS
*/
abstract class LLMS_Admin_Metabox {

	/**
	 * Function responsible for outputing the meta box.
	 * Parses the array of fields passed to it, then calls
	 * a helper method to generate the actual html
	 * 
	 * @param  object $post Global WP post object
	 * @param  array $meta_fields_course_main Array of fields to be displayed in the box
	 * @return void
	 */
	public static function new_output( $post, $meta_fields_course_main ) {
		global $post;
		wp_nonce_field( 'lifterlms_save_data', 'lifterlms_meta_nonce' );
					
		ob_start(); ?>

		<div class="container">
			<!--hidden field to pass info to js to load correct classes-->
			<input type="hidden" name="llms_post_edit_type" id="llms_post_edit_type" value="course">
			
			<!--Begin Tab Navigation-->
			<ul class="tabs">
				<?php 
				$i = 0;
				foreach ($meta_fields_course_main as $meta_box) : 
					$i++
				?>
					<li class="tab-link d-1of6 t-1of2 m-all
						<?php echo $i === 1 ? 'current' : ''; ?>" data-tab="tab-<?php echo $i; ?>">
						<?php echo $meta_box['title']; ?></li>

				<?php endforeach; ?>
			</ul> <!--End Tab Navigation-->

			<?php 
			$i = 0;
			foreach ($meta_fields_course_main as $meta_box) : 
				$i++
			?>
			<div id="tab-<?php echo $i; ?>" class="tab-content <?php echo $i === 1 ? 'current' : ''; ?>">

				<ul>
					<?php foreach( $meta_box['fields'] as $field ) :
						echo self::output_field($field);
					endforeach; ?>
				</ul>

			</div>

			<?php endforeach; ?>
			</div><!-- container -->

		<?php echo ob_get_clean();
	}

	/**
	 * TBH I'm not sure exactly what this does... But removing it makes everything break.
	 * Your best bet is to ask Mark...
	 * 
	 * @param  [type]
	 * @param  [type]
	 * @return [type]
	 */
	public static function get_post_meta($post_id, $field_id) {

		if ( $field_id === '_post_course_difficulty' ) {
			$difficulties = wp_get_object_terms($post_id, 'course_difficulty');
			
			if ( $difficulties ) {
				return $difficulties[0]->slug;
			}
			
		} else {
			return get_post_meta($post_id, $field_id, true);
		}
		
	}

	/**
	 * This function generates the html for each of the varying fields.
	 * 
	 * @param  array $field The array containing field information
	 * @return HTML
	 */
	public static function output_field( $field ) { 
		global $post;

	 //foreach ($meta_fields_course_main as $field) {
		$meta = self::get_post_meta($post->ID, $field['id']); ?>

			<li class="llms-mb-list <?php echo $field['group']; ?>">
			
				<!--label and description-->
				<div class="description <?php echo $field['desc_class']; ?>">
					<label for="<?php echo $field['id']; ?>"><?php echo $field['label']; ?></label>
					<?php echo $field['desc'] ?>
				</div>

				<?php switch($field['type']) { 
					// text
					case 'text':?>
					
						<input type="text" 
							name="<?php echo $field['id']; ?>" 
							id="<?php echo $field['id']; ?>" 
							class="<?php echo esc_attr( $field['class'] ); ?>"
							value="<?php echo $meta; ?>" size="30" 
						/>
							
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
						if (is_numeric($meta)) { 
							$image = wp_get_attachment_image_src($meta, 'medium'); 
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
							if (!$meta) {
								$meta = $field['value'];
							}
						?>
						<input class="color-picker" type="text" name="<?php echo $field['id']; ?>" id="<?php echo $field['id']; ?>" value="<?php echo $meta; ?>" data-default-color="<?php echo $field['value']; ?>"/>
							<br /><span class="description"><?php echo $field['desc']; ?></span>
					
				<?php break;
					//checkbox
					case 'checkbox':?>
						
						<div class="llms-switch d-1of4 t-1of4 m-1of2">
							<div class="llms-toggle-icon-on">
								<?php echo LLMS_Svg::get_icon( 'llms-icon-checkmark', 'Off', 'Off', 'toggle-icon' ); ?>
							</div>
							<div class="llms-toggle-icon-off">
							<?php echo LLMS_Svg::get_icon( 'llms-icon-close', 'Off', 'Off', 'toggle-icon' ); ?>
							</div>

								<input 
									name="<?php echo esc_attr( $field['id'] ); ?>"
									id="<?php echo esc_attr( $field['id'] ); ?>"
									class="llms-toggle llms-toggle-round" 
									type="checkbox"
									value="<?php echo esc_attr( $field['value'] ); ?>"
									<?php echo $meta ? 'checked' : ''; ?>
								/> 

							<label for="<?php echo $field['id'] ?>"></label>
						</div>
						<?php break;
						//select
						case 'select':?>
					
							<select 
								id="<?php echo esc_attr( $field['id'] ); ?>" 
								name="<?php echo esc_attr( $field['id'] ); ?>"
								class="<?php echo esc_attr( $field['class'] ); ?>"
							>
							    <option value="">None</option>

								<?php foreach ( $field['value'] as $option  ) : 
									if ( $option['key'] == $meta ) :
								?>
									<option value="<?php echo $option['key']; ?>" selected="selected"><?php echo $option['title']; ?></option>

								<?php else : ?>
									<option value="<?php echo $option['key']; ?>"><?php echo $option['title']; ?></option>

								<?php endif; ?>
								<?php endforeach; ?>
					 		</select>

					 	<?php break;
					 	//button
					 	case 'button':?>

					 		<button 
					 			id="<?php echo esc_attr( $field['id'] ); ?>" 
					 			class="<?php echo esc_attr( $field['class'] ); ?>"
					 		>
					 			<?php echo esc_attr( $field['value'] ); ?>
					 		</button>

					 	<?php break;
					 	//post excerpt
					 	case 'post-excerpt':

					 	$settings = array(
							'textarea_name'	=> 'excerpt',
							'quicktags' 	=> array( 'buttons' => 'em,strong,link' ),
							'tinymce' 	=> array(
								'theme_advanced_buttons1' => 'bold,italic,strikethrough,separator,bullist,numlist,separator,blockquote,separator,justifyleft,justifycenter,justifyright,separator,link,unlink,separator,undo,redo,separator',
								'theme_advanced_buttons2' => '',
							),
							'editor_class' => 'llms-post-editor',
							'editor_css'	=> '<style>#excerpt_ifr{height:300px}#wp-excerpt-editor-container .wp-editor-area{height:300px; width:100%;}</style>',
							'drag_drop_upload' => true
						);

						wp_editor( htmlspecialchars_decode( 
							$post->post_excerpt ), 
							'excerpt', apply_filters( 'lifterlms_course_short_description_editor_settings', $settings ) );

						?><div class="clear"></div><?php
					 	break;
					 	//post content
					 	case 'post-content':

					 	$settings = array(
							'textarea_name'	=> 'content',
							'quicktags' 	=> array( 'buttons' => 'em,strong,link' ),
							'tinymce' 	=> array(
								'theme_advanced_buttons1' => 'bold,italic,strikethrough,separator,bullist,numlist,separator,blockquote,separator,justifyleft,justifycenter,justifyright,separator,link,unlink,separator,undo,redo,separator',
								'theme_advanced_buttons2' => '',
							),
							'editor_css'	=> '<style>#wp-content-editor-container .wp-editor-area{height:300px; width:100%;}</style>',
							'drag_drop_upload' => true
						);

						wp_editor( htmlspecialchars_decode( 
							$post->post_content ), 
							'content', apply_filters( 'lifterlms_course_full_description_editor_settings', $settings ) );

					 	break;

					 	case 'date':?>

					 		<input type="text" 
								name="<?php echo $field['id']; ?>" 
								id="<?php echo $field['id']; ?>" 
								class="<?php echo esc_attr( $field['class'] ); ?>"
								value="<?php echo !empty($meta) ? LLMS_Date::pretty_date($meta) : ''; ?>" size="30" 
							/>

					 	<?php break;
					 	//custom html
					 	case 'custom-html':?>

					 		<?php echo $field['value'];?>

					 	<?php break;

					} //end switch
				
				?>
				<div class="clear"></div>
			</li>
	<?php 
	}
}

endif;