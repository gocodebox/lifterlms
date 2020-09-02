<?php
/**
 * Achievements Base Class
 *
 * @package LifterLMS/Classes/Achievements
 *
 * @since 1.0.0
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * Main Achievements singleton
 *
 * @see llms()->achievements()
 *
 * @since 1.0.0
 * @since 3.24.0 Unknown.
 * @since [version] Added extends from `LLMS_Abstract_Engagement`.
 *              Added the `LLMS_Trait_Singleton`.
 */
class LLMS_Achievements extends LLMS_Abstract_Engagement {

	use LLMS_Trait_Singleton;

	/**
	 * Array of achievement types
	 *
	 * @var array
	 */
	public $achievements = array();

	/**
	 * Deprecated.
	 *
	 * @deprecated [version]
	 *
	 * @var null
	 */
	public $content;

	/**
	 * Includes achievement class
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
	 * Retrieves an instance of an engagement subclass identified by $type
	 *
	 * @since [version]
	 *
	 * @param string $type Engagement subclass type. This is an optional forward compatible variable as LifterLMS only supports "user" achievements.
	 * @return LLMS_Achievement_User
	 */
	protected function get_sub_class( $type = '' ) {
		return $this->achievements['LLMS_Achievement_User'];
	}

	/**
	 * Return the engagement type
	 *
	 * The core engagements types are "achievement" and "certificate".
	 *
	 * @since [version]
	 *
	 * @return string
	 */
	protected function get_type() {
		return 'achievement';
	}

	/**
	 * Get a list of achievement Achievement Template IDs for a given post
	 *
	 * @param   array|int $post_ids         Post IDs or single post ID to look for achievements by.
	 * @param   bool      $include_children If true, will include course children (sections, lessons, and quizzes).
	 * @return  array
	 * @since   3.24.0
	 * @version 3.24.0
	 */
	public function get_achievements_by_post( $post_ids, $include_children = true ) {

		if ( ! is_array( $post_ids ) ) {
			$post_ids = array( $post_ids );
		}

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

		$query = new WP_Query(
			array(
				'post_type'  => 'llms_engagement',
				'meta_query' => array(
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
			)
		);

		$achievements = array();

		foreach ( $query->posts as $engagement ) {
			$achievements[] = get_post_meta( $engagement->ID, '_llms_engagement', true );
		}

		return $achievements;

	}

}
