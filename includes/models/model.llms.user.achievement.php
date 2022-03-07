<?php
/**
 * LLMS_User_Achievement model
 *
 * @package LifterLMS/Models/Classes
 *
 * @since 3.8.0
 * @version 6.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * An achievement awarded to a student.
 *
 * @since 3.8.0
 * @since 6.0.0 Utilize `LLMS_Abstract_User_Engagement` abstract.
 *
 * @property int    $author     WP_User ID of the user who the achievement belongs to.
 * @property string $awarded    MySQL timestamp recorded when the achievement was first awarded.
 * @property string $content    The achievement content.
 * @property int    $engagement WP_Post ID of the `llms_engagement` post used to trigger the achievement.
 *                              An empty value or `0` indicates the achievement was awarded manually or
 *                              before the engagement value was stored.
 * @property int    $parent     WP_Post ID of the template `llms_achievement` post.
 * @property int    $related    WP_Post ID of the related post.
 * @property string $title      Achievement title.
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
		'awarded'    => 'string',
		'engagement' => 'absint',
		'related'    => 'absint',
	);

	/**
	 * Retrieve the image source for the achievement.
	 *
	 * @since 3.14.0
	 * @since 6.0.0 Set a default size when an empty array is passed and use global default image when possible.
	 *
	 * @param int[] $size   Dimensions of the image to return passed as [ width, height ] (in pixels).
	 * @param null  $unused Unused parameter inherited from the parent method.
	 * @return string Image source URL.
	 */
	public function get_image( $size = array(), $unused = null ) {

		$id     = $this->get( 'id' );
		$img_id = get_post_thumbnail_id( $id );

		$size = empty( $size ) ? array( 380, 380 ) : $size;

		/**
		 * Filters the size used to retrieve an achievement image.
		 *
		 * @since 6.0.0
		 *
		 * @param int[] $size Dimensions of the image passed as [ width, height ] (in pixels).
		 */
		$size = apply_filters( 'llms_achievement_image_size', $size );

		if ( ! $img_id ) {
			$src = llms()->achievements()->get_default_image( $id );
		} else {
			list( $src ) = wp_get_attachment_image_src( $img_id, $size );
		}

		/**
		 * Filter the image source URL for the achievement.
		 *
		 * @since 6.0.0
		 *
		 * @param string                $src         Image source URL.
		 * @param LLMS_User_Achievement $achievement The achievement object.
		 * @param int[]                 $size        Dimensions of the image to return passed as [ width, height ] (in pixels).
		 */
		return apply_filters( 'llms_achievement_get_image', $src, $this, $size );

	}

	/**
	 * Retrieve the HTML <img> for the achievement.
	 *
	 * @since 3.14.0
	 *
	 * @param array $size Dimensions of the image to return passed as [ width, height ] (in pixels).
	 * @return string
	 */
	public function get_image_html( $size = array() ) {

		/**
		 * Filters the HTML used to display an achievement image.
		 *
		 * @since 3.14.0
		 * @since 6.0.0 Added `$size` parameter.
		 *
		 * @param string                $html        Image HTML.
		 * @param LLMS_User_Achievement $achievement The achievement object.
		 * @param int[]                 $size        Dimensions of the image to return passed as [ width, height ] (in pixels).
		 */
		return apply_filters(
			'llms_achievement_get_image_html',
			sprintf(
				'<img alt="%1$s" class="llms-achievement-img" src="%2$s">',
				esc_attr( $this->get( 'title' ) ),
				$this->get_image( $size )
			),
			$this,
			$size
		);

	}

}
