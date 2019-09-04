<?php
/**
 * LLMS Section Model
 *
 * @since    1.0.0
 * @version  3.24.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Section model.
 */
class LLMS_Section extends LLMS_Post_Model {

	protected $properties = array(
		'order'         => 'absint',
		'parent_course' => 'absint',
	);

	protected $db_post_type    = 'section';
	protected $model_post_type = 'section';

	/**
	 * Retrieve the total number of elements in the section
	 *
	 * @return   int
	 * @since    3.16.0
	 * @version  3.16.0
	 */
	public function count_elements() {
		return count( $this->get_lessons( 'ids' ) );
	}

	/**
	 * Retrieve an instance of LLMS_Course for the sections's parent course
	 *
	 * @return   LLMS_Course|null|false LLMS_Course,
	 *                                  null if WP get_post() fails,
	 *                                  false if LLMS_Course class isn't found
	 * @since    3.6.0
	 * @version  3.6.0
	 */
	public function get_course() {
		return llms_get_post( $this->get( 'parent_course' ) );
	}

	/**
	 * An array of default arguments to pass to $this->create()
	 * when creating a new post
	 *
	 * @param    array $args   args of data to be passed to wp_insert_post
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

		$args = wp_parse_args(
			$args,
			array(
				'comment_status' => 'closed',
				'ping_status'    => 'closed',
				'post_author'    => get_current_user_id(),
				'post_content'   => '',
				'post_excerpt'   => '',
				'post_status'    => 'publish',
				'post_title'     => '',
				'post_type'      => $this->get( 'db_post_type' ),
			)
		);

		return apply_filters( 'llms_' . $this->model_post_type . '_get_creation_args', $args, $this );

	}

	/**
	 * Retrieve the previous section
	 *
	 * @return   LLMS_Section|false
	 * @since    3.13.0
	 * @version  3.24.0
	 */
	public function get_next() {

		$siblings = $this->get_siblings( 'ids' );
		$index    = array_search( $this->get( 'id' ), $siblings );

		// $index will be false if the current section isn't found (don't know why that would happen....)
		// $index will equal the length of the array if it's the last one (and there is no next)
		if ( false === $index || count( $siblings ) - 1 === $index ) {
			return false;
		}

		return llms_get_post( $siblings[ $index + 1 ] );

	}

	/**
	 * Retrieve section completion percentage
	 *
	 * @uses     LLMS_Student::get_progress()
	 * @param    string $user_id    WP_User ID, if none supplied users current user (if exists)
	 * @param    bool   $use_cache  when true, uses results from from the wp object cache (if available)
	 * @return   float
	 * @since    3.24.0
	 * @version  3.24.0
	 */
	public function get_percent_complete( $user_id = '', $use_cache = true ) {

		$student = llms_get_student( $user_id );
		if ( ! $student ) {
			/** This filter is documented in includes/models/model.llms.student.php */
			return apply_filters( 'llms_student_get_progress', 0, $this->get( 'id' ), 'section', $user_id );
		}
		return $student->get_progress( $this->get( 'id' ), 'section', $use_cache );

	}

	/**
	 * Retrieve the previous section
	 *
	 * @return   LLMS_Section|false
	 * @since    3.13.0
	 * @version  3.13.0
	 */
	public function get_previous() {

		$siblings = $this->get_siblings( 'ids' );
		$index    = array_search( $this->get( 'id' ), $siblings );

		// $index will be 0 if we're on the *first* section
		// $index will be false if the current section isn't found (don't know why that would happen....)
		if ( $index ) {
			return llms_get_post( $siblings[ $index - 1 ] );
		}

		return false;

	}

	/**
	 * Get all lessons in the section
	 *
	 * @param    string $return  type of return [ids|posts|lessons]
	 * @return   int[]|WP_Post[]|LLMS_Lesson[] type depends on value of $return
	 * @since    3.3.0
	 * @version  3.24.0
	 */
	public function get_lessons( $return = 'lessons' ) {

		$query = new WP_Query(
			array(
				'meta_key'       => '_llms_order',
				'meta_query'     => array(
					array(
						'key'   => '_llms_parent_section',
						'value' => $this->get( 'id' ),
					),
				),
				'order'          => 'ASC',
				'orderby'        => 'meta_value_num',
				'post_type'      => 'lesson',
				'posts_per_page' => 500,
			)
		);

		if ( 'ids' === $return ) {
			$ret = wp_list_pluck( $query->posts, 'ID' );
		} elseif ( 'posts' === $return ) {
			$ret = $query->posts;
		} else {
			$ret = array_map( 'llms_get_post', $query->posts );
		}

		return $ret;

	}

	/**
	 * Get sibling sections
	 *
	 * @param    string $return  type of return [ids|posts|sections]
	 * @return   int[]|WP_Post[]|LLMS_Section[] type depends on value of $return
	 * @since    3.13.0
	 * @version  3.13.0
	 */
	public function get_siblings( $return = 'sections' ) {
		$course = $this->get_course();
		return $course->get_sections( $return );
	}

	/**
	 * Add data to the course model when converted to array
	 * Called before data is sorted and returned by $this->jsonSerialize()
	 *
	 * @param    array $arr   data to be serialized
	 * @return   array
	 * @since    3.3.0
	 * @version  3.24.0
	 */
	public function toArrayAfter( $arr ) {

		$arr['lessons'] = array();

		foreach ( $this->get_lessons() as $lesson ) {
			$arr['lessons'][] = $lesson->toArray();
		}

		return $arr;

	}

