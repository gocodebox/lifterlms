<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
* Base Lesson Class
*
* Class used for instantiating lesson object
*
* @author codeBOX
*/
class LLMS_Lesson {

	/**
	* ID
	* @access public
	* @var int
	*/
	public $id;

	/**
	* Post Object
	* @access public
	* @var array
	*/
	public $post;


	/**
	* Constructor
	*
	* initializes the lesson object based on post data
	*/
	public function __construct( $lesson ) {

		if ( is_numeric( $lesson ) ) {

			$this->id   = absint( $lesson );
			$this->post = get_post( $this->id );

		} 

		elseif ( $lesson instanceof LLMS_Lesson ) {

			$this->id   = absint( $lesson->id );
			$this->post = $lesson;

		} 

		elseif ( $lesson instanceof LLMS_Post || isset( $lesson->ID ) ) {

			$this->id   = absint( $lesson->ID );
			$this->post = $lesson;

		}

	}

	/**
	* __isset function
	*
	* checks if metadata exists
	*
	* @param string $item
	*/
	public function __isset( $key ) {

		return metadata_exists( 'post', $this->id, '_' . $key );

	}

	/**
	* __get function
	*
	* initializes the course object based on post data
	*
	* @param string $item
	* @return string $value
	*/
	public function __get( $key ) {

		$value = get_post_meta( $this->id, '_' . $key, true );
		return $value;

	}

	/**
	 * Get Video (oembed)
	 *
	 * @return mixed (default: '')
	 */
	public function get_video() {

		if ( ! isset( $this->video_embed ) ) {

			return '';

		}

		else {

			return wp_oembed_get($this->video_embed);

		}

	}

	/**
	 * Get Audio (wp shortcode)
	 *
	 * @return mixed (default: '')
	 */
	public function get_audio() {

		if ( ! isset( $this->audio_embed ) ) {

			return '';

		}

		else {

			return do_shortcode('[audio src="'. $this->audio_embed . '"]');

		}

	}

	/**
	 * Get parent course
	 *
	 * @return string
	 */
	public function get_parent_course() {
		//$this->parent_course = get_post_meta( $this->ID, '_parent_course', true );
		return $this->parent_course;

	}

	/**
	 * Get the parent section
	 * Finds and returns parent section id
	 * 
	 * @return int [ID of parent section]
	 */
	public function get_parent_section() {
		
		$course = new LLMS_Course( $this->parent_course );

		$sections = array();
		
		if (is_object($course)) {
			$syllabus = $course->get_syllabus();
			foreach( $syllabus as $key => $value ) {
				$sections[$value['section_id']] = $value['lessons'];
				if($value['lessons']) {
					foreach($value['lessons'] as $keys => $values ) {
						if ($this->id == $values['lesson_id']) {
							$parent_section = $value['section_id'];
						}
					}
				}
			}
		}

		return $parent_section;

	}

