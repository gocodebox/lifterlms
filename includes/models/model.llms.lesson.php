<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * LifterLMS Lesson Model
 *
 * @since    1.0.0
 * @version  [version]
 *
 * @property  $assigned_quiz  (int)  WP Post ID of the llms_quiz
 * @property  $audio_embed  (string)  Audio embed URL
 * @property  $date_available  (string/date)  Date when lesson becomes available, applies when $drip_method is "date"
 * @property  $days_before_available  (int)  The number of days before the lesson is available, applies when $drip_method is "enrollment" or "start"
 * @property  $drip_method  (string) What sort of drip method to utilize [''(none)|date|enrollment|start]
 * @property  $free_lesson  (yesno)  Yes if the lesson is free
 * @property  $has_prerequisite  (yesno)  Yes if the lesson has a prereq lesson
 * @property  $order (int)  Lesson's order within its parent section
 * @property  $prerequisite  (int)  WP Post ID of the prerequisite lesson, only if $has_prequisite is 'yes'
 * @property  $parent_course (int)  WP Post ID of the course the lesson belongs to
 * @property  $parent_section (int)  WP Post ID of the section the lesson belongs to
 * @property  $require_passing_grade  (yesno)  Whether of not students have to pass the quiz to advance to the next lesson
 * @property  $time_available  (string)  Optional time to make lesson available on $date_available when $drip_method is "date"
 * @property  $video_embed  (string)  Video embed URL
 */
class LLMS_Lesson extends LLMS_Abstract_Course_Element_Post {

	protected $properties = array(

		'assigned_quiz' => 'absint',
		'audio_embed' => 'text',
		'free_lesson' => 'yesno',
		'has_prerequisite' => 'yesno',
		'prerequisite' => 'absint',
		'require_passing_grade' => 'yesno',
		'video_embed' => 'text',

	);

	protected $db_post_type = 'lesson';
	protected $model_post_type = 'lesson';

	/**
	 * Attempt to get oEmbed for an audio provider
	 * Falls back to the [audio] shortcode if the oEmbed fails
	 *
	 * @return string
	 * @since   1.0.0
	 * @version 3.0.0 -- updated to utilize oEmbed and fallback to audio shortcode
	 */
	public function get_audio() {

		if ( ! isset( $this->audio_embed ) ) {

			return '';

		} else {

			$r = wp_oembed_get( $this->get( 'audio_embed' ) );

			if ( ! $r ) {

				$r = do_shortcode( '[audio src="' . $this->get( 'audio_embed' ) . '"]' );

			}

			return $r;

		}

	}

	/**
	 * An array of default arguments to pass to $this->create()
	 * when creating a new post
	 * @param    array  $args   args of data to be passed to wp_insert_post
	 * @return   array
	 * @since    3.13.0
	 * @version  3.13.0
	 */
	protected function get_creation_args( $args = null ) {

		// allow nothing to be passed in
		if ( empty( $args ) ) {
			$args = array();
		}

		// backwards compat to original 3.0.0 format when just a title was passed in
		if ( is_string( $args ) ) {
			$args = array(
				'post_title' => $args,
			);
		}

		$args = wp_parse_args( $args, array(
			'comment_status' => 'closed',
			'ping_status'	 => 'closed',
			'post_author' 	 => get_current_user_id(),
			'post_content'   => '',
			'post_excerpt'   => '',
			'post_status' 	 => 'publish',
			'post_title'     => '',
			'post_type' 	 => $this->get( 'db_post_type' ),
		) );

		return apply_filters( 'llms_' . $this->model_post_type . '_get_creation_args', $args, $this );

	}

	/**
	 * Retrieves the lesson's order within its parent section
	 * @todo  this should be deprecated
	 * @return int
	 * @since  1.0.0
	 * @version  3.0.0
	 */
	public function get_order() {
		return $this->get( 'order' );
	}

	/**
	 * Get parent course id
	 * @return  int
	 * @since   1.0.0
	 * @version 3.0.0
	 */
	public function get_parent_course() {
		return absint( get_post_meta( $this->get( 'id' ), '_llms_parent_course', true ) );
	}

	/**
	 * Get parent section id
	 * @return  int
	 * @since   1.0.0
	 * @version 3.0.0
	 */
	public function get_parent_section() {
		return  absint( get_post_meta( $this->get( 'id' ), '_llms_parent_section', true ) );
	}

