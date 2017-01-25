<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
* Meta Box General
*
* diplays text input for oembed general
*
* @version 1.0
* @author codeBOX
* @project lifterLMS
*/
class LLMS_Meta_Box_Course_Outline {

	public static function section_tile( $section_id ) {

		$section = get_post( $section_id );
		$section_obj = new LLMS_Section( $section->ID );

		$html = '<li class="ui-state-default llms-section">';

		$html .= '<div class="section-content-wrapper d-all t-all m-all">';
		$html .= '<div class="description d-3of4 t-3of4 m-all">';
		$html .= LLMS_Svg::get_icon( 'llms-icon-circle', 'Section', 'Section', 'tree-icon' );
		$html .= ' ' . __( 'Section', 'lifterlms' ) . ' <span class="llms-section-order">' . $section_obj->get_order() . '</span>:
			<span class="llms-section-title">' . $section->post_title . '</span>';
		$html .= '</div>';

		$html .= '<div class="list-options d-1of4 t-1of4 m-all last-col">';

		//delete link
		$html .= '<a href="#" class="llms-delete-section-link llms-button-danger square" data-modal_id="llms-delete-section-modal" data-modal_title="Delete Section"><span class="dashicons dashicons-no"></span></a>';
		// $html .= LLMS_Svg::get_icon( 'llms-icon-close', 'Delete Section', 'Delete Section', 'button-icon' );
		// $html .= '</a>';
		//edit link
		$html .= '<a href="#" class="llms-edit-section-link llms-button-primary square" data-modal_id="llms-edit-section-modal" data-modal_title="Edit Section"><span class="dashicons dashicons-admin-generic"></span></a>';
		// $html .= LLMS_Svg::get_icon( 'llms-icon-gear', 'Edit Section', 'Edit Section', 'button-icon' );
		// $html .= '</a>';

		$html .= '</div>';

		$html .= '<input type="hidden" name="llms_section_id[]" value="' . $section->ID . '">';
			$html .= '<input type="hidden" name="llms_section_order[]" value="' . $section_obj->get_order() . '">';

			$html .= '</div>'; //end section content
			$html .= '<div class="clear"></div>';
			//lesson tree
			$html .= '<ul id="llms_section_tree_' . $section->ID . '" class="llms-lesson-tree">';

			$lessons = $section_obj->get_children_lessons();
		foreach ( $lessons as $lesson ) {
			$html .= self::lesson_tile( $lesson->ID, $section->ID );
		}
			$html .= '</ul>';
		//
			$html .= '<div class="clear"></div>';
	   	$html .= '</li>';

	   	return $html;

	}

	public static function lesson_tile( $lesson_id, $section_id ) {

		//$lesson = get_post($lesson_id);
		$lesson = new LLMS_Lesson( $lesson_id );

		$html = '<li class="ui-state-default llms-lesson" data-id="' . $lesson->id . '">';

		$html .= '<div class="lesson-content-wrapper d-all t-all m-all">';

		$html .= '<div class="description d-2of3 t-2of3 m-all">';

		$html .= LLMS_Svg::get_icon( 'llms-icon-circle-empty', 'Lesson', 'Lesson', 'tree-icon' );
		$html .= ' ' . __( 'Lesson', 'lifterlms' ) . ' <span class="llms-lesson-order">' . $lesson->get_order() . '</span>:
		<span class="llms-lesson-title">' . $lesson->post->post_title . '</span>';

		$html .= '<p class="llms-lesson-excerpt">';
		$html .= '<span class="llms-section-excerpt">' . $lesson->post->post_excerpt . '</span>';
		$html .= '</p>';

		$html .= '</div>'; //end description

		$html .= '<div class="list-options d-1of3 t-1of3 m-all last-col">';

		$html .= '<div class="list-lesson-links d-all t-all m-all">';
		//remove link
		$html .= '<a href="#" class="llms-remove-lesson-link llms-button-danger square"><span class="dashicons dashicons-no"></span></a>';
		// $html .= LLMS_Svg::get_icon( 'llms-icon-close', 'Remove Lesson', 'Remove Lesson', 'button-icon' );
		// $html .= '</a>';
		//edit link
		$html .= '<a href="#" class="llms-edit-lesson-link llms-button-primary square" data-modal_id="llms-edit-lesson-modal" data-modal_title="Edit Lesson"><span class="dashicons dashicons-admin-generic"></span></a>';
		// $html .= LLMS_Svg::get_icon( 'llms-icon-gear', 'Edit Lesson', 'Edit Lesson', 'button-icon' );
		// $html .= '</a>';
		//link to lesson post editor
		$html .= '<a href="' . get_edit_post_link( $lesson->id ) . '" class="llms-edit-lesson-content-link llms-button-primary">';
		$html .= __( 'Edit Content', 'lifterlms' ) . ' <span class="dashicons dashicons-plus"></span>';
		// $html .= 'Edit Content ' . LLMS_Svg::get_icon( 'llms-icon-plus', 'Edit Lesson Content', 'Edit Lesson Content', 'button-icon-attr' );
		$html .= '</a>';
		$html .= '</div>'; //end links

		//lesson information icons
		$html .= '<div class="list-lesson-details d-all t-all m-all">';
		$html .= self::get_lesson_details( $lesson );

		$html .= '</div>'; //end details

		$html .= '</div>'; //end options

		//hidden fields
		$html .= '<input type="hidden" name="llms_lesson_id[]" value="' . $lesson->id . '">';
		$html .= '<input type="hidden" name="llms_lesson_parent_section[]" value="' . $section_id . '">';
		$html .= '<input type="hidden" name="llms_lesson_order[]" value="' . $lesson->get_order() . '">';

		$html .= '</div>'; //end lesson-content-wrapper
		$html .= '<div class="clear"></div>';
		$html .= '</li>';

	   	return $html;

	}

