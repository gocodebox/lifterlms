<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
* Base Lesson Class
*
* Class used for instantiating lesson object
*
* @version 1.0
* @author codeBOX
* @project lifterLMS
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

		return $this->parent_course;

	}

	public function get_parent_section() {
		global $course;

		$sections = array();
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

		return $parent_section;

	}

	public function get_prerequisite() {

		if ( $this->has_prerequisite == 'on' ) {

			return $this->prerequisite;
		}
		else {
			return false;
		}
	}


	public function get_next_lesson() {
		global $course;

		$lessons = array();
		$current_lesson = $this->id;
		$parent_section = $this->get_parent_section();
		

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

		public function get_previous_lesson() {
		global $course;
		$lessons = array();
		$current_lesson = $this->id;
		$parent_section = $this->get_parent_section();
		

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

		return $previousValue;

	}

	public function is_complete() {
		$user = new LLMS_Person;
		$user_postmetas = $user->get_user_postmeta_data( get_current_user_id(), $this->id );

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

	public function single_mark_complete_text() {
		return apply_filters( 'lifterlms_mark_lesson_complete_button_text', __( 'Mark Complete', 'lifterlms' ), $this );
	}

}
