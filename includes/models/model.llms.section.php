<?php
/**
 * LLMS Section Model
 *
 * @package LifterLMS/Models/Classes
 *
 * @since 1.0.0
 * @version 6.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Section model class
 *
 * @since 1.0.0
 * @since 4.0.0 Remove deprecated class methods.
 * @since 5.7.0 Informed developers about the deprecated `LLMS_Section::get_next_available_lesson_order()` method.
 *              Informed developers about the deprecated `LLMS_Section::get_order()` method.
 *              Informed developers about the deprecated `LLMS_Section::get_parent_course()` method.
 *              Informed developers about the deprecated `LLMS_Section::set_parent_course()` method.
 * @since 6.0.0 Removed deprecated items.
 *              - `LLMS_Section::get_next_available_lesson_order()` method
 *              - `LLMS_Section::get_order()` method
 *              - `LLMS_Section::get_parent_course()` method
 *              - `LLMS_Section::set_parent_course()` method
 *
 * @property int    $order         The section's order within its parent course.
 * @property int    $parent_course The WP_Post ID of the section's parent course.
 * @property string $title         The title / display name of the section.
 */
class LLMS_Section extends LLMS_Post_Model {

	/**
	 * Post model properties.
	 *
	 * @var array
	 */
	protected $properties = array(
		'order'         => 'absint',
		'parent_course' => 'absint',
	);

	/**
	 * Database post type name.
	 *
	 * @var string
	 */
	protected $db_post_type = 'section';

	/**
	 * Model post type name.
	 *
	 * @var string
	 */
	protected $model_post_type = 'section';

	/**
	 * Retrieve the total number of elements in the section
	 *
	 * @since 3.16.0
	 *
	 * @return int
	 */
	public function count_elements() {
		return count( $this->get_lessons( 'ids' ) );
	}

	/**
	 * Retrieve an instance of LLMS_Course for the sections's parent course
	 *
	 * @since 3.6.0
	 *
	 * @return LLMS_Course|null|false Course object, `null` if `get_post()` fails, or `false` if LLMS_Course class isn't found.
	 */
	public function get_course() {
		return llms_get_post( $this->get( 'parent_course' ) );
	}

	/**
	 * An array of default arguments to pass to $this->create() when creating a new section
	 *
	 * @since 3.13.0
	 *
	 * @param array $args Data to be passed to `wp_insert_post()`.
	 * @return array
	 */
	protected function get_creation_args( $args = null ) {

		// Allow nothing to be passed in.
		if ( empty( $args ) ) {
			$args = array();
		}

		// Backwards compat to original 3.0.0 format when just a title was passed in.
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

		/**
		 * Filter arguments used to create a new section post
		 *
		 * @since 4.11.0
		 *
		 * @param array        $args    Data to be passed to `wp_insert_post()`.
		 * @param LLMS_Section $section Instance of the section object.
		 */
		return apply_filters( 'llms_section_get_creation_args', $args, $this );

	}

	/**
	 * Retrieve the previous section
	 *
	 * @since 3.13.0
	 * @since 3.24.0 Unknown.
	 *
	 * @return LLMS_Section|false
	 */
	public function get_next() {

		$siblings = $this->get_siblings( 'ids' );
		$index    = array_search( $this->get( 'id' ), $siblings );

		/**
		 * The `$index` var will be false if the current section isn't found and
		 * will equal the length of the array if it's the last one (and there is no next).
		 */
		if ( false === $index || count( $siblings ) - 1 === $index ) {
			return false;
		}

		return llms_get_post( $siblings[ $index + 1 ] );

	}

	/**
	 * Retrieve section completion percentage
	 *
	 * @since 3.24.0
	 *
	 * @see LLMS_Student::get_progress()
	 *
	 * @param string $user_id   Optional. WP_User ID, if none supplied uses current user (if exists). Default is empty string.
	 * @param bool   $use_cache Optional. When true, uses results from from the wp object cache (if available). Default is `false`.
	 * @return float
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
	 * @since 3.13.0
	 *
	 * @return LLMS_Section|false
	 */
	public function get_previous() {

		$siblings = $this->get_siblings( 'ids' );
		$index    = array_search( $this->get( 'id' ), $siblings );

		/**
		 * The `$index` var will be `0` if we're on the first section and
		 * will be `false` if the current section isn't found.
		 */
		if ( $index ) {
			return llms_get_post( $siblings[ $index - 1 ] );
		}

		return false;

	}

	/**
	 * Get all lessons in the section
	 *
	 * @since 3.3.0
	 * @since 3.24.0 Unknown.
	 *
	 * @param string $return Optional. Type of return [ids|posts|lessons]. Default is `lessons`.
	 * @return int[]|WP_Post[]|LLMS_Lesson[] Return ty depends on value of `$return` argument.
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
	 * @since 3.13.0
	 *
	 * @param string $return Optional. Type of return [ids|posts|sections]. Default is `sections`.
	 * @return int[]|WP_Post[]|LLMS_Section[] Return type depends on value of `$return` argument.
	 */
	public function get_siblings( $return = 'sections' ) {
		$course = $this->get_course();
		return $course->get_sections( $return );
	}

	/**
	 * Add data to the course model when converted to array
	 *
	 * Called before data is sorted and returned by $this->jsonSerialize().
	 *
	 * @since 3.3.0
	 * @since 3.24.0 Unknown.
	 *
	 * @param array $arr Data to be serialized.
	 * @return array
	 */
	public function toArrayAfter( $arr ) {

		$arr['lessons'] = array();

		foreach ( $this->get_lessons() as $lesson ) {
			$arr['lessons'][] = $lesson->toArray();
		}

		return $arr;

	}

}
