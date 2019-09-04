<?php
/**
 * Achievements Base Class
 *
 * @package  LifterLMS/Classes/Achievements
 * @since    1.0.0
 * @version  3.24.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Main Achievements singleton
 * Accessible via LLMS()->achievements()
 */
class LLMS_Achievements {

	public $achievements;

	public $content;

	private $_from_address;

	private $_from_name;

	private $_content_type;

	protected static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self(); }
		return self::$_instance;
	}

	/**
	 * Constructor
	 *
	 * @since    1.0.0
	 * @version  3.24.0
	 */
	private function __construct() {

		$this->init();

	}

	/**
	 * Includes achievement class
	 *
	 * @return void
	 * @since    1.0.0
	 * @version  ??
	 */
	public function init() {

		include_once 'class.llms.achievement.php';
		$this->achievements['LLMS_Achievement_User'] = include_once 'achievements/class.llms.achievement.user.php';

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

	/**
	 * Award an achievement to a user
	 * Calls trigger method passing arguments
	 *
	 * @param  int $person_id        [ID of the current user]
	 * @param  int $achievement      [Achievement template post ID]
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