	public static function create_course_dialog() {

		$html = '<div id="pop1" class="topModal">';
		$html .= '<div class="llms-modal-content">';

		$html .= '<div class="llms-modal-form">';
		$html .= '<h1>' . __( 'Ready to create a course?', 'lifterlms' ) . '</h1>';

		$html .= '<form>';
		$html .= '<label>' . __( 'Start by entering the title of a course:', 'lifterlms' ) . '</label>';
		$html .= '<input type="text" name="llms-course-name" id="llms-course-name"
			placeholder="' . __( 'Enter a name for your course', 'lifterlms' ) . '">';
		$html .= '<input type="hidden" name="llms-course-setup" id="llms-course-setup value="yes">';
		$html .= '<input type="submit" class="llms-button-primary full" id="llms-create-course-submit" value="'
			. __( 'Create Course', 'lifterlms' ) . '">';
		$html .= '</form>';

		$html .= '</div>';
		$html .= '</div></div>';

		return $html;
	}

	public static function new_section_dialog() {

		$html = '<div id="pop2" class="topModal">';
		$html .= '<div class="llms-modal-content">';

	    $html .= '<div id="llms-add-new-section" class="llms-modal-form">';
	    $html .= '<h3>' . __( 'Add a new section', 'lifterlms' ) . '</h3>';

	    //form
	    $html .= '<form id="llms_create_section">';
	    $html .= '<label>' . __( 'Enter the title of your new section', 'lifterlms' ) . '</label>';
	    $html .= '<input type="text" name="llms_section_name" id="llms-section-name"
	    	placeholder="' . __( 'Enter a title for the section', 'lifterlms' ) . '">';

	    $html .= '<input type="submit" class="llms-button-secondary llms-modal-cancel" value="' . __( 'Cancel', 'lifterlms' ) . '">';
	    $html .= '<input type="submit" class="llms-button-primary" value="' . __( 'Create Section', 'lifterlms' ) . '">';
	    $html .= '</form>';
	    //end form

	    $html .= '</div>';
	 	$html .= '</div>'; //end new section thickbox
	 	$html .= '</div>';

	 	return $html;

	}

