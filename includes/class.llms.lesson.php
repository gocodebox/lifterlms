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

	/**
	 * Get next lesson in sort order
	 *
	 * @return string
	 */
	public function get_next_lesson() {
		//todo
	}

	/**
	 * Get previous lesson in sort order
	 *
	 * @return string
	 */
	public function get_previous_lesson() {
		//todo
	}

}
