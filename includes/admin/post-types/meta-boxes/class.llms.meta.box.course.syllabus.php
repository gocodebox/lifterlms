<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
* Meta Box Course Syllabus
*
* main course metabox for organizing lessons, sections and setting course attributes. 
*
* @version 1.0
* @author codeBOX
* @project lifterLMS
*/
class LLMS_Meta_Box_Course_Syllabus {

	/**
	 * outputs syllabus fields
	 *
	 * @return string
	 * @param string $post
	 */
	public static function output( $post ) {
		global $post;

		if ( ! get_post_meta( $post->ID, '_sections') ) {
			add_post_meta( $post->ID, '_sections', '');
		}

		$syllabus = get_post_meta( $post->ID, '_sections');
		$lesson_length = get_post_meta( $post->ID, '_lesson_length', true );

    	
	    /**
		 * get section data
		 *
		 * @return string
		 * @param string $section_id
		 */
		function get_sections_select ($section_id) {
			global $post; 
			$html = '';
			$args = array(
			    'post_type' => 'section',
			);

			$query = new WP_Query( $args );
			$html .= '<select class="section-select">';
			$html .= '<option value="" selected disabled>Select a section...</option>';
			while ( $query->have_posts() ) : $query->the_post();
			
			if ($section_id == $post->ID) {
				$html .= '<option value="'.$post->ID.'" selected="selected">'. get_the_title($post->ID) . '</option>';
			}
			else {
				$html .= '<option value="'.$post->ID.'">'. get_the_title($post->ID) . '</option>';
			}
					
			endwhile;

			$html .= '</select>';

			return $html;
		}

 		/**
		 * get lesson data
		 *
		 * @return string
		 * @param string $section_id, $section_position, $lesson_id, $lesson_position
		 */
		function get_lessons_select( $section_id, $section_position, $lesson_id, $lesson_position ) {
			global $post; 
			$html = '';
			$args = array(
			    'post_type' => 'lesson',
			);

			$lessonId = $lesson_id;
			$query = new WP_Query( $args );
			$lesson_position = $lesson_position + 1;
			
			$html .= '<tr class="list_item" data-section_id="' . $section_id . '" data-order="' . $section_position . '" style="display: table-row;"><td>';
			$html .= '<select class="lesson-select">';
			$html .= '<option value="" selected disabled>Select a course...</option>';
			
			while ( $query->have_posts() ) : $query->the_post();

				if ($lesson_id == $post->ID) {
						$html .= '<option value="'.$post->ID.'" data-lesson_id="' . $lesson_id . '" selected="selected">'. get_the_title($post->ID) . '</option>';
				}
				else {
					$html .= '<option value="'.$post->ID.'">'. get_the_title($post->ID) . '</option>';
				}

			endwhile;
			
			$html .= '</select></td><td><i data-code="f153" class="dashicons dashicons-dismiss deleteBtn"></i></td></tr>';

			return $html;
		} 
		?>

	<div>

		<?php lifterlms_wp_text_input( array( 'id' => '_lesson_length', 'label' => __( 'Course Length (in hours)', 'lifterlms' ) ) ); ?>
		
        <div class="clear"></div>
		<br />

		<?php 
		echo '<label>Course Difficulty</label><input type="hidden" name="taxonomy_noncename" id="taxonomy_noncename" value="' . 
            wp_create_nonce( 'taxonomy_course_difficulty' ) . '" />';
     
    		// Get all course_difficulty taxonomy terms
    		$difficulties = get_terms('course_difficulty', 'hide_empty=0'); 
 
		?>
		<select name='post_course_difficulty' id='post_course_difficulty'>

    	<?php 
        $names = wp_get_object_terms($post->ID, 'course_difficulty'); 
       
        if (!count($names)) {
        	echo '<option class="course_difficulty-option" value="" selected disabled>Select a difficulty...</option>';
        }

    	foreach ($difficulties as $difficulty) {
        	if (!is_wp_error($names) && !empty($names) && !strcmp($difficulty->slug, $names[0]->slug)) {
            	echo "<option class='difficulty-option' value='" . $difficulty->slug . "' selected>" . $difficulty->name . "</option>\n"; 
        	}
        	else {
            	echo "<option class='difficulty-option' value='" . $difficulty->slug . "'>" . $difficulty->name . "</option>\n"; 
        	}
    	}
   		?>
		</select>    

        <br />
        <br />
        <p><strong><?php _e('Add Course Content', 'lifterlms'); ?></strong></p>
        <a href="#" class="button" id="addNewSection"/>Add a new Section</a>
        <div id="spinner"><img id="loading" alt="WordPress loading spinner" src="/wp-admin/images/spinner.gif"></div>		
        <div id="syllabus" data-post_id="<?php echo $post->ID ?>"> 

		<?php 

		if(is_array($syllabus[0])) {
			foreach($syllabus[0] as $key => $value ) {
				echo '<div id="' . $syllabus[0][$key]['position'] . '" class="course-section"> 
						<p class="title"><label class="order">Section ' . $syllabus[0][$key]['position'] . ': </label>
							' . get_sections_select($syllabus[0][$key]['section_id']) . '
							<i data-code="f153" data-section_id="' . $syllabus[0][$key]['position'] . '" class="dashicons dashicons-dismiss section-dismiss"></i>
						</p> 
						<table class="wp-list-table widefat fixed posts dad-list"> 
						<thead><tr><th>Name</th><th></th></tr></thead> 
						<tfoot><tr><th>Name</th><th></th></tr> 
						</tfoot><tbody>';

						if (isset($syllabus[0][$key]['lessons'])) {
							foreach($syllabus[0][$key]['lessons'] as $keys => $values ) {
								echo get_lessons_select ($syllabus[0][$key]['section_id'], $syllabus[0][$key]['position'], $syllabus[0][$key]['lessons'][$keys]['lesson_id'], $syllabus[0][$key]['lessons'][$keys]['position']);
							}
						}
						
						echo '</tbody></table>
						<a class="button button-primary add-lesson addNewLesson" id="section_' 
						. $syllabus[0][$key]['position'] . '" data-section="' . $syllabus[0][$key]['position'] 
						. '"data-section_id="' . $syllabus[0][$key]['section_id'] . '">Add Lesson</a>
				</div>';	
			}
		}
		?>
		</div>
	</div>

	<?php
	}

	/**
	 * save syllabus fields (not course builder)
	 *
	 * @return string
	 * @param $post_id, $post
	 */
	public static function save( $post_id, $post ) {
		global $wpdb;

		if ( isset( $_POST['_lesson_length'] ) ) {

			$lesson_length = llms_clean( stripslashes( $_POST['_lesson_length'] ) );
			update_post_meta( $post_id, '_lesson_length', ( $lesson_length === '' ? '' : llms_format_decimal( $lesson_length ) ) );

		}

		if ( isset( $_POST['post_course_difficulty'] ) ) {

			$course_difficulty = $_POST['post_course_difficulty'];
			wp_set_object_terms( $post_id,  $course_difficulty, 'course_difficulty' );

		}

	}

}