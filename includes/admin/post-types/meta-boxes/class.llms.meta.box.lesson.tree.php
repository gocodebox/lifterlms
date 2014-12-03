<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
* Meta Box Video
*
* diplays text input for oembed video
*
* @version 1.0
* @author codeBOX
* @project lifterLMS
*/
class LLMS_Meta_Box_Lesson_Tree {

	/**
	 * Set up video input
	 *
	 * @return string
	 * @param string $post
	 */
	public static function output( $post ) {
		global $wpdb, $post;
		wp_nonce_field( 'lifterlms_save_data', 'lifterlms_meta_nonce' );

		$parent_section = get_post_meta( $post->ID, '_parent_section', true );
		$parent_course = get_post_meta($post->ID, '_parent_course', true);
		$section_edit_link = '';
		$section_edit_link_html = '';
		$lessons = '';

		
		$section_args = array(
			'posts_per_page'   => -1,
			'post_status'      => 'publish',
			'orderby'          => 'title',
			'order'            => 'ASC',
			'post_type'        => 'section',
			'suppress_filters' => true 
		); 
		$sections = get_posts($section_args);

		if ( $parent_section ) {

			$parent_course_id = get_post_meta($parent_section, '_parent_course', true);
			
			if ($parent_course_id) {
				$course = new LLMS_Course($parent_course_id);
				$syllabus = $course->get_syllabus(); 
				

				//get associated lessons in same section
				foreach($syllabus as $key => $value) {
					if ($value['section_id'] == $parent_section) {
						$lessons = $value['lessons'];
					}
				}
			}
			else {
				$args = array(
					'post_type'   => 'lesson',
					'meta_query'  => array(
					array(
						'key' => '_parent_section',
						'value' => $parent_section,
						)
					)
				);
				$lessons_query = get_posts($args);
				$lessons = array();
				$i = 0;
				foreach($lessons_query as $key => $value) {
					$lessons[$i]['lesson_id'] = $value->ID;
					$lessons[$i]['position'] = $i + 1;
					$i++;
				}
				
			}

			$section_edit_link = get_edit_post_link($parent_section);
			$section_edit_link_html = '<a href="' . $section_edit_link .'">(View Section)</a>';
		}

		?>

		<div id="llms-access-options">
			<div class="llms-access-option">
				<label class="llms-access-levels-title"><?php _e('Associated Section ' . $section_edit_link_html, 'lifterlms') ?></label>
				<select data-placeholder="Choose a section..." style="width:350px;" id="associated_section" single name="associated_section" class="chosen-select">
					<option value="" selected>Select a section...</option>
					<?php foreach($sections as $key => $value) { 
							if ($value->ID == $parent_section) {
					?>
								<option value="<?php echo $value->ID; ?>" selected ><?php echo $value->post_title; ?></option>
							<?php } else { ?>
						<option value="<?php echo $value->ID; ?>"><?php echo $value->post_title; ?></option>
					<?php } } ?>
				</select>
			</div>

			<div class="llms-access-levels">
			
				<span class="llms-access-levels-title"><?php _e( 'Lessons in associated section', 'lifterlms' ) ?></span> 
					<?php
					if ($lessons) :
						echo '<ul class="llms-lesson-list">';
							foreach ($lessons as $key => $value) :
							
								$lesson = get_post($value['lesson_id']);
							
								if ($lesson->ID == $post->ID) {
									echo '<li><span><i class="fa fa-book"></i> ' . $lesson->post_title . '</span></li>';
								}
								else {
									echo '<li><span><a href="' . get_edit_post_link($lesson->ID) . '"><i class="fa fa-book"></i> ' . $lesson->post_title . '</a></span></li>';
								}
							endforeach;
						echo '</ul>';
					endif;
					?>
			</div>
		</div>

		<?php  
	}

	public static function save( $post_id, $post ) {
		global $wpdb;

		//get post data
		if (isset($_POST['associated_section'])) {
			$parent_section = ( llms_clean( $_POST['associated_section']  ) );

			//if parent section has not changed do nothing
			if($parent_section == get_post_meta($post_id, '_parent_section', true)) {
				return;
			}

			if (empty($parent_section)) {
				delete_post_meta($post_id, '_parent_section', $parent_section);
			}

			//check if lesson is already assigned to a course and if it is remove it from the previous course syllabus
			if ($prev_parent_course_id = get_post_meta($post_id, '_parent_course', true)) {
				//if parent course already assigned remove it from course _sections array
				$prev_parent_course = new LLMS_Course($prev_parent_course_id);
				$prev_syllabus = $prev_parent_course->get_syllabus();

				//remove lesson from course syllabus
				foreach($prev_syllabus as $key => $value) {
					foreach($value['lessons'] as $keys => $values) {
						if ($values['lesson_id'] == $post_id) {
							unset($prev_syllabus[$key]['lessons'][$keys]);
							$prev_syllabus[$key]['lessons']  = array_values($prev_syllabus[$key]['lessons']);
						}
					}
				}

				update_post_meta($prev_parent_course_id, '_sections', $prev_syllabus);
				delete_post_meta($post_id, '_parent_course', $prev_parent_course_id);
			}

			//if section is assigned to a course then update course syllabus
			//two ways to be associated 
			//1. _parent_course as of 1.0.5
			if (get_post_meta($parent_section, '_parent_course', true)) {
				$parent_course = get_post_meta($parent_section, '_parent_course', true);
				//if section is assigned to course add lesson to course syllabus
			}
			//2. loop through courses and look for _section_id that matches DEPRICATED (will be removed)
			else {
				$course_args = array(
					'posts_per_page'   => -1,
					'post_status'      => 'publish',
					'orderby'          => 'title',
					'order'            => 'ASC',
					'post_type'        => 'course',
					'suppress_filters' => true 
				); 
				$courses = get_posts($course_args);
				foreach($courses as $key => $value) {
					$course = new LLMS_Course($value->ID);
					$sections = $course->get_sections();
					if (!empty($sections)) {
						if (in_array($parent_section, $sections)) {
							$parent_course = $value->ID;
							break;
						}
					}
				}
				if (isset($parent_course)) {
					//in order to remove depreciated method update section _parent_course if it does not exist
					update_post_meta($parent_section, '_parent_course', $parent_course);
				}
			}

			//if parent course is found for section then update course syllabus
			if(!empty($parent_course)) {
				$course = new LLMS_Course($parent_course);
				$syllabus = $course->get_syllabus();

				foreach($syllabus as $key => $value) {
					if ($value['section_id'] == $parent_section) {

						$lesson_count = count($value['lessons']);
						$lesson_tree = array();
						$lesson_tree['lesson_id'] = $post_id;
						$lesson_tree['position'] = $lesson_count + 1;

						if (!$syllabus[$key]['lessons']) {
							$syllabus[$key]['lessons']  =array();
						}
						array_push($syllabus[$key]['lessons'], $lesson_tree);
						//add lesson to course syllabus
						update_post_meta($course->id, '_sections', $syllabus);
					}
				}
	
				//update lesson _parent_course post meta
				update_post_meta($post_id, '_parent_course', $course->id);
			}

			//update lesson _parent_section post meta
			update_post_meta($post_id, '_parent_section', $parent_section);
		}

	}

}