	/**
	 * Get CSS classes to display on the course syllabus .llms-lesson-preview element
	 * @return   string
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function get_preview_classes() {

		$classes = '';

		if ( $this->is_complete() ) {
			$classes = ' is-complete has-icon';
		} elseif ( apply_filters( 'lifterlms_display_lesson_complete_placeholders', true ) && llms_is_user_enrolled( get_current_user_id(), $this->get( 'id' ) ) ) {
			$classes = ' is-incomplete has-icon';
		} elseif ( $this->is_free() ) {
			$classes = ' is-free has-icon';
		} else {
			$classes = ' is-incomplete';
		}

		return apply_filters( 'llms_get_preview_classes', $classes );
	}

	/**
	 * Get HTML of the icon to display in the .llms-lesson-preview element on the syllabus
	 * @return   string
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function get_preview_icon_html() {

		$html = '';

		if ( llms_is_user_enrolled( get_current_user_id(), $this->get( 'id' ) ) ) {

			if ( $this->is_complete() || apply_filters( 'lifterlms_display_lesson_complete_placeholders', true ) ) {

				$html = '<span class="llms-lesson-complete"><i class="fa fa-' . apply_filters( 'lifterlms_lesson_complete_icon', 'check-circle' ) . '"></i></span>';

			}
		} elseif ( $this->is_free() ) {

			$html = '<span class="llms-icon-free">' . __( 'FREE', 'lifterlms' ) . '</span>';

		}

		return apply_filters( 'llms_get_preview_icon_html', $html );

	}

	/**
	 * Retrieve an object for the assignd quiz (if a quiz is assigned )
	 * @return   obj|false
	 * @since    3.3.0
	 * @version  [version]
	 */
	public function get_quiz() {
		if ( $this->has_quiz() ) {
			return new LLMS_Quiz( $this->get( 'assigned_quiz' ) );
		}
		return false;
	}

	/**
	 * Attempt to get oEmbed for a video provider
	 * Falls back to the [video] shortcode if the oEmbed fails
	 *
	 * @return string
	 * @since   1.0.0
	 * @version 3.1.0
	 */
	public function get_video() {

		if ( ! isset( $this->video_embed ) ) {

			return '';

		} else {

			$r = wp_oembed_get( $this->get( 'video_embed' ) );

			if ( ! $r ) {

				$r = do_shortcode( '[video src="' . $this->get( 'video_embed' ) . '"]' );

			}

			return $r;

		}

	}

	/**
	 * Determine if lesson prereq is enabled and a prereq lesson is selected
	 * @return   boolean
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function has_prerequisite() {

		return ( 'yes' == $this->get( 'has_prerequisite' ) && $this->get( 'prerequisite' ) );

	}

	/**
	 * Determine if the slug (post name) of a lesson has been modified
	 * Ensures that lessons created via the builder with "New Lesson" as the title (default slug "new-lesson-{$num}")
	 * have their slug renamed when the title is renamed for the first time
	 * @return   bool
	 * @since    3.14.8
	 * @version  3.14.8
	 */
	public function has_modified_slug() {

		$default = sanitize_title( __( 'New Lesson', 'lifterlms' ) );
		return ( false === strpos( $this->get( 'name' ), $default ) );

	}

	/**
	 * Determine if a quiz is assigned to this lesson
	 * @return   boolean
	 * @since    3.3.0
	 * @version  3.3.0
	 */
	public function has_quiz() {
		return ( $this->get( 'assigned_quiz' ) );
	}

	/**
	 * Determine if the lesson has been completed by a specific user
	 * @param   int    $user_id  WP_User ID of a student
	 * @return  bool
	 * @since   1.0.0
	 * @version 3.0.0  refactored to utilize LLMS_Student->is_complete()
	 *                 added $user_id param
	 */
	public function is_complete( $user_id = null ) {

		$user_id = $user_id ? $user_id : get_current_user_id();

		// incomplete b/c no user
		if ( ! $user_id ) {
			return false;
		}

		$student = new LLMS_Student( $user_id );

		return $student->is_complete( $this->get( 'id' ), 'lesson' );

	}

	/**
	 * Determine if a the lesson is marked as "free"
	 * @return   boolean
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function is_free() {
		return ( 'yes' === $this->get( 'free_lesson' ) );
	}

	/**
	 * Determine if the lesson is an orphan
	 * @return   bool
	 * @since    3.14.8
	 * @version  3.14.8
	 */
	public function is_orphan() {

		$statuses = array( 'publish', 'future', 'draft', 'pending', 'private', 'auto-draft' );

		foreach ( array( 'course', 'section' ) as $parent ) {

			$parent_id = $this->get( sprintf( 'parent_%s', $parent ) );

			if ( ! $parent_id ) {
				return true;
			} elseif ( ! in_array( get_post_status( $parent_id ), $statuses ) ) {
				return true;
			}
		}

		return false;

	}