	public static function edit_section_dialog() {

		$html = '<div id="llms-edit-section-modal" class="topModal">';
		$html .= '<div class="llms-modal-content">';

	    $html .= '<div id="llms-edit-section" class="llms-modal-form">';
	    $html .= '<h3>' . __( 'Edit section title', 'lifterlms' ) . '</h3>';

	    //form
	    $html .= '<form id="llms_edit_section">';
	    $html .= '<label>' . __( 'Edit the section title', 'lifterlms' ) . '</label>';
	    $html .= '<input type="text" name="llms_section_edit_name" id="llms-section-edit-name"
	    	placeholder="' . __( 'Enter a title for the section', 'lifterlms' ) . '">';

	    $html .= '<input type="hidden" name="llms_section_edit_id" id="llms-section-edit-id" value="">';
	    $html .= '<input type="submit" class="llms-button-secondary llms-modal-cancel" value="' . __( 'Cancel', 'lifterlms' ) . '">';
	    $html .= '<input type="submit" class="llms-button-primary" value="' . __( 'Update Section', 'lifterlms' ) . '">';
	    $html .= '</form>';
	    //end form

	    $html .= '</div>';
	 	$html .= '</div>'; //end new section thickbox
	 	$html .= '</div>';

	 	return $html;

	}

	public static function delete_section_dialog() {

		$html = '<div id="llms-delete-section-modal" class="topModal">';
		$html .= '<div class="llms-modal-content">';

	    $html .= '<div id="llms-delete-section" class="llms-modal-form">';
	    $html .= '<h3>' . __( 'Delete Section', 'lifterlms' ) . '</h3>';

	    //form
	    $html .= '<form id="llms_delete_section">';

	    $html .= '<p>' . __( 'Are you sure you want to delete this section?', 'lifterlms' )
	    	. '</p>';
	    $html .= '<p>' . __(
		'Deleting this section will remove all associated lessons from the course. Associated lessons will NOT be deleted.', 'lifterlms' )
	    	. '</p>';

	    $html .= '<input type="hidden" name="llms_section_delete_id" id="llms-section-delete-id" value="">';
	    $html .= '<input type="submit" class="llms-button-secondary llms-modal-cancel" value="' . __( 'Cancel', 'lifterlms' ) . '">';
	    $html .= '<input type="submit" class="llms-button-primary" value="' . __( 'Delete Section', 'lifterlms' ) . '">';
	    $html .= '</form>';
	    //end form

	    $html .= '</div>';
	 	$html .= '</div>'; //end new section thickbox
	 	$html .= '</div>';

	 	return $html;

	}

	public static function new_lesson_dialog() {

		$html = '<div id="llms-add-new-lesson-modal" class="topModal">';
		$html .= '<div class="llms-modal-content">';

	    $html .= '<div id="llms-add-new-lesson" class="llms-modal-form">';
	    $html .= '<h3>' . __( 'Add a new lesson', 'lifterlms' ) . '</h3>';

	    //form
	    $html .= '<form id="llms_create_lesson">';

	    $html .= '<label>' . __( 'Enter the title of your new lesson', 'lifterlms' ) . '</label>';
	    $html .= '<input type="text" name="llms_lesson_name" id="llms-lesson-name"
	    placeholder="' . __( 'Enter a title for the lesson', 'lifterlms' ) . '">';

	    $html .= '<label>' . __( 'Enter the short description', 'lifterlms' ) . '</label>';
	    $html .= '<textarea name="llms_lesson_excerpt"
	    	placeholder="' . __( 'Enter a brief description of the lesson...', 'lifterlms' ) . '"></textarea>';

	    $html .= '<label>' . __( 'Select the section to place your new lesson in', 'lifterlms' ) . '</label>';
	    $html .= '<select id="llms-section-select" name="llms_section" class="llms-chosen-select"></select>';

	    $html .= '<input type="submit" class="llms-button-secondary llms-modal-cancel" value="' . __( 'Cancel', 'lifterlms' ) . '">';
	    $html .= '<input type="submit" class="llms-button-primary" value="' . __( 'Create Lesson', 'lifterlms' ) . '">';
	    $html .= '</form>';
	    //end form

	    $html .= '</div>';
	 	$html .= '</div>'; //end new section thickbox
	 	$html .= '</div>';

	 	return $html;

	}

