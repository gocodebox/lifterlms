<?php
/**
 * LLMS_Controller_Awards class
 *
 * @package LifterLMS/Controllers/Classes
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * Callback controller for award posts.
 *
 * Handles actions for `llms_my_achievement` and `llms_my_certificate` post types.
 *
 * @since [version]
 */
class LLMS_Controller_Awards {

	/**
	 * List of supported post types.
	 *
	 * @var string[]
	 */
	private static $post_types = array(
		'llms_my_achievement',
		'llms_my_certificate',
	);

	/**
	 * Constructor.
	 *
	 * @since [version]

	 * @return void
	 */
	public static function init() {

		foreach ( self::$post_types as $post_type ) {

			$unprefixed = self::strip_prefix( $post_type );

			add_action( "llms_user_earned_{$unprefixed}", array( __CLASS__, 'on_earn' ), 20, 2 );
			add_action( "save_post_{$post_type}", array( __CLASS__, 'on_save' ), 20 );

		}

	}

	/**
	 * Convert post type to award type.
	 *
	 * @since [version]
	 *
	 * @param string $post_type A post type string.
	 * @return string
	 */
	private static function strip_prefix( $post_type ) {
		return llms_strip_prefixes( $post_type, array( 'llms_my_' ) );
	}

	/**
	 * Retrieves the award post model for the given WP_Post.
	 *
	 * @since [version]
	 *
	 * @param integer $post_id WP_Post ID.
	 * @return boolean|LLMS_User_Achievement|LLMS_User_Certificate Returns `false` for invalid post types.
	 *                                                             Otherwise returns the post model object.
	 */
	private static function get_object( $post_id ) {

		$post_type = get_post_type( $post_id );
		if ( ! in_array( $post_type, self::$post_types, true ) ) {
			return false;
		}

		$class_name = sprintf( 'LLMS_User_%s', ucwords( self::strip_prefix( $post_type ) ) );
		return new $class_name( $post_id );

	}

	/**
	 * Records a timestamp when the award is earned.
	 *
	 * @since [version]
	 *
	 * @param int $user_id WP_User ID of the user who earned the certificate.
	 * @param int $post_id WP_Post ID of the certificate post.
	 * @return boolean|string Returns `false` if the certificate could not be loaded, otherwise returns the current
	 *                        timestamp in MySQL format.
	 */
	public static function on_earn( $user_id, $post_id ) {

		$obj = self::get_object( $post_id );
		if ( ! $obj ) {
			return false;
		}

		$ts = llms_current_time( 'mysql' );
		$obj->set( 'awarded', $ts );

		return $ts;

	}


	/**
	 * Callback function when a post is saved or updated.
	 *
	 * This method automatically merges the certificates `post_content` and additionally triggers
	 * the creation actions to be fired if the certificate is newly created.
	 *
	 * @since [version]
	 *
	 * @param int $post_id WP_Post ID of the certificate.
	 * @return boolean Returns `true` if the certificate can't be loaded, otherwise returns `true`.
	 */
	public static function on_save( $post_id ) {

		$obj = self::get_object( $post_id );
		if ( ! $obj || 'publish' !== $obj->get( 'status' ) ) {
			return false;
		}

		$post_type = get_post_type( $post_id );

		remove_action( "save_post_{$post_type}", array( __CLASS__, 'on_save' ), 20 );

		if ( 'llms_my_certificate' === $post_type ) {
			/**
			 * Whenever an awarded certificate is updated we want to re-merge the content
			 * in the event that any shortcodes or merge codes were added.
			 */
			$obj->set( 'content', $obj->merge_content() );
		}

		/**
		 * If the award is being published for the first time, trigger the creation actions.
		 */
		if ( ! $obj->is_awarded() ) {

			if ( 'llms_my_certificate' === $post_type ) {
				$obj->update_sequential_id();
			}

			LLMS_Engagement_Handler::create_actions(
				self::strip_prefix( $post_type ),
				$obj->get_user_id(),
				$post_id,
				$obj->get( 'related' ),
				$obj->get( 'engagement' )
			);

		}

		add_action( "save_post_{$post_type}", array( __CLASS__, 'on_save' ), 20 );

		return true;

	}

}

return LLMS_Controller_Awards::init();