	/**
	 * Add data to the course model when converted to array
	 * Called before data is sorted and retuned by $this->jsonSerialize()
	 * @param    array     $arr   data to be serialized
	 * @return   array
	 * @since    3.3.0
	 * @version  3.3.0
	 */
	public function toArrayAfter( $arr ) {

		if ( $this->has_quiz() ) {

			$q = $this->get_quiz();
			$arr['assigned_quiz'] = $q->toArray();

		}

		return $arr;

	}




	public function update( $data ) {

		$updated_values = array();

		foreach ( $data as $key => $value ) {
			$method = 'set_' . $key;

			if ( method_exists( $this, $method ) ) {
				$updated_value = $this->$method($value);

				$updated_values[ $key ] = $updated_value;

			}
		}

		return $updated_values;

	}

	public function set_title( $title ) {

		return LLMS_Post_Handler::update_title( $this->id, $title );

	}

	public function set_excerpt( $excerpt ) {

		return LLMS_Post_Handler::update_excerpt( $this->id, $excerpt );

	}

	/**
	 * Set parent section
	 * Set's parent section in database
	 * @param [int] $meta [id section post]
	 * @return [mixed] $meta [if mta didn't exist returns the meta_id else t/f if update success]
	 * Returns False if section id is already parent
	 */
	public function set_parent_section( $section_id ) {

		return update_post_meta( $this->id, '_llms_parent_section', $section_id );

	}

	/**
	 * Set parent section
	 * Set's parent section in database
	 * @param [int] $meta [id section post]
	 * @return [mixed] $meta [if mta didn't exist returns the meta_id else t/f if update success]
	 * Returns False if section id is already parent
	 */
	public function set_order( $order ) {

		return update_post_meta( $this->id, '_llms_order', $order );

	}

	/**
	 * Set parent course
	 * Set's parent course in database
	 * @param [int] $meta [id course post]
	 * @return [mixed] $meta [if meta didn't exist returns the meta_id else t/f if update success]
	 * Returns False if course id is already parent
	 */
	public function set_parent_course( $course_id ) {

		return update_post_meta( $this->id, '_llms_parent_course', $course_id );

	}






	/**
	 * Get the lesson prerequisite
	 *
	 * @return int [ID of the prerequisite post]
	 */
	public function get_prerequisite() {

		if ( $this->has_prerequisite ) {

			return $this->prerequisite;
		} else {
			return false;
		}
	}

