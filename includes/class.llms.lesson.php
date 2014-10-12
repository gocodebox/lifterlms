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
	 * Get parent course
	 *
	 * @return string
	 */
	public function get_parent_course() {

		return $this->parent_course;

	}

	public function is_complete() {
		$user = new LLMS_Person;
		$user_postmetas = $user->get_user_postmeta_data( get_current_user_id(), $this->id );

		foreach( $user_postmetas as $key => $value ) {

			//$user_postmetas = $user->get_user_postmeta_data( get_current_user_id(), $value['lesson_id'] );
llms_log($user_postmetas['_is_complete']->post_id);
			if ( isset($user_postmetas['_is_complete']) && $user_postmetas['_is_complete']->post_id == $this->id) {
				return true;
				//llms_log('lesson complete1');
				//array_push($lessons_not_completed, $value['lesson_id']);
			}
			else {
				return false;
				//llms_log('lesson not complete');
			}
		}

		return $user_postmetas;
		// foreach( $array as $key => $value ) {
		// 	$user_postmetas = $user->get_user_postmeta_data( get_current_user_id(), $value );
		// 	if ( isset($user_postmetas['_is_complete']) ) {
		// 		if ( $user_postmetas['_is_complete']->meta_value === 'yes' ) {
		// 			$i++;
		// 		}
		// 	}
		// }
	}	

	/**
	 * Get previous lesson in sort order
	 *
	 * @return string
	 */
	public function get_previous_lesson() {
		//todo
	}

	public function single_mark_complete_text() {
		return apply_filters( 'lifterlms_mark_lesson_complete_button_text', __( 'Mark Complete', 'woocommerce' ), $this );
	}

}
