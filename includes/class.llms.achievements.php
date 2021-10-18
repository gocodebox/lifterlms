<?php
/**
 * Achievements Base Class
 *
 * @package LifterLMS/Classes/Achievements
 *
 * @since 1.0.0
 * @version 5.3.3
 */

defined( 'ABSPATH' ) || exit;

/**
 * Main Achievements singleton
 *
 * @see LLMS()->achievements()
 *
 * @since 1.0.0
 * @since 3.24.0 Unknown.
 * @since 5.3.0 Replace singleton code with `LLMS_Trait_Singleton`.
 */
class LLMS_Achievements {

	use LLMS_Trait_Singleton;

	public $achievements;

	public $content;

	private $_from_address;

	private $_from_name;

	private $_content_type;

	/**
	 * Singleton instance.
	 *
	 * @deprecated 5.3.0 Use {@see LLMS_Trait_Singleton::instance()}.
	 *
	 * @var LLMS_Achievements
	 */
	protected static $_instance = null;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @since 3.24.0 Unknown.
	 *
	 * @return void
	 */
	private function __construct() {

		$this->init();

	}

	/**
	 * Includes achievement class.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function init() {

		include_once 'class.llms.achievement.php';
		$this->achievements['LLMS_Achievement_User'] = include_once 'achievements/class.llms.achievement.user.php';

	}

	/**
	 * Get a list of achievement Achievement Template IDs for a given post.
	 *
	 * @since 3.24.0
	 * @since 5.3.3 Set the query limit to 500.
	 *
	 * @param array|int $post_ids         Post IDs or single post ID to look for achievements by.
	 * @param bool      $include_children If true, will include course children (sections, lessons, and quizzes).
	 * @return array
	 */
	public function get_achievements_by_post( $post_ids, $include_children = true ) {

		if ( ! is_array( $post_ids ) ) {
			$post_ids = array( $post_ids );
		}

		$original_post_ids = $post_ids;

		if ( $include_children ) {

			foreach ( $post_ids as $post_id ) {
				if ( 'course' === get_post_type( $post_id ) ) {
					$course   = llms_get_post( $post_id );
					$post_ids = array_merge(
						$post_ids,
						$course->get_sections( 'ids' ),
						$course->get_lessons( 'ids' ),
						$course->get_quizzes()
					);
				}
			}
		}

		/**
		 * Filters the query args to retrieve the achievements by post.
		 *
		 * @since 5.3.3
		 *
		 * @param array     $args              The query args to retrieve the achievements by post.
		 * @param array|int $post_ids          Post IDs or single post ID to look for achievements by.
		 * @param bool      $include_children  If true, will include course children (sections, lessons, and quizzes).
		 */
		$query_args = apply_filters(
			'llms_achievements_by_post_query_args',
			array(
				'post_type'      => 'llms_engagement',
				'meta_query'     => array(
					array(
						'key'   => '_llms_engagement_type',
						'value' => 'achievement',
					),
					array(
						'compare' => 'IN',
						'key'     => '_llms_engagement_trigger_post',
						'value'   => $post_ids,
					),
				),
				'posts_per_page' => 500,
			),
			$original_post_ids,
			$include_children
		);

		$query = new WP_Query( $query_args );

		$achievements = array();

		foreach ( $query->posts as $engagement ) {
			$achievements[] = get_post_meta( $engagement->ID, '_llms_engagement', true );
		}

		return $achievements;

	}

	/**
	 * Award an achievement to a user
	 *
	 * Calls trigger method passing arguments.
	 *
	 * @param  int $person_id        [ID of the current user]
	 * @param  int $achievement_id   [Achievement template post ID]
	 * @param  int $related_post_id  Post ID of the related engagement (eg lesson id)
	 * @return void
	 * @since    ??
	 * @version  ??
	 */
	public function trigger_engagement( $person_id, $achievement_id, $related_post_id ) {
		$achievement = $this->achievements['LLMS_Achievement_User'];
		$achievement->trigger( $person_id, $achievement_id, $related_post_id );
	}

}