	public function has_content() {
		if ( ! empty( $this->post->post_content ) ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Get Next lesson
	 * Finds and returns next lesson id
	 *
	 * @return int [ID of next lesson]
	 */
	public function get_next_lesson() {

		$parent_section = $this->get_parent_section();
		$current_position = $this->get_order();
		$next_position = $current_position + 1;

		$args = array(
			'posts_per_page' 	=> 1,
			'post_type' 		=> 'lesson',
			'nopaging' 			=> true,
			'post_status'   	=> 'publish',
			'meta_query' 		=> array(
				'relation' => 'AND',
				array(
				    'key' => '_llms_parent_section',
				    'value' => $parent_section,
				    'compare' => '=',
			    ),
			    array(
				    'key' => '_llms_order',
				    'value' => $next_position,
				    'compare' => '=',
			    )
			),
		);
		$lessons = get_posts( $args );

		//return the first one even if there for some crazy reason were more than one.
		if ( $lessons ) {
			return $lessons[0]->ID;
		} else {
			// See if there is another section after this section and get first lesson there
			$parent_course = $this->get_parent_course();
			$cursection = new LLMS_Section( $this->get_parent_section() );
			$current_position = $cursection->get_order();
			$next_position = $current_position + 1;

			$args = array(
				'post_type' 		=> 'section',
				'posts_per_page'	=> 500,
				'meta_key'			=> '_llms_order',
				'order'				=> 'ASC',
				'orderby'			=> 'meta_value_num',
				'meta_query' 		=> array(
					'relation' => 'AND',
					array(
					    'key' => '_llms_parent_course',
					    'value' => $parent_course,
					    'compare' => '=',
				    ),
				    array(
					    'key' => '_llms_order',
					    'value' => $next_position,
					    'compare' => '=',
				    )
				),
			);
			$sections = get_posts( $args );

			if ( $sections ) {
				$newsection = new LLMS_Section( $sections[0]->ID );
				$lessons = $newsection->get_children_lessons();
				if ( $lessons ) {
					return $lessons[0]->ID;
				} else {
					return false;
				}
			} else {
				return false;
			}
		}// End if().
	}

	/**
	 * Get previous lesson id
	 * @return int [ID of previous lesson]
	 */
	public function get_previous_lesson() {

		$parent_section = $this->get_parent_section();
		$current_position = $this->get_order();

		$previous_position = $current_position - 1;

		if ( $previous_position != 0 ) {

			$args = array(
				'posts_per_page' 	=> 1,
				'post_type' 		=> 'lesson',
				'nopaging' 			=> true,
				'post_status'   	=> 'publish',
				'meta_query' 		=> array(
					'relation' => 'AND',
					array(
					    'key' => '_llms_parent_section',
					    'value' => $parent_section,
					    'compare' => '=',
				    ),
				    array(
					    'key' => '_llms_order',
					    'value' => $previous_position,
					    'compare' => '=',
				    )
				),
			);
			$lessons = get_posts( $args );

			//return the first one even if there for some crazy reason were more than one.
			if ( $lessons ) {
				return $lessons[0]->ID;
			} else {
				return false;
			}
		} else {
			// See if there is a previous section
			$parent_course = $this->get_parent_course();
			$cursection = new LLMS_Section( $this->get_parent_section() );
			$current_position = $cursection->get_order();
			$previous_position = $current_position - 1;

			if ( $previous_position != 0 ) {
				$args = array(
					'post_type' 		=> 'section',
					'posts_per_page'	=> 500,
					'meta_key'			=> '_llms_order',
					'order'				=> 'ASC',
					'orderby'			=> 'meta_value_num',
					'meta_query' 		=> array(
						'relation' => 'AND',
						array(
						    'key' => '_llms_parent_course',
						    'value' => $parent_course,
						    'compare' => '=',
					    ),
					    array(
						    'key' => '_llms_order',
						    'value' => $previous_position,
						    'compare' => '=',
					    )
					),
				);
				$sections = get_posts( $args );

				if ( $sections ) {
					$newsection = new LLMS_Section( $sections[0]->ID );
					$lessons = $newsection->get_children_lessons();
					if ( ! $lessons ) {
						return false;
					}
					return $lessons[ count( $lessons ) - 1 ]->ID;
				} else {
					return false;
				}
			}
		}// End if().
	}




	/*
		 /$$$$$$$                                                                /$$                     /$$
		| $$__  $$                                                              | $$                    | $$
		| $$  \ $$  /$$$$$$   /$$$$$$   /$$$$$$   /$$$$$$   /$$$$$$$  /$$$$$$  /$$$$$$    /$$$$$$   /$$$$$$$
		| $$  | $$ /$$__  $$ /$$__  $$ /$$__  $$ /$$__  $$ /$$_____/ |____  $$|_  $$_/   /$$__  $$ /$$__  $$
		| $$  | $$| $$$$$$$$| $$  \ $$| $$  \__/| $$$$$$$$| $$        /$$$$$$$  | $$    | $$$$$$$$| $$  | $$
		| $$  | $$| $$_____/| $$  | $$| $$      | $$_____/| $$       /$$__  $$  | $$ /$$| $$_____/| $$  | $$
		| $$$$$$$/|  $$$$$$$| $$$$$$$/| $$      |  $$$$$$$|  $$$$$$$|  $$$$$$$  |  $$$$/|  $$$$$$$|  $$$$$$$
		|_______/  \_______/| $$____/ |__/       \_______/ \_______/ \_______/   \___/   \_______/ \_______/
		                    | $$
		                    | $$
		                    |__/
	*/

	/**
	 * Get the quiz associated with the lesson
	 * @return     false|int
	 * @deprecated 3.0.2
	 * @since      1.0.0
	 * @version    3.0.2
	 */
	public function get_assigned_quiz() {

		llms_deprecated_function( 'LLMS_Lesson::get_assigned_quiz()', '3.0.2', "LLMS_Lesson::get( 'assigned_quiz' )" );

		$id = $this->get( 'assigned_quiz' );
		if ( $id ) {
			return $id;
		} else {
			return false;
		}

	}

	/**
	 * Get the lesson drip days
	 * @return      int [ID of the prerequisite post]
	 * @deprecated  [version]
	 */
	public function get_drip_days() {

		llms_deprecated_function( 'LLMS_Lesson::get_drip_days()', '3.16.0', "LLMS_Lesson::get( 'days_before_available' )" );

		if ( $this->days_before_avalailable ) {
			return $this->days_before_avalailable;
		} else {
			return 0;
		}
	}

	/**
	 * Marks the current lesson complete
	 * @param      int     $user_id              WP User ID of the user
	 * @param      boolean $prevent_autoadvance  Deprecated
	 * @return     boolean
	 * @deprecated 3.3.1
	 * @since      1.0.0
	 * @version    3.3.1
	 */
	public function mark_complete( $user_id, $prevent_autoadvance = false ) {

		llms_deprecated_function( 'LLMS_Lesson::mark_complete()', '3.3.1', 'llms_mark_complete()' );
		return llms_mark_complete( $user_id, $this->get( 'id' ), 'lesson', 'lesson_' . $this->get( 'id' ) );

	}

}
