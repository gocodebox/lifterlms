<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
* Base Section Class
*
* Class used for instantiating section object
*
* @author codeBOX
*/
class LLMS_Section {

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
	* initializes the section object based on post data
	*/
	public function __construct( $section ) {

		if ( is_numeric( $section ) ) {

			$this->id   = absint( $section );
			$this->post = get_post( $this->id );

		} elseif ( $section instanceof LLMS_Section ) {

			$this->id   = absint( $section->id );
			$this->post = $section;

		} elseif ( $section instanceof LLMS_Post || isset( $section->ID ) ) {

			$this->id   = absint( $section->ID );
			$this->post = $section;

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
	 * Get Order
	 * retrieves the section order in the course
	 * @return [type] [description]
	 */
	public function get_order() {

		$order = get_post_meta( $this->id, '_llms_order', true );

		return $order;

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

	public function set_title( $title ) {

		return LLMS_Post_Handler::update_title( $this->id, $title );

	}

	/**
	 * Remove all associated lessons and delete section
	 * @return [type] [description]
	 */
	public function delete() {

		//remove any child lessons
		$this->remove_all_child_lessons();

		//hard delete post
		return wp_delete_post( $this->id, true );

	}

	/**
	 * Remove ALL child lessons
	 * @return [type] [description]
	 */
	public function remove_all_child_lessons() {

		//find all child lessons
		$lessons = $this->get_children_lessons();

		//if any lessons are found remove metadata that associates them with the section and course
		//remove the order as well
		if ( $lessons ) {
			foreach ( $lessons as $lesson ) {

				$this->remove_child_lesson( $lesson->ID );

			}
		}

	}

	/**
	 * Remove individual child lesson
	 * @param  [int] $lesson_id  [lesson post id]
	 * @return [bool]            [if lesson was deleted]
	 */
	public function remove_child_lesson( $lesson_id ) {

		$post_data = array(
			'parent_course' => '',
			'parent_section' => '',
			'order'	=> '',
		);

		$lesson = new LLMS_Lesson( $lesson_id );

		return $lesson->update( $post_data );

	}

	/**
	 * Get All child lessons
	 * @return [array] [array of post objects of all child lessons]
	 */
	public function get_children_lessons() {

		$args = array(
			'post_type' 		=> 'lesson',
			'posts_per_page'	=> 500,
			'meta_key'			=> '_llms_order',
			'order'				=> 'ASC',
			'orderby'			=> 'meta_value_num',
			'meta_query' 		=> array(
				array(
					'key' 		=> '_parent_section',
	      			'value' 	=> $this->id,
	      			'compare' 	=> '=',
	  			),
		  	),
		);

		$lessons = get_posts( $args );
		return $lessons;
	}

	/**
	 * Count child lessons
	 * @return [int] [number of child lessons in section]
	 */
	public function count_children_lessons() {

		$lessons = $this->get_children_lessons();
		return count( $lessons );
	}

	/**
	 * Get the next lesson order for assigning a lesson to a section
	 * @return [int] [number of child lesson plus 1]
	 */
	public function get_next_available_lesson_order() {

		return $this->count_children_lessons() + 1;

	}

	public function get_parent_course() {

		return $this->parent_course;

	}

	/**
	 * Set parent course
	 * Set's parent course in database
	 * @param [int] $meta [id course post]
	 * @return [mixed] $meta [if meta didn't exist returns the meta_id else t/f if update success]
	 * Returns False if course id is already parent
	 */
	public function set_parent_course( $course_id ) {

		$meta = update_post_meta( $this->id, '_parent_course', $course_id );

		return $meta;

	}

	/**
	 * Get percent complete
	 * Counts all lessons in section and determines percentage of completed lessons for current user.
	 * @return [int] [percent complete as whole number]
	 */
	public function get_percent_complete() {
		$lessons = $this->get_children_lessons();
		$total_lessons = $this->count_children_lessons();

		$total_completed_lessons = 0;

		foreach ($lessons as $lesson) {

			$user = new LLMS_Person;
			$user_postmetas = $user->get_user_postmeta_data( get_current_user_id(), $lesson->ID );
			if ( ! empty( $user_postmetas['_is_complete'] ) ) {
				if ( $user_postmetas['_is_complete']->meta_value === 'yes' ) {
					$total_completed_lessons++;

				}
			}
		}

		$percent_complete = ($total_lessons != 0) ? round( 100 / ( ( $total_lessons / $total_completed_lessons ) ), 0 ) : 0;

		return $percent_complete;
	}

} //end LLMS_Section
