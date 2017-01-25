<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
* Meta Box Lesson Tree
*
* Allows user to select associated syllabus and view all associated lessons
*/
class LLMS_Meta_Box_Lesson_Tree {

	/**
	 * Static output class.
	 *
	 * Displays MetaBox
	 *
	 * @param  object $post [WP post object]
	 * @return void
	 */
	public static function output( $post ) {
		global $wpdb, $post;
		wp_nonce_field( 'lifterlms_save_data', 'lifterlms_meta_nonce' );

		$parent_section_id = get_post_meta( $post->ID, '_llms_parent_section', true );
		$parent_section_id = $parent_section_id ? $parent_section_id : '';

		$parent_course_id = get_post_meta( $post->ID, '_llms_parent_course', true );
		$parent_course_id = $parent_course_id ? $parent_course_id : '';

		$all_sections = LLMS_Post_handler::get_posts( 'section' );

		$html = '';

		$html .= '<div id="llms-access-options">';
		$html = '<div class="llms-access-option">';
		$html .= '<label class="llms-access-levels-title">' .
			__( 'Associated Section', 'lifterlms' );

		if ( $parent_section_id ) {
			$html .= ': ' . get_the_title( $parent_section_id );
		}

		$html .= '</label>';

			$html .= '<select data-placeholder="Choose a section..."
				style="width:350px;"
				id="associated_section"
				single name="associated_section"
				class="chosen-select">';
			$html .= '<option value="" selected>Select a section...</option>';

		foreach ($all_sections as $key => $value) {
			if ($value->ID == $parent_section_id) {

				$html .= '<option value="' . $value->ID . '" selected >' . $value->post_title . '</option>';

			} else {
				$section_option = new LLMS_Section( $value->ID );
				$parent_course_title = get_the_title( $section_option->get_parent_course() );

				$html .= '<option value="' . $value->ID . '">' . $value->post_title . ' ( ' . $parent_course_title . ' )</option>';

			}
		}

			$html .= '</select>';
		$html .= '</div>';

		$html .= '<div class="llms-access-levels">';

		if ( $parent_course_id ) {
			$course = new LLMS_Course( $parent_course_id );
			$sections = $course->get_sections( 'posts' );

			$html .= '<span class="llms-access-levels-title"><a href="' . get_edit_post_link( $course->id ) . '">'
			. $course->post->post_title . '</a> '
			. __( 'Outline', 'lifterlms' ) . '</span>';

			if ( $sections ) {
				foreach ( $sections as $section ) {
					$section_obj = new LLMS_Section( $section->ID );
					$lessons = $section_obj->get_children_lessons();

					//section list start
					$html .= '<ul class="llms-lesson-list">';
						$html .= '<li>' . LLMS_Svg::get_icon( 'llms-icon-course-section', 'Section', 'Section', 'list-icon off' )
							. ' ' . $section->post_title;

							//lesson list start
							$html .= '<ul class="llms-lesson-list">';

					if ( $lessons ) {
						foreach ( $lessons as $lesson ) {

							if ($lesson->ID == $post->ID) {
								$html .= '<li><span>' . LLMS_Svg::get_icon( 'llms-icon-existing-lesson', 'Lesson', 'Lesson', 'list-icon off' )
								. ' ' . $lesson->post_title . '</span></li>';
							} else {
								$html .= '<li><span><a href="' . get_edit_post_link( $lesson->ID ) . '">'
									. LLMS_Svg::get_icon( 'llms-icon-existing-lesson', 'Lesson', 'Lesson', 'list-icon on' ) . ' ' . $lesson->post_title . '</a></span></li>';
							}
						}
					}

							$html .= '</ul>';

					$html .= '</li>'; //end section
					$html .= '</ul>'; //end outline
				}

			}

		}

		$html .= '</div>';

		echo $html;

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

		if ( isset( $_POST['associated_section'] ) ) {
			$parent_section = llms_clean( $_POST['associated_section'] );
			$parent_course = get_post_meta( $parent_section, '_llms_parent_course', true );
			$current_parent_section = get_post_meta( $post_id, '_llms_parent_section', true );

			if ( $current_parent_section !== $parent_section ) {

				if ( $parent_course ) {
					LLMS_Lesson_Handler::assign_to_course( $parent_course, $parent_section, $post_id, false );

				} else {

					LLMS_Admin_Meta_Boxes::add_error( __( 'There was an error assigning the lesson to a section. Please be sure a section is assigned to a course.', 'lifterlms' ) );

				}
			}
		}
	}
}
