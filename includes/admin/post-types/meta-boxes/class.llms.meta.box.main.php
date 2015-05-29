<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
* Meta Box Builder
* 
* Generates main metabox and builds forms
*/
class LLMS_Meta_Box_Main {

	public static $prefix = '_';

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
	
		$meta_fields_course_main = self::metabox_options();
					
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
			//endif; //end if in section check
		
		//} // end foreach 
		


		} 



	//echo ob_get_clean();
	//}	

	/**
	 * Builds array of metabox options.
	 * Array is called in output method to display options.
	 * Appropriate fields are generated based on type.
	 * 
	 * @return array [md array of metabox fields]
	 */
	public static function metabox_options() {
		global $post;
	
		//setup course select options
		$course_options = array();
		$course_posts = LLMS_Post_Handler::get_posts( 'course' );
		foreach ( $course_posts as $c_post ) {
			if ( $c_post->ID != $post->ID ) {
				$course_options[] = array(
					'key' => $c_post->ID,
					'title' => $c_post->post_title
				);
			}
			
		}

		//setup course difficulty select options
		$difficulty_terms = get_terms('course_difficulty', 'hide_empty=0');
		$difficulty_options = array();
		foreach( $difficulty_terms as $term ) {
			$difficulty_options[] = array(
				'key' => $term->slug,
				'title' => $term->name
			);
		}

		//billing period options
		////needs to move to paypal class
		$billing_periods = array(
			array (
				'key' => 'day',
				'title' => 'Day'
			),
			array (
				'key' => 'week',
				'title' => 'Week'
			),
			array (
				'key' => 'month',
				'title' => 'Month'
			),
			array (
				'key' => 'year',
				'title' => 'Year'
			),
		);

		//enrolled users
		$course = new LLMS_Course( $post->ID );
		$enrolled_students = $course->get_enrolled_students();
		$enrolled_student_options = array();
		if ( $enrolled_students ) {
			foreach( $enrolled_students as $student ) {
				$enrolled_student_options[] = array(
					'key' => $student->ID,
					'title' => $student->display_name . ' (' . $student->user_email . ')'
				);
			}
		}

		//non-enrolled users
		$users_not_enrolled = LLMS_Course_Handler::get_users_not_enrolled( 
			$post->ID, 
			$enrolled_students 
		);

		$users_not_enrolled_options = array();
		if ( $users_not_enrolled ) {
			foreach( $users_not_enrolled as $student ) {
				$users_not_enrolled_options[] = array(
					'key' => $student->ID,
					'title' => $student->display_name . ' (' . $student->user_email . ')'
				);
			}
		}

		$meta_fields_course_main = array(
			array(
				'title' => 'Description',
				'fields' => array(
					array(
						'type'=> 'post-content',
						'label'=> 'Enrolled user and non-enrolled visitor description',
						'desc' => 'This content will be displayed to enrolled users. If the non-enrolled users description
							field is left blank the content will be displayed to both enrolled users and non-logged / restricted 
							visitors.',
						'id' => '',
						'class' => '',
						'value' => '',
						'desc_class' => '',
						'group' => '',
					),
					array(
						'type'=> 'post-excerpt',
						'label'=> 'Restricted Access Description',
						'desc' => 'Enter content in this field if you would like visitors that 
							are not enrolled or are restricted to view different content from 
							enrolled users. Visitors who are not enrolled in the course 
							or are restricted from the course will see this description if it contains content.',
						'id' => '',
						'class' => '',
						'value' => '',
						'desc_class' => '',
						'group' => '',
					)
				)
			),
			array(
				'title' => 'General',
				'fields' => array(
					array(
						'type'=> 'text',
						'label'=> 'Course Length',
						'desc' => 'Enter a description of the estimated length. IE: 3 days',
						'id' => self::$prefix . 'lesson_length',
						'class' => 'input-full',
						'value' => '',
						'desc_class' => 'd-all',
						'group' => 'top',
					),
					array(
						'type'=> 'select',
						'label'=> 'Course Difficulty Category',
						'desc' => 'Choose a course difficulty level from the difficulty categories.',
						'id' => self::$prefix . 'post_course_difficulty',
						'class' => 'llms-chosen-select',
						'value' => $difficulty_options,
						'desc_class' => 'd-all',
						'group' => 'bottom',
					),
					array(
						'type'=> 'text',
						'label'=> 'Video Embed Url',
						'desc' => 'Paste the url for a Wistia, Vimeo or Youtube video.',
						'id' => self::$prefix . 'video_embed',
						'class' => 'code input-full',
						'value' => '',
						'desc_class' => 'd-all',
						'group' => '',
					),
					array(
						'type'=> 'text',
						'label'=> 'Audio Embed Url',
						'desc' => 'Paste the url for an externally hosted audio file.',
						'id' => self::$prefix . 'audio_embed',
						'class' => 'code input-full',
						'value' => '',
						'desc_class' => 'd-all',
						'group' => '',
					)
				)
			),
			array(
				'title' => 'Restrictions',
				'fields' => array(
					array(
						'type'=> 'checkbox',
						'label'=> 'Enable Prerequisite',
						'desc' => 'Enable to choose a prerequisite Course',
						'id' => self::$prefix . 'has_prerequisite',
						'class' => '',
						'value' => '1',
						'desc_class' => 'd-3of4 t-3of4 m-1of2',
						'group' => 'llms-prereq-top',
					),
					array(
						'type'=> 'select',
						'label'=> 'Choose Prerequisite',
						'desc' => 'Select the prerequisite course',
						'id' => self::$prefix . 'prerequisite',
						'class' => 'llms-chosen-select',
						'value' => $course_options,
						'desc_class' => 'd-all',
						'group' => 'bottom llms-prereq-bottom',
					),
					array(
						'type'=> 'text',
						'label'=> 'Course Capacity',
						'desc' => 'Limit the number of users that can enroll in this course. Leave empty to allow unlimited students.',
						'id' => self::$prefix . 'lesson_max_user',
						'class' => 'input-full',
						'value' => '',
						'desc_class' => 'd-all',
						'group' => '',
					),
					array(
						'type'=> 'date',
						'label'=> 'Course Start Date',
						'desc' => 'Enter a date the course becomes available.',
						'id' => self::$prefix . 'course_dates_from',
						'class' => 'datepicker input-full',
						'value' => '',
						'desc_class' => 'd-all',
						'group' => '',
					),
					array(
						'type'=> 'date',
						'label'=> 'Course End Date',
						'desc' => 'Enter a date the course ends.',
						'id' => self::$prefix . 'course_dates_to',
						'class' => 'datepicker input-full',
						'value' => '',
						'desc_class' => 'd-all',
						'group' => '',
					),



		
				)
				
			),
			array(
				'title' => 'Price Single',
				'fields' => array(
					array(
						'type'=> 'text',
						'label'=> 'SKU',
						'desc' => 'Enter an SKU for your course.',
						'id' => self::$prefix . 'sku',
						'class' => 'input-full',
						'value' => '',
						'desc_class' => 'd-all',
						'group' => '',
					),
					array(
						'type'=> 'text',
						'label'=> 'Single Payment Price ( ' . get_lifterlms_currency_symbol() . ' )',
						'desc' => 'Enter a price to offer your course for a one time purchase.',
						'id' => self::$prefix . 'regular_price',
						'class' => 'input-full',
						'value' => '',
						'desc_class' => 'd-all',
						'group' => '',
					),
					array(
						'type'=> 'checkbox',
						'label'=> 'Course is on sale',
						'desc' => 'Enable single payment sale for this course.',
						'id' => self::$prefix . 'on_sale',
						'class' => '',
						'value' => '1',
						'desc_class' => 'd-3of4 t-3of4 m-1of2',
						'group' => '',
					),
					array(
						'type'=> 'text',
						'label'=> 'Sale Price ( ' . get_lifterlms_currency_symbol() . ' )',
						'desc' => 'Enter a sale price for the course.',
						'id' => self::$prefix . 'sale_price',
						'class' => 'input-full',
						'value' => '',
						'desc_class' => 'd-all',
						'group' => '',
					),
					array(
						'type'=> 'date',
						'label'=> 'Sale Price Start Date',
						'desc' => 'Enter the date your sale will begin.',
						'id' => self::$prefix . 'sale_price_dates_from',
						'class' => 'datepicker input-full',
						'value' => '',
						'desc_class' => 'd-all',
						'group' => '',
					),
					array(
						'type'=> 'date',
						'label'=> 'Sale Price End Date',
						'desc' => 'Enter the date your sale will end.',
						'id' => self::$prefix . 'sale_price_dates_to',
						'class' => 'datepicker input-full',
						'value' => '',
						'desc_class' => 'd-all',
						'group' => '',
					),
					

				)
			),
			array(
				'title' => 'Price Recurring',
				'fields' => array(
					array(
						'type'=> 'checkbox',
						'label'=> 'Enable Recurring Payment',
						'desc' => 'Enable recurring payment options.',
						'id' => self::$prefix . 'llms_recurring_enabled',
						'class' => '',
						'value' => '1',
						'desc_class' => 'd-3of4 t-3of4 m-1of2',
						'group' => '',
					),
					array(
						'type'=> 'text',
						'label'=> 'Recurring Payment ( ' . get_lifterlms_currency_symbol() . ' )',
						'desc' => 'Enter the amount you will bill at set intervals.',
						'id' => self::$prefix . 'llms_subscription_price',
						'class' => 'input-full',
						'value' => '',
						'desc_class' => 'd-all',
						'group' => '',
					),
					array(
						'type'=> 'text',
						'label'=> 'First Payment ( ' . get_lifterlms_currency_symbol() . ' )',
						'desc' => 'Enter the payment amount you will charge on product purchase. This can be 0 to give users a free trial period.',
						'id' => self::$prefix . 'llms_subscription_first_payment',
						'class' => 'input-full',
						'value' => '',
						'desc_class' => 'd-all',
						'group' => '',
					),
					array(
						'type'=> 'select',
						'label'=> 'Billing Period',
						'desc' => 'Combine billing period and billing frequency set billing interval. IE: Billing period =  week and frequency 2 will bill every 2 weeks.',
						'id' => self::$prefix . 'llms_billing_period',
						'class' => 'input-full',
						'value' => $billing_periods,
						'desc_class' => 'd-all',
						'group' => '',
					),
					array(
						'type'=> 'text',
						'label'=> 'Billing Frequency',
						'desc' => 'Use with billing period to set billing interval',
						'id' => self::$prefix . 'llms_billing_freq',
						'class' => 'input-full',
						'value' => '',
						'desc_class' => 'd-all',
						'group' => '',
					),
					array(
						'type'=> 'text',
						'label'=> 'Billing Cycles',
						'desc' => 'Enter 0 to charge indefinately. IE: 12 would bill for 12 months.',
						'id' => self::$prefix . 'llms_billing_cycle',
						'class' => 'input-full',
						'value' => '',
						'desc_class' => 'd-all',
						'group' => '',
					)
				)
			),
			array(
				'title' => 'Students',
				'fields' => array(
					array(
						'type'=> 'select',
						'label'=> 'Add Student',
						'desc' => 'Add a user to the course.',
						'id' => self::$prefix . 'add_new_user',
						'class' => 'llms-chosen-select',
						'value' => $users_not_enrolled_options,
						'desc_class' => 'd-all',
						'group' => '',
					),
					array(
						'type'=> 'button',
						'label'=> '',
						'desc' => '',
						'id' => self::$prefix . 'add_student_submit',
						'class' => 'llms-button-primary',
						'value' => 'Add Student',
						'desc_class' => '',
						'group' => '',
					),
					array(
						'type'=> 'select',
						'label'=> 'Remove Student',
						'desc' => 'Remove a user from the course.',
						'id' => self::$prefix . 'remove_student',
						'class' => 'llms-chosen-select',
						'value' => $enrolled_student_options,
						'desc_class' => 'd-all',
						'group' => '',
					),
					array(
						'type'=> 'button',
						'label'=> '',
						'desc' => '',
						'id' => self::$prefix . 'remove_student_submit',
						'class' => 'llms-button-primary',
						'value' => 'Remove Student',
						'desc_class' => '',
						'group' => '',
					)
				)
			),
		);

		if(has_filter('llms_meta_fields_course_main')) {
			//Add Fields to the course main Meta Box
			$meta_fields_course_main = apply_filters('llms_meta_fields_course_main', $meta_fields_course_main);
		} 
		
		return $meta_fields_course_main;
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

		// $prefix = '_';
		// $title = $prefix . 'certificate_title';
		// $image = $prefix . 'certificate_image';

		// //update title
		// $update_title = ( llms_clean( $_POST[$title]  ) );
		// update_post_meta( $post_id, $title, ( $update_title === '' ) ? '' : $update_title );

		// //update background image
		// $update_image = ( llms_clean( $_POST[$image]  ) );
		// update_post_meta( $post_id, $image, ( $update_image === '' ) ? '' : $update_image );

	}

}