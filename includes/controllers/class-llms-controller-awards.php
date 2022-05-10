<?php
/**
 * LLMS_Controller_Awards class
 *
 * @package LifterLMS/Controllers/Classes
 *
 * @since 6.0.0
 * @version 6.4.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Callback controller for award posts.
 *
 * Handles actions for `llms_my_achievement` and `llms_my_certificate` post types.
 *
 * @since 6.0.0
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
	 * @since 6.0.0

	 * @return void
	 */
	public static function init() {

		foreach ( self::$post_types as $post_type ) {

			$unprefixed = self::strip_prefix( $post_type );

			add_action( "llms_user_earned_{$unprefixed}", array( __CLASS__, 'on_earn' ), 20, 2 );
			add_action( "save_post_{$post_type}", array( __CLASS__, 'on_save' ), 20 );

		}

		add_action( 'rest_after_insert_llms_my_certificate', array( __CLASS__, 'on_rest_insert' ), 20, 3 );

	}

	/**
	 * Convert post type to award type.
	 *
	 * @since 6.0.0
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
	 * @since 6.0.0
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
	 * @since 6.0.0
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
	 * Awarded certificate REST API insertion callback.
	 *
	 * Automatically syncs an awarded certificate with its parent when inserted via the REST API and
	 * sets a unique post name (slug).
	 *
	 * This method relies on the fact that there is (currently) no native way to insert an awarded
	 * certificate into the database via the REST API with a linked parent template without using the
	 * `AwardCertificateButton` Javascript component. The component sets the parent and student and allows
	 * this callback function to perform the remaining (necessary) sync operations.
	 *
	 * @since 6.0.0
	 *
	 * @param stdClass        $post     The post object.
	 * @param WP_Rest_Request $request  Rest request object.
	 * @param boolean         $creating Whether or not the post is being created.
	 * @return integer Returns an integer, primarily for unit tests: `0` if the insertion is an update,
	 *                 `1` if the post has not parent, and `2` when the certificate is synced and updated.
	 */
	public static function on_rest_insert( $post, $request, $creating ) {

		if ( ! $creating ) {
			return 0;
		}

		$cert = self::get_object( $post->ID );
		if ( ! $cert->get( 'parent' ) ) {
			return 1;
		}

		add_filter( 'llms_certificate_merge_data', array( __CLASS__, 'on_rest_insert_merge_data' ) );

		$cert->sync( 'create' );
		$cert->set( 'name', llms()->certificates()->get_unique_slug( $cert->get( 'title' ) ) );

		remove_filter( 'llms_certificate_merge_data', array( __CLASS__, 'on_rest_insert_merge_data' ) );

		return 2;

	}

	/**
	 * Modifies the merge data used when awarded a certificate using the REST API.
	 *
	 * This removes the `{sequential_id}` merge data. When creating the draft we don't want to use
	 * the default `1` or whatever the parent's ID is. If we use `1` that's just generally incorrect and
	 * if we use the parent's ID it might become the wrong ID by the time the certificate is published / awarded.
	 *
	 * Removing this will not merge the ID but on awarding the ID will be automatically merged with other merge codes.
	 *
	 * @since 6.0.0
	 *
	 * @param array $merge_data Merge data.
	 * @return array
	 */
	public static function on_rest_insert_merge_data( $merge_data ) {
		unset( $merge_data['{sequential_id}'] );
		return $merge_data;
	}

	/**
	 * Callback function when a post is saved or updated.
	 *
	 * This method automatically merges the certificates `post_content` and additionally triggers
	 * the creation actions to be fired if the certificate is newly created.
	 *
	 * @since 6.0.0
	 * @since 6.4.0 Added replacement of references to reusable blocks with their actual blocks.
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

		$is_awarded = $obj->is_awarded();

		if ( 'llms_my_certificate' === $post_type ) {

			if ( ! $is_awarded ) {
				$obj->update_sequential_id();
			}

			/**
			 * Whenever an awarded certificate is updated, we want to re-merge the content
			 * in the event that any shortcodes or merge codes were added.
			 */
			$content = $obj->get( 'content', true );
			$obj->set( 'content', $obj->merge_content( $content, true ) );
		}

		/**
		 * If the award is being published for the first time, trigger the creation actions.
		 */
		if ( ! $obj->is_awarded() ) {

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
