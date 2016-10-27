<?php
/**
 * LifterLMS Lesson Model
 *
 * @since    1.0.0
 * @version  3.0.0
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
 * @property  $require_passing_grade  (yesno)  Whether of not students have to pass the quiz to advance to the next lesson
 * @property  $time_available  (string)  Optional time to make lesson available on $date_available when $drip_method is "date"
 * @property  $video_embed  (string)  Video embed URL
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Lesson extends LLMS_Post_Model {

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
	 * Get the date a course became or will become available according to
	 * lesson drip settings
	 *
	 * If there are no drip settings, the published date of the lesson will be returned
	 *
	 * @param    string     $format  date format (passed to date_i18n() )
	 * @return   string
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function get_available_date( $format = 'F j, Y h:i A' ) {

		$drip_method = $this->get( 'drip_method' );

		$days = $this->get( 'days_before_available' ) * DAY_IN_SECONDS;

		switch ( $drip_method ) {

			// available on a specific date / time
			case 'date':

				$date = $this->get( 'date_available' );
				$time = $this->get( 'time_available' );

				if ( ! $time ) {
					$time = '12:00 AM';
				}

				$available = strtotime( $date . ' ' . $time );

			break;

			// available # of days after enrollment in course
			case 'enrollment':
				$student = new LLMS_Student();
				$available = $days + $student->get_enrollment_date( $this->get_parent_course(), 'enrolled', 'U' );
			break;

			// available # of days after course start date
			case 'start':
				$course = new LLMS_Course( $this->get_parent_course() );
				$available = $days + $course->get_date( 'start_date', 'U' );
			break;

			default:
				$available = $this->get_date( 'date', 'U' );

		}

		return date_i18n( $format, $available );

	}

	/**
	 * Retrieve an instance of LLMS_Course for the lesson's parent course
	 * @return   obj
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function get_course() {
		return new LLMS_Course( $this->get_parent_course() );
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
	 * @todo  refactor to use new api after a migration is written
	 */
	public function get_parent_course() {
		return absint( get_post_meta( $this->get( 'id' ), '_parent_course', true ) );
	}

	/**
	 * Get parent section id
	 * @return  int
	 * @since   1.0.0
	 * @version 3.0.0
	 * @todo  refactor to use new api after a migration is written
	 */
	public function get_parent_section() {
		return  absint( get_post_meta( $this->get( 'id' ), '_parent_section', true ) );
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

		return apply_filters( 'llms_get_preview_classes ', $classes );
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
	 * Get a property's data type for scrubbing
	 * used by $this->scrub() to determine how to scrub the property
	 * @param   string $key  property key
	 * @return  string
	 * @since   3.0.0
	 * @version 3.0.0
	 */
	protected function get_property_type( $key ) {

		switch ( $key ) {

			case 'prerequisite':
			case 'days_before_available':
			case 'parent_course':
				$type = 'absint';
			break;

			case 'has_prerequisite':
			default:
				$type = 'text';

		}

		return $type;

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
	 * Determine if a course is available based on drip settings
	 * If no settings, this will return true if the lesson's published
	 * date is in the past
	 *
	 * @return   boolean
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function is_available() {

		$drip_method = $this->get( 'drip_method' );

		// drip is no enabled, so the course is available
		if ( ! $drip_method ) {
			return true;
		}

		$available = $this->get_available_date( 'U' );
		$now = current_time( 'timestamp' );

		return ( $now > $available );

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
	 * Get the quiz associated with the lesson
	 * @deprecated use $this->get( 'assigned_quiz' ) instead
	 * @return  false|int
	 * @since   1.0.0
	 * @version 3.0.2
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

		return update_post_meta( $this->id, '_parent_section', $section_id );

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

		return update_post_meta( $this->id, '_parent_course', $course_id );

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


	/**
	 * Get the lesson drip days
	 *
	 * @return int [ID of the prerequisite post]
	 */
	public function get_drip_days() {

		if ( $this->days_before_avalailable ) {
			return $this->days_before_avalailable;
		} else {
			return 0;
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
				    'key' => '_parent_section',
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
					    'key' => '_parent_course',
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
		}
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
					    'key' => '_parent_section',
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

			if ($previous_position != 0) {
				$args = array(
					'post_type' 		=> 'section',
					'posts_per_page'	=> 500,
					'meta_key'			=> '_llms_order',
					'order'				=> 'ASC',
					'orderby'			=> 'meta_value_num',
					'meta_query' 		=> array(
						'relation' => 'AND',
						array(
						    'key' => '_parent_course',
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

				if ($sections) {
					$newsection = new LLMS_Section( $sections[0]->ID );
					$lessons = $newsection->get_children_lessons();
					return $lessons[ count( $lessons ) -1 ]->ID;
				} else {
					return false;
				}
			}
		}
	}



	/**
	 * Text to display on Mark Complete button
	 * @return string [Button text]
	 */
	public function single_mark_complete_text() {
		return apply_filters( 'lifterlms_mark_lesson_complete_button_text', __( 'Mark Complete', 'lifterlms' ), $this );
	}

	/**
	 * Mark lesson as complete
	 *
	 * @todo  refactor this function is disgusting
	 *
	 * @param  int $user_id [ID of user]
	 * @return void
	 */
	public function mark_complete( $user_id, $prevent_autoadvance = false ) {
		global $wpdb;

		$user = new LLMS_Person;
		$user_postmetas = $user->get_user_postmeta_data( $user_id, $this->id );

		if ( empty( $user_id ) ) {
			throw new Exception( '<strong>' . __( 'Error', 'lifterlms' ) . ':</strong> ' . __( 'User cannot be found.', 'lifterlms' ) );
		} elseif ( ! empty( $user_postmetas ) ) {

			if ( $user_postmetas['_is_complete']->meta_value === 'yes' ) {
				return;
			}
		} else {

			$key = '_is_complete';
			$value = 'yes';

			$update_user_postmeta = $wpdb->insert( $wpdb->prefix .'lifterlms_user_postmeta',
				array(
					'user_id' 			=> $user_id,
					'post_id' 			=> $this->id,
					'meta_key'			=> $key,
					'meta_value'		=> $value,
					'updated_date'		=> current_time( 'mysql' ),
				)
			);
			do_action( 'lifterlms_lesson_completed', $user_id, $this->id );

			llms_add_notice( sprintf( __( 'Congratulations! You have completed %s', 'lifterlms' ), get_the_title( $this->id ) ) );

			$course = new LLMS_Course( $this->get_parent_course() );
			$section = new LLMS_Section( $this->get_parent_section() );
			$section_completion = $section->get_percent_complete( $this->id );

			if ( $section_completion == '100' ) {

				$key = '_is_complete';
				$value = 'yes';

				$user_postmetas = $user->get_user_postmeta_data( $user_id, $section->id );
				if ( ! empty( $user_postmetas['_is_complete'] ) ) {
					if ( $user_postmetas['_is_complete']->meta_value === 'yes' ) {
	    				return;
	    			}
	    		}

				$update_user_postmeta = $wpdb->insert( $wpdb->prefix .'lifterlms_user_postmeta',
					array(
						'user_id' 			=> $user_id,
						'post_id' 			=> $section->id,
						'meta_key'			=> $key,
						'meta_value'		=> $value,
						'updated_date'		=> current_time( 'mysql' ),
					)
				);

				do_action( 'lifterlms_section_completed', $user_id, $section->id );

			}

			$course_completion = $course->get_percent_complete();
			if ( $course_completion == '100' ) {

				$key = '_is_complete';
				$value = 'yes';

				$user_postmetas = $user->get_user_postmeta_data( $user_id, $course->id );
				if ( ! empty( $user_postmetas['_is_complete'] ) ) {
					if ( $user_postmetas['_is_complete']->meta_value === 'yes' ) {
	    				return;
	    			}
	    		}

				$update_user_postmeta = $wpdb->insert( $wpdb->prefix .'lifterlms_user_postmeta',
					array(
						'user_id' 			=> $user_id,
						'post_id' 			=> $course->id,
						'meta_key'			=> $key,
						'meta_value'		=> $value,
						'updated_date'		=> current_time( 'mysql' ),
					)
				);

				do_action( 'lifterlms_course_completed', $user_id, $course->id );

				/**
				 * This variable is what will store the list of classes
				 * for each track that this class is a member of
				 * @var array
				 */
				$courses_in_track = array();

				// Get Track Information
				// This gets the information about all the tracks that
				// this course is a part of
				$tracks = wp_get_post_terms( $course->id,'course_track', array( 'fields' => 'all' ) );

				// Run through each of the tracks that this course is a member of
				foreach ((array) $tracks as $id => $track) {
					/**
					 * Variable that stores if the track has been completed
					 * @var boolean
					 */
					$completed_track = false;

					$args = array(
						'posts_per_page' 	=> 1000,
						'post_type' 		=> 'course',
						'nopaging' 			=> true,
						'post_status' 		=> 'publish',
						'orderby'          	=> 'post_title',
						'order'            	=> 'ASC',
						'suppress_filters' 	=> true,
						'tax_query' => array(
							array(
								'taxonomy' 	=> 'course_track',
								'field'		=> 'term_id',
								'terms'		=> $track->term_id,
							),
						),
					);
					$courses = get_posts( $args );

					// Run through each of the courses that is in the track
					// to see if all of the courses are completed
					foreach ( $courses as $key => $course ) {
						/**
						 * This variable stores the information about each course
						 * in the track
						 * @var array
						 */
						$data = LLMS_Course::get_user_post_data( $course->ID, $user_id );

						// If there is data about the course, parse it
						if ($data !== array()) {
							/**
							 * Create a variable to store whether or not the class is completed
							 * @var boolean
							 */
							$has_completed = false;

							// Run through each of the meta values in the array
						    foreach ($data as $key => $object) {
								// Check to see is the current object is the '_is_complete'
						        if (is_object( $object ) && $object->meta_key == '_is_complete' && $object->meta_value == 'yes') {
									// If so, the course has been completed
						        	$has_completed = true;
						        	break;
						        }
						    }

						   	// If the course is completed keep an update going
						   	if ($has_completed) {
								$completed_track = true;
						   	}
						} // If data is empty, break out of the loop because the

						// user has not enrolled in that course
						else {
							$completed_track = false;
							break;
						}
					}

					// If completed at the end of the track loop do the action
					if ($completed_track) {
						do_action( 'lifterlms_course_track_completed', $user_id, $track->term_id );
					}

					$courses_in_track[ $id ] = $courses;

				}

			} elseif ( ! $prevent_autoadvance && apply_filters( 'lifterlms_autoadvance', true ) ) {

				$next_lesson_id = $this->get_next_lesson();
				if ( $next_lesson_id ) {
					wp_redirect( get_permalink( $next_lesson_id ) );
					exit;
				}

			}

		}
	}

}