	/**
	 * Get the lesson prerequisite
	 * 
	 * @return int [ID of the prerequisite post]
	 */
	public function get_prerequisite() {

		if ( $this->has_prerequisite ) {

			return $this->prerequisite;
		}
		else {
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

		$course = new LLMS_Course( $this->parent_course );
		$lessons = array();
		$current_lesson = $this->id;
		$parent_section = $this->get_parent_section();
		
		if (is_object($course) ){
			$syllabus = $course->get_syllabus();

			foreach( $syllabus as $key => $value ) {

				if ( $parent_section == $value['section_id']) {

					foreach( $value['lessons'] as $keys => $value ) {
						array_push($lessons, $value['lesson_id']);
					}
				}
			}

			$firstElement = current($lessons);
			$lastElement = $lessons[sizeof($lessons)-1];

			$currentKey = array_search($this->id, $lessons);

			$currentValue = $lessons[$currentKey];

			$previousValue = "";
			$nextValue = "";

			if($this->id!=$lastElement){
			    $nextKey = $currentKey + 1;
			    $nextValue = $lessons[$nextKey];
			}

			return $nextValue;
		}
	}

	/**
	 * Get previous lesson id
	 * @return int [ID of previous lesson]
	 */
	public function get_previous_lesson() {

		$lessons = array();
		$current_lesson = $this->id;
		$parent_section = $this->get_parent_section();
		$course = new LLMS_Course( $this->parent_course );
		
		if (is_object( $course ) ){
			$syllabus = $course->get_syllabus();

			foreach( $syllabus as $key => $value ) {

				if ( $parent_section == $value['section_id']) {

					foreach( $value['lessons'] as $keys => $value ) {
						array_push($lessons, $value['lesson_id']);
					}
				}
			}

			$firstElement = current($lessons);
			$lastElement = $lessons[sizeof($lessons)-1];

			$currentKey = array_search($this->id, $lessons);
			$currentValue = $lessons[$currentKey];

			$previousValue = "";
			$nextValue = "";
			if($this->id!=$lastElement){
			    $nextKey = $currentKey + 1;
			    $nextValue = $lessons[$nextKey];
			}

			if($this->id!=$firstElement){

			    $previousKey = $currentKey - 1;
			   
			    $previousValue = $lessons[$previousKey];
			}
		}

		return $previousValue;

	}

	/**
	 * Check if lesson is complete
	 * @return bool [Is lesson marked complete for user]
	 */
	public function is_complete() {
		$user = new LLMS_Person;
		$user_postmetas = $user->get_user_postmeta_data( get_current_user_id(), $this->id );

		if ( empty($user_postmetas) ) {
			return false;
		}

		foreach( $user_postmetas as $key => $value ) {

			if ( isset($user_postmetas['_is_complete']) && $user_postmetas['_is_complete']->post_id == $this->id) {
				return true;
			}
			else {
				return false;

			}
		}

		return $user_postmetas;
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
	 * @param  int $user_id [ID of user]
	 * @return void
	 */
	public function mark_complete( $user_id ) {
		global $wpdb;

			
		$user = new LLMS_Person;
		$user_postmetas = $user->get_user_postmeta_data( $user_id, $this->id );

		if ( empty( $user_id ) ) {
			throw new Exception( '<strong>' . __( 'Error', 'lifterlms' ) . ':</strong> ' . __( 'User cannot be found.', 'lifterlms' ) );
		}
		elseif ( ! empty($user_postmetas) ) {
		
			if ( $user_postmetas['_is_complete']->meta_value === 'yes' ) {
				return;
			}
		}
		else {

			$key = '_is_complete';
			$value = 'yes';

			$update_user_postmeta = $wpdb->insert( $wpdb->prefix .'lifterlms_user_postmeta', 
				array( 
					'user_id' 			=> $user_id,
					'post_id' 			=> $this->id,
					'meta_key'			=> $key,
					'meta_value'		=> $value,
					'updated_date'		=> current_time('mysql'),
				)
			);
			do_action( 'lifterlms_lesson_completed', $user_id, $this->id);
	
			llms_add_notice( sprintf( __( 'Congratulations! You have completed %s', 'lifterlms' ), get_the_title($this->id) ) );


			$course = new LLMS_Course($this->get_parent_course());
			$section_completion = $course->get_section_percent_complete($this->id);
			$section_id = get_section_id($course->id, $this->id);
			
			if ( $section_completion == '100' ) {

				$key = '_is_complete';
				$value = 'yes';

				$user_postmetas = $user->get_user_postmeta_data( $user_id, $section_id );
				if ( ! empty( $user_postmetas['_is_complete'] ) ) {
					if ( $user_postmetas['_is_complete']->meta_value === 'yes' ) {
	    				return;
	    			}
	    		}

				$update_user_postmeta = $wpdb->insert( $wpdb->prefix .'lifterlms_user_postmeta', 
					array( 
						'user_id' 			=> $user_id,
						'post_id' 			=> $section_id,
						'meta_key'			=> $key,
						'meta_value'		=> $value,
						'updated_date'		=> current_time('mysql'),
					)
				);

				do_action('lifterlms_section_completed', $user_id, $section_id );
	
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
						'updated_date'		=> current_time('mysql'),
					)
				);

				do_action('lifterlms_course_completed', $user_id, $course->id );
	
			}

		}
	}

} //end LLMS_Lesson 
