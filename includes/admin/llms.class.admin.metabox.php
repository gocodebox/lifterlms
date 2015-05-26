<?php
if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! class_exists( 'LLMS_Admin_Metabox' ) ) :

// include all classes for each of the metabox types
foreach (glob(LLMS_PLUGIN_DIR . '/includes/admin/post-types/meta-boxes/fields/*.php') as $filename)
{
    include_once $filename;
}

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
						$fieldClassName = self::GenerateClass($field);
						$fieldClass = new $fieldClassName($field);
						$fieldClass->Output();	
						unset($fieldClass);													
					endforeach; ?>
				</ul>

			</div>

			<?php endforeach; ?>
			</div><!-- container -->

		<?php echo ob_get_clean();
	}

	/**
	 * This function returns a reference to a specific MetaBoxType Class
	 * (found in admin/post-types/meta-boxes/fields).
	 * 
	 * @param array $field array containing field info
	 * @return reference Reference to specific field type class
	 * @throws TypeNotFoundException Exception is thrown if field type is not found.
	 */
	public static function GenerateClass($field)
	{
		switch($field['type']) { 
			// text
			case 'text':
				return 'LLMS_Metabox_Text_Field';
			break;
			
			// textarea
			case 'textarea':
				return 'LLMS_Metabox_Textarea_Field';
			break;
					
			// textarea
			case 'textarea_w_tags':
				return 'LLMS_Metabox_Textarea_W_Tags_Field';
			break;
			
			// image
			case 'image':
				return 'LLMS_Metabox_Image_Field';
			break;					
			
			// color
			case 'color':
				return 'LLMS_Metabox_Color_Field';
			break;
			
			//checkbox
			case 'checkbox':
				return 'LLMS_Metabox_Checkbox_Field';
			break;
				
			//select
			case 'select':
				return 'LLMS_Metabox_Select_Field';
			break;
		 	
		 	//button
		 	case 'button':
		 		return 'LLMS_Metabox_Button_Field';
		 	break;
		 	
		 	//post excerpt
		 	case 'post-excerpt':
		 		return 'LLMS_Metabox_Post_Excerpt_Field';
		 	break;
		 	
		 	//post content
		 	case 'post-content':
		 		return 'LLMS_Metabox_Post_Content_Field';
		 	break;

		 	// date
		 	case 'date':
		 		return 'LLMS_Metabox_Date_Field';
		 	break;
		 	
		 	//custom html
		 	case 'custom-html':
		 		return 'LLMS_Metabox_Custom_Html_Field';
		 	break;

		 	default:
		 		// In here we could put logic to field custom post types.
		 		// These types could (in theory) be inserted into the database;
		 		// after being inserted, this part of the script could (again in theory)
		 		// run the the list of additional types and return the proper string.
		 		throw new Exception("Error. Type not found", 1);
		 		return null;
		} //end switch
	}	
}

endif;