	public static function add_existing_lesson_dialog() {

		$html = '<div id="llms-add-existing-lesson-modal" class="topModal">';
		$html .= '<div class="llms-modal-content">';

	    $html .= '<div id="llms-add-existing-lesson" class="llms-modal-form">';
	    $html .= '<h3>' . __( 'Add an existing lesson', 'lifterlms' ) . '</h3>';
	    $html .= '<p>';
	    $html .= __('You can add any lesson you have previously created.
	    	If the lesson is already assigned to a course a duplicate lesson will be created for you.', 'lifterlms' );
	    $html .= '</p>';
	    //form
	    $html .= '<form id="llms_add_existing_lesson">';

	    $html .= '<label>' . __( 'Select the lesson you would like to add.', 'lifterlms' ) . '</label>';
	    $html .= '<select id="llms-lesson-select" name="llms_lesson" class="llms-select2-post" data-placeholder="' . __( 'Select a lesson.', 'lifterlms' ) . '" data-post-type="lesson"></select>';

	    $html .= '<label>' . __( 'Select the section to place your lesson in', 'lifterlms' ) . '</label>';
	    $html .= '<select id="llms-section-select" name="llms_section" class="llms-select2"></select><br><br>';

	    $html .= '<input type="submit" class="llms-button-secondary llms-modal-cancel" value="' . __( 'Cancel', 'lifterlms' ) . '">';
	    $html .= '<input type="submit" class="llms-button-primary" value="' . __( 'Add Lesson', 'lifterlms' ) . '">';
	    $html .= '</form>';
	    //end form

	    $html .= '</div>';
	 	$html .= '</div>'; //end new section thickbox
	 	$html .= '</div>';

	 	return $html;

	}

	public static function edit_lesson_dialog() {

		$html = '<div id="llms-edit-lesson-modal" class="topModal">';
		$html .= '<div class="llms-modal-content">';

	    $html .= '<div id="llms-edit-lesson" class="llms-modal-form">';
	    $html .= '<h3>' . __( 'Edit Lesson', 'lifterlms' ) . '</h3>';

	    //form
	    $html .= '<form id="llms_edit_lesson">';

	    $html .= '<label>' . __( 'Update the title of the lesson', 'lifterlms' ) . '</label>';
	    $html .= '<input type="text" name="llms_lesson_edit_name" id="llms-lesson-edit-name"
	    placeholder="' . __( 'Enter a title for the lesson', 'lifterlms' ) . '">';

	    $html .= '<label>' . __( 'Update the short description', 'lifterlms' ) . '</label>';
	    $html .= '<textarea name="llms_lesson_edit_excerpt" id="llms-lesson-edit-excerpt"
	    	placeholder="' . __( 'Enter a brief description of the lesson...', 'lifterlms' ) . '"></textarea>';

	    $html .= '<input type="hidden" name="llms_lesson_edit_id" id="llms-lesson-edit-id" value="">';
	    $html .= '<input type="submit" class="llms-button-secondary llms-modal-cancel" value="' . __( 'Cancel', 'lifterlms' ) . '">';
	    $html .= '<input type="submit" class="llms-button-primary" value="' . __( 'Update Lesson', 'lifterlms' ) . '">';
	    $html .= '</form>';
	    //end form

	    $html .= '</div>';
	 	$html .= '</div>'; //end new section thickbox
	 	$html .= '</div>';

	 	return $html;

	}

	/**
	 * Set up general input
	 *
	 * @return string
	 * @param string $post
	 */
	public static function output( $post ) {

		if ( ! $post || 'auto-draft' === $post->post_status ) {
			_e( 'Your course must be published or saved as a draft before you can add sections and lessons to it.', 'lifterlms' );
			return;
		}

		$course = new LLMS_Course( $post->ID );
		$sections = $course->get_sections( 'posts' );

		$html = '';

		//dialog boxes
		$html .= self::create_course_dialog();
		$html .= self::new_section_dialog();
		$html .= self::new_lesson_dialog();
		$html .= self::add_existing_lesson_dialog();
		$html .= self::edit_section_dialog();
		$html .= self::edit_lesson_dialog();
		$html .= self::delete_section_dialog();

			$html .= '<div id="llms-course-outline">';

			//outline header
			$html .= '<div id="llms-ouline-header">';

			$html .= '<div class="clear-fix">';
			$html .= '<div class="m-3of4 t-3of4 d-3of4">';

		$html .= '<h1 class="outline-title">' . __( 'Course Outline', 'lifterlms' ) . '</h1>';

		$html .= '</div>';

		$html .= '<div class="m-1of4 t-1of4 d-1of4 last-col d-right">';

		//add button
			$html .= '<div class="menu-button">';

		$html .= '<button id="llms-outline-add" class="llms-button-primary bt">' . __( 'Add', 'lifterlms' ) . '</button>';

			$html .= '<div id="llms-outline-menu">';

			$html .= '<div id="triangle"></div>';

			$html .= '<div id="tooltip_menu">';

			//add new section link
			$html .= '<a href="#" class="llms-modal" data-modal_id="pop2" data-modal_title="Create Section">';
			$html .= LLMS_Svg::get_icon( 'llms-icon-course-section', 'Add a section', 'Add a section to the course.', 'menu-icon' );
			$html .= __( 'Add New Section', 'lifterlms' );
			$html .= '</a>';

			$html .= '<a href="#" class="llms-modal-new-lesson-link"
   			data-modal_id="llms-add-new-lesson-modal" data-modal_title="Create Lesson">';
			$html .= LLMS_Svg::get_icon( 'llms-icon-new-lesson', 'Add New Lesson', 'Add a new lesson.', 'menu-icon' );
			$html .= __( 'Add New Lesson', 'lifterlms' );
			$html .= '</a>';

			$html .= '<a href="#" class="llms-modal-existing-lesson-link menu_bottom"
   			data-modal_id="llms-add-existing-lesson-modal" data-modal_title="Add Existing Lesson">';
			$html .= LLMS_Svg::get_icon( 'llms-icon-existing-lesson', 'Add Existing Lesson', 'Add an existing lesson.', 'menu-icon' );
			$html .= __( 'Add Existing Lesson', 'lifterlms' );
			$html .= '</a>';

			$html .= '</div>';
			$html .= '</div>';
			$html .= '</div>'; //end add button
			$html .= '</div>'; //end clearfix
		$html .= '</div>';
			$html .= '</div>'; //end outline header

			$html .= '<div class="outline-body">';

			//Course Outline (sections and child lessons)
			$html .= '<ul id="llms_course_outline_sort" class="sortablewrapper">';
		foreach ( $sections as $section ) {
			$html .= self::section_tile( $section->ID );
		}
			$html .= '</ul>';

			$html .= '</div>'; //end outline body
			$html .= '</div>'; //end course outline

			echo $html;

	}

	public static function get_lesson_details( $lesson ) {

		// $lesson_details = array(
		// 	'prerequisite' => $lesson->get_prerequisite(),
		// 	'assigned_quiz' => $lesson->get_assigned_quiz(),
		// 	'drip_days' => get_drip_days(),
		// 	'content' =>
		// );

		$html = '<div class="llms-lesson-details">';

		//prerequisite
		if ( $lesson->has_prerequisite() ) {
			$icon_class = 'detail-icon on';
			$tooltip = sprintf( __( 'Prerequisite: %s', 'lifterlms' ), get_the_title( $lesson->get( 'prerequisite' ) ) );
		} else {
			$icon_class = 'detail-icon off';
			$tooltip = __( 'No Prerequisite', 'lifterlms' );
		}

			$html .= '<a href="#" class="tooltip"
				title="' . $tooltip . '">
				<span title="">';
				$html .= LLMS_Svg::get_icon( 'llms-icon-lock', 'Prerequisite', $tooltip, $icon_class );
			$html .= '</span></a></a>';

		if ( $quiz_id = $lesson->get( 'assigned_quiz' ) ) {
			$icon_class = 'detail-icon on';
			$tooltip = sprintf( __( 'Assigned Quiz: %s', 'lifterlms' ), get_the_title( $quiz_id ) );
		} else {
			$icon_class = 'detail-icon off';
			$tooltip = __( 'No Assigned Quiz', 'lifterlms' );
		}

			$html .= '<a href="#" class="tooltip"
				title="' . $tooltip . '">
				<span title="">';
				$html .= LLMS_Svg::get_icon( 'llms-icon-question', 'Quiz', $tooltip, $icon_class );
			$html .= '</span></a></a>';

		if ( $method = $lesson->get( 'drip_method' ) ) {

			$icon_class = 'detail-icon on';

			switch ( $method ) {

				case 'date':
					$tooltip = sprintf( __( 'Drip Delay: %s' ), $lesson->get_date( 'date_available', 'n/j/Y' ) );
				break;

				case 'enrollment':
				case 'start':
					$tooltip = sprintf( __( 'Drip Delay: %d days' ), $lesson->get( 'days_before_available' ) );
				break;

			}

		} else {
			$icon_class = 'detail-icon off';
			$tooltip = __( 'No Drip Delay', 'lifterlms' );
		}

			$html .= '<a href="#" class="tooltip"
				title="' . $tooltip . '">
				<span title="">';
				$html .= LLMS_Svg::get_icon( 'llms-icon-calendar', 'Drip Content', $tooltip, $icon_class );
			$html .= '</span></a></a>';

		if ( $lesson->has_content() ) {
			$icon_class = 'detail-icon on';
			$tooltip = __( 'Lesson has content', 'lifterlms' );
		} else {
			$icon_class = 'detail-icon off';
			$tooltip = __( 'No Lesson Content', 'lifterlms' );
		}

			$html .= '<a href="#" class="tooltip"
				title="' . $tooltip . '">
				<span title="">';
				$html .= LLMS_Svg::get_icon( 'llms-icon-paper', 'Lesson Content', $tooltip, $icon_class );
			$html .= '</span></a></a>';

		$html .= '</div>';

		return $html;

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

		/**
		 * New Course Creation Block
		 * if no sections exist create a sample section and lesson and append them to the course.
		 */
		if ( empty( $_POST['llms_section_id'] ) ) {

			//create new section and lesson
			$section_id = LLMS_Post_Handler::create( 'section', 'Your first section' );
			$lesson_id = LLMS_Post_Handler::create( 'lesson', 'Your first lesson', 'Short description of your first lesson.' );

			if ( $section_id && $lesson_id ) {

				//if section and lesson were created make them sort order of #1
				update_post_meta( $section_id, '_llms_order', 1 );
				update_post_meta( $lesson_id, '_llms_order', 1 );

				//add new section to course and new lesson to section
				$section = new LLMS_Section( $section_id );
				$updated_parent_course = $section->set_parent_course( $post_id );

				//only add lesson to section if section
				if ( $updated_parent_course ) {

					$lesson = new LLMS_Lesson( $lesson_id );
					$updated_parent_section = $lesson->set_parent_section( $section_id );

					//if lesson added to section then update lesson parent course
					if ( $updated_parent_section ) {

						$update_course_association = $lesson->set_parent_course( $post_id );

					}

				}

			}

		}

		//save section order
		if ( isset( $_POST['llms_section_id'] ) && isset( $_POST['llms_section_order'] ) ) {

			$sections = $_POST['llms_section_id'];
			$sections_order = $_POST['llms_section_order'];

			foreach ( $sections as $key => $section_id ) {
				$section_id = llms_clean( $section_id );
				$section_order = llms_clean( $sections_order[ $key ] );
				update_post_meta( $section_id, '_llms_order', $section_order );
			}

		}

		//save lesson order and parent section
		if ( isset( $_POST['llms_lesson_id'] )
			&& isset( $_POST['llms_lesson_parent_section'] )
			&& isset( $_POST['llms_lesson_order'] )
		) {

			$lessons = $_POST['llms_lesson_id'];
			$parent_sections = $_POST['llms_lesson_parent_section'];
			$lessons_order = $_POST['llms_lesson_order'];

			foreach ( $lessons as $key => $lesson_id ) {

				$lesson_id = llms_clean( $lesson_id );
				$parent_section = llms_clean( $parent_sections[ $key ] );
				$lesson_order = llms_clean( $lessons_order[ $key ] );

				update_post_meta( $lesson_id, '_llms_order', $lesson_order );
				update_post_meta( $lesson_id, '_llms_parent_section', $parent_section );
				update_post_meta( $lesson_id, '_llms_order', $lesson_order );

			}

		}

		// $general = (isset($_POST['_has_prerequisite']) ? true : false);
		// update_post_meta( $post_id, '_has_prerequisite', ( $general === '' ) ? '' : $general );

		// if ( isset( $_POST['_prerequisite'] ) ) {

		// 	//update prerequisite select
		// 	$prerequisite = ( llms_clean( $_POST['_prerequisite']  ) );
		// 	update_post_meta( $post_id, '_prerequisite', ( $prerequisite  === '' ) ? '' : $prerequisite );
		// }

	}

}
