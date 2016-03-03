<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
if ( ! defined( 'LLMS_Admin_Metabox' ) ) {
	// Include the file for the parent class
	include_once LLMS_PLUGIN_DIR . '/includes/admin/llms.class.admin.metabox.php';
}

/**
* Meta Box Builder
*
* Generates main metabox and builds forms
*/
class LLMS_Meta_Box_Lesson extends LLMS_Admin_Metabox{

	public static $prefix = '_';

	/**
	 * Function to field WP::output() method call
	 * Passes output instruction to parent
	 *
	 * @param object $post WP global post object
	 * @return void
	 */
	public static function output ( $post ) {
		global $post;
		parent::new_output( $post, self::metabox_options() );
	}

	/**
	 * Builds array of metabox options.
	 * Array is called in output method to display options.
	 * Appropriate fields are generated based on type.
	 *
	 * @return array [md array of metabox fields]
	 */
	public static function metabox_options() {
		global $post;

		//setup lesson select options
		$lesson_options = array();
		$lesson_posts = LLMS_Post_Handler::get_posts( 'lesson' );
		foreach ( $lesson_posts as $l_post ) {
			if ( $l_post->ID != $post->ID ) {
				$lesson_options[] = array(
					'key' 	=> $l_post->ID,
					'title' => $l_post->post_title,
				);
			}
		}

		//setup quiz select options
		$quiz_array = array();
		$quizzes = LLMS_Post_Handler::get_posts( 'llms_quiz' );
		foreach ( $quizzes as $quiz ) {
			if ( $quiz->ID != $post->ID ) {
				$quiz_array[] = array(
					'key' 	=> $quiz->ID,
					'title' => $quiz->post_title,
				);
			}
		}

		$days_before_avalailable = get_post_meta( $post->ID, '_days_before_avalailable', true );
		$assigned_quiz = get_post_meta( $post->ID, '_llms_assigned_quiz', true );
		$require_passing_grade = get_post_meta( $post->ID, '_llms_require_passing_grade', true );
		$video_embed = get_post_meta( $post->ID, '_video_embed', true );
		$audio_embed = get_post_meta( $post->ID, '_audio_embed', true );

		$meta_fields_lesson = array(
			array(
				'title' 	=> 'General',
				'fields' 	=> array(
					array(
						'type'		=> 'text',
						'label'		=> 'Video Embed Url',
						'desc' 		=> 'Paste the url for a Wistia, Vimeo or Youtube video.',
						'id' 		=> self::$prefix . 'video_embed',
						'class' 	=> 'code input-full',
						'value' 	=> $video_embed,
						'desc_class' => 'd-all',
						'group' 	=> '',
					),
					array(
						'type'		=> 'text',
						'label'		=> 'Audio Embed Url',
						'desc' 		=> 'Paste the url for an externally hosted audio file.',
						'id' 		=> self::$prefix . 'audio_embed',
						'class' 	=> 'code input-full',
						'value' 	=> $audio_embed,
						'desc_class' => 'd-all',
						'group' 	=> '',
					),
					array(
						'type'		=> 'checkbox',
						'label'		=> 'Free Lesson',
						'desc' 		=> 'Checking this box will allow guests to view the content of this lesson without registering or signing up for the course.',
						'id' 		=> self::$prefix . 'llms_free_lesson',
						'class' 	=> '',
						'value' 	=> '1',
						'desc_class' => 'd-3of4 t-3of4 m-1of2',
						'group' 	=> 'top',
					),
				),
			),
			array(
				'title' 	=> 'Quiz',
				'fields' 	=> array(
					array(
						'type'		=> 'select',
						'label'		=> 'Assigned Quiz',
						'desc' 		=> 'Quiz will be required to complete lesson.',
						'id' 		=> self::$prefix . 'llms_assigned_quiz',
						'class' 	=> 'llms-chosen-select',
						'value' 	=> $quiz_array,
						'desc_class' => 'd-all',
						'group' 	=> '',
					),
					array(
						'type'		=> 'checkbox',
						'label'		=> 'Require Passing Grade',
						'desc' 		=> 'Checking this box will require students to get a passing score on the above quiz to complete the lesson.',
						'id' 		=> self::$prefix . 'llms_require_passing_grade',
						'class' 	=> '',
						'value' 	=> '1',
						'desc_class' => 'd-3of4 t-3of4 m-1of2',
						'group' 	=> 'top',
					),
				),
			),
			array(
				'title' 	=> 'Restrictions',
				'fields' 	=> array(
					array(
						'type'		=> 'text',
						'label'		=> 'Drip Content (in days)',
						'desc' 		=> 'Number of days before lesson is available after course begins (date of purchase or set start date)',
						'id' 		=> self::$prefix . 'days_before_avalailable',
						'class' 	=> 'input-full',
						'value' 	=> $days_before_avalailable,
						'desc_class' => 'd-all',
						'group' 	=> '',
					),
					array(
						'type'		=> 'checkbox',
						'label'		=> 'Enable Prerequisite',
						'desc' 		=> 'Enable to choose a prerequisite Lesson',
						'id' 		=> self::$prefix . 'has_prerequisite',
						'class' 	=> '',
						'value' 	=> '1',
						'desc_class' => 'd-3of4 t-3of4 m-1of2',
						'group' 	=> 'llms-prereq-top',
					),
					array(
						'type'		=> 'select',
						'label'		=> 'Choose Prerequisite',
						'desc' 		=> 'Select the prerequisite lesson',
						'id' 		=> self::$prefix . 'prerequisite',
						'class' 	=> 'llms-chosen-select',
						'value' 	=> $lesson_options,
						'desc_class' => 'd-all',
						'group' 	=> 'bottom llms-prereq-bottom',
					),
				),
			),
		);

		if (has_filter( 'llms_meta_fields_lesson' )) {
			//Add Fields to the course main Meta Box
			$meta_fields_lesson = apply_filters( 'llms_meta_fields_lesson', $meta_fields_lesson );
		}

		return $meta_fields_lesson;
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

	}

}
