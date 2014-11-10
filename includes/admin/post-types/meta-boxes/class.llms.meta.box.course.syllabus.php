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
		global $post, $thepostid;;

		$thepostid = $post->ID;

		if ( ! get_post_meta( $post->ID, '_sections') ) {
			add_post_meta( $post->ID, '_sections', '');
		}

		$syllabus = get_post_meta( $post->ID, '_sections');
		$lesson_length = get_post_meta( $post->ID, '_lesson_length', true );
		$course_dates_from 	= ( $date = get_post_meta( $thepostid, '_course_dates_from', true ) ) ? date_i18n( 'Y-m-d', $date ) : '';
		$course_dates_to 	= ( $date = get_post_meta( $thepostid, '_course_dates_to', true ) ) ? date_i18n( 'Y-m-d', $date ) : '';

    	
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
			    'post_status' => 'publish',
			    'nopaging' 		=> true,
			);

			$query = get_posts( $args );
			$html .= '<select class="section-select">';
			//$html .= '<option value="" selected disabled>Select a section...</option>';
			foreach ( $query as $post ) : setup_postdata( $post );
			
			if ($section_id == $post->ID) {
				$html .= '<option value="'.$post->ID.'" selected="selected">'. get_the_title($post->ID) . '</option>';
			}
			else {
				$html .= '<option value="'.$post->ID.'">'. get_the_title($post->ID) . '</option>';
			}
					
			endforeach;

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
			    'post_status' => 'publish',
			    'nopaging' 		=> true,
			);
LLMS_log($section_position);
			$lessonId = $lesson_id;

			$query = null;
			$query = get_posts( $args );
			$lesson_position = $lesson_position + 1;
			
			$html .= '<tr class="list_item" id="row_' . $section_position . '_' . $lesson_position . '" data-section_id="' . $section_id . '" data-order="' . $section_position . '" style="display: table-row;"><td>';
			$html .= '<select id="list_item_' . $section_position . '_' . $lesson_position . '" class="lesson-select">';

			foreach ( $query as $post ) : setup_postdata( $post );

				if ($lesson_id == $post->ID) {
						$html .= '<option value="'.$post->ID.'" data-lesson_id="' . $lesson_id . '" selected="selected">'. get_the_title($post->ID) . '</option>';
				}
				else {
					$html .= '<option value="'.$post->ID.'">'. get_the_title($post->ID) . '</option>';
				}

			endforeach;
			
			$html .= '</select></td><td>
				<a href="' . get_edit_post_link($lesson_id) . '"><i class="fa fa-pencil-square-o llms-fa-edit-lesson"></i></a>
				<i class="fa fa-bars llms-fa-move-lesson"></i><i data-code="f153" class="dashicons dashicons-dismiss deleteBtn"></i>
				</td></tr>';

			return $html;
		} 
		?>

	<div>

	<table class="form-table">
		<tbody>
			<tr>
				<th><label for="_lesson_length">Course Length</label></th>
				<td>
					<input type="text" name="_lesson_length" id="_lesson_length" value="<?php echo $lesson_length ?>">
					<br /><span class="description">Enter a description of the estimated length. IE: 3 days</span>
				</td>
			</tr>
			<tr>
				<th><label>Course Availabilty</label></th>
				<td>
				<?php
					echo '	
					Begins <input type="text" class="datepicker short" name="_course_dates_from" id="_course_dates_from" value="' . esc_attr( $course_dates_from ) . '" placeholder="' . _x( 'From&hellip;', 'placeholder', 'lifterlms' ) . ' YYYY-MM-DD" maxlength="10" />
					Ends <input type="text" class="datepicker short" name="_course_dates_to" id="_course_dates_tp" value="' . esc_attr( $course_dates_to ) . '" placeholder="' . _x( 'To&hellip;', 'placeholder', 'lifterlms' ) . '  YYYY-MM-DD" maxlength="10" />';
				?>
				<br /><span class="description">Enter a Begin and/or End date for the course if it will only be available for a set period of time.</span>
				</td>
			</tr>

			<tr>
				<th>
					<?php  
					echo '<label>Course Difficulty</label><input type="hidden" name="taxonomy_noncename" id="taxonomy_noncename" value="' . 
			            wp_create_nonce( 'taxonomy_course_difficulty' ) . '" />';
			     
					// Get all course_difficulty taxonomy terms
					$difficulties = get_terms('course_difficulty', 'hide_empty=0'); 
					?>
				</th>
				<td>
					<select name='post_course_difficulty' id='post_course_difficulty'>
				    	<?php 
				        $names = wp_get_object_terms($post->ID, 'course_difficulty'); 
				       
				        if (!count($names)) {
				        	echo '<option class="course_difficulty-option" value="" selected disabled>Select a difficulty...</option>';
				        }
				        echo '<option class="course_difficulty-option" value="">None</option>';
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
				</td>
			</tr>
		</tbody>
	</table>

	
    <h2><?php _e('Create Course Syllabus', 'lifterlms'); ?></h2>
    <a href="#" class="button" id="addNewSection"/>Add a new Section</a>
    <div id="spinner"><img id="loading" alt="WordPress loading spinner" src="<?php echo admin_url('images/spinner.gif'); ?>"></div>		
    <div id="syllabus" data-post_id="<?php echo $post->ID ?>"> 

	<?php 

	if(is_array($syllabus[0])) {
		foreach($syllabus[0] as $key => $value ) {
			echo '<div id="' . $syllabus[0][$key]['position'] . '" class="course-section"> 
					<p class="title"><label class="order">Section ' . $syllabus[0][$key]['position'] . ': </label>
						' . get_sections_select($syllabus[0][$key]['section_id']) . '
						<a href="' . get_edit_post_link($syllabus[0][$key]['section_id']) . '"><i class="fa fa-pencil-square-o llms-fa-edit-lesson"></i></a>
						<i class="fa fa-bars llms-fa-move-lesson"></i>
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
	 * save syllabus fields (not course builder data)
	 *
	 * @return string
	 * @param $post_id, $post
	 */
	public static function save( $post_id, $post ) {
		global $wpdb;

		//if ( isset( $_POST['_course_start_date'] ) ) {
//Update Sales Price Dates
		$date_from = isset( $_POST['_course_dates_from'] ) ? $_POST['_course_dates_from'] : '';
		$date_to = isset( $_POST['_course_dates_to'] ) ? $_POST['_course_dates_to'] : '';

		// Dates
		if ( $date_from )
			update_post_meta( $post_id, '_course_dates_from', strtotime( $date_from ) );

		else
			update_post_meta( $post_id, '_course_dates_from', '' );

		if ( $date_to )

			update_post_meta( $post_id, '_course_dates_to', strtotime( $date_to ) );
		else
			update_post_meta( $post_id, '_course_dates_to', '' );

		if ( $date_to && ! $date_from )
			update_post_meta( $post_id, '_course_dates_from', strtotime( 'NOW', current_time( 'timestamp' ) ) );

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