	/*
		 /$$
		| $$
		| $$  /$$$$$$   /$$$$$$   /$$$$$$   /$$$$$$$ /$$   /$$
		| $$ /$$__  $$ /$$__  $$ |____  $$ /$$_____/| $$  | $$
		| $$| $$$$$$$$| $$  \ $$  /$$$$$$$| $$      | $$  | $$
		| $$| $$_____/| $$  | $$ /$$__  $$| $$      | $$  | $$
		| $$|  $$$$$$$|  $$$$$$$|  $$$$$$$|  $$$$$$$|  $$$$$$$
		|__/ \_______/ \____  $$ \_______/ \_______/ \____  $$
					   /$$  \ $$                     /$$  | $$
					  |  $$$$$$/                    |  $$$$$$/
					   \______/                      \______/
	 *
	 * Legacy functions below here will be deprecated in future versions
	 * Currently not being used by the LifterLMS core and are scheduled for cleanup and removal
	 * @todo    cleanup
	 */

	/**
	 * Retrieve the order of the section within the course
	 *
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
	 *
	 * @todo     deprecate
	 * @return   int
	 * @since    1.0.0
	 * @version  3.3.0
	 */
	public function get_parent_course() {
		return $this->get( 'parent_course' );
	}

	/**
	 * @todo     deprecate
	 */
	public function update( $data ) {

		$updated_values = array();

		foreach ( $data as $key => $value ) {
			$method = 'set_' . $key;

			if ( method_exists( $this, $method ) ) {
				$updated_value = $this->$method( $value );

				$updated_values[ $key ] = $updated_value;
			}
		}

		return $updated_values;

	}

	/**
	 * Set parent section
	 * Set's parent section in database
	 *
	 * @param [int] $meta [id section post]
	 * @return int|bool if meta didn't exist returns the meta_id else t/f if update success
	 * Returns False if section id is already parent
	 * @todo     deprecate
	 */
	public function set_order( $order ) {

		return update_post_meta( $this->id, '_llms_order', $order );

	}

	public function set_title( $title ) {

		return LLMS_Post_Handler::update_title( $this->id, $title );

	}

	/**
	 * Remove all associated lessons and delete section
	 *
	 * @return WP_Post|false|null
	 * @todo     deprecate
	 */
	public function delete() {

		// remove any child lessons
		$this->remove_all_child_lessons();

		// hard delete post
		return wp_delete_post( $this->id, true );

	}

	/**
	 * Remove ALL child lessons
	 *
	 * @return void
	 * @todo     deprecate
	 */
	public function remove_all_child_lessons() {

		// find all child lessons
		$lessons = $this->get_children_lessons();

		// if any lessons are found remove metadata that associates them with the section and course
		// remove the order as well
		if ( $lessons ) {
			foreach ( $lessons as $lesson ) {

				$this->remove_child_lesson( $lesson->ID );

			}
		}

	}

	/**
	 * Remove individual child lesson
	 *
	 * @param  int $lesson_id  lesson post id
	 * @return [bool]            [if lesson was deleted]
	 * @todo     deprecate
	 */
	public function remove_child_lesson( $lesson_id ) {

		$post_data = array(
			'parent_course'  => '',
			'parent_section' => '',
			'order'          => '',
		);

		$lesson = new LLMS_Lesson( $lesson_id );

		return $lesson->update( $post_data );

	}



	/**
	 * Count child lessons
	 *
	 * @return int number of child lessons in section
	 * @todo     deprecate
	 */
	public function count_children_lessons() {

		$lessons = $this->get_children_lessons();
		return count( $lessons );
	}

	/**
	 * Get the next lesson order for assigning a lesson to a section
	 *
	 * @return int number of child lesson plus 1
	 * @todo     deprecate
	 */
	public function get_next_available_lesson_order() {

		return $this->count_children_lessons() + 1;

	}



	/**
	 * Set parent course
	 * Set's parent course in database
	 *
	 * @param int $course_id id of course post
	 * @return int|bool if meta didn't exist returns the meta_id else t/f if update success
	 * Returns False if course id is already parent
	 * @todo     deprecate
	 */
	public function set_parent_course( $course_id ) {

		$meta = update_post_meta( $this->id, '_llms_parent_course', $course_id );
		return $meta;

	}

	/*
			   /$$                                                               /$$                     /$$
			  | $$                                                              | $$                    | $$
		  /$$$$$$$  /$$$$$$   /$$$$$$   /$$$$$$   /$$$$$$   /$$$$$$$  /$$$$$$  /$$$$$$    /$$$$$$   /$$$$$$$
		 /$$__  $$ /$$__  $$ /$$__  $$ /$$__  $$ /$$__  $$ /$$_____/ |____  $$|_  $$_/   /$$__  $$ /$$__  $$
		| $$  | $$| $$$$$$$$| $$  \ $$| $$  \__/| $$$$$$$$| $$        /$$$$$$$  | $$    | $$$$$$$$| $$  | $$
		| $$  | $$| $$_____/| $$  | $$| $$      | $$_____/| $$       /$$__  $$  | $$ /$$| $$_____/| $$  | $$
		|  $$$$$$$|  $$$$$$$| $$$$$$$/| $$      |  $$$$$$$|  $$$$$$$|  $$$$$$$  |  $$$$/|  $$$$$$$|  $$$$$$$
		 \_______/ \_______/| $$____/ |__/       \_______/ \_______/ \_______/   \___/   \_______/ \_______/
							| $$
							| $$
							|__/
	*/

	/**
	 * Get All child lessons
	 *
	 * @return      WP_Post[] [array of post objects of all child lessons]
	 * @since       1.0.0
	 * @version     3.24.0
	 * @deprecated  3.24.0
	 */
	public function get_children_lessons() {

		llms_deprecated_function( 'LLMS_Section->get_children_lessons()', '[version]', 'LLMS_Section->get_lessons( "posts" )' );
		return $this->get_lessons( 'posts' );

	}

}
