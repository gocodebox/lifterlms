<?php
/**
 * LLMS_User_Achievement model
 *
 * @package LifterLMS/Models/Classes
 *
 * @since 3.8.0
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * Achievements earned by a student.
 *
 * @since 3.8.0
 * @since [version] Utilize `LLMS_Abstract_User_Engagement` abstract.
 *
 * @property int    $author               WP_User ID of the user who the achievement belongs to.
 * @property int    $achievement_template WP_Post ID of the template `llms_achievement` post.
 * @property string $content              The achievement content.
 * @property int    $engagement           WP_Post ID of the `llms_engagement` post used to trigger the achievement.
 *                                        An empty value or `0` indicates the achievement was awarded manually or
 *                                        before the engagement value was stored.
 * @property int    $related              WP_Post ID of the related post.
 * @property string $title                Achievement title.
 */
class LLMS_User_Achievement extends LLMS_Abstract_User_Engagement {

	/**
	 * Database (WP) post type name
	 *
	 * @var string
	 */
	protected $db_post_type = 'llms_my_achievement';

	/**
	 * Post type model name
	 *
	 * @var string
	 */
	protected $model_post_type = 'achievement';

	/**
	 * Object properties
	 *
	 * @var array
	 */
	protected $properties = array(
		'achievement_template' => 'absint',
		'engagement'           => 'absint',
		'related'              => 'absint',
	);

	/**
	 * Retrieve the HTML <img> for the achievement
	 *
	 * @param    array $size  dimensions of the image to return (width x height)
	 * @return   string
	 * @since    3.14.0
	 * @version  3.14.0
	 */
	public function get_image_html( $size = array() ) {

		return apply_filters(
			'llms_achievement_get_image_html',
			sprintf( '<img alt="%1$s" class="llms-achievement-img" src="%2$s">', esc_attr( $this->get( 'title' ) ), $this->get_image( $size ) ),
			$this
		);

	}

	/**
	 * Retrieve the image source for the achievement
	 *
	 * @param    array $size  dimensions of the image to return (width x height)
	 * @return   string
	 * @since    3.14.0
	 * @version  3.14.0
	 */
	public function get_image( $size = array(), $key = 'achievement_image' ) {

		if ( ! $size ) {
			$size = apply_filters( 'llms_achievement_image_default_size', array( 300, 300 ) );
		}

		if ( ! $this->get( 'achievement_image' ) ) {
			$src = LLMS()->plugin_url() . '/assets/images/optional_achievement.png';
		} else {
			$src = parent::get_image( $size, $key );
		}

		return apply_filters( 'llms_achievement_get_image', $src, $this );

	}

}
