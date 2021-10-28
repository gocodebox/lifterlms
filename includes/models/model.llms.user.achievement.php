<?php
/**
 * LifterLMS User Achievement
 *
 * @package LifterLMS/Models/Classes
 *
 * @since 3.8.0
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_User_Achievement model.
 *
 * @since 3.8.0
 * @since [version] Utilize `LLMS_Abstract_User_Engagement` abstract.
 *              Declare `achievement_title` and `achievement_content` properties.
 */
class LLMS_User_Achievement extends LLMS_Abstract_User_Engagement {

	protected $db_post_type    = 'llms_my_achievement';
	protected $model_post_type = 'achievement';

	protected $properties = array(
		'achievement_title'    => 'string',
		'achievement_image'    => 'absint',
		'achievement_content'  => 'html',
		'achievement_template' => 'absint',
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
