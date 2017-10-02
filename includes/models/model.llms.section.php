<?php
/**
 * LLMS Section Model
 * @since    1.0.0
 * @version  3.13.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Section extends LLMS_Post_Model {

	protected $properties = array(
		'order' => 'absint',
		'parent_course' => 'absint',
	);

	protected $db_post_type = 'section';
	protected $model_post_type = 'section';

	/**
	 * Retrieve an instance of LLMS_Course for the sections's parent course
	 * @return   obj|null
	 * @since    3.6.0
	 * @version  3.6.0
	 */
	public function get_course() {
		return llms_get_post( $this->get( 'parent_course' ) );
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
	 * Retrieve the previous section
	 * @return   obj|false
	 * @since    3.13.0
	 * @version  3.13.0
	 */
	public function get_next() {

		$siblings = $this->get_siblings( 'ids' );
		$index = array_search( $this->get( 'id' ), $siblings );

		// $index will be false if the current section isn't found (don't know why that would happen....)
		// $index will equal the length of the array if it's the last one (and there is no next)
		if ( false === $index || $index === count( $siblings ) - 1 ) {
			return false;
		}

		return llms_get_post( $siblings[ $index + 1 ] );

	}

	/**
	 * Retrieve the previous section
	 * @return   obj|false
	 * @since    3.13.0
	 * @version  3.13.0
	 */
	public function get_previous() {

		$siblings = $this->get_siblings( 'ids' );
		$index = array_search( $this->get( 'id' ), $siblings );

		// $index will be 0 if we're on the *first* section
		// $index will be false if the current section isn't found (don't know why that would happen....)
		if ( $index ) {
			return llms_get_post( $siblings[ $index - 1 ] );
		}

		return false;

	}

	/**
	 * Get all lessons in the section
	 * @param    string  $return  type of return [ids|posts|lessons]
	 * @return   array
	 * @since    3.3.0
	 * @version  3.3.0
	 */
	public function get_lessons( $return = 'lessons' ) {

		$q = new WP_Query( array(
			'meta_key' => '_llms_order',
			'meta_query' => array(
				array(
					'key' => '_llms_parent_section',
					'value' => $this->get( 'id' ),
				),
			),
			'order' => 'ASC',
			'orderby' => 'meta_value_num',
			'post_type' => 'lesson',
			'posts_per_page' => 500,
		) );

		if ( $return === 'ids' ) {
			$r = wp_list_pluck( $q->posts, 'ID' );
		} elseif ( $return === 'posts' ) {
			$r = $q->posts;
		} else {
			$r = array();
			foreach ( $q->posts as $p ) {
				$r[] = new LLMS_Lesson( $p );
			}
		}

		return $r;

	}

	/**
	 * Get sibling sections
	 * @param    string  $return  type of return [ids|posts|sections]
	 * @return   array
	 * @since    3.13.0
	 * @version  3.13.0
	 */
	public function get_siblings( $return = 'sections' ) {
		$course = $this->get_course();
		return $course->get_sections( $return );
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

		$arr['lessons'] = array();

		foreach ( $this->get_lessons() as $s ) {
			$arr['lessons'][] = $s->toArray();
		}

		return $arr;

	}




	/**
	 * Retrieve the order of the section within the course
	 * @note     developers should not use these functions, instead use generic "get"
	 *           this function will be deprecated in the future
	 * @todo     deprecate
	 * @return   int
	 * @since    1.0.0
	 * @version  3.3.0
	 */
	public function get_order() {
		return $this->get( 'order' );
	}

	/**
	 * Retrieve the post ID of the section's parent course
	 * @note     developers should not use these functions, instead use generic "get"
	 *           this function will be deprecated in the future
	 * @todo     deprecate
	 * @return   int
	 * @since    1.0.0
	 * @version  3.3.0
	 */
	public function get_parent_course() {
		return $this->get( 'parent_course' );
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
					'key' 		=> '_llms_parent_section',
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



	/**
	 * Set parent course
	 * Set's parent course in database
	 * @param [int] $meta [id course post]
	 * @return [mixed] $meta [if meta didn't exist returns the meta_id else t/f if update success]
	 * Returns False if course id is already parent
	 */
	public function set_parent_course( $course_id ) {

		$meta = update_post_meta( $this->id, '_llms_parent_course', $course_id );

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

		foreach ( $lessons as $lesson ) {

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
