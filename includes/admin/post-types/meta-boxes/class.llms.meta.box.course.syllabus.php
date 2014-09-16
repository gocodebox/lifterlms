<?php
/**
 * Course Syllabus
 *
 * @author 		codeBOX
 * @category 	Admin
 * @package 	lifterLMS/Admin/Meta Boxes
 * @version     0.1
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * LLMS_Meta_Course_Syllabus
 */
class LLMS_Meta_Box_Course_Syllabus{

	/**
	 * Output the metabox
	 */
	public static function output( $post ) {
		global $post;

		if ( ! get_post_meta( $post->ID, '_sections') ) {
			add_post_meta( $post->ID, '_sections', '');
		}

		$syllabus = get_post_meta( $post->ID, '_sections');

    	
		function get_sections_select ($section_id) {
			global $post; 
			$html = '';
			$args = array(
			    'post_type' => 'section',
			);

			$query = new WP_Query( $args );
			$html .= '<select class="section-select">';
			$html .= '<option value="" selected disabled>Please select a a section...</option>';
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


		function get_lessons_select ($section_id, $section_position, $lesson_id, $lesson_position) {
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
			$html .= '<option value="" selected disabled>Please select a course...</option>';
			
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
		<label for="lesson_length">Estimated Time to complete course (In hours)</label>
		<input type="number" step=".5" id="lesson-length" name="lesson_length" value="BAHHHHH" size="25" class="regular-text ltr" />
        <div class="clear"></div>
		<label for="lesson_complexity">Course Difficulty</label>
		<select id="lesson-complexity-options" name="lesson_complexity" class="select lesson-complexity-select">
			<option value="">None</option>
			<option value="">Easy</option>
			<option value="">Normal</option>
        	<option value="">Hard</option>
		</select>
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
}