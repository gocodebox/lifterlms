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
 * @since 5.3.0 Replace singleton code with `LLMS_Trait_Singleton`.
 * @since [version] Changes:
 *              - Deprecated the unused public class property `LLMS_Achievements::$content` with no replacement.
 *              - Deprecated the `LLMS_Achievements::trigger_engagement()` method.
 *                Use the {@see LLMS_Engagement_Handler::handle_achievement()} method instead.
 *              - Removed the unused private `LLMS_Achievements::$_from_address` property.
 *              - Removed the unused private `LLMS_Achievements::$_from_name` property.
 *              - Removed the unused private `LLMS_Achievements::$_content_type` property.
 *              - Removed the deprecated `LLMS_Achievements::$_instance` property.
 *
 */
class LLMS_Achievements {

	use LLMS_Trait_Singleton;

	/**
	 * List of available achievement types.
	 *
	 * @var array
	 */
	public $achievements = array();

	/**
	 * Deprecated.
	 *
	 * @deprecated [version] Unused public class property `LLMS_Achievements::$content` is deprecated with no replacement.
	 *
	 * @var null
	 */
	public $content;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
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
	 * Retrieve the default achievement image for a given achievement.
	 *
	 * @since [version]
	 *
	 * @param int $achievement_id WP_Post ID of the earned achievement. This is passed so that anyone filtering the default image could
	 *                            provide a different default image based on the achievement.
	 * @return string The full image source url.
	 */
	public function get_default_image( $achievement_id ) {

		$src = '';

		// Retrieve the stored value from the database.
		$id = $this->get_default_image_id();
		if ( $id ) {
			$src = wp_get_attachment_url( $id );
		}

		// Use the attachment stored for the option in the DB and fallback to the default image from the plugin's assets dir.
		$src = $src ? $src : llms()->engagements()->get_default_default_image_src( 'achievement' );

		/**
		 * Filters the default achievement image source.
		 *
		 * @since [version]
		 *
		 * @param string $src            The full image source url.
		 * @param int    $achievement_id The earned achievement ID.
		 */
		return apply_filters(
			'lifterlms_achievement_image_placeholder_src',
			$src,
			$achievement_id
		);
	}

	/**
	 * Retrieve attachment ID of the default achievement image.
	 *
	 * If the attachment post doesn't exist will return false. This would happen
	 * if the post is deleted from the media library.
	 *
	 * @since [version]
	 *
	 * @return int Returns the WP_Post ID of the attachment or `0` if not set.
	 */
	public function get_default_image_id() {
		$id = get_option( 'lifterlms_achievement_default_img', 0 );
		return $id && get_post( $id ) ? absint( $id ) : 0;
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
	 * @since 1.0.0
	 * @deprecated [version] `LLMS_Achievments::trigger_engagement()` is deprecated in favor of `LLMS_Engagement_Handler::handle_achievement()`.
	 *
	 * @param int $person_id       WP_User ID.
	 * @param int $achievement_id  WP_Post ID of the achievement template.
	 * @param int $related_post_id WP_Post ID of the related post, for example a lesson id.
	 * @return void
	 */
	public function trigger_engagement( $person_id, $achievement_id, $related_post_id ) {
		_deprecated_function( 'LLMS_Achievements::trigger_engagement()', '[version]', 'LLMS_Engagement_Handler::handle_achievements()' );
		LLMS_Engagement_Handler::handle_achievement( array( $person_id, $achievement_id, $related_post_id, null ) );
	}

}
