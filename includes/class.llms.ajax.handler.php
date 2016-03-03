<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
* lifterLMS AJAX Event Handler
*
* Handles server side ajax communication.
*
* @author codeBOX
* @project lifterLMS
*/
class LLMS_AJAX_Handler {

	public static function test_ajax_call() {

		return $_REQUEST;

	}

	public static function create_section( $request ) {

		$section_id = LLMS_Post_Handler::create_section( $request['post_id'], $request['title'] );

		$html = LLMS_Meta_Box_Course_Outline::section_tile( $section_id );

		return $html;

	}

	public static function get_course_sections( $request ) {

		$course = new LLMS_Course( $request['post_id'] );
		$sections = $course->get_children_sections();

		return $sections;
	}

	public static function create_lesson( $request ) {

		$lesson_id = LLMS_Post_Handler::create_lesson(
			$request['post_id'],
			$request['section_id'],
			$request['title'],
			$request['excerpt']
		);

		$html = LLMS_Meta_Box_Course_Outline::lesson_tile( $lesson_id, $request['section_id'] );

		return $html;

	}

	public static function get_lesson_options_for_select( $request ) {

		return LLMS_Post_Handler::get_lesson_options_for_select_list();

	}

	public static function add_lesson_to_course( $request ) {

		$lesson_id = LLMS_Lesson_Handler::assign_to_course( $request['post_id'], $request['section_id'], $request['lesson_id'] );

		$html = LLMS_Meta_Box_Course_Outline::lesson_tile( $lesson_id, $request['section_id'] );

		return $html;

	}

	public static function get_course_section( $request ) {

		return new LLMS_Section( $request['section_id'] );
	}

	public static function update_course_section( $request ) {

		$section = new LLMS_Section( $request['section_id'] );
		return $section->set_title( $request['title'] );

	}

	public static function get_course_lesson( $request ) {

		return new LLMS_Lesson( $request['lesson_id'] );

	}

	public static function update_course_lesson( $request ) {

		$post_data = array(
			'title' => $request['title'],
			'excerpt' => $request['excerpt'],
		);

		$lesson = new LLMS_Lesson( $request['lesson_id'] );

		return $lesson->update( $post_data );

	}

	public static function remove_course_lesson( $request ) {

		$post_data = array(
			'parent_course' => '',
			'parent_section' => '',
			'order'	=> '',
		);

		$lesson = new LLMS_Lesson( $request['lesson_id'] );

		return $lesson->update( $post_data );

	}

	public static function delete_course_section( $request ) {

		$section = new LLMS_Section( $request['section_id'] );
		return $section->delete();
	}

	public static function update_section_order( $request ) {

		$updated_data;

		foreach ( $request['sections'] as $key => $value ) {

			$section = new LLMS_Section( $key );
			$updated_data[ $key ] = $section->update( array( 'order' => $value ) );

		}

		return $updated_data;

	}

	public static function update_lesson_order( $request ) {

		$updated_data;

		foreach ( $request['lessons'] as $key => $value ) {

			$lesson = new LLMS_Lesson( $key );
			$updated_data[ $key ] = $lesson->update(
				array(
					'parent_section' => $value['parent_section'],
					'order' => $value['order'],
				)
			);

		}

		return $updated_data;

	}


}

new LLMS_AJAX_Handler();
